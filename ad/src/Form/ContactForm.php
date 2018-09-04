<?php

namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Validation\Validator;
use Cake\Mailer\Email;

// http://book.cakephp.org/3.0/en/core-libraries/form.html

class ContactForm extends Form
{
    protected function _buildSchema(Schema $schema)
    {
        return $schema
            ->addField('name', 'string')
            ->addField('subject', 'string')
            ->addField('email', ['type' => 'string'])
            ->addField('message', ['type' => 'text']);
    }

    protected function _buildValidator(Validator $validator)
    {
        return $validator
            ->notEmpty('name', __('A name is required'))
            ->add('email', 'format', [
                'rule' => 'email',
                'message' => __('A valid email address is required')
            ])
            ->notEmpty('subject', __('A subject is required'))
            ->notEmpty('message', __('Message body is required'));
    }

    protected function _execute(array $data)
    {
        $email = new Email();
        $email
            ->profile(get_option('email_method', 'default'))
            ->replyTo($data['email'], $data['name'])
            ->to(get_option('admin_email'))
            ->subject(h(get_option('site_name')) . ': ' . h($data['subject']))
            ->viewVars([
                'name' => $data['name'],
                'message' => $data['message']
            ])
            ->template('contact')// By default template with same name as method name is used.
            ->emailFormat('html')
            ->send();
        return true;
    }
}
