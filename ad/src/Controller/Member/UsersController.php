<?php

namespace App\Controller\Member;

use App\Controller\Member\AppMemberController;
use Cake\Mailer\MailerAwareTrait;
use Cake\I18n\Time;
use Cake\Network\Exception\NotFoundException;
use Cake\Cache\Cache;

class UsersController extends AppMemberController
{
    use MailerAwareTrait;

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

        if (($total_views = Cache::read('total_views_' . $this->Auth->user('id'), '15min')) === false) {
            $total_views = $this->Users->Statistics->find()
                ->where([
                    'Statistics.publisher_earn >' => 0,
                    'Statistics.user_id' => $this->Auth->user('id')
                ])
                ->count();
            Cache::write('total_views_' . $this->Auth->user('id'), $total_views, '15min');
        }
        $this->set('total_views', $total_views);

        if (($total_earnings = Cache::read('total_earnings_' . $this->Auth->user('id'), '15min')) === false) {
            $total_earnings = $this->Users->Statistics->find()
                ->select(['total' => 'SUM(Statistics.publisher_earn)'])
                ->where([
                    'Statistics.publisher_earn >' => 0,
                    'Statistics.user_id' => $this->Auth->user('id')
                ])
                ->first();
            Cache::write('total_earnings_' . $this->Auth->user('id'), $total_earnings, '15min');
        }
        $this->set('total_earnings', $total_earnings->total);

        if (($referral_earnings = Cache::read('referral_earnings_' . $this->Auth->user('id'), '15min')) === false) {
            $referral_earnings = $this->Users->Statistics->find()
                ->select(['total' => 'SUM(Statistics.referral_earn)'])
                ->where([
                    'Statistics.referral_earn >' => 0,
                    'Statistics.referral_id' => $this->Auth->user('id')
                ])
                ->first();
            Cache::write('referral_earnings_' . $this->Auth->user('id'), $referral_earnings, '15min');
        }
        $this->set('referral_earnings', $referral_earnings->total);

        ///////////////////////////

        $last_record = $this->Users->Statistics->find()
            ->select('created')
            ->where(['user_id' => $this->Auth->user('id')])
            ->order(['created' => 'DESC'])
            ->first();

        if (!$last_record) {
            $last_record = Time::now();
        } else {
            $last_record = $last_record->created;
        }

        $first_record = $this->Users->Statistics->find()
            ->select('created')
            ->where(['user_id' => $this->Auth->user('id')])
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

        $views_publisher = Cache::read('views_publisher_' . $this->Auth->user('id').'_'.$date1.'_'.$date2, '15min');
        if ($views_publisher === false) {
            $views_publisher = $this->Users->Statistics->find()
                ->select([
                    'day' => 'DATE_FORMAT(Statistics.created,"%d-%m-%Y")',
                    'count' => 'COUNT(Statistics.id)',
                    'publisher_earnings' => 'SUM(Statistics.publisher_earn)',
                ])
                ->where([
                    "Statistics.created BETWEEN :date1 AND :date2",
                    'Statistics.publisher_earn >' => 0,
                    'Statistics.user_id' => $this->Auth->user('id')
                ])
                ->order(['Statistics.id' => 'DESC'])
                ->bind(':date1', $date1, 'datetime')
                ->bind(':date2', $date2, 'datetime')
                ->group('day')
                ->toArray();
            Cache::write('views_publisher_' . $this->Auth->user('id').'_'.$date1.'_'.$date2, $views_publisher, '15min');
        }

        $views_referral = Cache::read('views_referral_' . $this->Auth->user('id').'_'.$date1.'_'.$date2, '15min');
        if ($views_referral === false) {
            $views_referral = $this->Users->Statistics->find()
                ->select([
                    'day' => 'DATE_FORMAT(Statistics.created,"%d-%m-%Y")',
                    'referral_earnings' => 'SUM(Statistics.referral_earn)',
                ])
                ->where([
                    "Statistics.created BETWEEN :date1 AND :date2",
                    'Statistics.referral_earn >' => 0,
                    'Statistics.referral_id' => $this->Auth->user('id')
                ])
                ->order(['Statistics.id' => 'DESC'])
                ->bind(':date1', $date1, 'datetime')
                ->bind(':date2', $date2, 'datetime')
                ->group('day')
                ->toArray();
            Cache::write('views_referral_' . $this->Auth->user('id').'_'.$date1.'_'.$date2, $views_referral, '15min');
        }

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
        foreach ($views_publisher as $view) {
            $day = Time::now()->modify($view->day)->format('j-n-Y');
            $CurrentMonthDays[$day]['view'] = $view->count;
            $CurrentMonthDays[$day]['publisher_earnings'] = $view->publisher_earnings;
        }
        unset($view);
        foreach ($views_referral as $view) {
            $day = Time::now()->modify($view->day)->format('j-n-Y');
            $CurrentMonthDays[$day]['referral_earnings'] = $view->referral_earnings;
        }
        unset($view);

        $this->set('CurrentMonthDays', $CurrentMonthDays);

        $popularLinks = Cache::read('popularLinks_' . $this->Auth->user('id').'_'.$date1.'_'.$date2, '15min');
        if ($popularLinks === false) {
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
                    'Statistics.publisher_earn >' => 0,
                    'Statistics.user_id' => $this->Auth->user('id')
                ])
                ->order(['views' => 'DESC'])
                ->bind(':date1', $date1, 'datetime')
                ->bind(':date2', $date2, 'datetime')
                ->limit(10)
                ->group('Statistics.link_id')
                ->toArray();
            Cache::write('popularLinks_' . $this->Auth->user('id').'_'.$date1.'_'.$date2, $popularLinks, '15min');
        }

        $this->set('popularLinks', $popularLinks);

        $this->loadModel('Announcements');

        $announcements = $this->Announcements->find()
            ->where(['Announcements.published' => 1])
            ->order(['Announcements.id DESC'])
            ->limit(3)
            ->toArray();
        $this->set('announcements', $announcements);
    }

    public function referrals()
    {
        $query = $this->Users->find()
            ->where(['referred_by' => $this->Auth->user('id')]);
        $referrals = $this->paginate($query);

        $this->set('referrals', $referrals);
    }

    public function profile()
    {
        $user = $this->Users->find()->contain(['Plans'])->where(['Users.id' => $this->Auth->user('id')])->first();

        if ($this->request->is(['post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->data);
            //debug($user->errors());
            if ($this->Users->save($user)) {
                if ($this->Auth->user('id') === $user->id) {
                    $data = $user->toArray();
                    unset($data['password']);

                    $this->Auth->setUser($data);
                }
                $this->Flash->success(__('Profile has been updated'));
                $this->redirect(['action' => 'profile']);
            } else {
                $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
            }
        }
        unset($user->password);
        $this->set('user', $user);
    }

    public function plans()
    {
        if ((bool)get_option('enable_premium_membership') === false) {
            throw new NotFoundException(__('Invalid request'));
        }

        $user = $this->Users->findById($this->Auth->user('id'))->contain(['Plans'])->first();
        $this->set('user', $user);

        $plans = $this->Users->plans->find()->where(['enable' => 1]);
        $this->set('plans', $plans);
    }

    public function payPlan($id = null, $period = null)
    {
        if ((bool)get_option('enable_premium_membership') === false) {
            throw new NotFoundException(__('Invalid request'));
        }

        $this->request->allowMethod(['post']);

        if (!$id || !$period) {
            throw new NotFoundException(__('Invalid request'));
        }

        $plan = $this->Users->Plans->findById($id)->first();

        $amount = $plan->yearly_price;
        $period_name = __("Yearly");
        if ($period === 'm') {
            $amount = $plan->monthly_price;
            $period_name = __("Monthly");
        }

        $data = [
            'status' => 2, //Unpaid Invoice
            'user_id' => $this->Auth->user('id'),
            'description' => __("{0} Premium Membership: {1}", [$period_name, $plan->title]),
            'type' => 1, //Plan Invoice
            'rel_id' => $plan->id, //Plan Id
            'payment_method' => '',
            'amount' => $amount,
            'data' => serialize([
                'payment_period' => $period
            ]),
        ];

        $invoice = $this->Users->Invoices->newEntity($data);

        if ($this->Users->Invoices->save($invoice)) {
            $this->Flash->success(__('An invoice with id: {0} has been generated.', $invoice->id));
            return $this->redirect(['controller' => 'Invoices', 'action' => 'view', $invoice->id]);
        }
    }

    public function changeEmail($username = null, $key = null)
    {
        if (!$username && !$key) {
            $user = $this->Users->findById($this->Auth->user('id'))->first();

            if ($this->request->is(['post', 'put'])) {
                $uuid = \Cake\Utility\Text::uuid();

                $user->activation_key = \Cake\Utility\Security::hash($uuid, 'sha1', true);

                $user = $this->Users->patchEntity($user, $this->request->data, ['validate' => 'changEemail']);

                if ($this->Users->save($user)) {
                    // Send rest email
                    $this->getMailer('User')->send('changeEmail', [$user]);

                    $this->Flash->success(__('Kindly check your email to confirm it.'));

                    $this->redirect(['action' => 'changeEmail']);
                } else {
                    $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
                }
            }
            $this->set('user', $user);
        } else {
            $user = $this->Users->find('all')
                ->contain(['Plans'])
                ->where([
                    'Users.status' => 1,
                    'Users.username' => $username,
                    'Users.activation_key' => $key
                ])
                ->first();

            if (!$user) {
                $this->Flash->error(__('Invalid Activation.'));
                return $this->redirect(['action' => 'changeEmail']);
            }

            $user->email = $user->temp_email;
            $user->temp_email = '';
            $user->activation_key = '';

            if ($this->Users->save($user)) {
                if ($this->Auth->user('id') === $user->id) {
                    $data = $user->toArray();
                    unset($data['password']);

                    $this->Auth->setUser($data);
                }
                $this->Flash->success(__('Your email has been confirmed.'));
                return $this->redirect(['action' => 'signin', 'prefix' => 'auth']);
            } else {
                $this->Flash->error(__('Unable to confirm your email.'));
                return $this->redirect(['action' => 'changeEmail']);
            }
        }
    }

    public function changePassword()
    {
        $user = $this->Users->findById($this->Auth->user('id'))->first();

        if ($this->request->is(['post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->data, ['validate' => 'changePassword']);
            //debug($user->errors());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('Password has been updated'));
                $this->redirect(['action' => 'changePassword']);
            } else {
                $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
            }
        }
        unset($user->password);
        $this->set('user', $user);
    }
}
