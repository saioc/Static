<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Migrations\Migrations;
use Cake\ORM\TableRegistry;

class InstallController extends AppController
{
    public function initialize()
    {
        parent::initialize();
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        $this->Auth->allow();

        //$this->Security->config('unlockedActions', ['index', 'database', 'data', 'adminuser', 'finish']);
        $this->eventManager()->off($this->Csrf);
        $this->eventManager()->off($this->Security);

        $this->viewBuilder()->layout('install');
    }

    protected function check()
    {
        if (is_app_installed()) {
            //$this->Session->setFlash( 'Already Installed' );
            return $this->redirect('/');
        }
    }

    public function index()
    {
        $this->check();
    }

    public function database()
    {
        $this->check();

        if (!empty($this->request->data)) {
            try {
                $host = $this->request->data['host'];
                $username = $this->request->data['username'];
                $password = $this->request->data['password'];
                $database = $this->request->data['database'];

                $conn = new \PDO("mysql:host=$host;dbname=$database", $username, $password);
                // set the PDO error mode to exception
                $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $conn = null;
            } catch (\PDOException $e) {
                return $this->Flash->error(__("Connection failed: ") . $e->getMessage());
            }

            $result = $this->createConfigureFile($this->request->data);

            if ($result !== true) {
                $this->Flash->error($result);
            } else {
                return $this->redirect(array('action' => 'data'));
            }
        }
    }

    public function data()
    {
        $this->check();

        if (isset($this->request->query['run'])) {
            set_time_limit(10 * MINUTE);

            try {
                $migrations = new Migrations();
                $result = $migrations->migrate();
            } catch (\Exception $ex) {
                $result = __('Can not load initial data. ') . $ex->getMessage();
            }

            if ($result !== true) {
                return $this->Flash->error($result);
            }

            return $this->redirect(array('action' => 'adminuser'));
        }
    }

    public function adminuser()
    {
        $this->check();

        $this->loadModel('Users');

        $user = $this->Users->newEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->data);

            $user->role = 'admin';
            $user->status = 1;

            $user->api_token = \Cake\Utility\Security::hash(\Cake\Utility\Text::uuid(), 'sha1', true);
            $user->activation_key = '';


            if ($this->Users->save($user)) {
                return $this->redirect(array('action' => 'finish'));
            }
            $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
        }
        $this->set('user', $user);
    }

    public function finish()
    {
        $this->check();

        $Options = TableRegistry::get('Options');
        $Options->updateAll(['value' => 1], ['name' => 'installed']);

        Configure::write('Adlinkfly.installed', 1);
        Configure::dump('app_vars', 'default', ['Adlinkfly']);
    }

    protected function createConfigureFile($data)
    {
        $config = array(
            'host' => 'localhost',
            'username' => 'root',
            'password' => '',
            'database' => ''
        );

        foreach ($data as $key => $value) {
            if (isset($data[$key])) {
                $config[$key] = $value;
            }
        }


        $result = copy(CONFIG . 'configure.install.php', CONFIG . 'configure.php');
        if (!$result) {
            return __('Could not copy configure.php file.');
        }

        $file = new File(CONFIG . 'configure.php');
        $content = $file->read();

        foreach ($config as $configKey => $configValue) {
            $content = str_replace('{default_' . $configKey . '}', $configValue, $content);
        }

        $content = str_replace('__SALT__', generate_random_string(50), $content);

        if (!$file->write($content)) {
            return __('Could not write configure.php file.');
        }

        return true;
    }
}
