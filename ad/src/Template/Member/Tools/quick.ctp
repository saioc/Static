<?php
$this->assign('title', __('Quick Link'));
$this->assign('description', '');
$this->assign('content_title', __('Quick Link'));

?>

<div class="box box-primary">
    <div class="box-body">

        <?php if ($notice) : ?>
            <div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-triangle"></i><?= $notice ?></div>
        <?php endif; ?>

        <div class="callout callout-success">
            <h4><?= __('Your API token:') ?></h4>
            <p>
            <pre><?= $user->api_token ?></pre>
            </p>
        </div>

        <p><?= __('Everyone can use the shortest way to shorten links with {0}.', get_option('site_name')) ?></p>

        <p><?= __(
            'Just copy the link below to address bar into your web browser, change last part to ' .
                'destination link and press ENTER. {0} will redirect you to your shortened link. Copy it wherever ' .
                'you want and get paid.',
            get_option('site_name')
        ) ?></p>

        <pre><?= $this->Url->build('/', true); ?>st/?api=<b><?= $user->api_token ?></b>&url=<b><?= urlencode('yourdestinationlink.com') ?></b></pre>

        <?php
        $allowed_ads = get_allowed_ads();
        unset($allowed_ads[get_option('member_default_advert', 1)]);
        ?>

        <?php if (array_key_exists(1, $allowed_ads)) : ?>
            <p><?= __("If you want to use Quick Link with the interstitial advertising add the below code ".
                    "to the end of the URL") ?></p>
            <pre>&type=1</pre>
        <?php endif; ?>

        <?php if (array_key_exists(2, $allowed_ads)) : ?>
            <p><?= __("If you want to use Quick Link with the banner advertising add the below code to the ".
                    "end of the URL") ?></p>
            <pre>&type=2</pre>
        <?php endif; ?>

        <?php if (array_key_exists(0, $allowed_ads)) : ?>
            <p><?= __("If you want to use Quick Link without advertising add the below code to the end ".
                    "of the URL") ?></p>
            <pre>&type=0</pre>
        <?php endif; ?>

    </div>
</div>
