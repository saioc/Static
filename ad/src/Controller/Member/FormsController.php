<?php

namespace App\Controller\Member;

use App\Controller\Member\AppMemberController;
use App\Form\ContactForm;

class FormsController extends AppMemberController
{
    public function initialize()
    {
        parent::initialize();
    }

    public function support()
    {
        $contact = new ContactForm();

        if ($this->request->is('post')) {
            if ($contact->execute($this->request->data)) {
                $this->Flash->success('We will get back to you soon.');
                return $this->redirect(['action' => 'support']);
            } else {
                $this->Flash->error('There was a problem submitting your form.');
            }
        }
        $this->set('contact', $contact);
    }
}
