<?= $this->Html->charset(); ?>
<title><?= h($this->fetch('title')); ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="<?= h($this->fetch('description')); ?>">

<?= $this->Html->meta('icon'); ?>

<link href="//fonts.googleapis.com/css?family=Montserrat:400,700%7CMuli:300,300i,400" rel="stylesheet">

<?= $this->Html->css('/vendor/bootstrap/css/bootstrap.min.css'); ?>
<?= $this->Html->css('/vendor/font-awesome/css/font-awesome.min.css'); ?>
<?= $this->Html->css('/vendor/animate.min.css'); ?>

<?= $this->Html->css('/vendor/owl/owl.carousel.min.css'); ?>
<?= $this->Html->css('/vendor/owl/owl.theme.default.css'); ?>

<?= $this->Html->css('front.css?ver=' . APP_VERSION); ?>
<?= $this->Html->css('app.css?ver=' . APP_VERSION); ?>

<?php
echo $this->fetch('meta');
echo $this->fetch('css');
echo $this->fetch('script');

?>

<?= get_option('head_code'); ?>
<?= $this->fetch('scriptTop') ?>

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
