<?php
$this->assign('title', __('Manage Withdraws'));
$this->assign('description', '');
$this->assign('content_title', __('Manage Withdraws'));
?>

<?php
$statuses = [
    1 => __('Approved'),
    2 => __('Pending'),
    3 => __('Complete')
]
?>

<div class="row">
    <div class="col-sm-3">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?= display_price_currency($publishers_earnings); ?></h3>
                <p><?= __('Publishers Available Balance') ?></p>
            </div>
            <div class="icon"><i class="fa fa-money"></i></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?= display_price_currency($referral_earnings); ?></h3>
                <p><?= __('Referral Earnings') ?></p>
            </div>
            <div class="icon"><i class="fa fa-exchange"></i></div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?= display_price_currency($pending_withdrawn); ?></h3>
                <p><?= __('Pending Withdrawn') ?></p>
            </div>
            <div class="icon"><i class="fa fa-share"></i></div>
        </div>
    </div>
    <div class="col-sm-3">
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
        <table class="table table-hover table-striped">
            <thead>
            <tr>
                <th><?= __('Id') ?></th>
                <th><?= __('User') ?></th>
                <th><?= __('Date') ?></th>
                <th><?= __('Status') ?></th>
                <th><?= __('Publisher Earnings') ?></th>
                <th><?= __('Referral Earnings') ?></th>
                <th><?= __('Amount') ?></th>
                <th><?= __('Method') ?></th>
                <th><?= __('Action') ?></th>
            </tr>
            </thead>
            <?php foreach ($withdraws as $withdraw) : ?>
                <tr>
                    <td><?= $withdraw->id; ?></td>
                    <td><?= $this->Html->link($withdraw->user->username, array(
                            'controller' => 'Users',
                            'action' => 'view',
                            $withdraw->user->id,
                            'prefix' => 'admin'
                        )); ?></td>
                    <td><?= display_date_timezone($withdraw->created); ?></td>
                    <td><?= $statuses[$withdraw->status] ?></td>
                    <td><?= display_price_currency($withdraw->publisher_earnings); ?></td>
                    <td><?= display_price_currency($withdraw->referral_earnings); ?></td>
                    <td><?= display_price_currency($withdraw->amount); ?></td>
                    <td><?= $withdraw->method ?></td>
                    <td>
                        <?php if ($withdraw->status == 2) : ?>
                            <?= $this->Form->postLink(
                                __('Approve'),
                                ['action' => 'approve', $withdraw->id],
                                ['confirm' => __('Are you sure?'), 'class' => 'btn btn-success btn-xs']
                            ); ?>
                        <?php endif; ?>

                        <?php if ($withdraw->status == 1) : ?>
                            <?= $this->Form->postLink(
                                __('Complete'),
                                ['action' => 'complete', $withdraw->id],
                                ['confirm' => __('Are you sure?'), 'class' => 'btn btn-success btn-xs']
                            ); ?>
                        <?php endif; ?>

                    </td>
                </tr>
            <?php endforeach; ?>
            <?php unset($withdraw); ?>
        </table>

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
        echo $this->Paginator->prev(
            '«',
            array('tag' => 'li'),
            null,
            array('class' => 'disabled', 'tag' => 'li', 'disabledTag' => 'a')
        );
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
        echo $this->Paginator->next(
            '»',
            array('tag' => 'li'),
            null,
            array('class' => 'disabled', 'tag' => 'li', 'disabledTag' => 'a')
        );
    }

    ?>
</ul>
