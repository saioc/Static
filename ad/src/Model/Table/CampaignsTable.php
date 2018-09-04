<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CampaignsTable extends Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Users');
        $this->hasMany('CampaignItems', [
            'dependent' => true,
            'cascadeCallbacks' => true
        ]);
        $this->hasOne('Invoices', [
            'foreignKey' => 'rel_id',
            'conditions' => ['type' => 2]
        ]);
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->notEmpty('user_id', __('This value should not be blank.'))
            ->add('status', 'inList', [
                'rule' => ['inList', [1, 2, 3, 4, 5, 6, 7, 8]],
                'message' => __('Choose a valid value.')
            ])
            ->notEmpty('name', __('This value should not be blank.'))
            ->notEmpty('website_title', __('This value should not be blank.'))
            ->notEmpty('website_url', __('This value should not be blank.'))
            ->add('website_url', 'url', [
                'rule' => 'url',
                'message' => __('URL must be valid.')
            ])
            ->add('website_url', 'checkProtocol', [
                'rule' => function ($value, $context) {
                    $scheme = parse_url($value, PHP_URL_SCHEME);

                    if (in_array($scheme, ['http', 'https'])) {
                        return true;
                    }
                    return false;
                },
                'message' => __('http and https urls only allowed.')
            ])
            /*
            ->add('website_url', 'checkXFrameOptions', [
                'rule' => function ($value, $context) {
                    $headers = get_http_headers( $value );
                    if ( isset( $headers[ "x-frame-options" ] ) ) {
                        return false;
                    }
                    return true;
                },
                'message' => __('This website URL refused to be used in interstitial ads.')
            ])
            */
            ->notEmpty('banner_name', __('This value should not be blank.'))
            ->add('banner_size', 'inList', [
                'rule' => ['inList', ['728x90', '468x60', '336x280']],
                'message' => __('Choose a valid value.')
            ])
            ->notEmpty('banner_code', __('This value should not be blank.'))
            ->notEmpty('price', __('You must have a purchase.'))
            ->add('traffic_source', 'inList', [
                'rule' => ['inList', [1, 2, 3]],
                'message' => __('Choose a valid value.')
            ])
            ->add('website_terms', 'termsAccept', [
                'rule' => function ($value, $context) {
                    if ($value == 1) {
                        return true;
                    }
                    return false;
                },
                'message' => __('You must accept our terms and conditions.')
            ])
            ->add('payment_method', 'inList', [
                'rule' => [
                    'inList',
                    [
                        'paypal',
                        'payza',
                        'skrill',
                        'coinbase',
                        'webmoney',
                        'banktransfer',
                        'wallet'
                    ]
                ],
                'message' => __('Choose a valid value.')
            ]);

        return $validator;
    }

    public function isOwnedBy($id, $user_id)
    {
        return $this->exists(['id' => $id, 'user_id' => $user_id]);
    }
}
