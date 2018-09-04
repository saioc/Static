<?php
$this->assign('title', __('Support'));
$this->assign('description', '');
$this->assign('content_title', __('Support'));

?>

<div class="box box-primary">
    <div class="box-body">

        <?= $this->Form->create($contact); ?>

        <?=
        $this->Form->input('name', [
            'label' => __('Name'),
            'class' => 'form-control',
            'type' => 'text'
        ]);

        ?>

        <?=
        $this->Form->input('subject', [
            'label' => __('Subject'),
            'class' => 'form-control',
            'type' => 'text'
        ]);

        ?>

        <?=
        $this->Form->input('email', [
            'label' => __('Email'),
            'class' => 'form-control',
            'type' => 'email'
        ]);

        ?>

        <?=
        $this->Form->input('message', [
            'label' => __('Message'),
            'class' => 'form-control',
            'type' => 'textarea'
        ]);

        ?>

        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']); ?>

        <?= $this->Form->end(); ?>

    </div>
</div>
