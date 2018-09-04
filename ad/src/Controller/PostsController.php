<?php

namespace App\Controller;

use App\Controller\FrontController;
use Cake\Event\Event;
use Cake\Network\Exception\NotFoundException;

class PostsController extends FrontController
{
    public $paginate = [
        'limit' => 10
    ];

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(['index', 'view']);
    }

    public function index()
    {
        $query = $this->Posts->find()
            ->where(['Posts.published' => 1])
            ->order(['Posts.id' => 'DESC']);
        $posts = $this->paginate($query);

        $this->set('posts', $posts);
    }

    public function view($id = null, $slug = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid Post.'));
        }

        $post = $this->Posts->find()->where(['Posts.id' => $id, 'Posts.published' => 1])->first();

        if (!$post) {
            throw new NotFoundException(__('Invalid Post.'));
        }

        $this->set('post', $post);
    }
}
