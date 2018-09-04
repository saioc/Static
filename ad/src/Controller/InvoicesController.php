<?php

namespace App\Controller;

use Cake\Event\Event;

class InvoicesController extends AppController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(['ipn']);

        if (in_array($this->request->action, ['ipn'])) {
            $this->eventManager()->off($this->Csrf);
            $this->eventManager()->off($this->Security);
        }
    }

    public function ipn()
    {
        $this->autoRender = false;

        if (!empty($this->request->data)) {
            // PayPal IPN
            if (isset($this->request->data['txn_id'])) {
                $this->ipn_paypal($this->request->data);
                //return $this->response;
            }

            // Payza IPN
            if (isset($this->request->data['ap_merchant'])) {
                $this->ipn_payza($this->request->data);
                //return $this->response;
            }

            // Skrill IPN
            if (isset($this->request->data['mb_amount'])) {
                $this->ipn_skrill($this->request->data);
                //return $this->response;
            }

            // Payza IPN
            if (isset($this->request->data['LMI_PAYEE_PURSE'])) {
                $this->ipn_webmoney($this->request->data);
                //return $this->response;
            }
        }

        // Coinbase IPN
        $raw_body = json_decode(file_get_contents('php://input'));
        if (isset($raw_body->type)) {
            $this->ipn_coinbase($raw_body);
            //return $this->response;
        }
    }

    protected function success_payment($invoice = null)
    {
        if (!$invoice) {
            return false;
        }

        // Plans
        if ($invoice->type === 1) {
            $this->loadModel('Users');

            $user = $this->Users->find()->contain(['Plans'])->where(['Users.id' => $invoice->user_id])->first();

            if ($invoice->status === 1) {
                $payment_period = unserialize($invoice->data)['payment_period'];
                $expiration = (new \Cake\I18n\Time($user->expiration))->addYear();
                if ($payment_period === 'm') {
                    $expiration = (new \Cake\I18n\Time($user->expiration))->addMonth();
                }
                $user->expiration = $expiration;
                $user->plan_id = $invoice->rel_id;

                $this->Users->save($user);
                if ($this->Auth->user('id') !== null) {
                    if ($this->Auth->user('id') === $user->id) {
                        $data = $this->Users->find()->contain(['Plans'])
                            ->where(['Users.id' => $user->id])
                            ->first()
                            ->toArray();
                        unset($data['password']);

                        $this->Auth->setUser($data);
                    }
                }
            }
        }

        // Campaigns
        if ($invoice->type === 2) {
            $this->loadModel('Campaigns');

            $campaign = $this->Campaigns->get($invoice->rel_id);

            if ($invoice->status === 1) {
                $campaign->status = 1;
                $this->Campaigns->save($campaign);
            } elseif ($invoice->status === 4) {
                $campaign->status = 7;
                $this->Campaigns->save($campaign);
            } elseif ($invoice->status === 5) {
                $campaign->status = 8;
                $this->Campaigns->save($campaign);
            }
        }

        // Wallet
        if ($invoice->type === 3) {
            $this->loadModel('Users');
        }
    }

    protected function ipn_webmoney($data)
    {
        if (isset($data['LMI_PAYMENT_NO'])) {
            $invoice_id = (int)$data['LMI_PAYMENT_NO'];
            $invoice = $this->Invoices->get($invoice_id);

            if ($invoice->amount == $data['LMI_PAYMENT_AMOUNT']) {
                $invoice->status = 1;
                $invoice->paid_date = date("Y-m-d H:i:s");
                $this->Invoices->save($invoice);
                $message = 'VERIFIED';
            } else {
                $invoice->status = 4;
                $this->Invoices->save($invoice);
                $message = 'INVALID';
            }

            $this->success_payment($invoice);
        }
    }

    protected function ipn_coinbase($data)
    {
        // Todo check IPN https://developers.coinbase.com/api/v2?shell#show-a-checkout

        $invoice_id = (int)$data->data->metadata->invoice_id;
        $invoice = $this->Invoices->get($invoice_id);

        if ($data->type == 'wallet:orders:paid') {
            $invoice_amount = (float)$invoice->amount;
            $coinbase_amount = (float)$data->data->amount->amount;

            if ($invoice_amount != $coinbase_amount) {
                $invoice->status = 4;
                $this->Invoices->save($invoice);
                $message = 'INVALID';
            } else {
                $invoice->status = 1;
                $invoice->paid_date = date("Y-m-d H:i:s");
                $this->Invoices->save($invoice);
                $message = 'VERIFIED';
            }
        }

        if ($data->type == 'wallet:orders:mispaid') {
            $invoice->status = 4;
            $this->Invoices->save($invoice);
            $message = 'INVALID';
        }

        $this->success_payment($invoice);
    }

    protected function ipn_payza($data)
    {
        $token = [
            'token' => urlencode($data['token'])
        ];

        // https://dev.payza.com/resources/references/ipn-variables

        $url = 'https://secure.payza.com/ipn2.ashx';

        $res = curlRequest($url, 'POST', $token);

        if (strlen($res) > 0) {
            $invoice_id = (int)$data['apc_1'];
            $invoice = $this->Invoices->get($invoice_id);

            if (urldecode($res) != "INVALID TOKEN") {
                switch ($data['ap_transactionstate']) {
                    case 'Refunded':
                        $invoice->status = 5;
                        break;
                    case 'Completed':
                        $invoice->status = 1;
                        $invoice->paid_date = date("Y-m-d H:i:s");
                        break;
                }

                $this->Invoices->save($invoice);
                $message = 'VERIFIED';
            } else {
                $invoice->status = 4;
                $this->Invoices->save($invoice);
                $message = 'INVALID';
            }

            $this->success_payment($invoice);
        }
    }

    protected function ipn_skrill($data)
    {
        $concatFields = $data['merchant_id']
            . $data['transaction_id']
            . strtoupper(md5(get_option('skrill_secret_word')))
            . $data['mb_amount']
            . $data['mb_currency']
            . $data['status'];

        $MBEmail = get_option('skrill_email');


        $invoice_id = (int)$data['transaction_id'];
        $invoice = $this->Invoices->get($invoice_id);

        if ($invoice->amount == $data['amount']) {
            if (
                strtoupper(md5($concatFields)) == $data['md5sig'] &&
                $data['status'] == 2 &&
                $data['pay_to_email'] == $MBEmail
            ) {
                $invoice->status = 1;
                $invoice->paid_date = date("Y-m-d H:i:s");
                $this->Invoices->save($invoice);
                $message = 'VERIFIED';
            }
        } else {
            $invoice->status = 4;
            $this->Invoices->save($invoice);
            $message = 'INVALID';
        }

        $this->success_payment($invoice);
    }

    protected function ipn_paypal($data)
    {
        $data['cmd'] = '_notify-validate';

        // https://developer.paypal.com/docs/classic/ipn/integration-guide/IPNTesting/?mark=IPN%20troubleshoot#invalid

        $paypalURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';

        if (get_option('paypal_sandbox', 'no') == 'no') {
            $paypalURL = 'https://www.paypal.com/cgi-bin/webscr?';
        }

        $res = curlRequest($paypalURL, 'POST', $data);

        $invoice_id = (int)$data['custom'];
        $invoice = $this->Invoices->get($invoice_id);

        if (strcmp($res, "VERIFIED") == 0) {
            switch ($data['payment_status']) {
                case 'Refunded':
                    $invoice->status = 5;
                    break;
                case 'Completed':
                    $invoice->status = 1;
                    $invoice->paid_date = date("Y-m-d H:i:s");
                    break;
            }

            $this->Invoices->save($invoice);
            $message = 'VERIFIED';
        } elseif (strcmp($res, "INVALID") == 0) {
            $invoice->status = 4;
            $this->Invoices->save($invoice);
            $message = 'INVALID';
        }

        $this->success_payment($invoice);
    }
}
