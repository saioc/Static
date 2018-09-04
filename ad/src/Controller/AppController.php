<?php

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Security');
        $this->loadComponent('Csrf');
        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
            'loginAction' => [
                'plugin' => false,
                'controller' => 'Users',
                'action' => 'signin',
                'prefix' => 'auth'
            ],
            'authenticate' => [
                'Form' => [
                    'finder' => 'auth'
                ],
                'ADmad/HybridAuth.HybridAuth' => [
                    // All keys shown below are defaults
                    'fields' => [
                        'provider' => 'provider',
                        'openid_identifier' => 'openid_identifier',
                        'email' => 'email'
                    ],
                    'profileModel' => 'ADmad/HybridAuth.SocialProfiles',
                    'profileModelFkField' => 'user_id',
                    'userModel' => 'Users',
                    'finder' => 'social',
                    // The URL Hybridauth lib should redirect to after authentication.
                    // If no value is specified you are redirect to this plugin's
                    // HybridAuthController::authenticated() which handles persisting
                    // user info to AuthComponent and redirection.
                    'hauth_return_to' => null
                ]
            ],
            'authorize' => 'Controller',
            'loginRedirect' => [
                'plugin' => false,
                'controller' => 'Users',
                'action' => 'dashboard',
                'prefix' => 'member'
            ],
            'logoutRedirect' => [
                'plugin' => false,
                'controller' => 'Users',
                'action' => 'signin',
                'prefix' => 'auth'
            ]
        ]);
        $this->loadComponent('Paginator');
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        // Check if SSL is enabled.
        if ($this->forceSSL()) {
            return $this->redirect('https://' . env('SERVER_NAME') . env('REQUEST_URI'), 301);
        }

        // Check if you are on the main domain
        if ($this->redirectMainDomain()) {
            $protocol = (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") ? "http://" : "https://";
            return $this->redirect($protocol . get_option('main_domain') . env('REQUEST_URI'), 301);
        }

        // Set the frontend layout
        $this->viewBuilder()->layout('front');
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Network\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }

    protected function forceSSL()
    {
        if ((bool)get_option('ssl_enable', false)) {
            $controller = $this->request->params['controller'];
            $action = $this->request->params['action'];

            if (!(
                (in_array($controller, ['Links']) && in_array($action, ['view', 'go', 'popad'])) ||
                (in_array($controller, ['Tools']) && in_array($action, ['st', 'api', 'full'])) ||
                (in_array($controller, ['Invoices']) && in_array($action, ['ipn'])) ||
                (in_array($controller, ['Users']) && in_array($action, ['multidomainsAuth']))
            )
            ) {
                if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
                    return true;
                }
            }
        }
        return false;
    }

    protected function redirectMainDomain()
    {
        $main_domain = get_option('main_domain');

        if (empty($main_domain)) {
            return false;
        }

        $controller = $this->request->params['controller'];
        $action = $this->request->params['action'];

        if (!(in_array($controller, ['Links', 'Invoices', 'Users']) &&
            in_array($action, ['st', 'api', 'view', 'go', 'popad', 'ipn', 'multidomainsAuth']))
        ) {
            if (env("HTTP_HOST", "") != $main_domain) {
                return true;
            }
        }
        return false;
    }
}
