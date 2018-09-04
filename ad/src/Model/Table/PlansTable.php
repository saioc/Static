<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class PlansTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');
        $this->hasMany('Users');
        //$this->addBehavior('Translate', ['fields' => ['title']]);
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->notEmpty('title')
            ->boolean('enable', __('Choose a valid value.'))
            ->numeric('monthly_price', __('Choose a valid value.'))
            ->numeric('yearly_price', __('Choose a valid value.'))
            ->boolean('edit_link', __('Choose a valid value.'))
            ->boolean('edit_long_url', __('Choose a valid value.'))
            ->boolean('ads', __('Choose a valid value.'))
            ->boolean('direct', __('Choose a valid value.'))
            ->boolean('alias', __('Choose a valid value.'))
            ->boolean('referral', __('Choose a valid value.'))
            ->boolean('stats', __('Choose a valid value.'))
            ->boolean('api_quick', __('Choose a valid value.'))
            ->boolean('api_mass', __('Choose a valid value.'))
            ->boolean('api_full', __('Choose a valid value.'))
            ->boolean('api_developer', __('Choose a valid value.'))
            ->notEmpty('plan_replace', __('Choose a valid value.'));

        return $validator;
    }
}
