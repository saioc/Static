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
    echo $this->Html->css('//adminlte.io/themes/AdminLTE/dist/css/skins/_all-skins.min.css');
    echo $this->Html->css('app.css?ver=' . APP_VERSION);
    if (get_option('language_direction') == 'rtl') {
        echo $this->Html->css('app-rtl');
    }
    echo $this->fetch('meta');
    echo $this->fetch('css');
    echo $this->fetch('script');

    ?>

    <?= get_option('admin_head_code'); ?>

    <?= $this->fetch('scriptTop') ?>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  <style>.treeview-menu{display: block;}.fade:not(.show) { opacity: 1;} </style>
</head>
<body class="<?= (get_option('language_direction') == 'rtl' ? "rtl" : "") ?> hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <!-- Main Header -->
    <header class="main-header">

        <!-- Logo -->
        <a href="<?= $this->Url->build('/'); ?>" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><?= h(preg_replace('/(\B.|\s+)/', '', get_option('site_name'))) ?></span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><?= h(get_option('site_name')) ?></span>
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
                                'prefix' => 'member'
                            ]); ?>">
                                <i class="fa fa-dashboard"></i> <?= __('Member Area') ?>
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

            <?php if (in_array($user['role'], ['admin']) && require_database_upgrade()) : ?>
                <div class="text-center" style="padding: 10px 0;">
                    <button class="btn btn-danger" onclick="location.href='<?= $this->Url->build([
                        'controller' => 'Upgrade',
                        'action' => 'index',
                        'prefix' => 'admin'
                    ]); ?>'"><i class="fa fa-refresh"></i> <?= __('Complete Upgrade Process') ?></button>
                </div>
            <?php endif; ?>

            <!-- Sidebar Menu -->
            <ul class="sidebar-menu">
                <li><a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'dashboard']); ?>"><i
                                class="fa fa-dashboard"></i> <span><?= __('Statistics') ?></span></a></li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-pie-chart"></i> <span><?= __('Reports') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Reports',
                                'action' => 'campaigns'
                            ]); ?>"><?= __('Campaigns') ?></a></li>
                    </ul>
                </li>

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
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Links',
                                'action' => 'inactive'
                            ]); ?>"><?= __('Inactive Links') ?></a></li>
                    </ul>
                </li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-database"></i> <span><?= __('Campaigns') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Campaigns',
                                'action' => 'index'
                            ]); ?>"><?= __('List') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Campaigns',
                                'action' => 'createInterstitial'
                            ]); ?>"><?= __('Create Interstitial Campaign') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Campaigns',
                                'action' => 'createBanner'
                            ]); ?>"><?= __('Create Banner Campaign') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Campaigns',
                                'action' => 'createPopup'
                            ]); ?>"><?= __('Create Popup Campaign') ?></a></li>
                    </ul>
                </li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-line-chart"></i> <span><?= __('Ads') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Options',
                                'action' => 'interstitial',
                                'prefix' => 'admin'
                            ]); ?>"><?= __('Interstitial') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Options',
                                'action' => 'banner',
                                'prefix' => 'admin'
                            ]); ?>"><?= __('Banner') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Options',
                                'action' => 'popup',
                                'prefix' => 'admin'
                            ]); ?>"><?= __('Popup') ?></a></li>
                    </ul>
                </li>

                <li><a href="<?php echo $this->Url->build(['controller' => 'Withdraws', 'action' => 'index']); ?>"><i
                                class="fa fa-dollar"></i> <span><?= __('Withdraws') ?></span></a></li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-users"></i> <span><?= __('Users') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Users',
                                'action' => 'index'
                            ]); ?>"><?= __('List') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Users',
                                'action' => 'add'
                            ]); ?>"><?= __('Add') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Users',
                                'action' => 'referrals'
                            ]); ?>"><?= __('Referrals') ?></a></li>
                    </ul>
                </li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-bars"></i> <span><?= __('Membership Plans') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Plans',
                                'action' => 'index'
                            ]); ?>"><?= __('List') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Plans',
                                'action' => 'add'
                            ]); ?>"><?= __('Add') ?></a></li>
                    </ul>
                </li>

                <li><a href="<?php echo $this->Url->build(['controller' => 'Invoices', 'action' => 'index']); ?>"><i
                                class="fa fa-credit-card"></i> <span><?= __('Invoices') ?></span></a></li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-file-text-o"></i> <span><?= __('Blog') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Posts',
                                'action' => 'index'
                            ]); ?>"><?= __('Posts List') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Posts',
                                'action' => 'add'
                            ]); ?>"><?= __('Add Post') ?></a></li>
                    </ul>
                </li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-files-o"></i> <span><?= __('Pages') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Pages',
                                'action' => 'index'
                            ]); ?>"><?= __('List') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Pages',
                                'action' => 'add'
                            ]); ?>"><?= __('Add') ?></a></li>
                    </ul>
                </li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-quote-left"></i> <span><?= __('Testimonials') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Testimonials',
                                'action' => 'index'
                            ]); ?>"><?= __('List') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Testimonials',
                                'action' => 'add'
                            ]); ?>"><?= __('Add') ?></a></li>
                    </ul>
                </li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-bullhorn"></i> <span><?= __('Announcements') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Announcements',
                                'action' => 'index'
                            ]); ?>"><?= __('List') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Announcements',
                                'action' => 'add'
                            ]); ?>"><?= __('Add') ?></a></li>
                    </ul>
                </li>

                <li class="treeview">
                    <a href="#"><i class="fa fa-gears"></i> <span><?= __('Settings') ?></span> <i
                                class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Options',
                                'action' => 'index'
                            ]); ?>"><?= __('Settings') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Options',
                                'action' => 'email'
                            ]); ?>"><?= __('Email') ?></a></li>
                        <li><a href="<?php echo $this->Url->build([
                                'controller' => 'Options',
                                'action' => 'socialLogin'
                            ]); ?>"><?= __('Social Login') ?></a></li>
                    </ul>
                </li>

            </ul>
            <!-- /.sidebar-menu -->
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
            <?php if (version_compare(PHP_VERSION, '5.6.0') < 0) : ?>
                <div class="alert alert-danger" role="alert">
                    <?= __("AdLinkFly will work only on PHP <b>5.6.0</b> or higher starting from the next updates so please ask your hosting company to upgrade PHP version for you as soon as possible.") ?>
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
            <a href="http://www.dibei.org/?ref=adf372.footer" target="_blank"><?= __('Version') ?> <?= APP_VERSION ?></a>
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
<?= $this->Html->script('//cdn.rawgit.com/almasaeed2010/AdminLTE/v2.3.11/dist/js/app.js'); ?>
<?= $this->fetch('scriptBottom') ?>
</body>
</html>
