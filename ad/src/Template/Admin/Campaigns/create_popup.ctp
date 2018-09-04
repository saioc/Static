<?php
$this->assign('title', __('Create Popup Campaign'));
$this->assign('description', '');
$this->assign('content_title', __('Create Popup Campaign'));
?>

<div class="box box-primary">
    <div class="box-body">

        <?php if (
            isset($this->request->query['traffic_source']) &&
            in_array($this->request->query['traffic_source'], [1, 2, 3])
        ) : ?>

            <?php
            $traffic_source = $this->request->query['traffic_source'];
            $popup_price = get_option('popup_price');
            $countries = get_countries(true);
            $i = 0;
            ?>

            <div class="callout callout-success">
                <h4>
                    <i class="fa fa-question-circle"></i> <?= __("Why views/earnings/Statistics/analytics are not counting?") ?>
                </h4>
                <ul>
                    <li><?= __("Important: <b>Default</b> campaigns will not count earnings so you need to create a non-default campaigns and earnings will start count.") ?></li>
                    <li><?= __("Anonymous(not registered users) short links will not earn, only registered can earn.") ?></li>
                    <li><?= __('Visitors must be unique within a 24 hours.') ?></li>
                    <li><?= __('Visitors must have JavaScript enabled') ?></li>
                    <li><?= __('Visitors must have Cookies enabled') ?></li>
                    <li><?= __('Must view your website for at least {0} seconds',
                            h(get_option('counter_value', 5))) ?></li>
                </ul>
            </div>

            <?= $this->Form->create($campaign, ['id' => 'campaign-create']); ?>

            <?=
            $this->Form->hidden('traffic_source', ['value' => $traffic_source]);
            ?>

            <?=
            $this->Form->input('default_campaign', [
                'label' => __('Campaign Type'),
                'options' => [
                    0 => __('Non-default Campaign'),
                    1 => __('Default Campaign')
                ],
                'class' => 'form-control'
            ]);
            ?>
            <span class="help-block">
                <?= __('Default means that campaign will not count earning for publisher.') ?>
            </span>

            <?=
            $this->Form->input('user_id', [
                'label' => __('User'),
                'options' => $users,
                'empty' => __('Choose'),
                'class' => 'form-control'
            ]);

            ?>

            <?=
            $this->Form->input('name', [
                'label' => __('Campaign Name'),
                'class' => 'form-control'
            ]);

            ?>

            <?=
            $this->Form->input('status', [
                'label' => __('Status'),
                'options' => [
                    1 => __('Active'),
                    2 => __('Paused'),
                    3 => __('Canceled'),
                    4 => __('Finished'),
                    5 => __('Under Review'),
                    6 => __('Pending Payment'),
                    7 => __('Invalid Payment'),
                    8 => __('Refunded')
                ],
                'empty' => __('Choose'),
                'class' => 'form-control'
            ]);

            ?>

            <legend><?= __('Website Details') ?></legend>

            <?=
            $this->Form->input('website_title', [
                'label' => __('Title'),
                'class' => 'form-control'
            ]);

            ?>

            <?=
            $this->Form->input('website_url', [
                'label' => __('URL'),
                'class' => 'form-control',
                'type' => 'url'
            ]);

            ?>

            <legend><?= __('Advertising Rates') ?></legend>

            <table class="table table-hover table-striped">
                <thead>
                <tr>
                    <th><?= __('Country') ?></th>
                    <th><?= __('Price / 1,000') ?></th>
                    <th><?= __('Purchase') ?></th>
                </tr>
                </thead>
                <?php foreach ($popup_price as $key => $value) : ?>
                    <?php
                    if (empty($value[$traffic_source]['advertiser'])) {
                        continue;
                    }

                    ?>
                    <tr>
                        <td>
                            <?= $countries[$key] ?>
                            <?= $this->Form->hidden("campaign_items.$i.country", ['value' => $key]); ?>
                            <?= $this->Form->input("campaign_items.$i.advertiser_price",
                                ['type' => 'hidden', 'value' => $value[$traffic_source]['advertiser']]); ?>
                            <?= $this->Form->input("campaign_items.$i.publisher_price",
                                ['type' => 'hidden', 'value' => $value[$traffic_source]['publisher']]); ?>
                        </td>
                        <td>
                            <?= display_price_currency($value[$traffic_source]['advertiser']); ?>
                        </td>
                        <td>
                            <?=
                            $this->Form->input("campaign_items.$i.purchase", [
                                'label' => false,
                                'class' => 'form-control',
                                'type' => 'number'
                            ]);

                            ?>
                        </td>
                    </tr>
                    <?= (0 == $i % 2) ? '</div><div class="row">' : ''; ?>
                    <?php $i++ ?>
                <?php endforeach; ?>
            </table>

            <div class="text-center">
                <p class="text-success"
                   style="font-size: 23px;"><?= __("You have ordered {0} visitors for a total of {1}",
                        "<span id='total-visitors'>0</span>",
                        get_option('currency_symbol', '$') . " <span id='total-price'>0.00</span>") ?></p>
                <?= $this->Form->button(__('Pay Campaign'), ['class' => 'btn btn-success btn-lg']); ?>
            </div>

            <?= $this->Form->end(); ?>

        <?php else : ?>

            <?= $this->Form->create(null, ['type' => 'get']); ?>

            <?=
            $this->Form->input('traffic_source', [
                'label' => __('Traffic Sources'),
                'options' => [
                    '1' => __('Desktop, Mobile and Tablet'),
                    '2' => __('Desktop Only'),
                    '3' => __('Mobile / Tablet Only')
                ],
                'empty' => __('Choose'),
                'class' => 'form-control'
            ]);
            ?>

            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary btn-lg']); ?>

            <?= $this->Form->end(); ?>

        <?php endif; ?>

    </div><!-- /.box-body -->
</div>
