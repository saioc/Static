<?php

namespace App\Controller;

use App\Controller\FrontController;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Network\Exception\NotFoundException;
use Cake\Network\Exception\BadRequestException;
use Cake\ORM\TableRegistry;

class LinksController extends FrontController
{
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Cookie');
        $this->loadComponent('Captcha');
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->viewBuilder()->layout('front');
        $this->Auth->allow(['shorten', 'view', 'go', 'popad']);
    }

    public function shorten()
    {
        $this->autoRender = false;

        $this->response->type('json');

        if (!$this->request->is('ajax')) {
            $content = [
                'status' => 'error',
                'message' => __('Bad Request.'),
                'url' => ''
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        $user_id = 1;
        if (null !== $this->Auth->user('id')) {
            $user_id = $this->Auth->user('id');
        }


        if ($user_id === 1 &&
            (bool)get_option('enable_captcha_shortlink_anonymous', false) &&
            isset_captcha() &&
            !$this->Captcha->verify($this->request->data)
        ) {
            $content = [
                'status' => 'error',
                'message' => __('The CAPTCHA was incorrect. Try again'),
                'url' => ''
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }


        if ($user_id == 1 && get_option('home_shortening_register') === 'yes') {
            $content = [
                'status' => 'error',
                'message' => __('Bad Request.'),
                'url' => ''
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        $user = $this->Links->Users->find()->where(['status' => 1, 'id' => $user_id])->first();

        if (!$user) {
            $content = [
                'status' => 'error',
                'message' => __('Invalid user'),
                'url' => ''
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        $this->request->data['url'] = trim($this->request->data['url']);
        $this->request->data['url'] = str_replace(" ", "%20", $this->request->data['url']);
        $this->request->data['url'] = parse_url(
            $this->request->data['url'],
            PHP_URL_SCHEME
        ) === null ? 'http://' . $this->request->data['url'] : $this->request->data['url'];

        $domain = '';
        if (isset($this->request->data['domain'])) {
            $domain = $this->request->data['domain'];
        }
        if (!in_array($domain, get_multi_domains_list())) {
            $domain = '';
        }

        $linkWhere = [
            'user_id' => $user->id,
            'status' => 1,
            'ad_type' => $this->request->data['ad_type'],
            'url' => $this->request->data['url']
        ];

        if (isset($this->request->data['alias']) && strlen($this->request->data['alias']) > 0) {
            $linkWhere['alias'] = $this->request->data['alias'];
        }

        $link = $this->Links->find()->where($linkWhere)->first();

        if ($link) {
            $content = [
                'status' => 'success',
                'message' => '',
                'url' => get_short_url($link->alias, $domain)
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        $link = $this->Links->newEntity();
        $data = [];

        $data['user_id'] = $user->id;
        $data['url'] = $this->request->data['url'];

        $data['domain'] = $domain;

        if (empty($this->request->data['alias'])) {
            $data['alias'] = $this->Links->geturl();
        } else {
            $data['alias'] = $this->request->data['alias'];
        }

        $data['ad_type'] = $this->request->data['ad_type'];
        $link->status = 1;
        $link->hits = 0;
        $link->method = 1;

        $linkMeta = [
            'title' => '',
            'description' => '',
            'image' => ''
        ];

        if ($user_id === 1 && get_option('disable_meta_home') === 'no') {
            $linkMeta = $this->Links->getLinkMeta($this->request->data['url']);
        }

        if ($user_id !== 1 && get_option('disable_meta_member') === 'no') {
            $linkMeta = $this->Links->getLinkMeta($this->request->data['url']);
        }

        $link->title = $linkMeta['title'];
        $link->description = $linkMeta['description'];
        $link->image = $linkMeta['image'];


        $link = $this->Links->patchEntity($link, $data);
        if ($this->Links->save($link)) {
            $content = [
                'status' => 'success',
                'message' => '',
                'url' => get_short_url($link->alias, $domain)
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        $message = __('Invalid URL.');
        if ($link->errors()) {
            $error_msg = [];
            foreach ($link->errors() as $errors) {
                if (is_array($errors)) {
                    foreach ($errors as $error) {
                        $error_msg[] = $error;
                    }
                } else {
                    $error_msg[] = $errors;
                }
            }

            if (!empty($error_msg)) {
                $message = implode("<br>", $error_msg);
            }
        }

        $content = [
            'status' => 'error',
            'message' => $message,
            'url' => ''
        ];
        $this->response->body(json_encode($content));
        return $this->response;
    }

    public function view($alias = null)
    {
        $this->response->header('X-Frame-Options', 'SAMEORIGIN');

        if (!$alias) {
            throw new NotFoundException(__('Invalid link'));
        }

        $link = $this->Links->find()
            ->contain(['Users.Plans'])
            ->where([
                'Links.alias' => $alias,
                'Links.status <>' => 3,
                'Users.status' => 1
            ])->first();

        if (!$link) {
            throw new NotFoundException(__('404 Not Found'));
        }
        $this->set('link', $link);

        $detector = new \Detection\MobileDetect();
        if ((bool)$detector->is("Bot")) {
            return $this->redirect($link->url, 301);
        }

        $plan_disable_ads = $plan_disable_captcha = $plan_direct = false;
        if ((bool)get_option('enable_premium_membership')) {
            if ($this->Auth->user()) {
                $auth_user = $this->Auth->user();
                if (get_user_plan($auth_user)->disable_ads) {
                    $plan_disable_ads = true;
                }
                if (get_user_plan($auth_user)->disable_captcha) {
                    $plan_disable_captcha = true;
                }
                if (get_user_plan($auth_user)->direct) {
                    $plan_direct = true;
                }
            }
        }

        $ad_type = $link->ad_type;
        if (!array_key_exists($ad_type, get_allowed_ads())) {
            $ad_type = get_option('member_default_advert', 1);
        }
        if ($link->user_id == 1) {
            $ad_type = get_option('anonymous_default_advert', 1);
        }

        // No Ads
        if ($plan_direct || $ad_type == 0) {
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, [
                'ci' => 0,
                'cui' => 0,
                'cii' => 0,
                'ref' => (env('HTTP_REFERER')) ? env('HTTP_REFERER') : '',
            ], get_ip(), 10);
            return $this->redirect($link->url, 301);
        }

        $captcha_ad = get_option('ad_captcha', '');
        if ($plan_disable_ads) {
            $captcha_ad = '';
        }

        $this->set('captcha_ad', $captcha_ad);

        $this->viewBuilder()->layout('captcha');
        $this->render('captcha');

        if ($plan_disable_captcha ||
            !((get_option('enable_captcha_shortlink') == 'yes') && isset_captcha()) ||
            $this->request->is('post')
        ) {
            if (!$plan_disable_captcha &&
                (get_option('enable_captcha_shortlink') == 'yes') &&
                isset_captcha() &&
                !$this->Captcha->verify($this->request->data)
            ) {
                $this->Flash->error(__('The CAPTCHA was incorrect. Try again'));
                return $this->redirect('/'.$this->request->url);
            }

            //env('HTTP_REFERER', $this->request->data['ref']);

            $_SERVER['HTTP_REFERER'] = ($plan_disable_captcha ||
                !((get_option('enable_captcha_shortlink') == 'yes') && isset_captcha())
            ) ? env('HTTP_REFERER') : $this->request->data['ref'];

            $this->setVisitorCookie();

            $country = $this->Links->Statistics->get_country(get_ip());

            if ($detector->isMobile()) {
                $traffic_source = 3;
            } else {
                $traffic_source = 2;
            }

            $campaign_item = $this->getCampaignItem($ad_type, $traffic_source, $country);
            $this->set('campaign_item', $campaign_item);

            if (get_option('enable_popup', 'yes') == 'yes') {
                $pop_ad = [
                    'link' => $link,
                    'country' => $country,
                    'traffic_source' => $traffic_source
                ];
                $this->set('pop_ad', data_encrypt($pop_ad));
            }

            // Interstitial Ads
            if ($ad_type == 1) {
                $interstitial_ads = get_option('interstitial_ads', '');
                if ($plan_disable_ads) {
                    $interstitial_ads = '';
                }
                $this->set('interstitial_ads', $interstitial_ads);
                $this->set('plan_disable_ads', $plan_disable_ads);

                $this->viewBuilder()->layout('go_interstitial');
                $this->render('view_interstitial');
            }

            // Banner Ads
            if ($ad_type == 2) {
                $banner_728x90 = get_option('banner_728x90', '');
                if ('728x90' == $campaign_item->campaign->banner_size) {
                    $banner_728x90 = $campaign_item->campaign->banner_code;
                }

                $banner_468x60 = get_option('banner_468x60', '');
                if ('468x60' == $campaign_item->campaign->banner_size) {
                    $banner_468x60 = $campaign_item->campaign->banner_code;
                }

                $banner_336x280 = get_option('banner_336x280', '');
                if ('336x280' == $campaign_item->campaign->banner_size) {
                    $banner_336x280 = $campaign_item->campaign->banner_code;
                }

                if ($plan_disable_ads) {
                    $banner_728x90 = '';
                    $banner_468x60 = '';
                    $banner_336x280 = '';
                }

                $this->set('banner_728x90', $banner_728x90);
                $this->set('banner_468x60', $banner_468x60);
                $this->set('banner_336x280', $banner_336x280);

                $this->viewBuilder()->layout('go_banner');
                $this->render('view_banner');
            }
        }
    }

    public function popad()
    {
        $this->autoRender = false;

        if ($this->request->is('post')) {
            $pop_ad = data_decrypt($this->request->data['pop_ad']);

            $campaign_item = $this->getCampaignItem(3, $pop_ad['traffic_source'], $pop_ad['country']);
            $data = [
                'alias' => $pop_ad['link']->alias,
                'ci' => $campaign_item->campaign_id,
                'cui' => $campaign_item->campaign->user_id,
                'cii' => $campaign_item->id,
                'ref' => strtolower(env('HTTP_REFERER'))
            ];
            $content = $this->calcEarnings($data, $pop_ad['link'], 3);

            return $this->redirect($campaign_item->campaign->website_url, 301);
        }
        //die("Invalid Request");
    }

    public function go()
    {
        $this->autoRender = false;
        $this->response->type('json');

        if (!$this->request->is('ajax')) {
            $content = [
                'status' => 'error',
                'message' => 'Bad Request.',
                'url' => ''
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        $link = $this->Links->find()->contain(['Users'])->where([
            'Links.alias' => $this->request->data['alias'],
            'Links.status <>' => 3
        ])->first();
        if (!$link) {
            $content = [
                'status' => 'error',
                'message' => '404 Not Found.',
                'url' => ''
            ];
            $this->response->body(json_encode($content));
            return $this->response;
        }

        $data = $this->request->data;

        $content = $this->calcEarnings($data, $link, $link->ad_type);

        $this->response->body(json_encode($content));
        return $this->response;
    }

    protected function getCampaignItem($ad_type, $traffic_source, $country)
    {
        $CampaignItems = TableRegistry::get('CampaignItems');

        $campaign_items = $CampaignItems->find()
            ->contain(['Campaigns'])
            ->where([
                'Campaigns.default_campaign' => 0,
                'Campaigns.ad_type' => $ad_type,
                'Campaigns.status' => 1,
                "Campaigns.traffic_source IN (1, :traffic_source)",
                'CampaignItems.weight <' => 100,
                'CampaignItems.country' => $country,
                //'Campaigns.user_id <>' => $link->user_id
            ])
            ->order(['CampaignItems.weight' => 'ASC'])
            ->bind(':traffic_source', $traffic_source, 'integer')
            ->limit(10)
            ->toArray();

        if (count($campaign_items) == 0) {
            $campaign_items = $CampaignItems->find()
                ->contain(['Campaigns'])
                ->where([
                    'Campaigns.default_campaign' => 0,
                    'Campaigns.ad_type' => $ad_type,
                    'Campaigns.status' => 1,
                    "Campaigns.traffic_source IN (1, :traffic_source)",
                    'CampaignItems.weight <' => 100,
                    'CampaignItems.country' => 'all',
                    //'Campaigns.user_id <>' => $link->user_id
                ])
                //->order(['CampaignItems.weight' => 'ASC'])
                ->bind(':traffic_source', $traffic_source, 'integer')
                ->limit(10)
                ->toArray();
        }

        if (count($campaign_items) == 0) {
            $campaign_items = $CampaignItems->find()
                ->contain(['Campaigns'])
                ->where([
                    'Campaigns.default_campaign' => 1,
                    'Campaigns.ad_type' => $ad_type,
                    'Campaigns.status' => 1,
                    "Campaigns.traffic_source IN (1, :traffic_source)",
                    'CampaignItems.weight <' => 100,
                    "CampaignItems.country IN ( 'all', :country)",
                    //'Campaigns.user_id <>' => $link->user_id
                ])
                ->order(['CampaignItems.weight' => 'ASC'])
                ->bind(':traffic_source', $traffic_source, 'integer')
                ->bind(':country', $country, 'string')
                ->limit(10)
                ->toArray();
        }

        shuffle($campaign_items);
        return array_values($campaign_items)[0];
    }

    protected function calcEarnings($data, $link, $ad_type)
    {
        /**
         * Views reasons
         * 1- Earn
         * 2- Disabled cookie
         * 3- Anonymous user
         * 4- Adblock
         * 5- Proxy
         * 6- IP changed
         * 7- Not unique
         * 8- Full weight
         * 9- Default campaign
         * 10- Direct
         */
        /**
         * Check if cookie valid
         */
        $cookie = $this->Cookie->read('visitor');
        if (!is_array($cookie)) {
            // Update link hits
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, get_ip(), 2);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because no cookie',
                'url' => $link->url
            ];
            return $content;
        }

        /**
         * Check if anonymous user
         */
        if ('anonymous' == $link->user->username) {
            // Update link hits
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 3);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because anonymous user',
                'url' => $link->url
            ];
            return $content;
        }

        /**
         * Check for Adblock
         */
        if (!empty($this->request->cookie('adblockUser'))) {
            // Update link hits
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 4);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because Adblock',
                'url' => $link->url
            ];
            return $content;
        }

        /**
         * Check if proxy
         */
        if ($this->isProxy()) {
            // Update link hits
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, get_ip(), 5);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because proxy',
                'url' => $link->url
            ];
            return $content;
        }

        /**
         * Check if IP changed
         */
        if ($cookie['ip'] != get_ip()) {
            // Update link hits
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 6);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because IP changed',
                'url' => $link->url
            ];
            return $content;
        }

        /**
         * Check for unique vistits within last 24 hour
         */
        $startOfToday = Time::today()->format('Y-m-d H:i:s');
        $endOfToday = Time::now()->endOfDay()->format('Y-m-d H:i:s');

        $statistics = $this->Links->Statistics->find()
            ->where([
                'Statistics.ip' => $cookie['ip'],
                'Statistics.campaign_id' => $data['ci'],
                'Statistics.publisher_earn >' => 0,
                'Statistics.created BETWEEN :startOfToday AND :endOfToday'
            ])
            ->bind(':startOfToday', $startOfToday, 'datetime')
            ->bind(':endOfToday', $endOfToday, 'datetime')
            ->count();

        if ($statistics >= get_option('campaign_paid_views_day', 1)) {
            // Update link hits
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 7);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because Not unique.',
                'url' => $link->url
            ];
            return $content;
        }


        /**
         * Check Campaign Item weight
         */
        $CampaignItems = TableRegistry::get('CampaignItems');

        $campaign_item = $CampaignItems->find()
            ->contain(['Campaigns'])
            ->where(['CampaignItems.id' => $data['cii']])
            ->where(['CampaignItems.weight <' => 100])
            ->where(['Campaigns.status' => 1])
            ->first();


        if (!$campaign_item) {
            // Update link hits
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 8);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because Campaign Item weight is full.',
                'url' => $link->url
            ];
            return $content;
        }

        /**
         * Check if default campaign
         */
        if ($campaign_item->campaign->default_campaign) {
            // Update link hits
            $this->updateLinkHits($link);
            $this->addNormalStatisticEntry($link, $ad_type, $data, $cookie['ip'], 9);
            $content = [
                'status' => 'success',
                'message' => 'Go without Earn because Default Campaign.',
                'url' => $link->url
            ];
            return $content;
        }

        /**
         * Add statistic record
         */

        $owner_earn = ($campaign_item['advertiser_price'] - $campaign_item['publisher_price']) / 1000;
        $publisher_earn = $campaign_item['publisher_price'] / 1000;

        $user_update = $this->Links->Users->find()->contain('Plans')
            ->where(['Users.id' => $link->user_id])->first();

        $publisher_user_earnings = true;
        if ((bool)get_option('enable_premium_membership')) {
            if ($this->Auth->user()) {
                $auth_user = $this->Auth->user();
                if (get_user_plan($auth_user)->disable_ads) {
                    $publisher_user_earnings = false;
                }
            }
        }

        if ($publisher_user_earnings) {
            $user_update->publisher_earnings += $publisher_earn;
            $this->Links->Users->save($user_update);
        }

        $referral_id = $referral_earn = 0;

        if ($publisher_user_earnings && !empty($user_update->referred_by)) {
            $user_referred_by = $this->Links->Users->find()
                ->contain(['Plans'])
                ->where(['Users.id' => $user_update->referred_by, 'Users.status' => 1])
                ->first();

            if ($user_referred_by) {
                $plan_referral = true;
                if ((bool)get_option('enable_premium_membership')) {
                    if (!get_user_plan($user_referred_by)->referral) {
                        $plan_referral = false;
                    }
                }

                if ($plan_referral) {
                    $referral_percentage = get_option('referral_percentage', 20) / 100;
                    $referral_value = $publisher_earn * $referral_percentage;

                    $user_referred_by->referral_earnings += $referral_value;

                    $this->Links->Users->save($user_referred_by);

                    $referral_id = $user_update->referred_by;
                    $referral_earn = $referral_value;
                }
            }
        }

        $country = $this->Links->Statistics->get_country($cookie['ip']);

        $statistic = $this->Links->Statistics->newEntity();

        $statistic->link_id = $link->id;
        $statistic->user_id = $link->user_id;
        $statistic->ad_type = $campaign_item['campaign']['ad_type'];
        $statistic->campaign_id = $campaign_item['campaign']['id'];
        $statistic->campaign_user_id = $campaign_item['campaign']['user_id'];
        $statistic->campaign_item_id = $campaign_item['id'];
        $statistic->ip = $cookie['ip'];
        $statistic->country = $country;
        $statistic->owner_earn = $owner_earn - $referral_earn;
        $statistic->publisher_earn = $publisher_earn;
        $statistic->referral_id = $referral_id;
        $statistic->referral_earn = $referral_earn;
        $statistic->referer_domain = (parse_url($data['ref'], PHP_URL_HOST) ?: 'Direct');
        $statistic->referer = $data['ref'];
        $statistic->user_agent = env('HTTP_USER_AGENT');
        $statistic->reason = 1;
        $this->Links->Statistics->save($statistic);

        /**
         * Update campaign item views and weight
         */
        $campaign_item_update = $CampaignItems->newEntity();
        $campaign_item_update->id = $campaign_item['id'];
        $campaign_item_update->views = $campaign_item['views'] + 1;
        $campaign_item_update->weight = (($campaign_item['views'] + 1) / ($campaign_item['purchase'] * 1000)) * 100;
        $CampaignItems->save($campaign_item_update);

        // Update link hits
        $this->updateLinkHits($link);
        $content = [
            'status' => 'success',
            'message' => 'Go With earning :)',
            'url' => $link->url
        ];
        return $content;
    }

    protected function addNormalStatisticEntry($link, $ad_type, $data, $ip, $reason = 0)
    {
        if (!$ip) {
            $ip = get_ip();
        }
        $country = $this->Links->Statistics->get_country($ip);

        $statistic = $this->Links->Statistics->newEntity();

        $statistic->link_id = $link->id;
        $statistic->user_id = $link->user_id;
        $statistic->ad_type = $ad_type;
        $statistic->campaign_id = $data['ci'];
        $statistic->campaign_user_id = $data['cui'];
        $statistic->campaign_item_id = $data['cii'];
        $statistic->ip = $ip;
        $statistic->country = $country;
        $statistic->owner_earn = 0;
        $statistic->publisher_earn = 0;
        $statistic->referer_domain = (parse_url($data['ref'], PHP_URL_HOST) ?: 'Direct');
        $statistic->referer = $data['ref'];
        $statistic->user_agent = env('HTTP_USER_AGENT');
        $statistic->reason = $reason;
        $this->Links->Statistics->save($statistic);
    }

    protected function setVisitorCookie()
    {
        $cookie = $this->Cookie->read('visitor');

        if (isset($cookie)) {
            return true;
        }

        $cookie_data = [
            'ip' => get_ip(),
            'date' => (new Time())->toDateTimeString()
        ];
        $this->Cookie->configKey('visitor', [
            'expires' => '+1 day',
            'httpOnly' => true
        ]);
        $this->Cookie->write('visitor', $cookie_data);

        return true;
    }

    protected function updateLinkHits($link = null)
    {
        if (!$link) {
            return;
        }
        $link->hits += 1;
        $link->modified = $link->modified;
        $this->Links->save($link);
        return;
    }

    protected function isProxy()
    {
        $db = new \IP2Proxy\Database();
        try {
            $db->open(
                CONFIG . 'binary/ip2proxy/IP2PROXY-LITE-PX1.BIN',
                \IP2Proxy\Database::FILE_IO,
                \IP2Proxy\Database::IS_PROXY
            );
            $isProxy = ($db->isProxy(get_ip()) == 1) ? true : false;
            $db->close();
            return $isProxy;
        } catch (\Exception $ex) {
        }
        return false;
    }
}
