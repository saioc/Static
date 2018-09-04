<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\I18n\I18n;

class FrontController extends AppController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);

        // Check if SSL is enabled.
        if ($this->setLanguage()) {
            $protocol = (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") ? "http://" : "https://";
            return $this->redirect($protocol . env('SERVER_NAME') . env('REQUEST_URI'), 301);
        }

        if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], get_site_languages(true))) {
            I18n::locale($_COOKIE['lang']);
        }
    }

    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
        $this->viewBuilder()->theme(get_option('theme', 'ClassicTheme'));
    }

    protected function setLanguage()
    {
        if (empty(get_option('site_languages'))) {
            return false;
        }
        if (isset($this->request->query['lang']) &&
            in_array($this->request->query['lang'], get_site_languages(true))
        ) {
            if (isset($_COOKIE['lang']) && $_COOKIE['lang'] == $this->request->query['lang']) {
                return false;
            }
            setcookie('lang', $this->request->query['lang'], time() + (86400 * 30 * 12), '/');
            return true;
        }

        if ((bool)get_option('language_auto_redirect', false)) {
            if (!isset($_COOKIE['lang']) && isset($this->request->acceptLanguage()[0])) {
                $lang = substr($this->request->acceptLanguage()[0], 0, 2);

                $langs = get_site_languages(true);

                $valid_langs = [];
                foreach ($langs as $key => $value) {
                    if (preg_match('/^' . $lang . '/', $value)) {
                        $valid_langs[] = $value;
                    }
                }

                if (isset($valid_langs[0])) {
                    setcookie('lang', $valid_langs[0], time() + (86400 * 30 * 12));
                    return true;
                }
            }
        }
        return false;
    }
}
