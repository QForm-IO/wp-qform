<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || die();

global $wpdb;
delete_option( 'qform_main_token' );
$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}qform_short_code`;" );
