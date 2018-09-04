<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;
use Cake\Network\Exception\NotFoundException;

class CampaignsController extends AppAdminController
{
    public function index()
    {
        $conditions = [];

        $filter_fields = ['id', 'user_id', 'status', 'ad_type', 'name', 'other_fields'];

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
                    } elseif (in_array($param_name, ['id', 'user_id', 'status', 'ad_type'])) {
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
            ->contain(['Users', 'CampaignItems'])
            ->where($conditions);
        $campaigns = $this->paginate($query);

        $this->set('campaigns', $campaigns);
    }

    public function view($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }

        $campaign = $this->Campaigns->findById($id)
            ->contain(['CampaignItems'])
            ->first();

        if (!$campaign) {
            throw new NotFoundException(__('Campaign Not Found'));
        }

        $this->set('campaign', $campaign);

        $this->loadModel('Statistics');

        $campaign_earnings = $this->Statistics->find()
            ->select([
                'reason',
                'count' => 'COUNT(reason)',
                'earnings' => 'SUM(publisher_earn)',
            ])
            ->where([
                'campaign_id' => $campaign->id
            ])
            ->order(['count' => 'DESC'])
            ->group(['reason'])
            ->toArray();

        $this->set('campaign_earnings', $campaign_earnings);

        $campaign_countries = $this->Statistics->find()
            ->select([
                'country',
                'count' => 'COUNT(country)',
                'earnings' => 'SUM(publisher_earn)',
            ])
            ->where([
                'campaign_id' => $campaign->id
            ])
            ->order(['count' => 'DESC'])
            ->group(['country'])
            ->toArray();

        $this->set('campaign_countries', $campaign_countries);

        $campaign_referers = $this->Statistics->find()
            ->select([
                'referer_domain',
                'count' => 'COUNT(referer_domain)',
                'earnings' => 'SUM(publisher_earn)',
            ])
            ->where([
                'campaign_id' => $campaign->id
            ])
            ->order(['count' => 'DESC'])
            ->group(['referer_domain'])
            ->toArray();

        $this->set('campaign_referers', $campaign_referers);

        /*
        $campaign_statistics = $this->Statistics->find()
            ->select([
                'reason',
                'reason_count' => 'COUNT(reason)',
                'earnings' => 'SUM(publisher_earn)',
            ])
            ->where([
                'campaign_id' => $campaign->id
            ])
            ->group(['reason'])
            ->toArray();

        $this->set('campaign_statistics', $campaign_statistics);
        */
    }

    public function createInterstitial()
    {
        if ($this->request->is(['get']) && empty($this->request->query['traffic_source'])) {
            return;
        }

        $campaign = $this->Campaigns->newEntity(null, ['associated' => ['CampaignItems']]);
        $this->set('campaign', $campaign);

        $users = $this->Campaigns->Users->find('list', [
            'keyField' => 'id',
            'valueField' => 'username'
        ]);
        $this->set('users', $users);

        if ($this->request->is('post')) {
            $campaign->ad_type = 1;

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
                $this->Flash->success(__('Your campaign has been created.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('Unable to create your campaign.'));
            }
        }
        $this->set('campaign', $campaign);
    }

    public function createBanner()
    {
        if ($this->request->is(['get']) && empty($this->request->query['traffic_source'])) {
            return;
        }

        $campaign = $this->Campaigns->newEntity(null, ['associated' => ['CampaignItems']]);
        $this->set('campaign', $campaign);

        $users = $this->Campaigns->Users->find('list', [
            'keyField' => 'id',
            'valueField' => 'username'
        ]);
        $this->set('users', $users);

        if ($this->request->is('post')) {
            $campaign->ad_type = 2;

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
                $this->Flash->success(__('Your campaign has been created.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('Unable to create your campaign.'));
            }
        }
        $this->set('campaign', $campaign);
    }

    public function createPopup()
    {
        if ($this->request->is(['get']) && empty($this->request->query['traffic_source'])) {
            return;
        }

        $campaign = $this->Campaigns->newEntity(null, ['associated' => ['CampaignItems']]);
        $this->set('campaign', $campaign);

        $users = $this->Campaigns->Users->find('list', [
            'keyField' => 'id',
            'valueField' => 'username'
        ]);
        $this->set('users', $users);

        if ($this->request->is('post')) {
            $campaign->ad_type = 3;

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
                $this->Flash->success(__('Your campaign has been created.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('Unable to create your campaign.'));
            }
        }
        $this->set('campaign', $campaign);
    }

    public function edit($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }

        $campaign = $this->Campaigns->find()
            ->where(['Campaigns.id' => $id])
            ->contain(['CampaignItems'])
            ->first();

        if (!$campaign) {
            throw new NotFoundException(__('Invalid campaign'));
        }

        $users = $this->Campaigns->Users->find('list', [
            'keyField' => 'id',
            'valueField' => 'username'
        ]);

        if ($this->request->is(['post', 'put'])) {

            /*
            $this->request->data['price'] = 0;

            foreach ($this->request->data['campaign_items'] as $key => $value) {
                $this->request->data['price'] += $value['purchase'] * $value['advertiser_price'];
            }
            */

            $this->Campaigns->patchEntity($campaign, $this->request->data);
            if ($this->Campaigns->save($campaign)) {
                $this->Flash->success(__('Campaign has been updated.'));
                return $this->redirect(['action' => 'edit', $id]);
            } else {
                $this->Flash->error(__('Unable to update campaign.'));
            }
        }

        $this->set('campaign', $campaign);
        $this->set('users', $users);
    }

    public function pause($id)
    {
        $this->request->allowMethod(['post', 'put']);

        $campaign = $this->Campaigns->findById($id)
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
        $this->request->allowMethod(['post', 'put']);

        $campaign = $this->Campaigns->findById($id)
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
