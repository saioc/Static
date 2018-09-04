<?php
$this->assign('title', get_option('site_name'));
$this->assign('description', get_option('description'));
$this->assign('content_title', get_option('site_name'));
$this->assign('og_title', $link->title);
$this->assign('og_description', $link->description);
$this->assign('og_image', $link->image);
?>

<?php $this->start('scriptTop'); ?>
<script type="text/javascript">
    if (window.self !== window.top) {
        window.top.location.href = window.location.href;
    }
</script>
<?php $this->end(); ?>

<div class="box-main">

    <?php if (!empty($captcha_ad)) : ?>
        <div class="banner banner-captcha">
            <div class="banner-inner">
                <?= $captcha_ad; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
    $col_num = 6;
    $table_row = 'is-table-row';
    $hidden_class = '';
    if (empty($link->image) && empty($link->title) && empty($link->description)) {
        $col_num = 12;
        $table_row = '';
    }
    if (get_option('short_link_content', 'no') === 'no') {
        $col_num = 12;
        $table_row = '';
        $hidden_class = 'hidden';
    }

    ?>

    <div class="row <?= $table_row ?>">
        <div class="col-md-<?= $col_num ?> <?= $hidden_class ?>">
            <?php if (get_option('short_link_content', 'no') === 'yes') : ?>
                <div class="link-details">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <img class="link-image" src="<?= h($link->image) ?>"/>
                            <h4 class="link-title"><?= h($link->title) ?></h4>
                            <p class="link-description"><?= h($link->description) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-<?= $col_num ?>">

            <?= $this->Flash->render() ?>

            <?= $this->Form->create(null, ['id' => 'link-view']); ?>

            <p style="font-size: 17px;">
                <?= __('Please check the captcha box to proceed to the destination page.') ?>
            </p>

            <?= $this->Form->hidden('ref', ['value' => strtolower(env('HTTP_REFERER'))]); ?>

            <div class="form-group text-center">
                <div id="captchaShortlink" style="display: inline-block;"></div>
            </div>

            <?= $this->Form->button(__('Click here to continue'), [
                'class' => 'btn btn-primary btn-captcha',
                'id' => 'invisibleCaptchaShortlink'
            ]); ?>

            <?= $this->Form->end() ?>
        </div>
    </div>

</div>

<div class="text-left">

    <h3><?= __('What is {0}?', h(get_option('site_name'))) ?></h3>
    <p><?= __(
        '{0} is a completely free tool where you can create short links, which apart from being '.
            'free, you get paid! So, now you can make money from home, when managing and protecting your links. '.
            'Register now!',
        h(get_option('site_name'))
    ) ?></p>

    <h3><?= __('Shorten URLs and earn money') ?></h3>
    <p><?= __("Signup for an account in just 2 minutes. Once you've completed your registration just start '.
    'creating short URLs and sharing the links with your family and friends.") ?></p>

</div>
