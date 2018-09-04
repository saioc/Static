<?php
$this->assign('title', __('Sign In'));
$this->assign('description', '');
?>

<p class="login-box-msg"><?= __('Sign in to start your session') ?></p>

<?php
// echo $this->Flash->render('auth')

?>

<?= $this->Form->create($user, ['id' => 'signin-form']); ?>

<?=
$this->Form->input('username', [
    'label' => false,
    'placeholder' => __('Username or email address'),
    'class' => 'form-control'
])

?>

<?=
$this->Form->input('password', [
    'label' => false,
    'placeholder' => __('Password'),
    'class' => 'form-control'
])

?>

<?= $this->Form->button(__('Sign In'), ['class' => 'btn btn-primary btn-block btn-flat']); ?>

<?= $this->Form->end() ?>

<div class="social-auth-links text-center">
    <p>- <?= __("OR") ?> -</p>

    <?php if ((bool)get_option('social_login_facebook', false)) : ?>
        <a class="btn btn-block btn-social btn-facebook" href="<?= $this->Url->build([
            'controller' => 'Users',
            'action' => 'signin',
            '?' => ['provider' => 'Facebook']
        ]); ?>">
            <i class="fa fa-facebook"></i> <?= __("Sign in with Facebook") ?>
        </a>
    <?php endif; ?>

    <?php if ((bool)get_option('social_login_twitter', false)) : ?>
        <a class="btn btn-block btn-social btn-twitter" href="<?= $this->Url->build([
            'controller' => 'Users',
            'action' => 'signin',
            '?' => ['provider' => 'Twitter']
        ]); ?>">
            <i class="fa fa-twitter"></i> <?= __("Sign in with Twitter") ?>
        </a>
    <?php endif; ?>

    <?php if ((bool)get_option('social_login_google', false)) : ?>
        <a class="btn btn-block btn-social btn-google" href="<?= $this->Url->build([
            'controller' => 'Users',
            'action' => 'signin',
            '?' => ['provider' => 'Google']
        ]); ?>">
            <i class="fa fa-google-plus"></i> <?= __("Sign in with Google") ?>
        </a>
    <?php endif; ?>

</div>

<a href="<?= $this->Url->build([
    'controller' => 'Users',
    'action' => 'forgotPassword',
    'prefix' => 'auth'
]); ?>"><?= __('I forgot my password') ?></a>
<br>
<?php if ((bool)get_option('close_registration', false) === false) : ?>
    <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'signup', 'prefix' => 'auth']); ?>"
       class="text-center"><?= __('Register a new membership') ?></a>
<?php endif; ?>

<?php $this->start('scriptBottom'); ?>

<script>
    var url_href = window.location.href;
    if (url_href.substr(-1) === '#') {
        history.pushState("", document.title, url_href.substr(0, url_href.length - 1));
    }
</script>

<?php $this->end(); ?>
