<?php
$this->assign('title', __('Profile'));
$this->assign('description', '');
$this->assign('content_title', __('Profile'));

?>

<div class="box box-primary">
    <div class="box-body">

        <?= $this->Form->create($user); ?>

        <?= $this->Form->hidden('id'); ?>

        <legend><?= __('Billing Address') ?></legend>

        <div class="row">
            <div class="col-sm-6">
                <?=
                $this->Form->input('first_name', [
                    'label' => __('First Name'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
            <div class="col-sm-6">
                <?=
                $this->Form->input('last_name', [
                    'label' => __('Last Name'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <?=
                $this->Form->input('address1', [
                    'label' => __('Address 1'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
            <div class="col-sm-6">
                <?=
                $this->Form->input('address2', [
                    'label' => __('Address 2'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <?=
                $this->Form->input('city', [
                    'label' => __('City'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
            <div class="col-sm-6">
                <?=
                $this->Form->input('state', [
                    'label' => __('State'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <?=
                $this->Form->input('zip', [
                    'label' => __('ZIP'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
            <div class="col-sm-6">
                <?=
                $this->Form->input('country', [
                    'label' => __('Country'),
                    'options' => get_countries(),
                    'empty' => __('Choose'),
                    'class' => 'form-control'
                ]);

                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <?=
                $this->Form->input('phone_number', [
                    'label' => __('Phone Number'),
                    'class' => 'form-control'
                ])

                ?>
            </div>
        </div>

        <legend><?= __('Withdrawal Info') ?></legend>

        <?php
        $withdrawal_methods = [];

        if ((bool)get_option('wallet_enable', false)) {
            $withdrawal_methods['wallet'] = __('My Wallet');
        }

        if (get_option('paypal_enable', 'no') == 'yes') {
            $withdrawal_methods['paypal'] = __('PayPal');
        }

        if (get_option('payza_enable', 'no') == 'yes') {
            $withdrawal_methods['payza'] = __('Payza');
        }

        if ((bool)get_option('skrill_enable', false)) {
            $withdrawal_methods['skrill'] = __('Skrill');
        }

        if (get_option('coinbase_enable', 'no') == 'yes') {
            $withdrawal_methods['coinbase'] = __('Bitcoin');
        }

        if (get_option('webmoney_enable', 'no') == 'yes') {
            $withdrawal_methods['webmoney'] = __('Web Money');
        }

        if (get_option('banktransfer_enable', 'no') == 'yes') {
            $withdrawal_methods['banktransfer'] = __('Bank Transfer');
        }
        ?>

        <?=
        $this->Form->input('withdrawal_method', [
            'label' => __('Withdrawal Method'),
            'options' => $withdrawal_methods,
            'empty' => __('Choose'),
            'class' => 'form-control'
        ]);

        ?>

        <?=
        $this->Form->input('withdrawal_account', [
            'label' => __('Withdrawal Account'),
            'class' => 'form-control',
            'type' => 'textarea',
            'required' => false
        ])

        ?>
        <div class="help-block">
            <p><?= __('- For PayPal, Payza and Skrill add your email.') ?></p>
            <p><?= __('- For Coinbase add your wallet address.') ?></p>
            <p><?= __('- For Web Money add your purse.') ?></p>
            <p><?= __('- For bank transfer add your account holder name, Bank Name, City/Town, Country, Account number, SWIFT, IBAN and Account currency') ?></p>
        </div>

        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary btn-lg']); ?>

        <?= $this->Form->end() ?>

    </div>
</div>
