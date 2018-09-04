<?php
$this->assign('title', __('Add Plan'));
$this->assign('description', '');
$this->assign('content_title', __('Add Plan'));
?>

<link rel="stylesheet"
      href="//cdn.rawgit.com/olance/jQuery-switchButton/e8a0e7ce8d735bcf9d417a6d8922790eeefee35c/jquery.switchButton.css">
<style>
    .switch-button-label {
        font-size: 25px;
        line-height: 25px;
    }
</style>

<div class="box box-primary">
    <div class="box-body">

        <?= $this->Form->create($plan); ?>

        <?=
        $this->Form->input('enable', [
            'label' => __('Enable')
        ]);
        ?>

        <?=
        $this->Form->input('title', [
            'label' => __('Title'),
            'class' => 'form-control',
            'type' => 'text'
        ]);
        ?>

        <div class="row">
            <div class="col-sm-6">
                <?=
                $this->Form->input('monthly_price', [
                    'label' => __('Monthly Price'),
                    'class' => 'form-control',
                    'type' => 'text'
                ]);
                ?>
            </div>
            <div class="col-sm-6">
                <?=
                $this->Form->input('yearly_price', [
                    'label' => __('Yearly Price'),
                    'class' => 'form-control',
                    'type' => 'text'
                ]);
                ?>
            </div>
        </div>

        <?=
        $this->Form->input('description', [
            'label' => __('Description'),
            'class' => 'form-control text-editor',
            'type' => 'textarea'
        ]);
        ?>

        <table class="table table-hover table-striped">
            <tr>
                <td>
                    <span style="font-weight: bold;"><?= __('Edit Link') ?></span>
                    <span class="help-block"><?= __("Enabling this feature will allow short link creator " .
                            "to edit his links but without editing the long URL.") ?></span>
                </td>
                <td><?= $this->Form->checkbox('edit_link', ['class' => 'switchButton']); ?></td>
            </tr>
            <tr>
                <td>
                    <span style="font-weight: bold;"><?= __('Edit Long URL') ?></span>
                    <span class="help-block">
                        <?= __("Enabling this feature will allow short link creator to edit the long URL " .
                            "for his links. You must enable 'Edit Link' feature to use this feature.") ?>
                    </span>
                </td>
                <td><?= $this->Form->checkbox('edit_long_url', ['class' => 'switchButton']); ?></td>
            </tr>
            <tr>
                <td>
                    <span style="font-weight: bold;">* <?= __('Remove Ads') ?></span>
                    <span class="help-block"><?= __("Enabling this feature will allow the logged in visitors " .
                            "in who are on this plan not to show the ads on area 1.") ?></span>
                </td>
                <td><?= $this->Form->checkbox('disable_ads', ['class' => 'switchButton']); ?></td>
            </tr>
            <tr>
                <td>
                    <span style="font-weight: bold;">* <?= __('Remove Captcha') ?></span>
                    <span class="help-block"><?= __("Enabling this feature will allow the logged in visitors " .
                            "to escape the captcha step and see the short link page directly.") ?></span>
                </td>
                <td><?= $this->Form->checkbox('disable_captcha', ['class' => 'switchButton']); ?></td>
            </tr>
            <tr>
                <td>
                    <span style="font-weight: bold;">* <?= __('Direct') ?></span>
                    <span class="help-block"><?= __("Enabling this feature will allow the logged in visitors " .
                            "to go to directly the long URL without seeing the short link page.") ?></span>
                </td>
                <td><?= $this->Form->checkbox('direct', ['class' => 'switchButton']); ?></td>
            </tr>
            <tr>
                <td>
                    <span style="font-weight: bold;"><?= __('Custom Alias') ?></span>
                    <span class="help-block">
                        <?= __("Enabling this feature will allow short link creator to add a custom alias " .
                            "when shorten a url.") ?>
                    </span>
                </td>
                <td><?= $this->Form->checkbox('alias', ['class' => 'switchButton']); ?></td>
            </tr>
            <tr>
                <td>
                    <span style="font-weight: bold;"><?= __('Referral Earnings') ?></span>
                    <span class="help-block">
                        <?= __("Enabling this feature will allow the publisher to earn from his referrals.") ?>
                    </span>
                </td>
                <td><?= $this->Form->checkbox('referral', ['class' => 'switchButton']); ?></td>
            </tr>
            <tr>
                <td>
                    <span style="font-weight: bold;"><?= __('Link Statistics') ?></span>
                    <span class="help-block">
                        <?= __("Enabling this feature will allow short link creator to view short link Statistics.") ?>
                    </span>
                </td>
                <td><?= $this->Form->checkbox('stats', ['class' => 'switchButton']); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><?= __('Quick Link Tool') ?></td>
                <td><?= $this->Form->checkbox('api_quick', ['class' => 'switchButton']); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><?= __('Mass Shrinker Tool') ?></td>
                <td><?= $this->Form->checkbox('api_mass', ['class' => 'switchButton']); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><?= __('Full Page Script Tool') ?></td>
                <td><?= $this->Form->checkbox('api_full', ['class' => 'switchButton']); ?></td>
            </tr>
            <tr>
                <td style="font-weight: bold;"><?= __('Developers API Tool') ?></td>
                <td><?= $this->Form->checkbox('api_developer', ['class' => 'switchButton']); ?></td>
            </tr>
        </table>

        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']); ?>

        <?= $this->Form->end(); ?>

        <br>

        <p>* <?= __("This feature requires the visitor to the short link to be logged in then this feature " .
                "will take effect.") ?></p>
    </div>
</div>

<?php $this->start('scriptBottom'); ?>

<script src="//cdn.ckeditor.com/4.6.2/full/ckeditor.js"></script>
<script>
    $(document).ready(function () {
        CKEDITOR.replaceClass = 'text-editor';
        CKEDITOR.config.allowedContent = true;
        CKEDITOR.dtd.$removeEmpty['span'] = false;
        CKEDITOR.dtd.$removeEmpty['i'] = false;
    });
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="//cdn.rawgit.com/olance/jQuery-switchButton/e8a0e7ce8d735bcf9d417a6d8922790eeefee35c/jquery.switchButton.js"></script>

<script>
    $("input.switchButton[type=checkbox]").switchButton({
        width: 50,
        height: 20,
        button_width: 25,
        on_label: '<?= __("Yes") ?>',
        off_label: '<?= __("No") ?>'
    });
</script>

<?php $this->end(); ?>
