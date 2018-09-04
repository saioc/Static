<!DOCTYPE html>
<html lang="<?= locale_get_primary_language(null) ?>">
<head>
    <?= $this->Html->charset(); ?>
    <title><?= h($this->fetch('title')); ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= h($this->fetch('description')); ?>">
  
    <?php
    echo $this->Html->meta('icon');

    //echo $this->Html->css( 'base.css' );
    //echo $this->Html->css( 'cake.css' );
    echo $this->Html->css('//cdn.bootcss.com/bootstrap/4.1.1/css/bootstrap.min.css');
    //echo $this->Html->css( '//cdn.bootcss.com/bootstrap-rtl/3.3.4/css/bootstrap-flipped.min.css' );
    if (get_option('language_direction') == 'rtl') {
        echo $this->Html->css('//cdn.bootcss.com/bootstrap-rtl/3.3.4/css/bootstrap-flipped.min.css');
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

    <?= get_option('head_code'); ?>

    <?= $this->fetch('scriptTop') ?>

    <!-- error -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="<?= (get_option('language_direction') == 'rtl' ? "rtl" : "") ?> layout-top-nav skin-blue">
<?= get_option('after_body_tag_code'); ?>

<div class="wrapper">
    <div class="content-wrapper">
        <section class="content">
            <div class="container">
                <?= $this->Flash->render() ?>
                <?= $this->fetch('content') ?>
            </div>
        </section>
    </div>

</div>
<?= $this->Html->script('//cdn.bootcss.com/jquery/3.3.1/jquery.min.js'); ?>
<?= $this->Html->script('//cdn.bootcss.com/bootstrap/4.1.1/js/bootstrap.min.js'); ?>
<?= $this->Html->script('//cdn.bootcss.com/clipboard.js/2.0.1/clipboard.min.js'); ?>

<?= $this->element('js_vars'); ?>

<?= $this->Html->script('app.js?ver=' . APP_VERSION); ?>
<?= $this->Html->script('//cdn.rawgit.com/almasaeed2010/AdminLTE/v2.3.11/dist/js/app.js'); ?>
<?= $this->fetch('scriptBottom') ?>
<?= get_option('footer_code'); ?>
</body>
</html>
