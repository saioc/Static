<script type='text/javascript'>
    /* <![CDATA[ */
    var app_vars = [];
    app_vars['base_url'] = '<?= $this->Url->build('/', true); ?>';
    app_vars['language'] = '<?= locale_get_default() ?>';
    app_vars['copy'] = '<?= __("Copy"); ?>';
    app_vars['copied'] = '<?= __("Copied!"); ?>';
    app_vars['user_id'] = '<?= $this->request->session()->read('Auth.User.id'); ?>';
    app_vars['home_shortening_register'] = '<?= (get_option('home_shortening_register') == 'yes') ? 'yes' : 'no' ?>';
    app_vars['enable_captcha'] = '<?= get_option('enable_captcha', 'no'); ?>';
    app_vars['captcha_type'] = '<?= get_option('captcha_type', "recaptcha"); ?>';
    app_vars['reCAPTCHA_site_key'] = '<?= get_option('reCAPTCHA_site_key'); ?>';
    app_vars['invisible_reCAPTCHA_site_key'] = '<?= get_option('invisible_reCAPTCHA_site_key'); ?>';
    app_vars['solvemedia_challenge_key'] = '<?= get_option('solvemedia_challenge_key'); ?>';
    app_vars['captcha_short_anonymous'] = '<?= get_option('enable_captcha_shortlink_anonymous', 0) ?>';
    app_vars['captcha_shortlink'] = '<?= get_option('enable_captcha_shortlink', 'no') ?>';
    app_vars['captcha_signup'] = '<?= get_option('enable_captcha_signup', 'no') ?>';
    app_vars['captcha_forgot_password'] = '<?= get_option('enable_captcha_forgot_password', 'no') ?>';
    app_vars['captcha_contact'] = '<?= get_option('enable_captcha_contact', 'no') ?>';
    /* ]]> */
</script>
