<p><?= __('Hello') ?>,</p>

<p><?= __('You have a new user registration') ?></p>

<p><?= __('User Id') ?>: <?= $user->id ?></p>
<p><?= __('Username') ?>: <?= $user->username ?></p>
<p><?= __('Email') ?>: <?= $user->email ?></p>

<p>
    <?= __('Thanks,') ?><br>
    <?= h(get_option('site_name')) ?>
</p>