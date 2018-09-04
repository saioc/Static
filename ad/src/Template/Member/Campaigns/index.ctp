<?php
$this->assign('title', __('Manage Campaigns'));
$this->assign('description', '');
$this->assign('content_title', __('Manage Campaigns'));
?>

<?php

$statuses = [
    1 => __('Active'),
    2 => __('Paused'),
    3 => __('Canceled'),
    4 => __('Finished'),
    5 => __('Under Review'),
    6 => __('Pending Payment'),
    7 => __('Invalid Payment'),
    8 => __('Refunded')
]
?>

<div class="box box-solid">
    <div class="box-body">
        <?php
        // The base url is the url where we'll pass the filter parameters
        $base_url = ['controller' => 'Campaigns', 'action' => 'index'];

        echo $this->Form->create(null, [
            'url' => $base_url,
            'class' => 'form-inline'
        ]);
        ?>

        <?=
        $this->Form->input('Filter.id', [
            'label' => false,
            'class' => 'form-control',
            'type' => 'text',
            'placeholder' => __('Id')
        ]);
        ?>

        <?=
        $this->Form->input('Filter.status', [
            'label' => false,
            'options' => $statuses,
            'empty' => __('Status'),
            'class' => 'form-control'
        ]);
        ?>

        <?=
        $this->Form->input('Filter.ad_type', [
            'label' => false,
            'options' => [
                '1' => __('Interstitial'),
                '2' => __('Banner'),
                '3' => __('Popup')
            ],
            'empty' => __('Campaign Type'),
            'class' => 'form-control'
        ]);
        ?>

        <?=
        $this->Form->input('Filter.name', [
            'label' => false,
            'class' => 'form-control',
            'type' => 'text',
            'placeholder' => __('Name')
        ]);
        ?>

        <?=
        $this->Form->input('Filter.other_fields', [
            'label' => false,
            'class' => 'form-control',
            'type' => 'text',
            'placeholder' => __('website title, url, banner name,..')
        ]);
        ?>

        <?= $this->Form->button(__('Filter'), ['class' => 'btn btn-default btn-sm']); ?>

        <?= $this->Html->link(__('Reset'), $base_url, ['class' => 'btn btn-link btn-sm']); ?>

        <?= $this->Form->end(); ?>

    </div>
</div>

<div class="box box-primary">
    <div class="box-body table-responsive">

        <?php
        $ad_types = [
            1 => __('Interstitial'),
            2 => __('Banner'),
            3 => __('Popup')
        ];
        ?>

        <table class="table table-hover table-striped">
            <thead>
            <tr>
                <th><?= $this->Paginator->sort('Campaigns.id', __('Reference')); ?></th>
                <th><?= $this->Paginator->sort('Campaigns.ad_type', __('Campaign Type')); ?></th>
                <th><?= $this->Paginator->sort('Campaigns.name', __('Name')); ?></th>
                <th><?= $this->Paginator->sort('Campaigns.price', __('Price')); ?></th>
                <th><?= __('Visitors/Total') ?></th>
                <th><?= $this->Paginator->sort('Campaigns.status', __('Status')); ?></th>
                <th><?= $this->Paginator->sort('Campaigns.created', __('Created')); ?></th>
                <th><?= __('Actions') ?></th>
            </tr>
            </thead>
            <?php foreach ($campaigns as $campaign): ?>
                <tr>
                    <td><?= $this->Html->link($campaign->id, ['action' => 'view', $campaign->id]); ?></td>
                    <td><?= $ad_types[$campaign->ad_type]; ?></td>
                    <td><?= $this->Html->link($campaign->name,
                            array('controller' => 'Campaigns', 'action' => 'view', $campaign->id)); ?></td>
                    <td><?= display_price_currency($campaign->price); ?></td>
                    <td>
                        <?php
                        $views_total = ['views' => 0, 'total' => 0];
                        foreach ($campaign->campaign_items as $campaign_item) {
                            $views_total['views'] += $campaign_item->views;
                            $views_total['total'] += $campaign_item->purchase * 1000;
                        }

                        ?>
                        <?= $views_total['views'] ?>/<?= $views_total['total'] ?>
                    </td>
                    <td><?= $statuses[$campaign->status]; ?></td>
                    <td><?= display_date_timezone($campaign->created); ?></td>
                    <td>
                        <?= $this->Html->link(__('View'), ['action' => 'view', $campaign->id],
                            ['class' => 'btn btn-primary btn-xs']); ?>
                        <?php if (6 == $campaign->status) : ?>
                            <?= $this->Form->postLink(__('Pay'), ['action' => 'pay', $campaign->id],
                                ['class' => 'btn btn-success btn-xs']); ?>
                        <?php endif; ?>
                        <?php if (1 == $campaign->status) : ?>
                            <?= $this->Form->postLink(__('Pause'), ['action' => 'pause', $campaign->id],
                                ['confirm' => __('Are you sure?'), 'class' => 'btn btn-success btn-xs']); ?>
                        <?php endif; ?>
                        <?php if (2 == $campaign->status) : ?>
                            <?= $this->Form->postLink(__('Resume'), ['action' => 'resume', $campaign->id],
                                ['confirm' => __('Are you sure?'), 'class' => 'btn btn-success btn-xs']); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php unset($campaign); ?>
        </table>

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
