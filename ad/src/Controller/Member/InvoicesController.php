<?php

namespace App\Controller\Member;

use App\Controller\Member\AppMemberController;
use Cake\Routing\Router;
use Cake\Network\Exception\NotFoundException;

class InvoicesController extends AppMemberController
{
    public function index()
    {
        $query = $this->Invoices->find()->where(['user_id' => $this->Auth->user('id')]);
        $invoices = $this->paginate($query);

        $this->set('invoices', $invoices);
    }

    public function view($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid Invoice'));
        }

        $invoice = $this->Invoices->findById($id)->where(['user_id' => $this->Auth->user('id')])->first();
        if (!$invoice) {
            throw new NotFoundException(__('Invalid Invoice'));
        }
        $this->set('invoice', $invoice);
    }

    public function checkout()
    {
        $this->autoRender = false;

        $this->response->type('json');

        if (!$this->request->is('ajax')) {
            $content = [
                'status' => 'error',
                'message' => __('Bad Request.'),
                'form' => ''
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        $user = $this->Invoices->Users->find()->contain(['Plans'])
            ->where(['Users.id' => $this->Auth->user('id')])->first();

        $invoice = $this->Invoices->findById($this->request->data['id'])->first();

        if ('wallet' == $this->request->data['payment_method']) {
            if ($invoice->amount > $user->wallet_money) {
                $content = [
                    'status' => 'error',
                    'message' => __("You don't have enough money in your wallet.")
                ];
                $this->response->body(json_encode($content));
                return $this->response;
            }

            $invoice->payment_method = 'wallet';
            $invoice->status = 1;
            $invoice->paid_date = date("Y-m-d H:i:s");
            $this->Invoices->save($invoice);

            $user->wallet_money -= $invoice->amount;
            if ($invoice->type === 1) {
                $payment_period = unserialize($invoice->data)['payment_period'];
                $expiration = (new \Cake\I18n\Time($user->expiration))->addYear();
                if ($payment_period === 'm') {
                    $expiration = (new \Cake\I18n\Time($user->expiration))->addMonth();
                }
                $user->expiration = $expiration;
                $user->plan_id = $invoice->rel_id;
            }
            $this->Invoices->Users->save($user);

            if ($this->Auth->user('id') === $user->id) {
                $data = $user->toArray();
                unset($data['password']);

                $this->Auth->setUser($data);
            }

            if ($invoice->type === 2) {
                $this->loadModel('Campaigns');
                $campaign = $this->Campaigns->findById($invoice->rel_id)
                    ->where(['user_id' => $this->Auth->user('id')])
                    ->first();

                if (!$campaign) {
                    $content = [
                        'status' => 'error',
                        'message' => __('Not found campaign.'),
                        'form' => ''
                    ];
                    $this->response->body(json_encode($content));
                    return $this->response;
                }

                $campaign->payment_method = 'wallet';
                $campaign->status = 5;
                $this->Campaigns->save($campaign);
            }

            $content = [
                'status' => 'success',
                'message' => '',
                'type' => 'offline',
                'url' => Router::url(['controller' => 'Invoices', 'action' => 'view', $invoice->id], true)
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        if ('paypal' == $this->request->data['payment_method']) {
            $return_url = Router::url(['controller' => 'Invoices', 'action' => 'view', $invoice->id], true);
            $notify_url = Router::url(['controller' => 'Invoices', 'action' => 'ipn', 'prefix' => false], true);

            $paymentData = [
                'business' => get_option('paypal_email'),
                'cmd' => '_xclick',
                'currency_code' => get_option('currency_code'),
                'amount' => $invoice->amount,
                'item_name' => __("Invoice"),
                'item_number' => '#' . $invoice->id,
                'page_style' => 'paypal',
                'return' => $return_url,
                'notify_url' => $notify_url,
                'rm' => '0',
                'cancel_return' => $return_url,
                'custom' => $invoice->id,
                'no_shipping' => 1,
                'lc' => 'US'
            ];

            $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

            if (get_option('paypal_sandbox', 'no') == 'no') {
                $url = 'https://www.paypal.com/cgi-bin/webscr';
            }

            $form = $this->redirect_post($url, $paymentData);

            $invoice->payment_method = 'paypal';
            $this->Invoices->save($invoice);

            $content = [
                'status' => 'success',
                'message' => '',
                'type' => 'form',
                'form' => $form
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        if ('payza' == $this->request->data['payment_method']) {
            $return_url = Router::url(['controller' => 'Invoices', 'action' => 'view', $invoice->id], true);
            $alert_url = Router::url(['controller' => 'Invoices', 'action' => 'ipn', 'prefix' => false], true);

            $paymentData = [
                'ap_merchant' => get_option('payza_email'),
                'apc_1' => $invoice->id,
                'ap_purchasetype' => 'service',
                'ap_amount' => $invoice->amount,
                'ap_quantity' => 1,
                'ap_itemname' => __("Invoice"),
                'ap_itemcode' => '#' . $invoice->id,
                'ap_currency' => get_option('currency_code'),
                'ap_returnurl' => $return_url,
                'ap_cancelurl' => $return_url,
                'ap_alerturl' => $alert_url,
                'ap_ipnversion' => 2,
            ];

            $url = 'https://secure.payza.com/checkout';

            $form = $this->redirect_post($url, $paymentData);

            $invoice->payment_method = 'payza';
            $this->Invoices->save($invoice);

            $content = [
                'status' => 'success',
                'message' => '',
                'type' => 'form',
                'form' => $form
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        if ('skrill' == $this->request->data['payment_method']) {
            $return_url = Router::url(['controller' => 'Invoices', 'action' => 'view', $invoice->id], true);
            $status_url = Router::url(['controller' => 'Invoices', 'action' => 'ipn', 'prefix' => false], true);

            $paymentData = [
                'pay_to_email' => get_option('skrill_email'),
                'recipient_description' => get_option('site_name'),
                'status_url' => $status_url,
                'amount' => $invoice->amount,
                'currency' => get_option('currency_code'),
                'detail1_description' => __("Invoice"),
                'detail1_text' => '#' . $invoice->id,
                'transaction_id' => $invoice->id,
                'return_url' => $return_url,
                'cancel_url' => $return_url
            ];

            $url = 'https://pay.skrill.com';

            $form = $this->redirect_post($url, $paymentData);

            $invoice->payment_method = 'skrill';
            $this->Invoices->save($invoice);

            $content = [
                'status' => 'success',
                'message' => '',
                'type' => 'form',
                'form' => $form
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        if ('coinbase' == $this->request->data['payment_method']) {
            $return_url = Router::url(['controller' => 'Invoices', 'action' => 'view', $invoice->id], true);
            $alert_url = Router::url(['controller' => 'Invoices', 'action' => 'ipn', 'prefix' => false], true);

            $paymentData = [
                'amount' => $invoice->amount,
                'currency' => get_option('currency_code'),
                'name' => __("Invoice") . ' #' . $invoice->id,
                //'description' => '',
                'type' => 'order',
                'success_url' => $return_url,
                'cancel_url' => $return_url,
                'notifications_url' => $alert_url,
                'auto_redirect' => true,
                'metadata' => [
                    'invoice_id' => $invoice->id
                ]
            ];

            $sandbox = '';
            if (get_option('coinbase_sandbox', 'no') == 'yes') {
                $sandbox = 'sandbox.';
            }

            $url = "https://api.{$sandbox}coinbase.com/v2/checkouts";

            /*
             * Get Coinbase timestamp
            $headers = [
                "CB-VERSION: 2016-09-12",
                "Content-Type: application/json",
            ];

            $url = 'https://api.coinbase.com/v2/time';
            $response = json_decode(curlRequest($url, "GET", [], $headers));

            pr($response->data->epoch);
            */

            $timestamp = time();
            $method = 'POST';
            $path = '/v2/checkouts';
            $body = json_encode($paymentData);

            $sign = hash_hmac('sha256', $timestamp . $method . $path . $body, get_option('coinbase_api_secret'));

            $headers = [
                "CB-ACCESS-KEY: " . get_option('coinbase_api_key'),
                "CB-ACCESS-SIGN: {$sign}",
                "CB-ACCESS-TIMESTAMP: {$timestamp}",
                "CB-VERSION: 2016-09-12",
                "Content-Type: application/json",
            ];
            $response = json_decode(curlRequest($url, "POST", $body, $headers));

            if (isset($response->errors)) {
                $message = '';
                foreach ($response->errors as $error) {
                    $message .= $error->id . " - " . $error->message . "\n";
                }

                $content = [
                    'status' => 'error',
                    'message' => $message,
                    'type' => 'url',
                    'url' => ''
                ];
                $this->response->body(json_encode($content));
                return $this->response;
            }

            $sandbox = 'www';
            if (get_option('coinbase_sandbox', 'no') == 'yes') {
                $sandbox = 'sandbox';
            }

            $redirect_url = "https://{$sandbox}.coinbase.com/checkouts/" . $response->data->embed_code;

            $invoice->payment_method = 'coinbase';
            $this->Invoices->save($invoice);

            $content = [
                'status' => 'success',
                'message' => '',
                'type' => 'url',
                'url' => $redirect_url
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        if ('webmoney' == $this->request->data['payment_method']) {
            $return_url = Router::url(['controller' => 'Invoices', 'action' => 'view', $invoice->id], true);
            $result_url = Router::url(['controller' => 'Invoices', 'action' => 'ipn', 'prefix' => false], true);

            // https://wiki.wmtransfer.com/projects/webmoney/wiki/Web_Merchant_Interface
            $paymentData = [
                'LMI_PAYMENT_AMOUNT' => $invoice->amount,
                'LMI_PAYMENT_DESC' => __("Invoice") . ' #' . $invoice->id,
                'LMI_PAYMENT_NO' => $invoice->id,
                'LMI_PAYEE_PURSE' => get_option('webmoney_merchant_purse'),
                'LMI_RESULT_URL' => $result_url,
                'LMI_SUCCESS_URL' => $return_url,
                'LMI_FAIL_URL' => $return_url
            ];

            $url = 'https://merchant.wmtransfer.com/lmi/payment.asp';

            $form = $this->redirect_post($url, $paymentData);

            $invoice->payment_method = 'webmoney';
            $this->Invoices->save($invoice);

            $content = [
                'status' => 'success',
                'message' => '',
                'type' => 'form',
                'form' => $form
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        if ('banktransfer' == $this->request->data['payment_method']) {
            $invoice->payment_method = 'banktransfer';
            $this->Invoices->save($invoice);

            $content = [
                'status' => 'success',
                'message' => '',
                'type' => 'offline',
                'url' => Router::url(['controller' => 'Invoices', 'action' => 'view', $invoice->id], true)
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        $content = [
            'status' => 'error',
            'message' => __("Invalide payment method.")
        ];
        $this->response->body(json_encode($content));
        return $this->response;
    }

    protected function redirect_post($url, array $data)
    {
        ob_start(); ?>
        <form id="checkout-redirect-form" method="post" action="<?= $url; ?>">
            <?php
            if (!is_null($data)) {
                foreach ($data as $k => $v) {
                    echo '<input type="hidden" name="' . $k . '" value="' . $v . '"> ';
                }
            } ?>
        </form>
        <?php
        $form = ob_get_contents();
        ob_end_clean();

        return $form;
    }

    public function edit($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid Invoice'));
        }

        $invoice = $this->Invoices->findById($id)->where(['user_id' => $this->Auth->user('id')])->first();
        if (!$invoice) {
            throw new NotFoundException(__('Invalid Invoice'));
        }

        if ($this->request->is(['post', 'put'])) {
            $invoice = $this->Invoices->patchEntity($invoice, $this->request->data);

            if ($this->Invoices->save($invoice)) {
                $this->Flash->success(__('Invoice has been updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
        }
        $this->set('invoice', $invoice);
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $invoice = $this->Invoices->findById($id)->where(['user_id' => $this->Auth->user('id')])->first();
        ;

        if ($this->Invoices->delete($invoice)) {
            $this->Flash->success(__('The invoice with id: {0} has been deleted.', $invoice->id));
            return $this->redirect(['action' => 'index']);
        }
    }
}
