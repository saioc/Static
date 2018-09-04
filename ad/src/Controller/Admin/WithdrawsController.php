<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;

class WithdrawsController extends AppAdminController
{
    public function index()
    {
        $query = $this->Withdraws->find()
            ->contain(['Users']);
        $withdraws = $this->paginate($query);

        $this->set('withdraws', $withdraws);

        $publishers_earnings = $this->Withdraws->Users->find()
            ->select(['total' => 'SUM(publisher_earnings)'])
            ->first();
        $this->set('publishers_earnings', $publishers_earnings->total);

        $referral_earnings = $this->Withdraws->Users->find()
            ->select(['total' => 'SUM(referral_earnings)'])
            ->first();
        $this->set('referral_earnings', $referral_earnings->total);

        $pending_withdrawn = $this->Withdraws->find()
            ->select(['total' => 'SUM(amount)'])
            ->where(['status' => 2])
            ->first();

        $this->set('pending_withdrawn', $pending_withdrawn->total);

        $tolal_withdrawn = $this->Withdraws->find()
            ->select(['total' => 'SUM(amount)'])
            ->where(['status' => 3])
            ->first();

        $this->set('tolal_withdrawn', $tolal_withdrawn->total);
    }

    /*
    public function edit($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid Withdraw'));
        }

        $withdraw = $this->Withdraws->find()->contain(['Users'])->where(['Withdraws.id' => $id])->first();
        if (!$withdraw) {
            throw new NotFoundException(__('Invalid Withdraw'));
        }

        if ($this->request->is(['post', 'put'])) {
            $this->request->data['amount'] = $withdraw->amount;
            $withdraw = $this->Withdraws->patchEntity($withdraw, $this->request->data);
            if ($this->Withdraws->save($withdraw)) {
                $this->Flash->success(__('The withdraw has been updated.'));
                return $this->redirect(['action' => 'index']);
            } else {
                debug($withdraw->errors());
                $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
            }
        }
        $this->set('withdraw', $withdraw);
    }
    */

    public function approve($id)
    {
        $this->request->allowMethod(['post', 'put']);

        $withdraw = $this->Withdraws->get($id);

        $withdraw->status = 1;

        if ($this->Withdraws->save($withdraw)) {
            $this->Flash->success(__('The campaign with id: {0} has been approved.', $id));
            return $this->redirect(['action' => 'index']);
        }
    }

    public function complete($id)
    {
        $this->request->allowMethod(['post', 'put']);

        $withdraw = $this->Withdraws->get($id);

        $withdraw->status = 3;

        if ($this->Withdraws->save($withdraw)) {
            if ($withdraw->method == 'wallet') {
                $user = $this->Withdraws->Users->get($withdraw->user_id);
                $user->wallet_money += $withdraw->amount;
                $this->Withdraws->Users->save($user);
            }
            $this->Flash->success(__('The campaign with id: {0} has been completed.', $id));
            return $this->redirect(['action' => 'index']);
        }
    }
}
