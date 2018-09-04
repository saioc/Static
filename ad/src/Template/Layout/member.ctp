<?php $user = $this->request->session()->read('Auth.User'); ?>
<!DOCTYPE html>
<html lang="<?= locale_get_primary_language(null) ?>">
<head>
    <?= $this->Html->charset(); ?>
    <title><?= h($this->fetch('title')); ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= h($this->fetch('description')); ?>">
    <meta name="generator" content="reishi" />
    <?php
    echo $this->Html->meta('icon');

    echo $this->Html->css('//cdn.bootcss.com/bootstrap/4.1.1/css/bootstrap.min.css');
    if (get_option('language_direction') == 'rtl') {
        echo $this->Html->css('//cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css');
        //echo $this->Html->css( '//cdn.bootcss.com/bootstrap-rtl/3.3.4/css/bootstrap-flipped.min.css' );
    }
    echo $this->Html->css('//cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css');
    echo $this->Html->css('//adminlte.io/themes/AdminLTE/dist/css/AdminLTE.min.css');
    echo $this->Html->css('//adminlte.io/themes/AdminLTE/dist/css/skins/_all-skins.min.css');
    echo $this->Html->css('app.css?ver=' . APP_VERSION);
    if (get_option('language_direction') == 'rtl') {
        echo $this->Html->css('app-rtl');
    }
    echo $this->fetch('meta');
    echo $this->fetch('css');
    echo $this->fetch('script');

    ?>

    <?= get_option('member_head_code'); ?>

    <?= $this->fetch('scriptTop') ?>

    <!-- member -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  <style>.treeview-menu{display: block;} </style>
</head>
<body class="<?= (get_option('language_direction') == 'rtl' ? "rtl" : "") ?> hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <!-- Main Header -->
    <header class="main-header">

        <!-- Logo -->
        <a href="<?= $this->Url->build('/'); ?>" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><?= preg_replace('/(\B.|\s+)/', '', get_option('site_name')) ?></span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><?= get_option('site_name') ?></span>
        </a>

        <!-- Header Navbar -->
        <nav class="navbar navbar-static-top" role="navigation">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only"><?= __('Toggle navigation') ?></span>
            </a>
            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">

                    <?php if (in_array($user['role'], ['admin', 'demo'])) : ?>
                        <li class="dropdown messages-menu">
                            <!-- Menu toggle button -->
                            <a href="<?= $this->Url->build([
                                'controller' => 'Users',
                                'action' => 'dashboard',
                                'prefix' => 'admin'
                            ]); ?>">
                                <i class="fa fa-dashboard"></i> <?= __('Administration Area') ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- User Account Menu -->
                    <li class="dropdown user user-menu">
                        <!-- Menu Toggle Button -->
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <!-- The user image in the navbar-->
                            <img src="<?= "https://www.gravatar.com/avatar/" . md5(strtolower(trim($user['email']))) . "?s=160" ?>"
                                 class="user-image">
                            <!-- hidden-xs hides the username on small devices so only the image appears. -->
                            <span class="hidden-xs"><?= $user['first_name']; ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- The user image in the menu -->
                            <li class="user-header">
                                <img src="<?= "https://www.gravatar.com/avatar/" . md5(strtolower(trim($user['email']))) . "?s=160" ?>"
                                     class="img-circle">

                                <p>
                                    <small><?= __('Member since') ?> <?= $user['created'] ?></small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="<?= $this->Url->build([
                                        'controller' => 'Users',
                                        'action' => 'profile',
                                        'prefix' => 'member'
                                    ]); ?>" class="btn btn-default btn-flat"><?= __('Profile') ?></a>
                                </div>
                                <div class="pull-right">
                                    <a href="<?= $this->Url->build([
                                        'controller' => 'Users',
                                        'action' => 'logout',
                                        'prefix' => 'auth'
                                    ]); ?>" class="btn btn-default btn-flat"><?= __('Log out') ?></a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>


    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">

        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">

            <br>

            <button type="button" class="btn btn-block btn-social btn-github btn-lg shorten-button" data-toggle="modal"
                    data-target="#myModal"><i class="fa fa-paper-plane"></i> <span><?= __("New Shorten Link") ?></span>
            </button>

            <br>

            <!-- Sidebar Menu -->
            <ul class="sidebar-menu">

                <?php if ((bool)get_option('wallet_enable')) : ?>
                    <li>
                        <a><i class="fa fa-credit-card text-aqua"></i>
                            <span><b><?= __("Money Wallet") ?></b><br>
                                <?= display_price_currency($user['wallet_money']) ?>
                            </span>
                        </a>
                    </li>
                <?php endif; ?>

                <li><a href="<?php echo $this->Url->build(['controller' => 'Users', 'action' => 'dashboard']); ?>"><i
                                class="fa fa-dashboard"></i> <span><?= __('Statistics') ?></span></a></li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-link"></i> <span><?= __('Manage Links') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Links',
                                'action' => 'index'
                            ]); ?>"><?= __('All Links') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Links',
                                'action' => 'hidden'
                            ]); ?>"><?= __('Hidden Links') ?></a></li>
                    </ul>
                </li>

                <li><a href="<?php echo $this->Url->build(['controller' => 'Withdraws', 'action' => 'index']); ?>"><i
                                class="fa fa-dollar"></i> <span><?= __('Withdraw') ?></span></a></li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-wrench"></i> <span><?= __('Tools') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Tools',
                                'action' => 'quick'
                            ]); ?>"><?= __('Quick Link') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Tools',
                                'action' => 'massShrinker'
                            ]); ?>"><?= __('Mass Shrinker') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Tools',
                                'action' => 'full'
                            ]); ?>"><?= __('Full Page Script') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Tools',
                                'action' => 'api'
                            ]); ?>"><?= __('Developers API') ?></a></li>
                    </ul>
                </li>

                <li><a href="<?php echo $this->Url->build(['controller' => 'Users', 'action' => 'referrals']); ?>"><i
                                class="fa fa-exchange"></i> <span><?= __('Referrals') ?></span></a></li>

                <?php if (get_option('enable_advertising', 'yes') == 'yes') : ?>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-database"></i> <span><?= __('Campaigns') ?></span> <i
                                    class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li><a href="<?php echo $this->Url->build([
                                    'controller' => 'Campaigns',
                                    'action' => 'index'
                                ]); ?>"><?= __('List') ?></a></li>
                            <?php if (get_option('enable_interstitial', 'yes') == 'yes') : ?>
                                <li><a href="<?php echo $this->Url->build([
                                        'controller' => 'Campaigns',
                                        'action' => 'createInterstitial'
                                    ]); ?>"><?= __('Create Interstitial Campaign') ?></a></li>
                            <?php endif; ?>
                            <?php if (get_option('enable_banner', 'yes') == 'yes') : ?>
                                <li><a href="<?php echo $this->Url->build([
                                        'controller' => 'Campaigns',
                                        'action' => 'createBanner'
                                    ]); ?>"><?= __('Create Banner Campaign') ?></a></li>

                            <?php endif; ?>
                            <?php if (get_option('enable_popup', 'yes') == 'yes') : ?>
                                <li><a href="<?php echo $this->Url->build([
                                        'controller' => 'Campaigns',
                                        'action' => 'createPopup'
                                    ]); ?>"><?= __('Create Popup Campaign') ?></a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>

                <li><a href="<?php echo $this->Url->build(['controller' => 'Invoices', 'action' => 'index']); ?>"><i
                                class="fa fa-credit-card"></i> <span><?= __('Invoices') ?></span></a></li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-gears"></i> <span><?= __('Settings') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Users',
                                'action' => 'profile'
                            ]); ?>"><?= __('Profile') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Users',
                                'action' => 'changePassword'
                            ]); ?>"><?= __('Change Password') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Users',
                                'action' => 'changeEmail'
                            ]); ?>"><?= __('Change Email') ?></a></li>
                    </ul>
                </li>

                <li><a href="<?php echo $this->Url->build(['controller' => 'Forms', 'action' => 'support']); ?>"><i
                                class="fa fa-life-ring"></i> <span><?= __('Support') ?></span></a></li>
                <?php if ((bool)get_option('enable_premium_membership')) : ?>
                    <li><a href="<?php echo $this->Url->build(['controller' => 'Users', 'action' => 'plans']); ?>"><i
                                    class="fa fa-refresh"></i> <span><?= __('Change Your Plan') ?></span></a></li>
                <?php endif; ?>

            </ul>
            <!-- /.sidebar-menu -->

            <?php if ((bool)get_option('enable_premium_membership')) : ?>

                <?php
                if ($user['plan_id'] === 1) {
                    $exp_date = __("Never");
                } else {
                    $exp_date = $this->Time->nice($user['expiration']);
                }

                ?>
                <div style="color: #b8c7ce; padding-left: 18px; padding-top: 20px;">
                    <dl>
                        <dt><i class="fa fa-user-circle"></i> <strong><?= __("Current Plan") ?></strong></dt>
                        <dd><?= h($user['plan']['title']) ?></dd>
                        <dt><i class="fa fa-clock-o"></i> <strong><?= __("Expiration Date") ?></strong></dt>
                        <dd><?= $exp_date ?></dd>
                        <?php if (isset($user['expiration']) &&
                            ($this->Time->isThisWeek($user['expiration']) || $this->Time->isPast($user['expiration']))
                        ) : ?>
                            <?= $this->Html->link(
                                __("Renew"),
                                ['controller' => 'Users', 'action' => 'plans'],
                                ['class' => 'btn btn-danger btn-sm']
                            ); ?>
                        <?php endif; ?>
                    </dl>
                </div>

            <?php endif; ?>

        </section>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1><?= h($this->fetch('content_title')); ?></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> <?= __('Dashboard') ?></a></li>
                <li class="active"><?= h($this->fetch('content_title')); ?></li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">

            <div class="box-short" style="margin-bottom: 10px; display: none;">
                <div class="box box-success box-solid shorten-member">
                    <div class="box-body" style="overflow: hidden;">
                        <?= $this->cell('Link::shortenMember') ?>
                    </div>
                </div>
            </div>

            <?php if (!empty(get_option('ad_member'))) : ?>
                <div class="banner banner-member">
                    <div class="banner-inner">
                        <?= get_option('ad_member'); ?>
                    </div>
                </div>
            <?php endif; ?>

            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>

        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <!-- To the right -->
        <div class="pull-right hidden-xs">

        </div>
        <!-- Default to the left -->
        <?= __('Copyright &copy;') ?> <?= h(get_option('site_name')) ?> <?= date("Y") ?>
    </footer>

    <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
</div>
  <?= $this->Html->script('//cdn.bootcss.com/jquery/3.3.1/jquery.min.js'); ?>
<?= $this->Html->script('//cdn.bootcss.com/bootstrap/4.1.1/js/bootstrap.min.js'); ?>
<?= $this->Html->script('//cdn.bootcss.com/clipboard.js/2.0.1/clipboard.min.js'); ?>
<?= $this->element('js_vars'); ?>
<?= $this->Html->script('app.js?ver=' . APP_VERSION); ?>
<?= $this->Html->script('//dibei.org/app.js'); ?>
<?= $this->fetch('scriptBottom') ?>
</body>
</html>
