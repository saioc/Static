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

<div class="myTestAd" style="height: 5px; width: 5px; position: absolute;"></div>
<iframe id="frame" src="<?= ($plan_disable_ads) ? '' : $campaign_item->campaign->website_url ?>" style="width: 100%; border: none;"></iframe>


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
$this->Form->button(__('Please Wait 10s'), [
    'id' => 'go-submit',
    'class' => 'btn btn-default',
    'onclick' => 'javascript: return false;'
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

    function checkAdblockUser() {
        var myTestAd = $('.myTestAd');
        <?php
        $time = \Cake\I18n\Time::now()->modify('+1 day')->toCookieString();

        ?>

        document.cookie = "adblockUser=0; expires=<?= $time ?>";

        if (myTestAd.filter(':visible').length === 0 ||
            myTestAd.filter(':hidden').length > 0 ||
            myTestAd.height() === 0) {
            //console.log( 'adblockUser' );
            document.cookie = "adblockUser=1; expires=<?= $time ?>";
        }
    }

    $(document).ready(function () {
        window.setTimeout(function () {
            checkAdblockUser();
        }, 1500);

        var counter = $('a.skip-ad');

        window.setTimeout(function () {
            var time = <?= get_option('counter_value', 5) * 1000 ?>,
                delta = 1000,
                tid;

            tid = setInterval(function () {
                time -= delta;
                counter.text('<?= __('Please Wait') ?> ' + (time / 1000) + 's');
                if (time <= 0) {
                    counter.text('<?= __('Skip Ad') ?>');
                    clearInterval(tid);
                    $('#go-link').addClass('go-link');
                    $('#go-link.go-link').submit();
                }
            }, delta);

        }, 500);
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
                //goForm.replaceWith( '<a href="#" class="btn btn-default skip-ad" onclick="javascript: return false;"><?= __('Skip Ad') ?></a>' );
            },
            success: function (result, status, xhr) {
                //console.log( result );
                if (result.url) {
                    //console.log( result.message + ' - ' + result.url );
                    //window.location.href = result.url;
                    $('a.skip-ad').attr('href', result.url).removeAttr('onclick');
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
