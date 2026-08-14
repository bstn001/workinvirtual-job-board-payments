<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIVJA_System_Status {
	public static function checks() {
		$settings = WIVJA_Stripe::settings();
		return array(
			'WordPress 6.0+'       => version_compare( get_bloginfo( 'version' ), '6.0', '>=' ),
			'PHP 7.4+'             => version_compare( PHP_VERSION, '7.4', '>=' ),
			'WP Job Manager active'=> class_exists( 'WP_Job_Manager' ) || function_exists( 'wp_job_manager' ),
			'Stripe Test key'      => 0 === strpos( trim( $settings['test_secret_key'] ), 'sk_test_' ),
			'Webhook secret'       => 0 === strpos( trim( $settings['webhook_secret'] ), 'whsec_' ),
			'Packages page'        => absint( $settings['packages_page_id'] ) && 'publish' === get_post_status( absint( $settings['packages_page_id'] ) ),
		);
	}
}
