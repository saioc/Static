<?php
$this->assign('title', __('View User #{0}', $user->id));
$this->assign('description', '');
$this->assign('content_title', __('View User #{0}', $user->id));

?>

<?php

$statuses = [
    1 => __('Active'),
    2 => __('Pending'),
    3 => __('Inactive')
]

?>

<div class="box box-primary">
    <div class="box-body">

        <legend><?= __('Account Info.') ?></legend>
        <table class="table table-striped table-hover">
            <tr>
                <td><?= __('Id') ?></td>
                <td><?= $user->id ?></td>
            </tr>
            <tr>
                <td><?= __('Status') ?></td>
                <td><?= $statuses[$user->status] ?></td>
            </tr>
            <tr>
                <td><?= __('Username') ?></td>
                <td><?= h($user->username) ?></td>
            </tr>
            <tr>
                <td><?= __('role') ?></td>
                <td><?= h($user->role) ?></td>
            </tr>
            <tr>
                <td><?= __('Email') ?></td>
                <td><?= h($user->email) ?></td>
            </tr>
            <tr>
                <td><?= __('Temp Email') ?></td>
                <td><?= h($user->tempEmail) ?></td>
            </tr>
            <tr>
                <td><?= __('Api Token') ?></td>
                <td><?= h($user->api_token) ?></td>
            </tr>
            <tr>
                <td><?= __('Current Publisher Earnings') ?></td>
                <td><?= display_price_currency($user->publisher_earnings) ?></td>
            </tr>
            <tr>
                <td><?= __('Current Referral Earnings') ?></td>
                <td><?= display_price_currency($user->referral_earnings) ?></td>
            </tr>
            <tr>
                <td><?= __('Modified') ?></td>
                <td><?= display_date_timezone($user->modified) ?></td>
            </tr>
            <tr>
                <td><?= __('Created') ?></td>
                <td><?= display_date_timezone($user->created) ?></td>
            </tr>
        </table>

        <hr>

        <legend><?= __('Withdrawal Info.') ?></legend>
        <table class="table table-striped table-hover">
            <tr>
                <td><?= __('Withdrawal Method') ?></td>
                <td><?= h($user->withdrawal_method) ?></td>
            </tr>
            <tr>
                <td><?= __('Withdrawal Email') ?></td>
                <td><?= h($user->withdrawal_account) ?></td>
            </tr>
        </table>

        <hr>

        <legend><?= __('Billing Info.') ?></legend>
        <table class="table table-striped table-hover">
            <tr>
                <td><?= __('First Name') ?></td>
                <td><?= h($user->first_name) ?></td>
            </tr>
            <tr>
                <td><?= __('Last Name') ?></td>
                <td><?= h($user->last_name) ?></td>
            </tr>
            <tr>
                <td><?= __('Address 1') ?></td>
                <td><?= h($user->address1) ?></td>
            </tr>
            <tr>
                <td><?= __('Address 2') ?></td>
                <td><?= h($user->address2) ?></td>
            </tr>
            <tr>
                <td><?= __('City') ?></td>
                <td><?= h($user->city) ?></td>
            </tr>
            <tr>
                <td><?= __('State') ?></td>
                <td><?= h($user->state) ?></td>
            </tr>
            <tr>
                <td><?= __('ZIP') ?></td>
                <td><?= h($user->zip) ?></td>
            </tr>
            <tr>
                <td><?= __('Country') ?></td>
                <td><?= h($user->country) ?></td>
            </tr>
            <tr>
                <td><?= __('Phone Number') ?></td>
                <td><?= h($user->phone_number) ?></td>
            </tr>
        </table>


        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id], ['class' => 'btn btn-primary']); ?>

        <?= $this->Form->postLink(__('Deactivate'), ['action' => 'deactivate', $user->id],
            ['confirm' => __('Are you sure?'), 'class' => 'btn btn-danger']);

        ?>

    </div>
</div>
