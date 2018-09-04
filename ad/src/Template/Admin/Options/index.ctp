<?php
$this->assign('title', __('Settings'));
$this->assign('description', '');
$this->assign('content_title', __('Settings'));
?>

<?= $this->Form->create($options, [
    'id' => 'form-settings',
    'onSubmit' => "save_settings.disabled=true; save_settings.innerHTML='" . __('Saving ...') . "'; return true;"
]); ?>

<div class="nav-tabs-custom">

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation"><a href="#general" aria-controls="general" role="tab"
                                   data-toggle="tab"><?= __('General') ?></a></li>
        <li role="presentation"><a href="#links" aria-controls="links" role="tab"
                                   data-toggle="tab"><?= __('Links') ?></a></li>
        <li role="presentation"><a href="#integration" aria-controls="integration" role="tab"
                                   data-toggle="tab"><?= __('Integration') ?></a></li>
        <li role="presentation"><a href="#admin-ads" aria-controls="admin-ads" role="tab"
                                   data-toggle="tab"><?= __('Admin Ads') ?></a></li>
        <li role="presentation"><a href="#captcha" aria-controls="captcha" role="tab"
                                   data-toggle="tab"><?= __('Captcha') ?></a></li>
        <li role="presentation"><a href="#security" aria-controls="security" role="tab"
                                   data-toggle="tab"><?= __('Security') ?></a></li>
        <li role="presentation"><a href="#payment" aria-controls="payment" role="tab"
                                   data-toggle="tab"><?= __('Payment') ?></a></li>
        <li role="presentation"><a href="#blog" aria-controls="blog" role="tab" data-toggle="tab"><?= __('Blog') ?></a>
        </li>
        <li role="presentation"><a href="#social" aria-controls="Social Media" role="tab"
                                   data-toggle="tab"><?= __('Social Media') ?></a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" id="general" class="tab-pane fade in active">
            <p></p>
            <div class="row">
                <div class="col-sm-2"><?= __('Site Name') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['site_name']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['site_name']['value']
                    ]);
                    ?>
                    <span class="help-block"><?= __('This is your site name as well as the site meta title.') ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Main Domain') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['main_domain']['id'] . '.value', [
                        'label' => false,
                        'placeholder' => env("HTTP_HOST", ""),
                        'required' => 'required',
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['main_domain']['value']
                    ]);
                    ?>
                    <span class="help-block">
                        <?= __('Main domain used for all pages expect the short link page. Make sure to ' .
                            'remove the "http" or "https" and the trailing slash (/)!. Example: <b>domain.com</b>') ?>
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Default Short URL Domain') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['default_short_domain']['id'] . '.value', [
                        'label' => false,
                        'placeholder' => __("Ex. domian.com"),
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['default_short_domain']['value']
                    ]);
                    ?>
                    <span class="help-block">
                        <?= __('Add the default domain used for the short links. If it is empty, the main ' .
                            'domain will be used. Make sure to remove the "http" or "https" and the trailing slash ' .
                            '(/)!. Example: <b>domain.com</b>') ?>
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Multi Domains') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['multi_domains']['id'] . '.value', [
                        'label' => false,
                        'placeholder' => 'domain1.com,domain2.com',
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['multi_domains']['value']
                    ]);
                    ?>
                    <span class="help-block">
                        <?= __("Add the other domains(don't add the default short URL domain or the main " .
                            "domain) you want users to select between when short links. ex. " .
                            "<b>domain1.com,domain2.com</b> These domains should be parked/aliased to the main " .
                            "domain. Separate by comma, no spaces. Make sure to remove the 'http' or 'https' and " .
                            "the trailing slash (/)!") ?>
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Site Description') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['description']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['description']['value']
                    ]);
                    ?>
                </div>
            </div>

            <?php
            $locale = new \Cake\Filesystem\Folder(APP . 'Locale');
            $languages = $locale->subdirectories(null, false);
            ?>

            <div class="row">
                <div class="col-sm-2"><?= __('Default Language') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['language']['id'] . '.value', [
                        'label' => false,
                        'options' => array_combine($languages, $languages),
                        'value' => $settings['language']['value'],
                        //'empty'   => __( 'Choose' ),
                        'class' => 'form-control'
                    ]);
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Site Languages') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['site_languages']['id'] . '.value', [
                        'label' => false,
                        'type' => 'select',
                        'multiple' => true,
                        'options' => array_combine($languages, $languages),
                        'value' => unserialize($settings['site_languages']['value']),
                        //'empty'   => __( 'Choose' ),
                        'class' => 'form-control'
                    ]);
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Language Automatic Redirect') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['language_auto_redirect']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            1 => __('Yes'),
                            0 => __('No')
                        ],
                        'value' => $settings['language_auto_redirect']['value'],
                        'class' => 'form-control'
                    ]);
                    ?>
                    <span class="help-block">
                        <?= __("Automatically redirect the website visitors to browse the website based on " .
                            " their browser language if it is already available.") ?>
                    </span>
                </div>
            </div>

            <?php
            $plugins_path = new \Cake\Filesystem\Folder(ROOT . '/plugins');
            $plugins = $plugins_path->subdirectories(null, false);
            $themes = [];
            foreach ($plugins as $key => $value) {
                if (preg_match('/Theme$/', $value)) {
                    $themes[$value] = $value;
                }
            }
            ?>

            <div class="row">
                <div class="col-sm-2"><?= __('Select Theme') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['theme']['id'] . '.value', [
                        'label' => false,
                        'options' => $themes,
                        'value' => $settings['theme']['value'],
                        //'empty'   => __( 'Choose' ),
                        'class' => 'form-control'
                    ]);
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Close Registration') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['close_registration']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            1 => __('Yes'),
                            0 => __('No')
                        ],
                        'value' => $settings['close_registration']['value'],
                        'class' => 'form-control'
                    ]);
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Premium Membership') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['enable_premium_membership']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            1 => __('Yes'),
                            0 => __('No')
                        ],
                        'value' => $settings['enable_premium_membership']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>

                </div>
            </div>

            <div class="row hidden">
                <div class="col-sm-2"><?= __('Language Direction') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['language_direction']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'ltr' => __('LTR'),
                            'rtl' => __('RTL')
                        ],
                        'value' => $settings['language_direction']['value'],
                        //'empty'   => __( 'Choose' ),
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Time Zone') ?></div>
                <div class="col-sm-10">
                    <?php
                    $DateTimeZone = \DateTimeZone::listIdentifiers(DateTimeZone::ALL);
                    echo $this->Form->input('Options.' . $settings['timezone']['id'] . '.value', [
                        'label' => false,
                        'options' => array_combine($DateTimeZone, $DateTimeZone),
                        'value' => $settings['timezone']['value'],
                        //'empty'   => __( 'Choose' ),
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Logo URL') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['logo_url']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['logo_url']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Logo URL - Alternative') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['logo_url_alt']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['logo_url_alt']['value']
                    ]);

                    ?>
                    <span class="help-block"><?= __('Alternative logo used on the login page') ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Account Activation by Email') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['account_activate_email']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['account_activate_email']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Advertising') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['enable_advertising']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['enable_advertising']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Referral Percentage') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['referral_percentage']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'number',
                        'value' => $settings['referral_percentage']['value']
                    ]);

                    ?>
                    <span class="help-block"><?= __('Enter the referral earning percentage. Ex. 20') ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Minimum Withdrawal Amount') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['minimum_withdrawal_amount']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['minimum_withdrawal_amount']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Paid Views Per Day For Each Campaign') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['campaign_paid_views_day']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'number',
                        'min' => 1,
                        'step' => 1,
                        'value' => $settings['campaign_paid_views_day']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Referral Banners Code') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['referral_banners_code']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['referral_banners_code']['value']
                    ]);

                    ?>
                    <span class="help-block"><?= __("Here you can add your referral banners html code. You can use [referral_link] as a placeholder for member referral link.") ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Reserved Usernames') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['reserved_usernames']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['reserved_usernames']['value']
                    ]);

                    ?>
                    <span class="help-block"><?= __('Separate by comma, no spaces.') ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Fake Users Base') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['fake_users']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'number',
                        'value' => $settings['fake_users']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Fake Links Base') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['fake_links']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'number',
                        'value' => $settings['fake_links']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Fake Clicks base') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['fake_clicks']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'number',
                        'value' => $settings['fake_clicks']['value']
                    ]);

                    ?>
                </div>
            </div>

        </div>
        <div role="tabpanel" id="links" class="tab-pane fade in active">
            <p></p>

            <legend><?= __("Advertisement Types") ?></legend>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Interstitial Advertisement') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['enable_interstitial']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['enable_interstitial']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Banner Advertisement') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['enable_banner']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['enable_banner']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Popup Advertisement') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['enable_popup']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['enable_popup']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable No Advert') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['enable_noadvert']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['enable_noadvert']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __("Default Advertisement Types") ?></legend>

            <div class="row">
                <div class="col-sm-2"><?= __('Anonymous Default Advert') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['anonymous_default_advert']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            '1' => __('Interstitial Advertisement'),
                            '2' => __('Ad Banner'),
                            '0' => __('No Advert')
                        ],
                        'value' => $settings['anonymous_default_advert']['value'],
                        //'empty'   => __( 'Choose' ),
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Member Default Advert') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['member_default_advert']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            '1' => __('Interstitial Advertisement'),
                            '2' => __('Ad Banner'),
                            '0' => __('No Advert')
                        ],
                        'value' => $settings['member_default_advert']['value'],
                        //'empty'   => __( 'Choose' ),
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __("Metadata Fetching") ?></legend>
            <p><?= __("When shortening a URL, the URL page is downloaded and title, description & image are fetched from this page. If you have performance issues you can disable this behaviour from the below options.") ?></p>

            <div class="row">
                <div class="col-sm-2"><?= __('Disable Metadata Fetching on Homepage') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['disable_meta_home']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['disable_meta_home']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Disable Metadata Fetching on Member Area') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['disable_meta_member']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['disable_meta_member']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Disable Metadata Fetching on API') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['disable_meta_api']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['disable_meta_api']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                    <span class="help-block"><?= __("This is applicable for Quick Tool, Mass Shrinker, Full Page Script & Developers API.") ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Display Short link content(title, description and image)') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['short_link_content']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['short_link_content']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                    <span class="help-block"><?= __("Useful if your ads are displayed based on page content.") ?></span>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-sm-2"><?= __('Make Link Info Available for Public') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['link_info_public']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['link_info_public']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Display Home URL Shortening Box') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['home_shortening']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['home_shortening']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Redirect Anonymous Users to Register') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['home_shortening_register']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['home_shortening_register']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Make Link Info Available for Members') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['link_info_member']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['link_info_member']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Counter Value') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['counter_value']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'number',
                        'value' => $settings['counter_value']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Mass Shrinker Limit') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['mass_shrinker_limit']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'number',
                        'value' => $settings['mass_shrinker_limit']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Disallowed Domains') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['disallowed_domains']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['disallowed_domains']['value']
                    ]);

                    ?>
                    <span class="help-block"><?= __('Disallow links with certain domains from being shortened. Separate by comma, no spaces.') ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Reserved Aliases') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['reserved_aliases']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['reserved_aliases']['value']
                    ]);

                    ?>
                    <span class="help-block"><?= __('Disallow aliases from being used for short links. Separate by comma, no spaces.') ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Alias Min. Length') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['alias_min_length']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'number',
                        'value' => $settings['alias_min_length']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Alias Max. Length') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['alias_max_length']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'number',
                        'max' => 30,
                        'value' => $settings['alias_max_length']['value']
                    ]);

                    ?>
                </div>
            </div>


        </div>

        <div role="tabpanel" id="integration" class="tab-pane fade in">
            <p></p>

            <div class="row">
                <div class="col-sm-2"><?= __('Front Head Code') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['head_code']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['head_code']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Auth Head Code') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['auth_head_code']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['auth_head_code']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Member Head Code') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['member_head_code']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['member_head_code']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Admin Head Code') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['admin_head_code']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['admin_head_code']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Footer Code') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['footer_code']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['footer_code']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('After Body Tag Code') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['after_body_tag_code']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['after_body_tag_code']['value']
                    ]);

                    ?>
                </div>
            </div>

        </div>

        <div role="tabpanel" id="admin-ads" class="tab-pane fade in">
            <p></p>

            <div class="row">
                <div class="col-sm-2"><?= __('Member Area Ad') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['ad_member']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['ad_member']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Captcha Ad') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['ad_captcha']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['ad_captcha']['value']
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __('Interstitial Ads') ?></legend>

            <p><?= __('This ad will be displayed between logo and counter.') ?></p>

            <div class="row">
                <div class="col-sm-2"><?= __('Interstitial Page Ad Code') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['interstitial_ads']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['interstitial_ads']['value']
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __('Banner Ads') ?></legend>

            <p><?= __('Let say you have a campaign for 728×90 space then the other places 468×60 and 336×280 will be populated with the below banner ads.') ?></p>

            <div class="row">
                <div class="col-sm-2"><?= __('Banner 728x90') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['banner_728x90']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['banner_728x90']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Banner 468x60') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['banner_468x60']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['banner_468x60']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Banner 336x280') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['banner_336x280']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['banner_336x280']['value']
                    ]);

                    ?>
                </div>
            </div>

        </div>

        <div role="tabpanel" id="captcha" class="tab-pane fade in">
            <p></p>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Captcha') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['enable_captcha']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['enable_captcha']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Captcha Type') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['captcha_type']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'recaptcha' => __('reCAPTCHA'),
                            'invisible-recaptcha' => __('Invisible reCAPTCHA'),
                            'solvemedia' => __('Solve Media')
                        ],
                        'value' => $settings['captcha_type']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __('reCAPTCHA Settings') ?></legend>

            <div class="row">
                <div class="col-sm-2"><?= __('reCAPTCHA Site key') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['reCAPTCHA_site_key']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['reCAPTCHA_site_key']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('reCAPTCHA Secret key') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['reCAPTCHA_secret_key']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['reCAPTCHA_secret_key']['value']
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __('Invisible reCAPTCHA Settings') ?></legend>

            <div class="row">
                <div class="col-sm-2"><?= __('Invisible reCAPTCHA Site key') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['invisible_reCAPTCHA_site_key']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['invisible_reCAPTCHA_site_key']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Invisible reCAPTCHA Secret key') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['invisible_reCAPTCHA_secret_key']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['invisible_reCAPTCHA_secret_key']['value']
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __('Solve Media Settings') ?></legend>

            <div class="row">
                <div class="col-sm-2"><?= __('Solve Media Challenge Key') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['solvemedia_challenge_key']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['solvemedia_challenge_key']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Solve Media Verification Key') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['solvemedia_verification_key']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['solvemedia_verification_key']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Solve Media Authentication Hash Key') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['solvemedia_authentication_key']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['solvemedia_authentication_key']['value']
                    ]);

                    ?>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable on Home Anonymous Short Link Box') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['enable_captcha_shortlink_anonymous']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            1 => __('Yes'),
                            0 => __('No')
                        ],
                        'value' => $settings['enable_captcha_shortlink_anonymous']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>

                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable on Short Links Page') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['enable_captcha_shortlink']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['enable_captcha_shortlink']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable on Signup Form') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['enable_captcha_signup']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['enable_captcha_signup']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable on Forgot Password Form') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['enable_captcha_forgot_password']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['enable_captcha_forgot_password']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable on Contact Form') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['enable_captcha_contact']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'yes' => __('Yes'),
                            'no' => __('No')
                        ],
                        'value' => $settings['enable_captcha_contact']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

        </div>

        <div role="tabpanel" id="security" class="tab-pane fade in">
            <p></p>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable SSL Integration') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['ssl_enable']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            1 => __('Yes'),
                            0 => __('No')
                        ],
                        'value' => $settings['ssl_enable']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                    <span class="help-block"><?= __('You should install SSL into your website before enable SSL integration. For more information about SSL, pleask ask your hosting company.') ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Google Safe Browsing API Key') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['google_safe_browsing_key']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['google_safe_browsing_key']['value']
                    ]);

                    ?>
                    <span class="help-block"><?= __('You can get your key from <a href="{0}" target="_blank">here</a>.',
                            'https://developers.google.com/safe-browsing/v4/get-started') ?></span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('PhishTank API key') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['phishtank_key']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['phishtank_key']['value']
                    ]);

                    ?>
                    <span class="help-block"><?= __('You can get your key from <a href="{0}" target="_blank">here</a>.',
                            'https://www.phishtank.com/api_register.php') ?></span>
                </div>
            </div>

        </div>

        <div role="tabpanel" id="payment" class="tab-pane fade in">
            <p></p>

            <div class="row">
                <div class="col-sm-2"><?= __('Currency Code') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['currency_code']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['currency_code']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Currency Symbol') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['currency_symbol']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['currency_symbol']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Currency Position') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['currency_position']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'before' => __('Before Price'),
                            'after' => __('After Price')
                        ],
                        'value' => $settings['currency_position']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __('Wallet Settings') ?></legend>

            <p><?= __("Your users will be able to withdraw money to their wallet then use it to pay campaigns.") ?></p>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Wallet') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['wallet_enable']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            0 => __('No'),
                            1 => __('Yes')
                        ],
                        'value' => $settings['wallet_enable']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __('PayPal Settings') ?></legend>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable PayPal') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['paypal_enable']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'no' => __('No'),
                            'yes' => __('Yes')
                        ],
                        'value' => $settings['paypal_enable']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Payment Business Email') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['paypal_email']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'email',
                        'value' => $settings['paypal_email']['value'],
                        'autocomplete' => 'off'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable PayPal Sandbox') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['paypal_sandbox']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'no' => __('No'),
                            'yes' => __('Yes')
                        ],
                        'value' => $settings['paypal_sandbox']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __('Payza Settings') ?></legend>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Payza') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['payza_enable']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'no' => __('No'),
                            'yes' => __('Yes')
                        ],
                        'value' => $settings['payza_enable']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Payza Merchant Email') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['payza_email']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'email',
                        'value' => $settings['payza_email']['value'],
                        'autocomplete' => 'off'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Payza Test Mode') ?></div>
                <div class="col-sm-10">
                    <p class="form-group"><?= __('You can enable Payza sandox from your Payza account settings.') ?></p>
                </div>
            </div>

            <legend><?= __('Skrill Settings') ?></legend>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Skrill') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['skrill_enable']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            0 => __('No'),
                            1 => __('Yes')
                        ],
                        'value' => $settings['skrill_enable']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Skrill Merchant Email') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['skrill_email']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'email',
                        'value' => $settings['skrill_email']['value'],
                        'autocomplete' => 'off'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Skrill Secret Word') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['skrill_secret_word']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['skrill_secret_word']['value'],
                        'autocomplete' => 'off'
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __('Coinbase Settings') ?></legend>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Coinbase') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['coinbase_enable']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'no' => __('No'),
                            'yes' => __('Yes')
                        ],
                        'value' => $settings['coinbase_enable']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Coinbase API Key') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['coinbase_api_key']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['coinbase_api_key']['value'],
                        'autocomplete' => 'off'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Coinbase API Secret') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['coinbase_api_secret']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['coinbase_api_secret']['value'],
                        'autocomplete' => 'off'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Coinbase Sandbox') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['coinbase_sandbox']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'no' => __('No'),
                            'yes' => __('Yes')
                        ],
                        'value' => $settings['coinbase_sandbox']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __('Webmoney Settings') ?></legend>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Webmoney') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['webmoney_enable']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'no' => __('No'),
                            'yes' => __('Yes')
                        ],
                        'value' => $settings['webmoney_enable']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Webmoney Merchant Purse') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['webmoney_merchant_purse']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['webmoney_merchant_purse']['value'],
                        'autocomplete' => 'off'
                    ]);

                    ?>
                </div>
            </div>

            <legend><?= __('Bank Transfer Settings') ?></legend>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Bank Transfer') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['banktransfer_enable']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            'no' => __('No'),
                            'yes' => __('Yes')
                        ],
                        'value' => $settings['banktransfer_enable']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Bank Transfer Instructions') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['banktransfer_instructions']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'textarea',
                        'value' => $settings['banktransfer_instructions']['value']
                    ]);

                    ?>
                    <span class="help-block"><?= __("You can use these placeholders [invoice_id], [invoice_amount], [invoice_description]") ?></span>
                </div>
            </div>

        </div>

        <div role="tabpanel" id="blog" class="tab-pane fade in">
            <p></p>
            <div class="row">
                <div class="col-sm-2"><?= __('Enable Blog') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['blog_enable']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            1 => __('Yes'),
                            0 => __('No')
                        ],
                        'value' => $settings['blog_enable']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Enable Comments') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['blog_comments_enable']['id'] . '.value', [
                        'label' => false,
                        'options' => [
                            1 => __('Yes'),
                            0 => __('No')
                        ],
                        'value' => $settings['blog_comments_enable']['value'],
                        'class' => 'form-control'
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Disqus Shortname') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['disqus_shortname']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'text',
                        'value' => $settings['disqus_shortname']['value']
                    ]);

                    ?>
                    <span class="help-block"><?= __("To display comment box, you must create an account at Disqus website by signing up from <a href='https://disqus.com/profile/signup/' target='_blank'>here</a> then add your website their from <a href='https://disqus.com/admin/create/' target='_blank'>here</a> and get your shortname.") ?></span>
                </div>
            </div>


        </div>

        <div role="tabpanel" id="social" class="tab-pane fade in">
            <p></p>
            <div class="row">
                <div class="col-sm-2"><?= __('Facebook Page URL') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['facebook_url']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'url',
                        'value' => $settings['facebook_url']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Twitter Profile URL') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['twitter_url']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'url',
                        'value' => $settings['twitter_url']['value']
                    ]);

                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-2"><?= __('Google Plus URL') ?></div>
                <div class="col-sm-10">
                    <?=
                    $this->Form->input('Options.' . $settings['google_plus_url']['id'] . '.value', [
                        'label' => false,
                        'class' => 'form-control',
                        'type' => 'url',
                        'value' => $settings['google_plus_url']['value']
                    ]);

                    ?>
                </div>
            </div>


        </div>

    </div>

</div>

<?= $this->Form->button(__('Save'), ['name' => 'save_settings', 'class' => 'btn btn-primary']); ?>
<?= $this->Form->end(); ?>
