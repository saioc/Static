<div class="row">
    <div class="col-xs-12 col-sm-6">

        <?=
        $this->Form->create(null, [
            'url' => ['controller' => 'Forms', 'action' => 'contact', 'prefix' => false],
            'id' => 'contact-form'
        ]);
        ?>

        <?php
        $this->Form->templates([
            'inputContainer' => '{{content}}',
            'error' => '{{content}}',
            'inputContainerError' => '{{content}}'
        ]);
        ?>

        <div class="form-group wow fadeInUp">
            <?=
            $this->Form->input('name', [
                'label' => __('Your Name *'),
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control'
            ]);
            ?>
            <p class="help-block text-danger"></p>
        </div>
        <div class="form-group wow fadeInUp">
            <?=
            $this->Form->input('email', [
                'label' => __('Your Email *'),
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control'
            ]);
            ?>
            <p class="help-block text-danger"></p>
        </div>
        <div class="form-group wow fadeInUp">
            <?=
            $this->Form->input('subject', [
                'label' => __('Your Subject *'),
                'type' => 'text',
                'required' => 'required',
                'class' => 'form-control'
            ]);
            ?>
            <p class="help-block text-danger"></p>
        </div>
        <div class="form-group wow fadeInUp">
            <?=
            $this->Form->input('message', [
                'label' => __('Your Message *'),
                'type' => 'textarea',
                'required' => 'required',
                'class' => 'form-control'
            ]);
            ?>
            <p class="help-block text-danger"></p>
        </div>

        <div class="wow fadeInUp">
            <?php if ((get_option('enable_captcha_contact') == 'yes') && isset_captcha()) : ?>
                <div class="form-group captcha">
                    <div id="captchaContact" style="display: inline-block;"></div>
                </div>
                <?php
                $this->Form->unlockField('g-recaptcha-response');
                $this->Form->unlockField('adcopy_challenge');
                $this->Form->unlockField('adcopy_response');
                ?>
            <?php endif; ?>
        </div>

        <div class="wow fadeInUp">
            <div id="success"></div>
            <?= $this->Form->button(__('Send Message'), [
                'class' => 'btn btn-contact btn-captcha',
                'id' => 'invisibleCaptchaContact'
            ]); ?>
        </div>

        <?= $this->Form->end(); ?>

        <div class="contact-result"></div>

    </div>
    <div class="hidden-xs col-sm-6 text-center">
        <?= $this->Html->image('Connection-Image.png'); ?>
    </div>

</div>
