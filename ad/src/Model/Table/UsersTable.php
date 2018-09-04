<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Auth\DefaultPasswordHasher;

class UsersTable extends Table
{
    public function initialize(array $config)
    {
        $this->hasMany('Campaigns');
        $this->hasMany('Links');
        $this->hasMany('Statistics');
        $this->hasMany('Withdraws');
        $this->belongsTo('Plans');
        $this->hasMany('Invoices');
        $this->addBehavior('Timestamp');

        $this->hasMany('ADmad/HybridAuth.SocialProfiles');
        \Cake\Event\EventManager::instance()->on('HybridAuth.newUser', [$this, 'createUser']);
    }

    public function findAuth(\Cake\ORM\Query $query, array $options)
    {
        $user_status = 1;
        if (version_compare(get_option('app_version'), '3.0.0', '<')) {
            $user_status = 'active';
        }
        if (version_compare(get_option('app_version'), '3.6.0', '<')) {
            $query
                ->where(['Users.username' => $options['username']])
                ->orwhere(['Users.email' => $options['username']])
                ->andwhere(['Users.status' => $user_status]);

            return $query;
        }
        $query
            ->contain(['Plans'])
            ->where(['Users.username' => $options['username']])
            ->orwhere(['Users.email' => $options['username']])
            ->andwhere(['Users.status' => $user_status]);

        return $query;
    }

    public function findSocial(\Cake\ORM\Query $query, array $options)
    {
        $query
            ->contain(['Plans'])
            ->where(['Users.status' => 1]);

        return $query;
    }

    public function validationDefault(Validator $validator)
    {
        return $validator
            ->notEmpty('username', 'A username is required')
            ->add('username', [
                'alphaNumeric' => [
                    'rule' => ['alphaNumeric'],
                    'message' => __('alphaNumeric Only')
                ],
                'minLength' => [
                    'rule' => ['minLength', 5],
                    'message' => __('Minimum Length 5')
                ],
                'maxLength' => [
                    'rule' => ['maxLength', 255],
                    'message' => __('Maximum Length 255')
                ]
            ])
            ->add('username', 'checkReserved', [
                'rule' => function ($value, $context) {
                    $reserved_domains = explode(',', get_option('reserved_usernames'));
                    $reserved_domains = array_map('trim', $reserved_domains);
                    $reserved_domains = array_filter($reserved_domains);

                    if (in_array(strtolower($value), $reserved_domains)) {
                        return false;
                    }
                    return true;
                },
                'message' => __('This username is a reserved word.')
            ])
            ->add('username', [
                'unique' => [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                    'message' => __('Username already exists')
                ]
            ])
            ->add('username_compare', [
                'compare' => [
                    'rule' => ['compareWith', 'username'],
                    'message' => __('Not the same')
                ]
            ])
            ->notEmpty('password', 'A password is required')
            ->add('password', [
                'minLength' => [
                    'rule' => ['minLength', 5],
                    'message' => __('Minimum Length 5')
                ],
                'maxLength' => [
                    'rule' => ['maxLength', 25],
                    'message' => __('Maximum Length 25')
                ]
            ])
            ->add('password_compare', [
                'compare' => [
                    'rule' => ['compareWith', 'password'],
                    'message' => __('Not the same')
                ]
            ])
            ->notEmpty('email', 'An email is required')
            ->add('email', 'validFormat', [
                'rule' => 'email',
                'message' => __('E-mail must be valid')
            ])
            ->add('email', [
                'unique' => [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                    'message' => __('E-mail must be unique')
                ]
            ])
            ->add('email_compare', [
                'compare' => [
                    'rule' => ['compareWith', 'email'],
                    'message' => __('Not the same')
                ]
            ])
            ->notEmpty('first_name', __('This field should not be blank.'))
            ->notEmpty('last_name', __('This field should not be blank.'))
            ->notEmpty('address1', __('This field should not be blank.'))
            ->notEmpty('city', __('This field should not be blank.'))
            ->notEmpty('state', __('This field should not be blank.'))
            ->notEmpty('zip', __('This field should not be blank.'))
            ->notEmpty('country', __('This field should not be blank.'))
            ->notEmpty('phone_number', __('This field should not be blank.'))
            ->notEmpty('withdrawal_method', __('This field should not be blank.'))
            ->add('withdrawal_method', 'inList', [
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
            ])
            ->notEmpty('withdrawal_account', __('This field should not be blank.'), function ($context) {
                return !($context['data']['withdrawal_method'] === 'wallet');
            });
    }

    public function validationChangeEmail(Validator $validator)
    {
        //$validator = $this->validateDefault($validator);
        return $validator
            ->notEmpty('temp_email', 'An email is required')
            ->add('temp_email', 'validFormat', [
                'rule' => 'email',
                'message' => __('E-mail must be valid')
            ])
            ->add('temp_email', 'custom', [
                'rule' => function ($value, $context) {
                    $count = $this->find('all')
                        ->where(['email' => $value])
                        ->count();
                    if ($count > 0) {
                        return false;
                    } else {
                        return true;
                    }
                },
                'message' => __('E-mail must be unique')
            ])
            ->add('confirm_email', [
                'compare' => [
                    'rule' => ['compareWith', 'temp_email'],
                    'message' => __('Not the same')
                ]
            ]);
    }

    public function validationChangePassword(Validator $validator)
    {
        //$validator = $this->validateDefault($validator);
        return $validator
            ->notEmpty('current_password', 'Please enter current password.')
            ->add('current_password', 'custom', [
                'rule' => function ($value, $context) {
                    $user = $this->findById($context['data']['id'])->first();
                    return (new DefaultPasswordHasher)->check($value, $user->password);
                },
                'message' => __('Please enter current password.')
            ])
            ->notEmpty('password', 'A password is required')
            ->add('password', [
                'minLength' => [
                    'rule' => ['minLength', 5],
                    'message' => __('Minimum Length 5')
                ],
                'maxLength' => [
                    'rule' => ['maxLength', 25],
                    'message' => __('Maximum Length 25')
                ]
            ])
            ->add('confirm_password', [
                'compare' => [
                    'rule' => ['compareWith', 'password'],
                    'message' => __('Not the same')
                ]
            ]);
    }

    public function validationForgotPassword(Validator $validator)
    {
        //$validator = $this->validateDefault($validator);
        return $validator
            ->notEmpty('email', 'An email is required')
            ->add('email', 'validFormat', [
                'rule' => 'email',
                'message' => __('E-mail must be valid')
            ])
            ->notEmpty('password', 'A password is required')
            ->add('password', [
                'minLength' => [
                    'rule' => ['minLength', 5],
                    'message' => __('Minimum Length 5')
                ],
                'maxLength' => [
                    'rule' => ['maxLength', 25],
                    'message' => __('Maximum Length 25')
                ]
            ])
            ->add('confirm_password', [
                'compare' => [
                    'rule' => ['compareWith', 'password'],
                    'message' => __('Not the same')
                ]
            ]);
    }

    public function createUser(\Cake\Event\Event $event)
    {
        // Entity representing record in social_profiles table
        $profile = $event->data()['profile'];

        $referred_by_id = 0;
        if (isset($_COOKIE['ref'])) {
            $user_referred_by = $this->find()
                ->where([
                    'username' => $_COOKIE['ref'],
                    'status' => 1
                ])
                ->first();

            if ($user_referred_by) {
                $referred_by_id = $user_referred_by->id;
            }
        }

        $user = $this->newEntity([
            'status' => 1,
            'username' => $profile->identifier,
            'password' => generate_random_string(10),
            'plan_id' => 1,
            'role' => 'member',
            'email' => $profile->email,
            'referred_by' => $referred_by_id,
            'api_token' => \Cake\Utility\Security::hash(\Cake\Utility\Text::uuid(), 'sha1', true),
        ]);

        if ($this->save($user)) {
            $user = $this->find()->contain(['Plans'])->where(['Users.id' => $user->id])->first();
            return $user;
        } else {
            //debug($user->errors());
            session_destroy();
            throw new \RuntimeException('Unable to save new user');
        }
    }
}
