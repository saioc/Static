<?php

namespace App\Controller\Member;

use App\Controller\Member\AppMemberController;
use Cake\Event\Event;
use Cake\Routing\Router;
use Cake\Network\Exception\NotFoundException;

class CampaignsController extends AppMemberController
{
    public function initialize()
    {
        parent::initialize();
    }

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
        return $this->redirect('/payment/ipn', 307);
    }

    protected function checkEnableAdvertising()
    {
        if (get_option('enable_advertising', 'yes') == 'no') {
            $this->Flash->error(__('Creating campaigns is currently disabled.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }
    }

    public function view($id = null)
    {
        $this->checkEnableAdvertising();

        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }

        $campaign = $this->Campaigns->findById($id)
            ->contain(['CampaignItems'])
            ->where(['user_id' => $this->Auth->user('id')])
            ->first();
        if (!$campaign) {
            throw new NotFoundException(__('Campaign Not Found'));
        }

        $this->set('campaign', $campaign);
    }

    public function index()
    {
        $this->checkEnableAdvertising();

        $conditions = [];

        $filter_fields = ['id', 'status', 'ad_type', 'name', 'other_fields'];

        //Transform POST into GET
        if ($this->request->is(['post', 'put']) && isset($this->request->data['Filter'])) {
            $filter_url = [];

            $filter_url['controller'] = $this->request->params['controller'];

            $filter_url['action'] = $this->request->params['action'];

            // We need to overwrite the page every time we change the parameters
            $filter_url['page'] = 1;

            // for each filter we will add a GET parameter for the generated url
            foreach ($this->request->data['Filter'] as $name => $value) {
                if (in_array($name, $filter_fields) && $value) {
                    // You might want to sanitize the $value here
                    // or even do a urlencode to be sure
                    $filter_url[$name] = urlencode($value);
                }
            }
            // now that we have generated an url with GET parameters,
            // we'll redirect to that page
            return $this->redirect($filter_url);
        } else {
            // Inspect all the named parameters to apply the filters
            foreach ($this->request->query as $param_name => $value) {
                $value = urldecode($value);
                if (in_array($param_name, $filter_fields)) {
                    if (in_array($param_name, ['name'])) {
                        $conditions[] = [
                            ['Campaigns.' . $param_name . ' LIKE' => '%' . $value . '%']
                        ];
                    } elseif (in_array($param_name, ['other_fields'])) {
                        $conditions['OR'] = [
                            ['Campaigns.website_title LIKE' => '%' . $value . '%'],
                            ['Campaigns.website_url LIKE' => '%' . $value . '%'],
                            ['Campaigns.banner_name LIKE' => '%' . $value . '%'],
                            ['Campaigns.banner_size LIKE' => '%' . $value . '%']
                        ];
                    } elseif (in_array($param_name, ['id', 'status', 'ad_type'])) {
                        if ($param_name == 'status' && !in_array($value, [1, 2, 3, 4, 5, 6, 7, 8])) {
                            continue;
                        }
                        if ($param_name == 'ad_type' && !in_array($value, [1, 2, 3])) {
                            continue;
                        }
                        $conditions['Campaigns.' . $param_name] = $value;
                    }
                    $this->request->data['Filter'][$param_name] = $value;
                }
            }
        }

        $query = $this->Campaigns->find()
            ->contain(['CampaignItems'])
            ->where(['user_id' => $this->Auth->user('id')])
            ->where($conditions);
        $campaigns = $this->paginate($query);

        $this->set('campaigns', $campaigns);
    }

    public function pay($id = null)
    {
        $this->request->allowMethod(['post']);

        if (!$id) {
            throw new NotFoundException(__('Invalid request'));
        }

        $campaign = $this->Campaigns->findById($id)->contain('Invoices')
            ->where(['Campaigns.user_id' => $this->Auth->user('id')])
            ->first();

        if (!$campaign) {
            throw new NotFoundException(__('Not found campaign'));
        }

        if (isset($campaign->invoice->id)) {
            return $this->redirect(['controller' => 'Invoices', 'action' => 'view', $campaign->invoice->id]);
        }

        $this->loadModel('Invoices');

        $data = [
            'status' => 2, //Unpaid Invoice
            'user_id' => $this->Auth->user('id'),
            'description' => __("Advertising Campaign #").$campaign->id,
            'type' => 2, //Campaign Invoice
            'rel_id' => $campaign->id, //Plan Id
            'payment_method' => '',
            'amount' => $campaign->price,
            'data' => serialize([])
        ];

        $invoice = $this->Invoices->newEntity($data);

        if ($this->Invoices->save($invoice)) {
            //$this->Flash->success(__('An invoice with id: {0} has been generated.', $invoice->id));
            return $this->redirect(['controller' => 'Invoices', 'action' => 'view', $invoice->id]);
        }
    }

    public function createInterstitial()
    {
        $this->checkEnableAdvertising();

        if (get_option('enable_interstitial', 'yes') == 'no') {
            $this->Flash->error(__('Creating interstitial campaigns is currently disabled.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }

        if ($this->request->is(['get']) && empty($this->request->query['traffic_source'])) {
            return;
        }

        $campaign = $this->Campaigns->newEntity(null, ['associated' => ['CampaignItems']]);
        $this->set('campaign', $campaign);

        if ($this->request->is('post')) {
            $campaign->user_id = $this->Auth->user('id');
            $campaign->ad_type = 1;
            $campaign->status = 6;

            $this->request->data['price'] = 0;

            foreach ($this->request->data['campaign_items'] as $key => $value) {
                if (empty($value['purchase'])) {
                    unset($this->request->data['campaign_items'][$key]);
                    continue;
                }
                $this->request->data['price'] += $value['purchase'] * $value['advertiser_price'];
            }

            if (count($this->request->data['campaign_items']) == 0) {
                return $this->Flash->error(__('You must purchase at least from one country.'));
            }

            $campaign = $this->Campaigns->patchEntity($campaign, $this->request->data);

            if ($this->Campaigns->save($campaign)) {
                $this->Flash->success(__('Your campaign has been added. After paying, we will review it and it will appear on the website.'));
                return $this->redirect(['action' => 'view', $campaign->id]);
            } else {
                //debug($campaign->errors());
                $this->Flash->error(__('Unable to create your campaign.'));
            }
        }
        $this->set('campaign', $campaign);
    }

    public function createBanner()
    {
        $this->checkEnableAdvertising();

        if (get_option('enable_banner', 'yes') == 'no') {
            $this->Flash->error(__('Creating banner campaigns is currently disabled.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }

        if ($this->request->is(['get']) && empty($this->request->query['traffic_source'])) {
            return;
        }

        $campaign = $this->Campaigns->newEntity(null, ['associated' => ['CampaignItems']]);
        $this->set('campaign', $campaign);

        if ($this->request->is('post')) {
            $campaign->user_id = $this->Auth->user('id');
            $campaign->ad_type = 2;
            $campaign->status = 6;

            $this->request->data['price'] = 0;

            foreach ($this->request->data['campaign_items'] as $key => $value) {
                if (empty($value['purchase'])) {
                    unset($this->request->data['campaign_items'][$key]);
                    continue;
                }
                $this->request->data['price'] += $value['purchase'] * $value['advertiser_price'];
            }

            if (count($this->request->data['campaign_items']) == 0) {
                return $this->Flash->error(__('You must purchase at least from one country.'));
            }

            $campaign = $this->Campaigns->patchEntity($campaign, $this->request->data);

            if ($this->Campaigns->save($campaign)) {
                $this->Flash->success(__('Your campaign has been added. After paying, we will review it and it will appear on the website.'));
                return $this->redirect(['action' => 'view', $campaign->id]);
            } else {
                $this->Flash->error(__('Unable to create your campaign.'));
            }
        }
        $this->set('campaign', $campaign);
    }

    public function createPopup()
    {
        $this->checkEnableAdvertising();

        if (get_option('enable_popup', 'yes') == 'no') {
            $this->Flash->error(__('Creating popup campaigns is currently disabled.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'dashboard']);
        }

        if ($this->request->is(['get']) && empty($this->request->query['traffic_source'])) {
            return;
        }

        $campaign = $this->Campaigns->newEntity(null, ['associated' => ['CampaignItems']]);
        $this->set('campaign', $campaign);

        if ($this->request->is('post')) {
            $campaign->user_id = $this->Auth->user('id');
            $campaign->ad_type = 3;
            $campaign->status = 6;

            $this->request->data['price'] = 0;

            foreach ($this->request->data['campaign_items'] as $key => $value) {
                if (empty($value['purchase'])) {
                    unset($this->request->data['campaign_items'][$key]);
                    continue;
                }
                $this->request->data['price'] += $value['purchase'] * $value['advertiser_price'];
            }

            if (count($this->request->data['campaign_items']) == 0) {
                return $this->Flash->error(__('You must purchase at least from one country.'));
            }

            $campaign = $this->Campaigns->patchEntity($campaign, $this->request->data);

            if ($this->Campaigns->save($campaign)) {
                $this->Flash->success(__('Your campaign has been added. After paying, we will review it and it will appear on the website.'));
                return $this->redirect(['action' => 'view', $campaign->id]);
            } else {
                //debug($campaign->errors());
                $this->Flash->error(__('Unable to create your campaign.'));
            }
        }
        $this->set('campaign', $campaign);
    }

    public function pause($id)
    {
        $this->checkEnableAdvertising();

        $this->request->allowMethod(['post', 'put']);

        $campaign = $this->Campaigns->findById($id)
            ->where(['user_id' => $this->Auth->user('id')])
            ->where(['status' => 1])
            ->first();

        if (!$campaign) {
            $this->Flash->success(__('Campaign not found'));
            return $this->redirect(['action' => 'index']);
        }

        $campaign->status = 2;
        $this->Campaigns->save($campaign);

        return $this->redirect(['action' => 'index']);
    }

    public function resume($id)
    {
        $this->checkEnableAdvertising();

        $this->request->allowMethod(['post', 'put']);

        $campaign = $this->Campaigns->findById($id)
            ->where(['user_id' => $this->Auth->user('id')])
            ->where(['status' => 2])
            ->first();

        if (!$campaign) {
            $this->Flash->success(__('Campaign not found'));
            return $this->redirect(['action' => 'index']);
        }

        $campaign->status = 1;
        $this->Campaigns->save($campaign);

        return $this->redirect(['action' => 'index']);
    }
}
