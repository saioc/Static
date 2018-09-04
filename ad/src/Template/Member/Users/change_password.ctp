<?php
$this->assign('title', __('Change Password'));
$this->assign('description', '');
$this->assign('content_title', __('Change Password'));

?>

<div class="box box-primary">
    <div class="box-body">

        <?= $this->Form->create($user); ?>

        <?= $this->Form->hidden('id'); ?>

        <?=
        $this->Form->input('current_password', [
            'label' => __('Current Password'),
            'class' => 'form-control',
            'type' => 'password'
        ])

        ?>

        <?=
        $this->Form->input('password', [
            'label' => __('New Password'),
            'class' => 'form-control',
            'type' => 'password'
        ])

        ?>

        <?=
        $this->Form->input('confirm_password', [
            'label' => __('Re-enter New Password'),
            'class' => 'form-control',
            'type' => 'password'
        ])

        ?>

        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary btn-lg']); ?>

        <?= $this->Form->end() ?>

    </div>
</div>
