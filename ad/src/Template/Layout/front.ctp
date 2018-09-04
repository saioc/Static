<?php $user = $this->request->session()->read('Auth.User'); ?>
<!DOCTYPE html>
<html lang="<?= locale_get_primary_language(null) ?>">

<head>
    <?= $this->Html->charset(); ?>
    <title><?= h($this->fetch('title')); ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= h($this->fetch('description')); ?>">

    <?= $this->Html->meta('icon'); ?>

    <?= $this->Html->css('/vendor/bootstrap/css/bootstrap.min.css'); ?>
    <?= $this->Html->css('/vendor/font-awesome/css/font-awesome.min.css'); ?>
    <?= $this->Html->css('/vendor/animate.min.css'); ?>

    <?= $this->Html->css('front'); ?>
    <?= $this->Html->css('app.css?ver=' . APP_VERSION); ?>

    <?php
    echo $this->fetch('meta');
    echo $this->fetch('css');
    echo $this->fetch('script');

    ?>

    <link href="//fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css">
    <link href='//fonts.googleapis.com/css?family=Droid+Serif:400,700,400italic,700italic' rel='stylesheet'
          type='text/css'>
    <link href='//fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700' rel='stylesheet' type='text/css'>

    <?= get_option('head_code'); ?>

    <?= $this->fetch('scriptTop') ?>

    <!-- front-->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body id="page-top" class="index <?= ($this->request->here == $this->request->webroot) ? '' : 'inner-page' ?>">
<?= get_option('after_body_tag_code'); ?>
<!-- Navigation -->
<nav id="mainNav" class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header page-scroll">
            <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only"><?= __('Toggle navigation') ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <?php
            $logo = get_logo();
            $class = '';
            if ($logo['type'] == 'image') {
                $class = 'logo-image';
            }
            ?>
            <a class="navbar-brand <?= $class ?>" href="<?= $this->Url->build('/'); ?>"><?= $logo['content'] ?></a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav navbar-right">
                <li class="hidden">
                    <a href="#page-top"></a>
                </li>
                <li>
                    <a href="<?= $this->Url->build('/'); ?>"><?= __('Home') ?></a>
                </li>
                <?php if (get_option('enable_advertising', 'yes') == 'yes') : ?>
                    <li>
                        <a href="<?= $this->Url->build('/advertising-rates'); ?>"><?= __('Advertising') ?></a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="<?= $this->Url->build('/payout-rates'); ?>"><?= __('Payout Rates') ?></a>
                </li>
                <li>
                    <a href="<?= $this->Url->build([
                        'controller' => 'Users',
                        'action' => 'dashboard',
                        'prefix' => 'member'
                    ]); ?>"><?= __('My Account') ?></a>
                </li>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container-fluid -->
</nav>

<?= $this->Flash->render() ?>
<?= $this->fetch('content') ?>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <span class="copyright"><?= __('Copyright &copy;') ?> <?= h(get_option('site_name')) ?> <?= date("Y") ?></span>
            </div>
            <div class="col-md-4">
                <ul class="list-inline social-buttons">
                    <?php if (get_option('facebook_url')) : ?>
                        <li><a href="<?= h(get_option('facebook_url')) ?>"><i class="fa fa-facebook"></i></a></li>
                    <?php endif; ?>
                    <?php if (get_option('twitter_url')) : ?>
                        <li><a href="<?= h(get_option('twitter_url')) ?>"><i class="fa fa-twitter"></i></a></li>
                    <?php endif; ?>
                    <?php if (get_option('google_plus_url')) : ?>
                        <li><a href="<?= h(get_option('google_plus_url')) ?>"><i class="fa fa-google-plus"></i></a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <ul class="list-inline quicklinks">
                    <li><a href="<?= $this->Url->build('/pages/privacy'); ?>"><?= __('Privacy Policy') ?></a>
                    </li>
                    <li><a href="<?= $this->Url->build('/pages/terms'); ?>"><?= __('Terms of Use') ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<?= $this->Html->script('/vendor/jquery.min.js'); ?>
<?= $this->Html->script('/vendor/bootstrap/js/bootstrap.min.js'); ?>
<?= $this->Html->script('/vendor/wow.min.js'); ?>
<?= $this->Html->script('/vendor/clipboard.min.js'); ?>

<?= $this->element('js_vars'); ?>

<!-- Custom Theme JavaScript -->
<?= $this->Html->script('front'); ?>
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

<?= get_option('footer_code'); ?>

</body>

</html>
