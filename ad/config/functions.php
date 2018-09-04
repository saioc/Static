<?php

use Cake\Core\Configure;

//\Cake\Cache\Cache::disable();

function database_connect()
{
    try {
        $connection = \Cake\Datasource\ConnectionManager::get('default');
        $connected = $connection->connect();
    } catch (\Exception $ex) {
        $connected = false;
        $errorMsg = $ex->getMessage();
        if (method_exists($ex, 'getAttributes')) {
            $attributes = $ex->getAttributes();
            if (isset($errorMsg['message'])) {
                $errorMsg .= '<br />' . $attributes['message'];
            }
        }
    }
    return $connected;
}

function is_app_installed()
{
    if (Configure::read('Adlinkfly.installed')) {
        return true;
    }

    if ((bool)get_option('installed', 0)) {
        return true;
    }

    return false;
}

function get_option($name, $default = '')
{
    if (!database_connect()) {
        return $default;
    }

    try {
        static $settings;

        if (!isset($settings)) {
            $options = \Cake\ORM\TableRegistry::get('Options');
            //$query   = $options->find()->select( ['name', 'value' ] )->cache( 'options' )->all();
            $query = $options->find()->select(['name', 'value'])->all();
            $settings = [];
            foreach ($query as $row) {
                $settings[$row->name] = (is_serialized($row->value)) ? unserialize($row->value) : $row->value;
            }
        }

        if (!array_key_exists($name, $settings)) {
            return $default;
        }

        if (is_array($settings[$name])) {
            return (!empty($settings[$name])) ? $settings[$name] : $default;
        } else {
            return (isset($settings[$name]) && strlen($settings[$name]) > 0) ? $settings[$name] : $default;
        }
    } catch (\Exception $ex) {
        return $default;
    }
}

// check this if error happened
// https://core.trac.wordpress.org/browser/tags/4.0.1/src/wp-includes/functions.php#L283
function is_serialized($data)
{
    if (@unserialize($data) === false) {
        return false;
    } else {
        return true;
    }
}

/**
 *
 */
function get_http_headers($url, $options = [])
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, true);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 1);

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    foreach ($options as $option => $value) {
        curl_setopt($ch, $option, $value);
    }
    $headers_string = curl_exec($ch);

    curl_close($ch);

    $data = [];
    //$headers = explode(PHP_EOL, $headers_string);
    $headers = explode("\n", str_replace("\r", "\n", $headers_string));
    foreach ($headers as $header) {
        $parts = explode(':', $header);
        if (count($parts) === 2) {
            $data[strtolower(trim($parts[0]))] = strtolower(trim($parts[1]));
        }
    }

    return $data;
}

/**
 *
 */
function curlRequest($url, $method = 'GET', $data = [], $headers = [], $options = [])
{
    $ch = curl_init();

    switch ($method) {
        case "POST":
            curl_setopt($ch, CURLOPT_POST, 1);

            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            break;
        case "PUT":
            curl_setopt($ch, CURLOPT_PUT, 1);
            break;
        default:
            if ($data) {
                $url = sprintf("%s?%s", $url, http_build_query($data));
            }
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    if ($headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if (empty(@ini_get('open_basedir'))) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
    }
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if (null != env('HTTP_USER_AGENT')) {
        curl_setopt($ch, CURLOPT_USERAGENT, env('HTTP_USER_AGENT'));
    }

    foreach ($options as $option => $value) {
        curl_setopt($ch, $option, $value);
    }

    $result = curl_exec($ch);
    //$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        \Cake\Log\Log::write('error', curl_error($ch));
    }

    curl_close($ch);

    return $result;
}

/**
 *
 */
function curlHtmlHeadRequest($url, $method = 'GET', $data = [], $headers = [], $options = [])
{
    $obj = new \stdClass(); //create an object variable to access class functions and variables
    $obj->result = '';

    $ch = curl_init();

    switch ($method) {
        case "POST":
            curl_setopt($ch, CURLOPT_POST, 1);

            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            break;
        case "PUT":
            curl_setopt($ch, CURLOPT_PUT, 1);
            break;
        default:
            if ($data) {
                $url = sprintf("%s?%s", $url, http_build_query($data));
            }
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    if ($headers) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $str) use ($obj) {
        $obj->result .= $str;
        /*
          if (stripos($obj->result, '<body') !== false) {
          return false;
          }
         */
        return strlen($str); //return the exact length
    });
    curl_setopt($ch, CURLOPT_NOPROGRESS, false);
    curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($ch, $downloadSize, $downloaded, $uploadSize, $uploaded) {
        // If $Downloaded exceeds 128KB, returning non-0 breaks the connection!
        return ($downloaded > (128 * 1024)) ? 1 : 0;
    });

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if (empty(@ini_get('open_basedir'))) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
    }
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    if (null != env('HTTP_USER_AGENT')) {
        curl_setopt($ch, CURLOPT_USERAGENT, env('HTTP_USER_AGENT'));
    }

    foreach ($options as $option => $value) {
        curl_setopt($ch, $option, $value);
    }

    curl_exec($ch);
    //$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        //\Cake\Log\Log::write('error', curl_error($ch));
    }

    curl_close($ch);

    return $obj->result;
}

function emptyTmp()
{
    $dir = new \Cake\Filesystem\Folder(TMP);
    $files = $dir->findRecursive('.*', true);

    foreach ($files as $file) {
        $file = new \Cake\Filesystem\File($file);
        if (!in_array($file->name, ['empty', 'ms_license_response_result'])) {
            @$file->delete();
        }
        $file->close();
    }
}

function emptyLogs()
{
    $dir = new \Cake\Filesystem\Folder(LOGS);
    $files = $dir->findRecursive('.*', true);

    foreach ($files as $file) {
        $file = new \Cake\Filesystem\File($file);
        if (!in_array($file->name, ['empty'])) {
            @$file->delete();
        }
        $file->close();
    }
}

function isset_captcha()
{
    $enable_captcha = get_option('enable_captcha', 'no');
    if ('yes' != $enable_captcha) {
        return false;
    }

    $captcha_type = get_option('captcha_type', 'recaptcha');

    if ($captcha_type == 'recaptcha') {
        $recaptcha_siteKey = get_option('reCAPTCHA_site_key');
        $recaptcha_secretKey = get_option('reCAPTCHA_secret_key');
        if (!empty($recaptcha_siteKey) && !empty($recaptcha_secretKey)) {
            return true;
        }
    }

    if ($captcha_type == 'invisible-recaptcha') {
        $recaptcha_siteKey = get_option('invisible_reCAPTCHA_site_key');
        $recaptcha_secretKey = get_option('invisible_reCAPTCHA_secret_key');
        if (!empty($recaptcha_siteKey) && !empty($recaptcha_secretKey)) {
            return true;
        }
    }

    if ($captcha_type == 'solvemedia') {
        $solvemedia_challenge_key = get_option('solvemedia_challenge_key');
        $solvemedia_verification_key = get_option('solvemedia_verification_key');
        $solvemedia_authentication_key = get_option('solvemedia_authentication_key');
        if (!empty($solvemedia_challenge_key) &&
            !empty($solvemedia_verification_key) &&
            !empty($solvemedia_authentication_key)
        ) {
            return true;
        }
    }

    return false;
}

function generate_random_string($length = 10, $special = false)
{
    $specialChars = '~!@#$%^&*(){}[],./?';
    $alphaNum = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $all_chars = $alphaNum;
    if ($special) {
        $all_chars .= $specialChars;
    }

    $string = '';
    $i = 0;
    while ($i < $length) {
        $random = mt_rand(0, strlen($all_chars) - 1);
        $string .= $all_chars{$random};
        $i = $i + 1;
    }
    return $string;
}

/**
 * Generate random IP address
 * @return string Random IP address
 */
function random_ipv4()
{
    // http://stackoverflow.com/a/10268612
    //return mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255);
    // // http://board.phpbuilder.com/showthread.php?10346623-Generating-a-random-IP-Address&p=10830872&viewfull=1#post10830872
    return long2ip(rand(0, 255 * 255) * rand(0, 255 * 255));
}

/**
 * Get client IP address
 * @return string IP address
 */
function get_ip()
{
    if (env("HTTP_CF_CONNECTING_IP")) {
        $ip = env("HTTP_CF_CONNECTING_IP");
    } elseif (env("HTTP_CLIENT_IP")) {
        $ip = env("HTTP_CLIENT_IP");
    } elseif (env("HTTP_X_FORWARDED_FOR")) {
        $ip = env("HTTP_X_FORWARDED_FOR");
        if (strstr($ip, ',')) {
            $tmp = explode(',', $ip);
            $ip = trim($tmp[0]);
        }
    } else {
        $ip = env("REMOTE_ADDR");
    }

    //$ip = random_ipv4();
    return $ip;
}

function display_price_currency($price, $options = [])
{
    $defaults = [
        'precision' => 6,
        'places' => 2,
        'locale' => locale_get_default(),
        get_option('currency_position', 'before') => get_option('currency_symbol', '$')
    ];
    $options = array_merge($defaults, $options);
    return \Cake\I18n\Number::format($price, $options);
}

function display_date_timezone($time)
{
    if (!$time) {
        return '';
    }
    return \Cake\I18n\Time::instance($time)->i18nFormat(null, get_option('timezone', 'UTC'), null);
}

function require_database_upgrade()
{
    if (version_compare(APP_VERSION, get_option('app_version', '1.0.0'), '>')) {
        return true;
    }
    return false;
}

function get_logo()
{
    $site_name = h(get_option('site_name'));
    $logo_url = h(get_option('logo_url'));

    $data = ['type' => 'text', 'content' => $site_name];

    if (!empty($logo_url)) {
        $data['type'] = 'image';
        $data['content'] = "<img src='{$logo_url}' alt='{$site_name}' />";
    }
    return $data;
}

function get_logo_alt()
{
    $site_name = h(get_option('site_name'));
    $logo_url = h(get_option('logo_url_alt'));

    $data = ['type' => 'text', 'content' => $site_name];

    if (!empty($logo_url)) {
        $data['type'] = 'image';
        $data['content'] = "<img src='{$logo_url}' alt='{$site_name}' />";
    }
    return $data;
}

function get_short_url($alias = '', $domain = '')
{
    if (empty($domain)) {
        $domain = get_default_short_domain();
    }
    /*
      $base_url = \Cake\Routing\Router::url([
      '_scheme' => 'http',
      '_host' => $domain,
      'controller' => null,
      'action' => null,
      'plugin' => null,
      'prefix' => false
      ], true);
     */
    $request = \Cake\Routing\Router::getRequest();
    $base_url = 'http://' . $domain . $request->base;
    //$base_url = str_ireplace('https://', 'http://', $base_url);
    return $base_url . '/' . $alias;
}

function get_default_short_domain()
{
    $default_short_domain = get_option('default_short_domain', '');
    if (!empty($default_short_domain)) {
        return $default_short_domain;
    }

    $main_domain = get_option('main_domain', '');
    if (!empty($main_domain)) {
        return $main_domain;
    }

    return env("HTTP_HOST", "");
}

function get_multi_domains_list()
{
    $domains = explode(',', get_option('multi_domains'));
    $domains = array_map('trim', $domains);
    $domains = array_filter($domains);
    $domains = array_unique($domains);
    $domains = array_combine($domains, $domains);

    $default_short_domain = get_option('default_short_domain', '');

    unset($domains[$default_short_domain]);

    return $domains;
}

function get_all_multi_domains_list()
{
    $domains = get_multi_domains_list();
    $add_domains = [];

    $default_short_domain = get_option('default_short_domain', '');
    if (!empty($default_short_domain)) {
        $add_domains[$default_short_domain] = $default_short_domain;
        return $add_domains + $domains;
    }

    $main_domain = get_option('main_domain', '');
    if (!empty($main_domain)) {
        $add_domains[$main_domain] = $main_domain;
        return $add_domains + $domains;
    }

    $add_domains[env("HTTP_HOST", "")] = env("HTTP_HOST", "");

    return $add_domains + $domains;
}

function get_all_domains_list()
{
    $domains = get_multi_domains_list();
    $add_domains = [];

    $default_short_domain = get_option('default_short_domain', '');
    if (!empty($default_short_domain)) {
        $add_domains[$default_short_domain] = $default_short_domain;
        $add_domains = $add_domains + $domains;
    }

    $main_domain = get_option('main_domain', '');
    if (!empty($main_domain)) {
        $add_domains[$main_domain] = $main_domain;
        $add_domains = $add_domains + $domains;
    }

    $add_domains[env("HTTP_HOST", "")] = env("HTTP_HOST", "");

    return $add_domains + $domains;
}

function get_allowed_ads()
{
    $ads = [];

    if (get_option('enable_interstitial', 'yes') == 'yes') {
        $ads['1'] = __('Interstitial');
    }

    if (get_option('enable_banner', 'yes') == 'yes') {
        $ads['2'] = __('Banner');
    }

    if (get_option('enable_noadvert', 'yes') == 'yes') {
        $ads['0'] = __('No Advert');
    }

    return $ads;
}

function get_statistics_reasons()
{
    return [
        0 => __("---"),
        1 => __("Earn"),
        2 => __("Disabled Cookies"),
        3 => __("Anonymous User"),
        4 => __("Adblock"),
        5 => __("Proxy"),
        6 => __("IP Changed"),
        7 => __("Not Unique"),
        8 => __("Full Weight"),
        9 => __("Default Campaign"),
        10 => __("Direct")
    ];
}

function get_payment_methods()
{
    $payment_methods = [];

    if ((bool)get_option('wallet_enable', false)) {
        $payment_methods['wallet'] = "<b>" . __("My Wallet") . "</b>";
    }

    if (get_option('paypal_enable', 'no') == 'yes') {
        $payment_methods['paypal'] = '<img src="https://www.paypalobjects.com/webstatic/en_US/i/btn/png/silver-rect-paypal-34px.png" alt="PayPal">';
    }

    if (get_option('payza_enable', 'no') == 'yes') {
        $payment_methods['payza'] = '<img src="https://www.payza.com/images/payza-buy-now.png" alt="Payza">';
    }

    if ((bool)get_option('skrill_enable', false)) {
        $payment_methods['skrill'] = '<img src="https://www.skrill.com/fileadmin/content/images/brand_centre/Skrill_Logos/skrill-85x37_en.gif" alt="Skrill">';
    }

    if (get_option('coinbase_enable', 'no') == 'yes') {
        $payment_methods['coinbase'] = '<img src="https://www.coinbase.com/assets/buttons/buy_now_small-cd4d106f61a3e365bf8af254c3d48d22c4207476e8e94a58e285c97c77f9b474.png" alt="Coinbase">';
    }

    if (get_option('webmoney_enable', 'no') == 'yes') {
        $payment_methods['webmoney'] = '<b>' . __('Web Money') . '</b> <img src="https://www.wmtransfer.com/img/icons/wmlogo_32.png" alt="Webmoney">';
    }

    if (get_option('banktransfer_enable', 'no') == 'yes') {
        $payment_methods['banktransfer'] = "<b>" . __("Bank Transfer") . "</b>";
    }
    return $payment_methods;
}

function get_site_languages($all = false)
{
    $default_language = get_option('language');
    $site_languages = get_option('site_languages', []);
    $site_languages = array_combine($site_languages, $site_languages);
    unset($site_languages[$default_language]);
    if ($all === true) {
        $site_languages[$default_language] = $default_language;
    }
    ksort($site_languages);
    return $site_languages;
}

function get_user_plan($user)
{
    if (is_array($user)) {
        $user = json_decode(json_encode($user), false);
    }

    if ($user->plan_id == 1) {
        return $user->plan;
    }

    static $free_plan;

    if (!isset($free_plan)) {
        $free_plan = \Cake\ORM\TableRegistry::get('Plans')->get(1);
    }

    if (!isset($user->expiration)) {
        return $free_plan;
    }

    $time = new \Cake\I18n\Time($user->expiration);

    if ($time->isPast()) {
        return $free_plan;
    }

    return $user->plan;
}

function data_encrypt($value)
{
    $key = \Cake\Utility\Security::salt();
    $value = serialize($value);
    $value = \Cake\Utility\Security::encrypt($value, $key);
    return base64_encode($value);
}

function data_decrypt($value)
{
    $key = \Cake\Utility\Security::salt();
    $value = base64_decode($value);
    $value = \Cake\Utility\Security::decrypt($value, $key);
    return unserialize($value);
}

function get_countries($campaing = false)
{
    $countries = [
        "AF" => __("Afganistan"),
        "AL" => __("Albania"),
        "DZ" => __("Algeria"),
        "AS" => __("American Samoa"),
        "AD" => __("Andorra"),
        "AO" => __("Angola"),
        "AI" => __("Anguilla"),
        "AQ" => __("Antarctica"),
        "AG" => __("Antigua and Barbuda"),
        "AR" => __("Argentina"),
        "AM" => __("Armenia"),
        "AW" => __("Aruba"),
        "AU" => __("Australia"),
        "AT" => __("Austria"),
        "AX" => __("Åland Islands"),
        "AZ" => __("Azerbaijan"),
        "BS" => __("Bahamas"),
        "BH" => __("Bahrain"),
        "BD" => __("Bangladesh"),
        "BB" => __("Barbados"),
        "BY" => __("Belarus"),
        "BE" => __("Belgium"),
        "BZ" => __("Belize"),
        "BJ" => __("Benin"),
        "BM" => __("Bermuda"),
        "BT" => __("Bhutan"),
        "BO" => __("Bolivia"),
        "BA" => __("Bosnia and Herzegowina"),
        "BW" => __("Botswana"),
        "BV" => __("Bouvet Island"),
        "BR" => __("Brazil"),
        "IO" => __("British Indian Ocean Territory"),
        "BN" => __("Brunei Darussalam"),
        "BG" => __("Bulgaria"),
        "BF" => __("Burkina Faso"),
        "BI" => __("Burundi"),
        "KH" => __("Cambodia"),
        "CM" => __("Cameroon"),
        "CA" => __("Canada"),
        "CV" => __("Cape Verde"),
        "KY" => __("Cayman Islands"),
        "CF" => __("Central African Republic"),
        "TD" => __("Chad"),
        "CL" => __("Chile"),
        "CN" => __("China"),
        "CX" => __("Christmas Island"),
        "CC" => __("Cocos (Keeling) Islands"),
        "CO" => __("Colombia"),
        "KM" => __("Comoros"),
        "CG" => __("Congo"),
        "CD" => __("Congo, the Democratic Republic of the"),
        "CK" => __("Cook Islands"),
        "CR" => __("Costa Rica"),
        "CI" => __("Cote d'Ivoire"),
        "CW" => __("Curaçao"),
        "HR" => __("Croatia (Hrvatska)"),
        "CU" => __("Cuba"),
        "CY" => __("Cyprus"),
        "CZ" => __("Czech Republic"),
        "DK" => __("Denmark"),
        "DJ" => __("Djibouti"),
        "DM" => __("Dominica"),
        "DO" => __("Dominican Republic"),
        "TP" => __("East Timor"),
        "EC" => __("Ecuador"),
        "EG" => __("Egypt"),
        "SV" => __("El Salvador"),
        "GQ" => __("Equatorial Guinea"),
        "ER" => __("Eritrea"),
        "EE" => __("Estonia"),
        "ET" => __("Ethiopia"),
        "FK" => __("Falkland Islands (Malvinas)"),
        "FO" => __("Faroe Islands"),
        "FJ" => __("Fiji"),
        "FI" => __("Finland"),
        "FR" => __("France"),
        "FX" => __("France, Metropolitan"),
        "GF" => __("French Guiana"),
        "PF" => __("French Polynesia"),
        "TF" => __("French Southern Territories"),
        "GA" => __("Gabon"),
        "GM" => __("Gambia"),
        "GE" => __("Georgia"),
        "DE" => __("Germany"),
        "GH" => __("Ghana"),
        "GI" => __("Gibraltar"),
        "GR" => __("Greece"),
        "GL" => __("Greenland"),
        "GD" => __("Grenada"),
        "GP" => __("Guadeloupe"),
        "GU" => __("Guam"),
        "GT" => __("Guatemala"),
        "GN" => __("Guinea"),
        "GW" => __("Guinea-Bissau"),
        "GY" => __("Guyana"),
        "HT" => __("Haiti"),
        "HM" => __("Heard and Mc Donald Islands"),
        "VA" => __("Holy See (Vatican City State)"),
        "HN" => __("Honduras"),
        "HK" => __("Hong Kong"),
        "HU" => __("Hungary"),
        "IS" => __("Iceland"),
        "IM" => __("Isle of Man"),
        "IN" => __("India"),
        "ID" => __("Indonesia"),
        "IR" => __("Iran (Islamic Republic of)"),
        "IQ" => __("Iraq"),
        "IE" => __("Ireland"),
        "IL" => __("Israel"),
        "IT" => __("Italy"),
        "JM" => __("Jamaica"),
        "JP" => __("Japan"),
        "JO" => __("Jordan"),
        "KZ" => __("Kazakhstan"),
        "KE" => __("Kenya"),
        "KI" => __("Kiribati"),
        "KP" => __("Korea, Democratic People's Republic of"),
        "KR" => __("Korea, Republic of"),
        "KW" => __("Kuwait"),
        "KG" => __("Kyrgyzstan"),
        "LA" => __("Lao People's Democratic Republic"),
        "LV" => __("Latvia"),
        "LB" => __("Lebanon"),
        "LS" => __("Lesotho"),
        "LR" => __("Liberia"),
        "LY" => __("Libyan Arab Jamahiriya"),
        "LI" => __("Liechtenstein"),
        "LT" => __("Lithuania"),
        "LU" => __("Luxembourg"),
        "MO" => __("Macau"),
        "MK" => __("Macedonia, The Former Yugoslav Republic of"),
        "MG" => __("Madagascar"),
        "MW" => __("Malawi"),
        "MY" => __("Malaysia"),
        "MV" => __("Maldives"),
        "ML" => __("Mali"),
        "MT" => __("Malta"),
        "MH" => __("Marshall Islands"),
        "MQ" => __("Martinique"),
        "MR" => __("Mauritania"),
        "MU" => __("Mauritius"),
        "YT" => __("Mayotte"),
        "MX" => __("Mexico"),
        "FM" => __("Micronesia, Federated States of"),
        "MD" => __("Moldova, Republic of"),
        "MC" => __("Monaco"),
        "ME" => __("Montenegro"),
        "MN" => __("Mongolia"),
        "MS" => __("Montserrat"),
        "MA" => __("Morocco"),
        "MZ" => __("Mozambique"),
        "MM" => __("Myanmar"),
        "NA" => __("Namibia"),
        "NR" => __("Nauru"),
        "NP" => __("Nepal"),
        "NL" => __("Netherlands"),
        "AN" => __("Netherlands Antilles"),
        "NC" => __("New Caledonia"),
        "NZ" => __("New Zealand"),
        "NI" => __("Nicaragua"),
        "NE" => __("Niger"),
        "NG" => __("Nigeria"),
        "NU" => __("Niue"),
        "NF" => __("Norfolk Island"),
        "MP" => __("Northern Mariana Islands"),
        "NO" => __("Norway"),
        "OM" => __("Oman"),
        "PK" => __("Pakistan"),
        "PW" => __("Palau"),
        "PA" => __("Panama"),
        "PG" => __("Papua New Guinea"),
        "PY" => __("Paraguay"),
        "PE" => __("Peru"),
        "PH" => __("Philippines"),
        "PN" => __("Pitcairn"),
        "PL" => __("Poland"),
        "PT" => __("Portugal"),
        "PR" => __("Puerto Rico"),
        "PS" => __("Palestine"),
        "QA" => __("Qatar"),
        "RE" => __("Reunion"),
        "RO" => __("Romania"),
        "RS" => __("Republic of Serbia"),
        "RU" => __("Russian Federation"),
        "RW" => __("Rwanda"),
        "KN" => __("Saint Kitts and Nevis"),
        "LC" => __("Saint LUCIA"),
        "VC" => __("Saint Vincent and the Grenadines"),
        "WS" => __("Samoa"),
        "SM" => __("San Marino"),
        "ST" => __("Sao Tome and Principe"),
        "SA" => __("Saudi Arabia"),
        "SN" => __("Senegal"),
        "SC" => __("Seychelles"),
        "SL" => __("Sierra Leone"),
        "SG" => __("Singapore"),
        "SK" => __("Slovakia (Slovak Republic)"),
        "SI" => __("Slovenia"),
        "SB" => __("Solomon Islands"),
        "SO" => __("Somalia"),
        "SX" => __("Sint Maarten"),
        "ZA" => __("South Africa"),
        "GS" => __("South Georgia and the South Sandwich Islands"),
        "ES" => __("Spain"),
        "LK" => __("Sri Lanka"),
        "SH" => __("St. Helena"),
        "PM" => __("St. Pierre and Miquelon"),
        "SD" => __("Sudan"),
        "SR" => __("Suriname"),
        "SJ" => __("Svalbard and Jan Mayen Islands"),
        "SZ" => __("Swaziland"),
        "SE" => __("Sweden"),
        "CH" => __("Switzerland"),
        "SY" => __("Syrian Arab Republic"),
        "TW" => __("Taiwan, Province of China"),
        "TJ" => __("Tajikistan"),
        "TZ" => __("Tanzania, United Republic of"),
        "TH" => __("Thailand"),
        "TG" => __("Togo"),
        "TK" => __("Tokelau"),
        "TO" => __("Tonga"),
        "TT" => __("Trinidad and Tobago"),
        "TN" => __("Tunisia"),
        "TR" => __("Turkey"),
        "TM" => __("Turkmenistan"),
        "TC" => __("Turks and Caicos Islands"),
        "TV" => __("Tuvalu"),
        "UG" => __("Uganda"),
        "UA" => __("Ukraine"),
        "AE" => __("United Arab Emirates"),
        "GB" => __("United Kingdom"),
        "US" => __("United States"),
        "UM" => __("United States Minor Outlying Islands"),
        "UY" => __("Uruguay"),
        "UZ" => __("Uzbekistan"),
        "VU" => __("Vanuatu"),
        "VE" => __("Venezuela"),
        "VN" => __("Vietnam"),
        "VG" => __("Virgin Islands (British)"),
        "VI" => __("Virgin Islands (U.S.)"),
        "WF" => __("Wallis and Futuna Islands"),
        "EH" => __("Western Sahara"),
        "YE" => __("Yemen"),
        "YU" => __("Yugoslavia"),
        "ZM" => __("Zambia"),
        "ZW" => __("Zimbabwe")
    ];

    if ($campaing) {
        $countries = ['all' => __('Worldwide Deal(All Countries)')] + $countries;
    }

    return $countries;
}
