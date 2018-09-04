<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;
use Cake\Network\Exception\NotFoundException;
use Cake\I18n\Time;
use Cake\Cache\Cache;

class UsersController extends AppAdminController
{
    public function dashboard()
    {
        $domains_auth_urls = [];
        $multi_domains = get_all_multi_domains_list();
        $main_domain = get_option('main_domain', '');
        unset($multi_domains[$main_domain]);

        if (isset($_SESSION['Auth']['User']['domains_auth']) &&
            $_SESSION['Auth']['User']['domains_auth'] == 'required' &&
            count($multi_domains)
        ) {
            $data = urlencode(data_encrypt([
                'session_name' => session_name(),
                'session_id' => session_id(),
                'time' => time()
            ]));

            foreach ($multi_domains as $key => $value) {
                $domains_auth_urls[] = '//' . $value . $this->request->base . '/auth/users/multidomains-auth' .
                    '?auth=' . $data;
            }
        }
        $this->set('domains_auth_urls', $domains_auth_urls);

        if (($owner_earnings = Cache::read('owner_earnings', '5min')) === false) {
            $owner_earnings = $this->Users->Statistics->find()
                ->select(['total' => 'SUM(Statistics.owner_earn)'])
                ->where(['Statistics.owner_earn >' => 0])
                ->first();
            Cache::write('owner_earnings', $owner_earnings, '5min');
        }
        $this->set('owner_earnings', $owner_earnings->total);

        if (($publisher_earnings = Cache::read('publisher_earnings', '5min')) === false) {
            $publisher_earnings = $this->Users->Statistics->find()
                ->select(['total' => 'SUM(Statistics.publisher_earn)'])
                ->where(['Statistics.publisher_earn >' => 0])
                ->first();
            Cache::write('publisher_earnings', $publisher_earnings, '5min');
        }
        $this->set('publisher_earnings', $publisher_earnings->total);

        if (($referral_earnings = Cache::read('referral_earnings', '5min')) === false) {
            $referral_earnings = $this->Users->Statistics->find()
                ->select(['total' => 'SUM(Statistics.referral_earn)'])
                ->where(['Statistics.referral_earn >' => 0])
                ->first();
            Cache::write('referral_earnings', $referral_earnings, '5min');
        }
        $this->set('referral_earnings', $referral_earnings->total);

        if (($total_views = Cache::read('total_views', '5min')) === false) {
            $total_views = $this->Users->Statistics->find()
                ->where(['Statistics.publisher_earn >' => 0])
                ->count();
            Cache::write('total_views', $total_views, '5min');
        }
        $this->set('total_views', $total_views);

        ///////////////////////////

        $last_record = $this->Users->Statistics->find()
            ->select('created')
            ->order(['created' => 'DESC'])
            ->first();

        if (!$last_record) {
            $last_record = Time::now();
        } else {
            $last_record = $last_record->created;
        }

        $first_record = $this->Users->Statistics->find()
            ->select('created')
            ->order(['created' => 'ASC'])
            ->first();

        if (!$first_record) {
            $first_record = Time::now()->modify('-1 second');
        } else {
            $first_record = $first_record->created;
        }

        $year_month = [];

        $last_month = Time::now()->setDate($last_record->year, $last_record->month, 01);
        $first_month = Time::now()->setDate($first_record->year, $first_record->month, 01);

        while ($first_month <= $last_month) {
            $year_month[$last_month->format('Y-m')] = $last_month->format('F Y');

            $last_month->modify('-1 month');
        }

        $this->set('year_month', $year_month);

        $to_month = Time::now()->format('Y-m');
        if (isset($this->request->query['month']) &&
            array_key_exists($this->request->query['month'], $year_month)
        ) {
            $to_month = explode('-', $this->request->query['month']);
            $year = (int)$to_month[0];
            $month = (int)$to_month[1];
        } else {
            $time = new Time($to_month);
            $current_time = $time->startOfMonth();

            $year = (int)$current_time->format('Y');
            $month = (int)$current_time->format('m');
        }

        $date1 = Time::now()->year($year)->month($month)->startOfMonth()->format('Y-m-d H:i:s');
        $date2 = Time::now()->year($year)->month($month)->endOfMonth()->format('Y-m-d H:i:s');

        if (($views = Cache::read('views_' . $date1 . '_' . $date2, '5min')) === false) {
            $views = $this->Users->Statistics->find()
                ->select([
                    'day' => 'DATE_FORMAT(Statistics.created,"%d-%m-%Y")',
                    'count' => 'COUNT(Statistics.created)',
                    'publisher_earnings' => 'SUM(Statistics.publisher_earn)',
                    'referral_earnings' => 'SUM(Statistics.referral_earn)',
                ])
                ->where([
                    "Statistics.created BETWEEN :date1 AND :date2",
                    'Statistics.publisher_earn >' => 0,
                ])
                ->order(['Statistics.created' => 'DESC'])
                ->bind(':date1', $date1, 'datetime')
                ->bind(':date2', $date2, 'datetime')
                ->group('day')
                ->toArray();
            Cache::write('views_' . $date1 . '_' . $date2, $views, '5min');
        }
        $this->set('views', $views);

        $CurrentMonthDays = [];

        $targetTime = Time::now();
        $targetTime->year($year)
            ->month($month)
            ->day(1);

        for ($i = 1; $i <= $targetTime->format('t'); $i++) {
            $CurrentMonthDays[$i . "-" . $month . "-" . $year] = [
                'view' => 0,
                'publisher_earnings' => 0,
                'referral_earnings' => 0,
            ];
        }
        foreach ($views as $view) {
            $day = Time::now()->modify($view->day)->format('j-n-Y');
            $CurrentMonthDays[$day]['view'] = $view->count;
            $CurrentMonthDays[$day]['publisher_earnings'] = $view->publisher_earnings;
            $CurrentMonthDays[$day]['referral_earnings'] = $view->referral_earnings;
        }
        $this->set('CurrentMonthDays', $CurrentMonthDays);

        if (($popularLinks = Cache::read('popularLinks_' . $date1 . '_' . $date2, '5min')) === false) {
            $popularLinks = $this->Users->Statistics->find()
                ->contain(['Links'])
                ->select([
                    'Links.alias',
                    'Links.url',
                    'Links.title',
                    'Links.domain',
                    'Links.created',
                    'views' => 'COUNT(Statistics.link_id)',
                    'publisher_earnings' => 'SUM(Statistics.publisher_earn)'
                ])
                ->where([
                    "Statistics.created BETWEEN :date1 AND :date2",
                    'Statistics.publisher_earn >' => 0
                ])
                ->order(['views' => 'DESC'])
                ->bind(':date1', $date1, 'datetime')
                ->bind(':date2', $date2, 'datetime')
                ->limit(10)
                ->group('Statistics.link_id')
                ->toArray();
            Cache::write('popularLinks_' . $date1 . '_' . $date2, $popularLinks, '5min');
        }

        $this->set('popularLinks', $popularLinks);
    }

    public function index()
    {
        $conditions = [];

        $filter_fields = ['id', 'status', 'username', 'email', 'country', 'other_fields'];

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
                    if (in_array($param_name, ['username', 'email'])) {
                        $conditions[] = [
                            ['Users.' . $param_name . ' LIKE' => '%' . $value . '%']
                        ];
                    } elseif (in_array($param_name, ['other_fields'])) {
                        $conditions['OR'] = [
                            ['Users.first_name LIKE' => '%' . $value . '%'],
                            ['Users.last_name LIKE' => '%' . $value . '%'],
                            ['Users.address1 LIKE' => '%' . $value . '%']
                        ];
                    } elseif (in_array($param_name, ['id', 'status', 'country'])) {
                        if ($param_name == 'status' && !in_array($value, [1, 2, 3])) {
                            continue;
                        }
                        $conditions['Users.' . $param_name] = $value;
                    }
                    $this->request->data['Filter'][$param_name] = $value;
                }
            }
        }

        $query = $this->Users->find()
            ->where($conditions)
            ->where(['Users.username <>' => 'anonymous']);
        $users = $this->paginate($query);
        $this->set('users', $users);
    }

    public function referrals()
    {
        $query = $this->Users->find()->where(['Users.referred_by >' => 0]);
        $referrals = $this->paginate($query);

        foreach ($referrals as $referral) {
            $referral->referred_by_username = $this->Users->get($referral->referred_by)->username;
        }

        $this->set('referrals', $referrals);
    }

    public function view($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid User'));
        }

        $user = $this->Users->findById($id)->first();
        if (!$user) {
            throw new NotFoundException(__('Invalid User'));
        }
        $this->set('user', $user);
    }

    public function add()
    {
        $user = $this->Users->newEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->data);

            $user->api_token = \Cake\Utility\Security::hash(\Cake\Utility\Text::uuid(), 'sha1', true);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been added.'));
                return $this->redirect(['action' => 'view', $user->id]);
            }
            $this->Flash->error(__('Unable to add the user.'));
        }
        $this->set('user', $user);
    }

    public function edit($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid User'));
        }

        $user = $this->Users->findById($id)->where(['Users.username <>' => 'anonymous'])->first();
        if (!$user) {
            throw new NotFoundException(__('Invalid User'));
        }

        $plans = $this->Users->Plans->find('list', [
            'keyField' => 'id',
            'valueField' => 'title'
        ])
            ->where(['enable' => 1]);

        $this->set('plans', $plans);

        if ($this->request->is(['post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->data);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Unable to edit user.'));
        }
        $this->set('user', $user);
    }

    public function deactivate($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $user = $this->Users->findById($id)->where(['Users.username <>' => 'anonymous'])->first();

        $user->status = 3;

        if ($this->Users->save($user)) {
            $this->Flash->success(__('The Link with id: {0} has been deactivated.', $user->id));
            return $this->redirect(['action' => 'index']);
        }
    }
}
