<?php

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
if (!Qform::checkToken()) : ?>
    <div class="wrap">
        <div style="margin-top:20px;">
            <p><?php
                echo esc_html__('To evaluate app capability and activation,',
                    'qform'); ?> <?php
                echo esc_html__('discover the token of the site to which it is linked.', 'qform'); ?>
                <a href="<?php
                echo esc_html__('https://app.qform.io/', 'qform'); ?>"> <?php
                    echo esc_html__('Where to get a token?', 'qform'); ?></a>
            </p>
            <a href="<?php
            echo esc_url(admin_url('/admin.php?page=qform-main')) ?>">
                <button class="button button-primary"><?php
                    echo esc_html(__('Connect token', 'qform')) ?></button>
            </a></div>
    </div>

<?php
else: ?>

    <div class="wrap">
        <?php
        $errors = get_transient('qform_form_errors');
        $success = get_transient('qform_form_success');

        ?>
        <?php
        if ($errors): ?>
            <div id="setting-error-settings_updated"
                 class="notice notice-error settings-error is-dismissible">
                <p><strong><?php
                        echo esc_html($errors); ?></strong></p>
            </div>
            <?php
            delete_transient('qform_form_errors') ?>
        <?php
        endif; ?>

        <?php
        if ($success): ?>
            <div id="setting-error-settings_updated"
                 class="notice notice-success settings-error is-dismissible">
                <p><strong><?php
                        echo esc_html($success); ?></strong></p>
            </div>
            <?php
            delete_transient('qform_form_success') ?>
        <?php
        endif; ?>

        <h1><?php
            echo esc_html(__('Shortcode List Page', 'qform')) ?></h1>
        <?php

        $forms = Qform_Admin::getForms();

        if (!$forms) : ?>
            <p><?php
                echo esc_html__('Add a form in your account',
                    'qform'); ?> <a href="<?php
                echo esc_html__('https://app.qform.io/', 'qform'); ?>"> <?php
                    echo esc_html__('https://app.qform.io/', 'qform'); ?></a>
            </p>
        <?php
        else: ?>

            <form action="<?php
            echo esc_url(admin_url('admin-post.php')) ?>" method="post">
                <?php
                wp_nonce_field('qform_short_code_action_add',
                    'qform_short_code_add') ?>
                <input type="hidden" name="action" value="qform_short_code_add">
                <table class="form-table" role="presentation">
                    <tbody>
                    <tr>
                        <th scope="row"><label for="default_category"><?php
                                echo esc_html(__('Choose form',
                                    'qform')) ?></label></th>
                        <td>
                            <select name="qform_id" id="qform_id"
                                    class="postform">
                                <?php
                                foreach ($forms as $form): ?>
                                    <?php
                                    if ($form['status'] == 1) : ?>
                                        <option class="level-0"
                                                value="<?php
                                                echo esc_html($form['formId'])
                                                    . '||'
                                                    . esc_html($form['name']) ?>"><?php
                                            echo esc_html($form['name']) ?></option>
                                    <?php
                                    endif; ?>
                                <?php
                                endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <p class="submit"><input type="submit" name="submit" id="submit"
                                         class="button button-primary"
                                         value="<?php
                                         echo esc_html(__('Create shortcode',
                                             'qform')) ?>"></p>

            </form>

        <?php
        endif; ?>

        <?php
        $per_page = 5;
        $short_codes_count = Qform_Admin::get_count_short_codes();
        $pagination = Qform_Admin::get_pagination_meta($per_page,
            $short_codes_count);

        $short_codes = Qform_Admin::get_short_codes($per_page,
            $pagination['start']);
        $big = 999999999;
        ?>

        <?php
        if ($short_codes): ?>
            <table
                class="wp-list-table widefat fixed striped table-view-list posts">
                <thead>
                <tr>
                    <th class="manage-column column-title column-primary"><?php
                        echo esc_html(__('Shortcode', 'qform')); ?></th>
                    <th class="manage-column column-categories">
                        <?php
                        echo esc_html(__('Actions', 'qform')); ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($short_codes as $short_code): ?>
                    <tr>
                        <td class="title column-title has-row-actions column-primary page-title"
                            data-colname="<?php
                            echo esc_attr(__('Shortcode', 'qform')); ?>">
                            <?php
                            echo esc_html($short_code['name']) . '<br />'; ?>
                            <?php
                            echo '[qform_short_code id="'
                                . esc_attr($short_code['id'])
                                . '"]'; ?>
                            <button type="button" class="toggle-row"><span
                                    class="screen-reader-text"><?php
                                    echo esc_html(__('Show more details',
                                        'qform')); ?></span>
                            </button>
                        </td>


                        <td class="qform-actions" data-colname="<?php
                        echo esc_attr(__('Actions', 'qform')); ?>">

                            <form action="<?php
                            echo esc_url(admin_url('admin-post.php')) ?>"
                                  method="post">
                                <?php
                                wp_nonce_field('qform_short_code_action',
                                    'qform_short_code_delete') ?>
                                <input type="hidden" name="action"
                                       value="qform_short_code_delete">
                                <input type="hidden" name="short_code_id"
                                       value="<?php
                                       echo esc_html($short_code['id']) ?>">
                                <button class="button button-link-delete"
                                        type="submit"><span
                                        class="dashicons dashicons-trash"></span>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php
                endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="qform-pagination">
                <?php

                echo wp_kses_post(paginate_links(array(
                    'base' => str_replace($big,
                        '%#%',
                        esc_url(get_pagenum_link($big))),
                    'format' => '?paged=%#%',
                    'current' => esc_html($pagination['paged']),
                    'total' => esc_html($pagination['total_pages']),
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'mid_size' => 5
                )));
                ?>
            </div>
            <!-- Pagination -->

        <?php
        else: ?>
            <p><?php
                echo esc_html(__('No entries found', 'qform')) ?></p>
        <?php
        endif; ?>

    </div>


<?php
endif; ?>