<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Exception;

class CaptchaComponent extends Component
{
    public function verify($post_data)
    {
        $captcha_type = get_option('captcha_type');

        if ($captcha_type == 'recaptcha') {
            return $this->recaptchaVerify($post_data);
        }

        if ($captcha_type == 'invisible-recaptcha') {
            return $this->invisibleRecaptchaVerify($post_data);
        }

        if ($captcha_type == 'solvemedia') {
            return $this->solvemediaVerify($post_data);
        }

        return false;
    }

    public function recaptchaVerify($post_data = [])
    {
        $recaptchaSecretKey = get_option('reCAPTCHA_secret_key');
        if (empty($recaptchaSecretKey)) {
            throw new Exception(__("You must set your Recaptcha secret key!"));
        }

        if (!isset($post_data['g-recaptcha-response'])) {
            return false;
        }

        $data = array(
            'secret' => $recaptchaSecretKey,
            'response' => $post_data['g-recaptcha-response'],
        );

        $result = curlRequest('https://www.google.com/recaptcha/api/siteverify', 'POST', $data);
        $responseData = json_decode($result, true);

        if ($responseData['success'] == false) {
            //$recaptchaError = '';
            //foreach ($responseData['error-codes'] as $code) {
            //    $recaptchaError .= $code . ' ';
            //}

            //$this->error = $recaptchaError;
        }

        return $responseData['success'];
    }

    public function invisibleRecaptchaVerify($post_data = [])
    {
        $recaptchaSecretKey = get_option('invisible_reCAPTCHA_secret_key');
        if (empty($recaptchaSecretKey)) {
            throw new Exception(__("You must set your Invisible Recaptcha secret key!"));
        }

        if (!isset($post_data['g-recaptcha-response'])) {
            return false;
        }

        $data = array(
            'secret' => $recaptchaSecretKey,
            'response' => $post_data['g-recaptcha-response'],
        );

        $result = curlRequest('https://www.google.com/recaptcha/api/siteverify', 'POST', $data);
        $responseData = json_decode($result, true);

        if ($responseData['success'] == false) {
            //$recaptchaError = '';
            //foreach ($responseData['error-codes'] as $code) {
            //    $recaptchaError .= $code . ' ';
            //}

            //$this->error = $recaptchaError;
        }

        return $responseData['success'];
    }

    public function solvemediaVerify($post_data = [])
    {
        $solvemedia_verification_key = get_option('solvemedia_verification_key');
        $solvemedia_authentication_key = get_option('solvemedia_authentication_key');

        if (!isset($post_data['adcopy_challenge']) || !isset($post_data['adcopy_response'])) {
            return false;
        }

        $data = array(
            'privatekey' => $solvemedia_verification_key,
            'challenge' => $post_data['adcopy_challenge'],
            'response' => $post_data['adcopy_response'],
            'remoteip' => get_ip()
        );

        $result = curlRequest('http://verify.solvemedia.com/papi/verify', 'POST', $data);
        $answers = explode("\n", $result);

        $hash = sha1($answers[0] . $post_data['adcopy_challenge'] . $solvemedia_authentication_key);

        if ($hash !== $answers[2]) {
            return false;
        }

        if (trim($answers[0]) == 'true') {
            return true;
        }

        return false;
    }
}
