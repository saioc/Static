<?php $user = $this->request->session()->read('Auth.User'); ?>
<!DOCTYPE html>
<html lang="<?= locale_get_primary_language(null) ?>">
<head>
    <?= $this->Html->charset(); ?>
    <title><?= h($this->fetch('title')); ?></title>
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= h($this->fetch('description')); ?>">
    <meta name="og:title" content="<?= h($this->fetch('og_title')); ?>">
    <meta name="og:description" content="<?= h($this->fetch('og_description')); ?>">
    <meta property="og:image" content="<?= h($this->fetch('og_image')); ?>"/>
    <?php
    echo $this->Html->meta('icon');

    //echo $this->Html->css( 'base.css' );
    //echo $this->Html->css( 'cake.css' );
    echo $this->Html->css('//cdn.bootcss.com/bootstrap/4.1.1/css/bootstrap.min.css');
    //echo $this->Html->css( '//cdn.rawgit.com/twbs/bootstrap/v3.3.6/dist/css/bootstrap-theme.min.css' );
    if (get_option('language_direction') == 'rtl') {
        //echo $this->Html->css('//cdn.bootcss.com/bootstrap-rtl/3.4.0/css/bootstrap-rtl.min.css');
        echo $this->Html->css( '//cdn.bootcss.com/bootstrap-rtl/3.3.4/css/bootstrap-flipped.min.css' );
    }
    echo $this->Html->css('//cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css');
    echo $this->Html->css('//adminlte.io/themes/AdminLTE/dist/css/AdminLTE.min.css');
    echo $this->Html->css('//cdn.rawgit.com/almasaeed2010/AdminLTE/v2.3.11/dist/css/skins/skin-blue.min.css');
    echo $this->Html->css('app.css?ver=' . APP_VERSION);
    if (get_option('language_direction') == 'rtl') {
        echo $this->Html->css('app-rtl');
    }
    echo $this->fetch('meta');
    echo $this->fetch('css');
    echo $this->fetch('script');

    ?>

    <?= get_option('head_code'); ?>

    <?= $this->fetch('scriptTop') ?>

    <!-- go_interstitial -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="<?= (get_option('language_direction') == 'rtl' ? "rtl" : "") ?> frame layout-top-nav skin-blue">
<?= get_option('after_body_tag_code'); ?>

<div class="wrapper">


    <header class="main-header">
        <!-- Fixed navbar -->
        <nav class="navbar">
            <div class="container">

                <div class="row is-table-row">
                    <div class="col-xs-6 col-sm-3">
                        <div class="navbar-header pull-left">
                            <?php
                            $logo = get_logo();
                            $class = '';
                            if ($logo['type'] == 'image') {
                                $class = 'logo-image';
                            }
                            ?>
                            <a class="navbar-brand <?= $class ?>"
                               href="<?= $this->Url->build('/'); ?>"><?= $logo['content'] ?></a>
                        </div>
                    </div>
                    <div class="hidden-xs col-sm-6">
                        <?php if (!empty($interstitial_ads)) : ?>
                            <div class="banner banner-468x60">
                                <div class="banner-inner">
                                    <?= $interstitial_ads; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-xs-6 col-sm-3">
                        <div class="pull-right counter">
                            <a href="" class="btn btn-default skip-ad"
                               onclick="javascript: return false;"><?= __('Please Wait') ?></a>
                        </div>
                    </div>
                </div>

            </div>
        </nav>
    </header>

    <?= $this->Flash->render() ?>
    <?= $this->fetch('content') ?>

</div>
<?= $this->Html->script('//cdn.bootcss.com/jquery/3.3.1/jquery.min.js'); ?>
<?= $this->Html->script('//cdn.bootcss.com/bootstrap/4.1.1/js/bootstrap.min.js'); ?>
<?= $this->Html->script('//cdn.bootcss.com/clipboard.js/2.0.1/clipboard.min.js'); ?>

<?= $this->element('js_vars'); ?>

<?= $this->Html->script('app.js?ver=' . APP_VERSION); ?>

<?php if (in_array(get_option('captcha_type', 'recaptcha'), ['recaptcha', 'invisible-recaptcha'])) : ?>
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadRecaptchaCallback&render=explicit"
            async defer></script>
<?php endif; ?>

<?php if (get_option('captcha_type') == 'solvemedia') : ?>
    <?php
    $sm_server = (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") ? "http://api" : "https://api-secure";
    ?>
    <script type="text/javascript" src="<?= $sm_server ?>.solvemedia.com/papi/challenge.ajax"></script>
<?php endif; ?>

<?= $this->Html->script('//cdn.rawgit.com/almasaeed2010/AdminLTE/v2.3.11/dist/js/app.js'); ?>
<?= $this->fetch('scriptBottom') ?>
<?= get_option('footer_code'); ?>
<!--本源码由顶点网www.topide.com提供-->
</body>
</html>
