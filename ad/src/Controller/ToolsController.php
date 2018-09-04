<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

class ToolsController extends AppController
{
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(['st', 'full', 'api']);
    }

    public function st()
    {
        $this->autoRender = false;

        $this->loadModel('Links');

        $message = '';

        if (!isset($this->request->query) ||
            !isset($this->request->query['api']) ||
            !isset($this->request->query['url'])
        ) {
            $message = __('Invalid Request.');
            $this->set('message', $message);
            return null;
        }

        $api = $this->request->query['api'];
        $url = urldecode($this->request->query['url']);

        $ad_type = get_option('member_default_advert', 1);
        if (isset($this->request->query['type'])) {
            if (array_key_exists($this->request->query['type'], get_allowed_ads())) {
                $ad_type = $this->request->query['type'];
            }
        }

        $user = $this->Links->Users->find()->contain('Plans')->where([
            'Users.api_token' => $api,
            'Users.status' => 1
        ])->first();

        if (!$user) {
            $message = __('Invalid API token.');
            $this->set('message', $message);
            return null;
        }

        if ((bool)get_option('enable_premium_membership')) {
            if (!get_user_plan($user)->api_quick) {
                $message = __('You must upgrade your plan so you can use this tool.');
                $this->set('message', $message);
                return null;
            }
        }

        $url = trim($url);
        $url = str_replace(" ", "%20", $url);
        $url = parse_url($url, PHP_URL_SCHEME) === null ? 'http://' . $url : $url;

        $link = $this->Links->find()->where([
            'user_id' => $user->id,
            'status' => 1,
            'ad_type' => $ad_type,
            'url' => $url
        ])->first();

        if ($link) {
            return $this->redirect(get_short_url($link->alias));
        }

        $link = $this->Links->newEntity();
        $data = [];

        $data['user_id'] = $user->id;
        $data['url'] = $url;
        $data['alias'] = $this->Links->geturl();
        $data['ad_type'] = $ad_type;

        $link->status = 1;
        $link->hits = 0;
        $link->method = 2;

        $linkMeta = [
            'title' => '',
            'description' => '',
            'image' => ''
        ];

        if (get_option('disable_meta_api') === 'no') {
            $linkMeta = $this->Links->getLinkMeta($url);
        }

        $link->title = $linkMeta['title'];
        $link->description = $linkMeta['description'];
        $link->image = $linkMeta['image'];

        $link = $this->Links->patchEntity($link, $data);
        if ($this->Links->save($link)) {
            return $this->redirect(get_short_url($link->alias));
        }

        $message = __('Error.');
        $this->set('message', $message);
        return null;
    }

    public function full()
    {
        $this->autoRender = false;

        $this->loadModel('Links');

        $message = '';

        if (!isset($this->request->query) ||
            !isset($this->request->query['api']) ||
            !isset($this->request->query['url'])
        ) {
            $message = __('Invalid Request.');
            $this->set('message', $message);
            return null;
        }

        $api = $this->request->query['api'];
        $url = urldecode($this->request->query['url']);

        $ad_type = get_option('member_default_advert', 1);
        if (isset($this->request->query['type'])) {
            if (array_key_exists($this->request->query['type'], get_allowed_ads())) {
                $ad_type = $this->request->query['type'];
            }
        }

        $user = $this->Links->Users->find()->contain('Plans')->where([
            'Users.api_token' => $api,
            'Users.status' => 1
        ])->first();

        if (!$user) {
            $message = __('Invalid API token.');
            $this->set('message', $message);
            return null;
        }

        if ((bool)get_option('enable_premium_membership')) {
            if (!get_user_plan($user)->api_full) {
                $message = __('You must upgrade your plan so you can use this tool.');
                $this->set('message', $message);
                return null;
            }
        }

        $url = trim($url);
        $url = str_replace(" ", "%20", $url);
        $url = parse_url($url, PHP_URL_SCHEME) === null ? 'http://' . $url : $url;

        $link = $this->Links->find()->where([
            'user_id' => $user->id,
            'status' => 1,
            'ad_type' => $ad_type,
            'url' => $url
        ])->first();

        if ($link) {
            return $this->redirect(get_short_url($link->alias));
        }

        $link = $this->Links->newEntity();
        $data = [];

        $data['user_id'] = $user->id;
        $data['url'] = $url;
        $data['alias'] = $this->Links->geturl();
        $data['ad_type'] = $ad_type;

        $link->status = 1;
        $link->hits = 0;
        $link->method = 4;

        $linkMeta = [
            'title' => '',
            'description' => '',
            'image' => ''
        ];

        if (get_option('disable_meta_api') === 'no') {
            $linkMeta = $this->Links->getLinkMeta($url);
        }

        $link->title = $linkMeta['title'];
        $link->description = $linkMeta['description'];
        $link->image = $linkMeta['image'];

        $link = $this->Links->patchEntity($link, $data);
        if ($this->Links->save($link)) {
            return $this->redirect(get_short_url($link->alias));
        }

        $message = __('Error.');
        $this->set('message', $message);
        return null;
    }

    public function api()
    {
        $this->autoRender = false;

        $this->loadModel('Links');

        $format = 'json';
        if (isset($this->request->query['format']) && strtolower($this->request->query['format']) === 'text') {
            $format = 'text';
        }
        $this->response->type($format);

        if (!isset($this->request->query) ||
            !isset($this->request->query['api']) ||
            !isset($this->request->query['url'])
        ) {
            $content = [
                'status' => 'error',
                'message' => 'Invalid API call',
                'shortenedUrl' => ''
            ];
            $this->response->body($this->apiContent($content, $format));
            return $this->response;
        }

        $api = $this->request->query['api'];
        $url = urldecode($this->request->query['url']);

        $ad_type = get_option('member_default_advert', 1);
        if (isset($this->request->query['type'])) {
            if (array_key_exists($this->request->query['type'], get_allowed_ads())) {
                $ad_type = $this->request->query['type'];
            }
        }

        $user = $this->Links->Users->find()->contain('Plans')->where([
            'Users.api_token' => $api,
            'Users.status' => 1
        ])->first();

        if (!$user) {
            $content = [
                'status' => 'error',
                'message' => 'Invalid API token',
                'shortenedUrl' => ''
            ];
            $this->response->body($this->apiContent($content, $format));
            return $this->response;
        }

        if ((bool)get_option('enable_premium_membership')) {
            if (!get_user_plan($user)->api_developer) {
                $content = [
                    'status' => 'error',
                    'message' => 'You must upgrade your plan so you can use this tool.',
                    'shortenedUrl' => ''
                ];
                $this->response->body($this->apiContent($content, $format));
                return $this->response;
            }
        }

        $url = trim($url);
        $url = str_replace(" ", "%20", $url);
        $url = parse_url($url, PHP_URL_SCHEME) === null ? 'http://' . $url : $url;

        $link = $this->Links->find()->where([
            'url' => $url,
            'user_id' => $user->id,
            'ad_type' => $ad_type
        ])->first();

        if ($link) {
            $content = [
                'status' => 'success',
                'shortenedUrl' => get_short_url($link->alias, $link->domain)
            ];
            $this->response->body($this->apiContent($content, $format));
            return $this->response;
        }

        $link = $this->Links->newEntity();
        $data = [];

        $data['user_id'] = $user->id;
        $data['url'] = $url;
        if (empty($this->request->query['alias'])) {
            $data['alias'] = $this->Links->geturl();
        } else {
            $data['alias'] = $this->request->query['alias'];
        }
        $data['ad_type'] = $ad_type;

        $link->status = 1;
        $link->hits = 0;
        $link->method = 5;

        $linkMeta = [
            'title' => '',
            'description' => '',
            'image' => ''
        ];

        if (get_option('disable_meta_api') === 'no') {
            $linkMeta = $this->Links->getLinkMeta($url);
        }

        $link->title = $linkMeta['title'];
        $link->description = $linkMeta['description'];
        $link->image = $linkMeta['image'];

        $link = $this->Links->patchEntity($link, $data);

        if ($this->Links->save($link)) {
            $content = [
                'status' => 'success',
                'message' => '',
                'shortenedUrl' => get_short_url($link->alias, $link->domain)
            ];
            $this->response->body($this->apiContent($content, $format));
            return $this->response;
        }

        $content = [
            'status' => 'error',
            'message' => 'Invalid URL',
            'shortenedUrl' => ''
        ];
        $this->response->body($this->apiContent($content, $format));
        return $this->response;
    }

    protected function apiContent($content = [], $format = 'json')
    {
        $body = json_encode($content);
        if ($format === 'text') {
            $body = $content['shortenedUrl'];
        }
        return $body;
    }
}
