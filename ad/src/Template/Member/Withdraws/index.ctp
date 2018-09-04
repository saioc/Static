<?php
$this->assign('title', __('Withdraw Funds'));
$this->assign('description', '');
$this->assign('content_title', __('Withdraw Funds'));
?>

<?php
$statuses = [
    1 => __('Approved'),
    2 => __('Pending'),
    3 => __('Complete')
]
?>

<div class="row">
    <div class="col-sm-4">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?= display_price_currency($user->publisher_earnings + $user->referral_earnings); ?></h3>
                <p><?= __('Available Balance') ?></p>
            </div>
            <div class="icon"><i class="fa fa-money"></i></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="small-box bg-red">
            <div class="inner">
                <h3><?= display_price_currency($pending_withdrawn); ?></h3>
                <p><?= __('Pending Withdrawn') ?></p>
            </div>
            <div class="icon"><i class="fa fa-share"></i></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="small-box bg-green">
            <div class="inner">
                <h3><?= display_price_currency($tolal_withdrawn); ?></h3>
                <p><?= __('Tolal Withdrawn') ?></p>
            </div>
            <div class="icon"><i class="fa fa-usd"></i></div>
        </div>
    </div>
</div>


<div class="box box-primary">
    <div class="box-body table-responsive">
        <div class="text-center"><?= $this->Form->postLink(__('Withdraw'), ['action' => 'request'],
                ['confirm' => __('Are you sure?'), 'class' => 'btn btn-success btn-lg']); ?></div>
        <hr>

        <p><?= __("When your account reaches the minimum amount of {0} or more, you may request your earnings by clicking the above button. The payment is then sent to your PayPal account during business days no longer than 4 days after requesting. Please do not contact us regarding payments before due dates.",
                display_price_currency(get_option('minimum_withdrawal_amount', 5))) ?></p>

        <p><?= __('In order to receive your payments you need to fill your payment method and payment ID <a href="{0}">here</a> if you haven\'t done so. You are also requested to fill all the required fields in the Account Details section with accurate data.',
                $this->Url->build(['controller' => 'Users', 'action' => 'profile', 'prefix' => 'member'])) ?></p>

        <hr>

        <table class="table table-hover table-striped">
            <thead>
            <tr>
                <th><?= __('Id') ?></th>
                <th><?= __('Date') ?></th>
                <th><?= __('Status') ?></th>
                <th><?= __('Publisher Earnings') ?></th>
                <th><?= __('Referral Earnings') ?></th>
                <th><?= __('Amount') ?></th>
                <th><?= __('Method') ?></th>
            </tr>
            </thead>
            <?php foreach ($withdraws as $withdraw): ?>
                <tr>
                    <td><?= $withdraw->id ?></td>
                    <td><?= display_date_timezone($withdraw->created); ?></td>
                    <td><?= $statuses[$withdraw->status] ?></td>
                    <td><?= display_price_currency($withdraw->publisher_earnings); ?></td>
                    <td><?= display_price_currency($withdraw->referral_earnings); ?></td>
                    <td><?= display_price_currency($withdraw->amount); ?></td>
                    <td><?= $withdraw->method ?></td>
                </tr>
            <?php endforeach; ?>
            <?php unset($withdraw); ?>
        </table>

        <hr>

        <ul>
            <li><?= __("Pending: The payment is being checked by our team.") ?></li>
            <li><?= __("Approved: The payment has been approved and is waiting to be sent.") ?></li>
            <li><?= __("Complete: The payment has been successfully sent to your Paypal account.") ?></li>
        </ul>
    </div><!-- /.box-body -->
</div>

<ul class="pagination">
    <!-- Shows the previous link -->
    <?php
    if ($this->Paginator->hasPrev()) {
        echo $this->Paginator->prev('«', array('tag' => 'li'), null,
            array('class' => 'disabled', 'tag' => 'li', 'disabledTag' => 'a'));
    }

    ?>
    <!-- Shows the page numbers -->
    <?php //echo $this->Paginator->numbers();    ?>
    <?php
    echo $this->Paginator->numbers(array(
        'modulus' => 4,
        'separator' => '',
        'ellipsis' => '<li><a>...</a></li>',
        'tag' => 'li',
        'currentTag' => 'a',
        'first' => 2,
        'last' => 2
    ));

    ?>
    <!-- Shows the next link -->
    <?php
    if ($this->Paginator->hasNext()) {
        echo $this->Paginator->next('»', array('tag' => 'li'), null,
            array('class' => 'disabled', 'tag' => 'li', 'disabledTag' => 'a'));
    }

    ?>
</ul>
