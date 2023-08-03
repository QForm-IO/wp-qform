<?php

class Qform_Activate {
	public static function active() {
		global $wpdb;
		$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}qform_short_code` (
							  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
							  `name` varchar(255) NOT NULL,
							  `form_id` text NOT NULL,
							  PRIMARY KEY (`id`)
							)");
	}
}
