<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CampaignItemsTable extends Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Campaigns');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            //->requirePresence('purchase')
            ->allowEmpty('purchase')
            ->naturalNumber('purchase', __('Write a valid natural number.'));

        return $validator;
    }
}
