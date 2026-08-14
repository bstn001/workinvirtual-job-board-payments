<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIVJA_Activator {
	public static function activate() {
		WIVJA_DB::create_tables();
		WIVJA_DB::seed_free_package();
		$defaults = array(
			'mode'                 => 'test',
			'currency'             => 'usd',
			'test_publishable_key' => '',
			'test_secret_key'      => '',
			'webhook_secret'       => '',
			'packages_page_id'     => 0,
			'submit_job_page_id'   => 0,
			'success_page_id'      => 0,
		);
		if ( ! get_option( 'wivja_settings' ) ) {
			add_option( 'wivja_settings', $defaults, '', false );
		}
		update_option( 'wivja_free_version', WIVJA_VERSION, false );
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}
}
