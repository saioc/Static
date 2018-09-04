<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;
use Cake\Network\Exception\NotFoundException;

class OptionsController extends AppAdminController
{
    public function index()
    {
        $options = $this->Options->find()->all();

        $settings = [];
        foreach ($options as $option) {
            $settings[$option->name] = [
                'id' => $option->id,
                'value' => $option->value
            ];
        }

        if ($this->request->is(['post', 'put'])) {
            foreach ($this->request->data['Options'] as $key => $optionData) {
                if (is_array($optionData['value'])) {
                    $optionData['value'] = serialize($optionData['value']);
                }
                $option = $this->Options->newEntity();
                $option->id = $key;
                $option = $this->Options->patchEntity($option, $optionData);
                $this->Options->save($option);
            }

            emptyTmp();

            $this->Flash->success(__('Settings have been saved.'));
            return $this->redirect(['action' => 'index']);
        }

        $this->set('options', $options);
        $this->set('settings', $settings);
    }

    public function email()
    {
        $options = $this->Options->find()->all();

        $settings = [];
        foreach ($options as $option) {
            $settings[$option->name] = [
                'id' => $option->id,
                'value' => $option->value
            ];
        }

        if ($this->request->is(['post', 'put'])) {
            foreach ($this->request->data['Options'] as $key => $optionData) {
                if (is_array($optionData['value'])) {
                    $optionData['value'] = serialize($optionData['value']);
                }
                $option = $this->Options->newEntity();
                $option->id = $key;
                $option = $this->Options->patchEntity($option, $optionData);
                $this->Options->save($option);
            }

            $this->createEmailFile();

            $this->Flash->success(__('Email settings have been saved.'));
            return $this->redirect(['action' => 'email']);
        }

        $this->set('options', $options);
        $this->set('settings', $settings);
    }

    public function socialLogin()
    {
        $options = $this->Options->find()->all();

        $settings = [];
        foreach ($options as $option) {
            $settings[$option->name] = [
                'id' => $option->id,
                'value' => $option->value
            ];
        }

        if ($this->request->is(['post', 'put'])) {
            foreach ($this->request->data['Options'] as $key => $optionData) {
                if (is_array($optionData['value'])) {
                    $optionData['value'] = serialize($optionData['value']);
                }
                $option = $this->Options->newEntity();
                $option->id = $key;
                $option = $this->Options->patchEntity($option, $optionData);
                $this->Options->save($option);
            }

            $this->Flash->success(__('Social login settings have been saved.'));
            return $this->redirect(['action' => 'socialLogin']);
        }

        $this->set('options', $options);
        $this->set('settings', $settings);
    }

    public function interstitial()
    {
        if ($this->request->is(['get']) && empty($this->request->query['source'])) {
            return;
        }

        $source = $this->request->query['source'];

        $option = $this->Options->findByName('interstitial_price')->first();
        if (!$option) {
            throw new NotFoundException(__('Invalid option'));
        }

        $option->value = unserialize($option->value);

        if ($this->request->is(['post', 'put'])) {
            foreach ($this->request->data['value'] as $key => $value) {
                if (!empty($value[$source]['advertiser']) && !empty($value[$source]['publisher'])) {
                    $option->value[$key][$source] = [
                        'advertiser' => abs($value[$source]['advertiser']),
                        'publisher' => abs($value[$source]['publisher'])
                    ];
                } else {
                    $option->value[$key][$source] = [
                        'advertiser' => '',
                        'publisher' => ''
                    ];
                }
            }
            unset($key, $value);

            $option->value = serialize($option->value);

            if ($this->Options->save($option)) {
                //debug($option);
                $this->Flash->success('Prices have been updated.');

                foreach (get_site_languages(true) as $lang) {
                    \Cake\Cache\Cache::delete('advertising_rates_' . $lang, '1day');
                    \Cake\Cache\Cache::delete('payout_rates_' . $lang, '1day');
                }

                return $this->redirect(['action' => 'interstitial', '?' => ['source' => $source]]);
            } else {
                $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
            }
        }

        $this->set('option', $option);
    }

    public function banner()
    {
        if ($this->request->is(['get']) && empty($this->request->query['source'])) {
            return;
        }

        $source = $this->request->query['source'];

        $option = $this->Options->findByName('banner_price')->first();
        if (!$option) {
            throw new NotFoundException(__('Invalid option'));
        }

        $option->value = unserialize($option->value);

        if ($this->request->is(['post', 'put'])) {
            foreach ($this->request->data['value'] as $key => $value) {
                if (!empty($value[$source]['advertiser']) && !empty($value[$source]['publisher'])) {
                    $option->value[$key][$source] = [
                        'advertiser' => abs($value[$source]['advertiser']),
                        'publisher' => abs($value[$source]['publisher'])
                    ];
                } else {
                    $option->value[$key][$source] = [
                        'advertiser' => '',
                        'publisher' => ''
                    ];
                }
            }
            unset($key, $value);

            $option->value = serialize($option->value);

            if ($this->Options->save($option)) {
                //debug($option);
                $this->Flash->success('Prices have been updated.');

                foreach (get_site_languages(true) as $lang) {
                    \Cake\Cache\Cache::delete('advertising_rates_' . $lang, '1day');
                    \Cake\Cache\Cache::delete('payout_rates_' . $lang, '1day');
                }

                return $this->redirect(['action' => 'banner', '?' => ['source' => $source]]);
            } else {
                $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
            }
        }

        $this->set('option', $option);
    }

    public function popup()
    {
        if ($this->request->is(['get']) && empty($this->request->query['source'])) {
            return;
        }

        $source = $this->request->query['source'];

        $option = $this->Options->findByName('popup_price')->first();
        if (!$option) {
            throw new NotFoundException(__('Invalid option'));
        }

        $option->value = unserialize($option->value);

        if ($this->request->is(['post', 'put'])) {
            foreach ($this->request->data['value'] as $key => $value) {
                if (!empty($value[$source]['advertiser']) && !empty($value[$source]['publisher'])) {
                    $option->value[$key][$source] = [
                        'advertiser' => abs($value[$source]['advertiser']),
                        'publisher' => abs($value[$source]['publisher'])
                    ];
                } else {
                    $option->value[$key][$source] = [
                        'advertiser' => '',
                        'publisher' => ''
                    ];
                }
            }
            unset($key, $value);

            $option->value = serialize($option->value);

            if ($this->Options->save($option)) {
                //debug($option);
                $this->Flash->success('Prices have been updated.');

                foreach (get_site_languages(true) as $lang) {
                    \Cake\Cache\Cache::delete('advertising_rates_' . $lang, '1day');
                    \Cake\Cache\Cache::delete('payout_rates_' . $lang, '1day');
                }

                return $this->redirect(['action' => 'popup', '?' => ['source' => $source]]);
            } else {
                $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
            }
        }

        $this->set('option', $option);
    }

    protected function createEmailFile()
    {
        $options = $this->Options->find()->all();

        $config = array(
            'email_from' => '',
            'email_method' => '',
            'email_smtp_host' => '',
            'email_smtp_port' => '',
            'email_smtp_username' => '',
            'email_smtp_password' => '',
            'email_smtp_tls' => ''
        );

        foreach ($options as $value) {
            if (array_key_exists($value['name'], $config)) {
                $config[$value['name']] = str_replace('\'', '\\\'', $value['value']);
            }
        }

        $result = copy(CONFIG . 'email.install', CONFIG . 'email.php');
        if (!$result) {
            return $this->Flash->error(__('Could not copy email.php file.'));
        }
        $file = new \Cake\Filesystem\File(CONFIG . 'email.php');
        $content = $file->read();

        foreach ($config as $configKey => $configValue) {
            $content = str_replace('{' . $configKey . '}', $configValue, $content);
        }

        if (!$file->write($content)) {
            return $this->Flash->error(__('Could not write email.php file.'));
        }

        return true;
    }
}
