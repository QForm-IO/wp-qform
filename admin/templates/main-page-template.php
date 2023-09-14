<?php

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly  ?>
<div class="wrap">
    <div class="qform-banner">
        <div class="qform-banner__wrapper">
            <i class="qform-banner__logo"></i>
            <span class="qform-banner__text"><?php
                echo esc_html(__('CREATING FORMS AND QUIZZES',
                    'qform')) ?></span>
        </div>
        <div class="qform-banner__circle"></div>
    </div>
    <div class="qform-admin-main">
        <?php
        if (Qform::checkToken()) : ?>
            <form action="<?php
            echo esc_url(admin_url('admin-post.php')) ?>" method="post">
                <?php
                wp_nonce_field('qform_delete_token_action',
                    'qform_delete_token') ?>
                <input type="hidden" name="action" value="qform_delete_token">
                <input type="text" name="token" value="<?php
                echo esc_html(get_option('qform_main_token')) ?>">
                <p class="submit">
                    <button class="button button-primary" type="submit"
                            id="submit">
                        <?php
                        echo esc_html(__('Disable token', 'qform')) ?>
                    </button>
                </p>
            </form>
        <?php
        else: ?>
            <p><?php
                echo esc_html__('To evaluate app capability and activation,',
                    'qform'); ?> <?php
                echo esc_html__('discover the token of the site to which it is linked.', 'qform'); ?>
                <a href="<?php
                echo esc_html__('https://app.qform.io/', 'qform'); ?>"> <?php
                    echo esc_html__('Where to get a token?', 'qform'); ?></a>
            </p>
            <?php
            settings_errors(); ?>
            <form action="<?php
            echo esc_url(admin_url('options.php')) ?>" method="post">
                <?php
                settings_fields('qform_main_group'); ?>
                <?php
                do_settings_sections('qform-main'); ?>
                <?php
                if (get_option('qform_main_token')) : ?>
                    <span style="color: red"><?php
                        echo esc_html(__('Error token', 'qform')) ?></span>
                <?php
                endif ?>
                <?php
                submit_button(esc_html(__('Connect token',
                    'qform'))); ?>
            </form>
        <?php
        endif; ?>
    </div>
</div>