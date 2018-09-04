<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class WithdrawsTable extends Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Users');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator)
    {
        $minimum_withdrawal_amount = get_option('minimum_withdrawal_amount', 5);

        $validator
            ->requirePresence('amount')
            ->notEmpty('amount', __('You must have a balance.'))
            ->greaterThanOrEqual('amount', $minimum_withdrawal_amount,
                __('Withdraw amount should be greater or equal to {0}.',
                    display_price_currency($minimum_withdrawal_amount)));
        return $validator;
    }
}
