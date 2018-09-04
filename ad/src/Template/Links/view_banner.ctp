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

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="box box-success">
            <div class="box-body text-center">
                <?php if (!empty($banner_728x90)) : ?>
                    <div class="banner banner-728x90">
                        <div class="banner-inner">
                            <?= $banner_728x90; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <h4><?= __('Your link is almost ready.') ?></h4>

                <span id="countdown" class="countdown">
                    <span id="timer" class="timer"><?= get_option('counter_value', 5) ?></span><br><?= __('Seconds') ?>
                </span>

                <?php if (!empty($banner_468x60)) : ?>
                    <div class="banner banner-468x60">
                        <div class="banner-inner">
                            <?= $banner_468x60; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom: 10px;">
                    <a href="javascript: void(0)" class="btn btn-success btn-lg get-link disabled">
                        <?= __('Please wait...') ?>
                    </a>
                </div>

                <?php if (!empty($banner_336x280)) : ?>
                    <div class="banner banner-336x280">
                        <div class="banner-inner">
                            <?= $banner_336x280; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="myTestAd" style="height: 5px; width: 5px; position: absolute;"></div>

            </div>
            <!-- /.box-body -->
        </div>
    </div>
</div>


<?=
$this->Form->create(null, [
    'url' => ['controller' => 'Links', 'action' => 'go', 'prefix' => false],
    'id' => 'go-link',
    'class' => 'hidden'
]);
?>

<?= $this->Form->hidden('alias', ['value' => $link->alias]); ?>
<?= $this->Form->hidden('ci', ['value' => $campaign_item->campaign_id]); ?>
<?= $this->Form->hidden('cui', ['value' => $campaign_item->campaign->user_id]); ?>
<?= $this->Form->hidden('cii', ['value' => $campaign_item->id]); ?>
<?= $this->Form->hidden('ref', ['value' => strtolower(env('HTTP_REFERER'))]); ?>

<?=
$this->Form->button(__('Submit'), [
    'id' => 'go-submit',
    'class' => 'hidden'
]);
?>

<?= $this->Form->end(); ?>

<?php if (get_option('enable_popup', 'yes') == 'yes') : ?>
    <?=
    $this->Form->create(null, [
        'url' => ['controller' => 'Links', 'action' => 'popad', 'prefix' => false],
        'target' => "_blank",
        'id' => 'go-popup',
        'class' => 'hidden'
    ]);
    ?>

    <?= $this->Form->hidden('pop_ad', ['value' => $pop_ad]); ?>

    <?= $this->Form->end(); ?>
<?php endif; ?>

<?php $this->start('scriptBottom'); ?>

<script>
    <?php if (get_option('enable_popup', 'yes') == 'yes') : ?>
    $(window).on('load', function () {
        $(document).one("click", function (e) {
            $('#go-popup').submit();
        });

        $('#go-popup').one("submit", function (e) {

            //var window_height = $(window).height()-150;
            //var window_width = $(window).width()-150;
            var window_height = screen.height - 150;
            var window_width = screen.width - 150;

            var window_left = Number((screen.width / 2) - (window_width / 2));
            var window_top = Number((screen.height / 2) - (window_height / 2));

            var w = window.open('about:blank', 'Popup_Window', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=' + window_width + ',height=' + window_height + ',left = ' + window_left + ',top = ' + window_top + '');
            this.target = 'Popup_Window';

        });
    });
    <?php endif; ?>

    function bannerHiddenDivs(element) {
        var element = $(element);
        if (element.filter(':visible').length === 0 ||
            element.filter(':hidden').length > 0 ||
            element.height() === 0) {
            return true;
        }
        return false;
    }

    function checkAdblockUser() {

        <?php $time = \Cake\I18n\Time::now()->modify('+1 day')->toCookieString(); ?>

        document.cookie = "adblockUser=0; expires=<?= $time ?>";

        if (bannerHiddenDivs('.myTestAd')) {
            //console.log( 'adblockUser' );
            document.cookie = "adblockUser=1; expires=<?= $time ?>";
        }
    }

    $(document).ready(function () {
        window.setTimeout(function () {
            checkAdblockUser();
        }, 1500);

        var timer = $('#timer');

        window.setTimeout(function () {
            var time = <?= get_option('counter_value', 5) * 1000 ?>,
                delta = 1000,
                tid;

            tid = setInterval(function () {
                if (window.blurred) {
                    return;
                }
                time -= delta;
                timer.text(time / 1000);
                if (time <= 0) {
                    clearInterval(tid);

                    $('#go-link').addClass('go-link');
                    $('#go-link.go-link').submit();
                }
            }, delta);
        }, 500);

        window.onblur = function () {
            window.blurred = true;
        };
        window.onfocus = function () {
            window.blurred = false;
        };
    });

    /**
     * Report invalid link
     */
    $("#go-link").one("submit", function (e) {
        e.preventDefault();
        var goForm = $(this);

        if (!goForm.hasClass('go-link')) {
            return;
        }

        var submitButton = goForm.find('button');

        $.ajax({
            dataType: 'json', // The type of data that you're expecting back from the server.
            type: 'POST', // he HTTP method to use for the request
            url: goForm.attr('action'),
            data: goForm.serialize(), // Data to be sent to the server.
            beforeSend: function (xhr) {
                submitButton.attr("disabled", "disabled");
                $('a.get-link').text('<?= __('Getting link...') ?>');
            },
            success: function (result, status, xhr) {
                //console.log( result );
                if (result.url) {
                    //console.log( result.message + ' - ' + result.url );
                    $('a.get-link').attr('href', result.url).removeClass('disabled').text('<?= __('Get Link') ?>');
                    //submitButton.text( 'Redirecting...' );
                    //goForm.replaceWith( '<button class="btn btn-default" onclick="javascript: return false;">Redirecting...</button>' );
                } else {
                    alert(result.message);
                }
            },
            error: function (xhr, status, error) {
                alert("An error occured: " + xhr.status + " " + xhr.statusText);
            },
            complete: function (xhr, status) {

            }
        });
    });

</script>

<?php $this->end(); ?>
