<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;
use Cake\Routing\Router;
use Cake\Network\Exception\NotFoundException;

class InvoicesController extends AppAdminController
{
    public function index()
    {
        $query = $this->Invoices->find()->contain(['Users']);
        $invoices = $this->paginate($query);

        $this->set('invoices', $invoices);
    }

    public function view($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid Invoice'));
        }

        $invoice = $this->Invoices->findById($id)->contain(['Users'])->first();
        if (!$invoice) {
            throw new NotFoundException(__('Invalid Invoice'));
        }
        $this->set('invoice', $invoice);
    }

    public function markPaid($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $invoice = $this->Invoices->findById($id)->where(['status <>' => 1])->first();

        $invoice->status = 1;
        $invoice->paid_date = date("Y-m-d H:i:s");
        $this->Invoices->save($invoice);

        if ($this->success_payment($invoice)) {
            $this->Flash->success(__('The invoice with id: {0} has been marked as paid.', $invoice->id));
        }
        return $this->redirect(['action' => 'index']);
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $invoice = $this->Invoices->findById($id)->first();

        if ($this->Invoices->delete($invoice)) {
            $this->Flash->success(__('The invoice with id: {0} has been deleted.', $invoice->id));
            return $this->redirect(['action' => 'index']);
        }
    }

    /*
     * Copied from InvoicesController->success_payment
     */
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
                return true;
            }
        }

        // Campaigns
        if ($invoice->type === 2) {
            $this->loadModel('Campaigns');

            $campaign = $this->Campaigns->get($invoice->rel_id);

            if ($invoice->status === 1) {
                $campaign->status = 1;
                $this->Campaigns->save($campaign);
                return true;
            } elseif ($invoice->status === 4) {
                $campaign->status = 7;
                $this->Campaigns->save($campaign);
                return true;
            } elseif ($invoice->status === 5) {
                $campaign->status = 8;
                $this->Campaigns->save($campaign);
                return true;
            }
        }

        // Wallet
        if ($invoice->type === 3) {
            $this->loadModel('Users');
            return true;
        }
    }
}
