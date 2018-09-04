<?php
$this->assign('title', __('Step 3: Create Admin User'));

?>

<div class="install">
    <?php
    echo $this->Form->create($user, [
        'url' => [
            'controller' => 'Install',
            'action' => 'adminuser'
        ]
    ]);

    ?>

    <?=
    $this->Form->input('email', [
        'label' => __('Email'),
        'class' => 'form-control',
        'type' => 'email',
        'required' => 'required'
    ]);

    ?>

    <?=
    $this->Form->input('username', [
        'label' => __('Username'),
        'class' => 'form-control',
        'type' => 'text',
        'required' => 'required'
    ]);

    ?>

    <?=
    $this->Form->input('password', [
        'label' => __('Password'),
        'class' => 'form-control',
        'type' => 'password',
        'required' => 'required'
    ]);

    ?>

    <?=
    $this->Form->input('password_compare', [
        'label' => __('Confirm Password'),
        'class' => 'form-control',
        'type' => 'password',
        'required' => 'required'
    ]);

    ?>

    <div class="form-actions">
        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']); ?>
    </div>
    <?= $this->Form->end(); ?>


</div>

