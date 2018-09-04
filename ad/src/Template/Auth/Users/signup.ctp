<?php
$this->assign('title', __('Create an Account'));
$this->assign('description', '');
?>

<p class="login-box-msg"><?= __('Register a new membership') ?></p>

<?php
// echo $this->Flash->render('auth')
?>

<?= $this->Form->create($user, ['id' => 'signup-form']); ?>

<?=
$this->Form->input('username', [
    'label' => false,
    'placeholder' => __('Username'),
    'class' => 'form-control'
])
?>


<?=
$this->Form->input('email', [
    'label' => false,
    'placeholder' => __('Email'),
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

<?=
$this->Form->input('password_compare', [
    'type' => 'password',
    'label' => false,
    'placeholder' => __('Re-enter Password'),
    'class' => 'form-control'
])
?>

<?php if ((get_option('enable_captcha_signup') == 'yes') && isset_captcha()) : ?>
    <div class="form-group captcha">
        <div id="captchaSignup" style="display: inline-block;"></div>
    </div>
<?php endif; ?>


<div class="form-group">
    <label><?= __(
        "By signing up, you agree to the {0} and {1}.",
        "<a href='" . $this->Url->build('/') . 'pages/terms' . "' target='_blank'>" .
            __('Terms of Service') . "</a>",
        "<a href='" . $this->Url->build('/') . 'pages/privacy' . "' target='_blank'>" .
        __('Privacy Policy') . "</a>"
    ) ?></label>
</div>

<?= $this->Form->button(__('Submit'), [
    'class' => 'btn btn-primary btn-block btn-flat btn-captcha',
    'id' => 'invisibleCaptchaSignup'
]); ?>

<?= $this->Form->end() ?>

<div class="social-auth-links text-center">
    <p>- <?= __("OR") ?> -</p>

    <?php if ((bool)get_option('social_login_facebook', false)) : ?>
        <a class="btn btn-block btn-social btn-facebook" href="<?= $this->Url->build([
            'controller' => 'Users',
            'action' => 'signin',
            '?' => ['provider' => 'Facebook']
        ]); ?>">
            <i class="fa fa-facebook"></i> <?= __("Sign up with Facebook") ?>
        </a>
    <?php endif; ?>

    <?php if ((bool)get_option('social_login_twitter', false)) : ?>
        <a class="btn btn-block btn-social btn-twitter" href="<?= $this->Url->build([
            'controller' => 'Users',
            'action' => 'signin',
            '?' => ['provider' => 'Twitter']
        ]); ?>">
            <i class="fa fa-twitter"></i> <?= __("Sign up with Twitter") ?>
        </a>
    <?php endif; ?>

    <?php if ((bool)get_option('social_login_google', false)) : ?>
        <a class="btn btn-block btn-social btn-google" href="<?= $this->Url->build([
            'controller' => 'Users',
            'action' => 'signin',
            '?' => ['provider' => 'Google']
        ]); ?>">
            <i class="fa fa-google-plus"></i> <?= __("Sign up with Google") ?>
        </a>
    <?php endif; ?>

</div>

<a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'signin', 'prefix' => 'auth']); ?>"
   class="text-center"><?= __('I already have a membership') ?></a>
