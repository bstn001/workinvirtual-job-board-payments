<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIVJA_Admin {
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_post_wivja_save_free_package', array( __CLASS__, 'save_package' ) );
		add_action( 'admin_post_wivja_create_free_pages', array( __CLASS__, 'create_pages' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function menu() {
		add_menu_page( __( 'Job Board Payments', 'workinvirtual-job-board-payments' ), __( 'Job Board Payments', 'workinvirtual-job-board-payments' ), 'manage_options', 'wivja', array( __CLASS__, 'dashboard' ), 'dashicons-money-alt', 56 );
		add_submenu_page( 'wivja', __( 'Basic Package', 'workinvirtual-job-board-payments' ), __( 'Basic Package', 'workinvirtual-job-board-payments' ), 'manage_options', 'wivja-package', array( __CLASS__, 'package' ) );
		add_submenu_page( 'wivja', __( 'Payments', 'workinvirtual-job-board-payments' ), __( 'Payments', 'workinvirtual-job-board-payments' ), 'manage_options', 'wivja-payments', array( __CLASS__, 'payments' ) );
		add_submenu_page( 'wivja', __( 'Settings', 'workinvirtual-job-board-payments' ), __( 'Settings', 'workinvirtual-job-board-payments' ), 'manage_options', 'wivja-settings', array( __CLASS__, 'settings' ) );
		add_submenu_page( 'wivja', __( 'System Status', 'workinvirtual-job-board-payments' ), __( 'System Status', 'workinvirtual-job-board-payments' ), 'manage_options', 'wivja-system-status', array( __CLASS__, 'system_status' ) );
		add_submenu_page( 'wivja', __( 'Help', 'workinvirtual-job-board-payments' ), __( 'Help', 'workinvirtual-job-board-payments' ), 'manage_options', 'wivja-help', array( __CLASS__, 'help' ) );
	}

	public static function assets( $hook ) {
		if ( false !== strpos( (string) $hook, 'wivja' ) ) {
			wp_enqueue_style( 'wivja-admin', WIVJA_URL . 'assets/css/admin.css', array(), WIVJA_VERSION );
		}
	}

	public static function register_settings() {
		register_setting( 'wivja_free_settings', 'wivja_settings', array( 'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ) ) );
	}

	public static function sanitize_settings( $input ) {
		$current = WIVJA_Stripe::settings();
		$input   = is_array( $input ) ? $input : array();
		$out     = $current;
		$out['mode']                   = 'test';
		$out['currency']               = strtolower( sanitize_key( $input['currency'] ?? 'usd' ) );
		$out['test_publishable_key']   = self::secret_value( $input, 'test_publishable_key', $current );
		$out['test_secret_key']        = self::secret_value( $input, 'test_secret_key', $current );
		$out['webhook_secret']         = self::secret_value( $input, 'webhook_secret', $current );
		$out['packages_page_id']       = absint( $input['packages_page_id'] ?? 0 );
		$out['submit_job_page_id']     = absint( $input['submit_job_page_id'] ?? 0 );
		$out['success_page_id']        = absint( $input['success_page_id'] ?? 0 );
		return $out;
	}

	private static function secret_value( $input, $key, $current ) {
		$value = trim( sanitize_text_field( wp_unslash( $input[ $key ] ?? '' ) ) );
		return '' === $value ? ( $current[ $key ] ?? '' ) : $value;
	}

	private static function header( $title, $subtitle ) {
		echo '<div class="wivja-header"><div><div class="wivja-kicker">' . esc_html__( 'Free Edition · Stripe Test Mode', 'workinvirtual-job-board-payments' ) . '</div><h1>' . esc_html( $title ) . '</h1><div class="wivja-subtitle">' . esc_html( $subtitle ) . '</div></div><div class="wivja-version">' . esc_html( WIVJA_VERSION ) . '</div></div>';
	}

	public static function dashboard() {
		self::guard();
		global $wpdb;
		$orders_table = WIVJA_DB::table( 'orders' );
		$orders = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $orders_table ) );
		$paid   = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE status=%s', $orders_table, 'paid' ) );
		echo '<div class="wrap">';
		self::header( __( 'Stripe Job Board Payments', 'workinvirtual-job-board-payments' ), __( 'Test a one-time paid listing workflow with WP Job Manager.', 'workinvirtual-job-board-payments' ) );
		echo '<div class="wivja-grid"><div class="wivja-card"><h2>' . esc_html( $orders ) . '</h2><p>' . esc_html__( 'Test orders', 'workinvirtual-job-board-payments' ) . '</p></div><div class="wivja-card"><h2>' . esc_html( $paid ) . '</h2><p>' . esc_html__( 'Paid test orders', 'workinvirtual-job-board-payments' ) . '</p></div></div>';
		echo '<div class="wivja-panel"><h2>' . esc_html__( 'Quick setup', 'workinvirtual-job-board-payments' ) . '</h2><ol><li>' . esc_html__( 'Add Stripe Test keys.', 'workinvirtual-job-board-payments' ) . '</li><li>' . esc_html__( 'Create and verify the signed webhook.', 'workinvirtual-job-board-payments' ) . '</li><li>' . esc_html__( 'Configure the basic package.', 'workinvirtual-job-board-payments' ) . '</li><li>' . esc_html__( 'Complete checkout with a Stripe test card.', 'workinvirtual-job-board-payments' ) . '</li></ol><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'wivja_create_free_pages' );
		echo '<input type="hidden" name="action" value="wivja_create_free_pages">';
		submit_button( __( 'Create / connect free pages', 'workinvirtual-job-board-payments' ), 'secondary', 'submit', false );
		echo '</form></div>';
		echo '<div class="wivja-panel"><h2>' . esc_html__( 'Professional', 'workinvirtual-job-board-payments' ) . '</h2><p>' . esc_html__( 'Live payments, subscriptions, featured credits, Customer Portal, advanced administration, and branding controls are available in Professional.', 'workinvirtual-job-board-payments' ) . '</p><a class="button button-primary" href="' . esc_url( WIVJA_UPGRADE_URL ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View Professional', 'workinvirtual-job-board-payments' ) . '</a></div></div>';
	}

	public static function package() {
		self::guard();
		$p = WIVJA_DB::get_package();
		echo '<div class="wrap"><h1>' . esc_html__( 'Basic One-Time Package', 'workinvirtual-job-board-payments' ) . '</h1><p>' . esc_html__( 'The free edition supports one standard Test-mode package with one job credit.', 'workinvirtual-job-board-payments' ) . '</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'wivja_save_free_package' );
		echo '<input type="hidden" name="action" value="wivja_save_free_package"><table class="form-table" role="presentation">';
		self::row( 'name', __( 'Package name', 'workinvirtual-job-board-payments' ), $p->name ?? 'Standard Job Listing', 'text' );
		self::row( 'price', __( 'Test price', 'workinvirtual-job-board-payments' ), $p->price ?? '49.00', 'number', '0.50' );
		self::row( 'currency', __( 'Currency', 'workinvirtual-job-board-payments' ), $p->currency ?? 'usd', 'text' );
		self::row( 'duration_days', __( 'Listing duration (days)', 'workinvirtual-job-board-payments' ), $p->duration_days ?? '30', 'number', '1' );
		echo '</table>';
		submit_button();
		echo '</form></div>';
	}

	public static function save_package() {
		self::guard();
		check_admin_referer( 'wivja_save_free_package' );
		global $wpdb;
		$p = WIVJA_DB::get_package();
		$price = isset( $_POST['price'] ) ? (float) sanitize_text_field( wp_unslash( $_POST['price'] ) ) : 0;
		$data = array(
			'name'          => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
			'slug'          => sanitize_title( wp_unslash( $_POST['name'] ?? '' ) ),
			'type'          => 'one_time',
			'price'         => max( 0.5, $price ),
			'currency'      => strtolower( sanitize_key( wp_unslash( $_POST['currency'] ?? 'usd' ) ) ),
			'duration_days' => max( 1, absint( $_POST['duration_days'] ?? 30 ) ),
			'job_limit'     => 1,
			'featured_limit'=> 0,
			'is_featured'   => 0,
			'is_active'     => 1,
			'updated_at'    => current_time( 'mysql' ),
		);
		if ( $p ) {
			$wpdb->update( WIVJA_DB::table( 'packages' ), $data, array( 'id' => absint( $p->id ) ) );
		} else {
			$data['created_at'] = current_time( 'mysql' );
			$wpdb->insert( WIVJA_DB::table( 'packages' ), $data );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=wivja-package&updated=1' ) );
		exit;
	}

	public static function payments() {
		self::guard();
		global $wpdb;
		$orders_table = WIVJA_DB::table( 'orders' );
		$orders = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %i ORDER BY id DESC LIMIT 100', $orders_table ) );
		echo '<div class="wrap"><h1>' . esc_html__( 'Test Payments', 'workinvirtual-job-board-payments' ) . '</h1><p>' . esc_html__( 'Only Stripe Test-mode records are created by this edition.', 'workinvirtual-job-board-payments' ) . '</p><table class="widefat striped"><thead><tr><th>ID</th><th>' . esc_html__( 'User', 'workinvirtual-job-board-payments' ) . '</th><th>' . esc_html__( 'Amount', 'workinvirtual-job-board-payments' ) . '</th><th>' . esc_html__( 'Status', 'workinvirtual-job-board-payments' ) . '</th><th>' . esc_html__( 'Created', 'workinvirtual-job-board-payments' ) . '</th></tr></thead><tbody>';
		foreach ( $orders as $order ) {
			echo '<tr><td>' . esc_html( $order->id ) . '</td><td>' . esc_html( $order->user_id ) . '</td><td>' . esc_html( strtoupper( $order->currency ) . ' ' . number_format_i18n( (float) $order->amount, 2 ) ) . '</td><td>' . esc_html( $order->status ) . '</td><td>' . esc_html( $order->created_at ) . '</td></tr>';
		}
		if ( empty( $orders ) ) {
			echo '<tr><td colspan="5">' . esc_html__( 'No Test-mode payments yet.', 'workinvirtual-job-board-payments' ) . '</td></tr>';
		}
		echo '</tbody></table></div>';
	}

	public static function settings() {
		self::guard();
		$s = WIVJA_Stripe::settings();
		echo '<div class="wrap"><h1>' . esc_html__( 'Stripe Test Settings', 'workinvirtual-job-board-payments' ) . '</h1><div class="notice notice-info inline"><p>' . esc_html__( 'The free edition is permanently limited to Stripe Test mode and cannot accept real payments.', 'workinvirtual-job-board-payments' ) . '</p></div><form method="post" action="options.php">';
		settings_fields( 'wivja_free_settings' );
		echo '<table class="form-table" role="presentation">';
		self::row( 'wivja_settings[currency]', __( 'Currency', 'workinvirtual-job-board-payments' ), $s['currency'], 'text' );
		self::row( 'wivja_settings[test_publishable_key]', __( 'Test Publishable Key', 'workinvirtual-job-board-payments' ), '', 'password', '', 'pk_test_…' );
		self::row( 'wivja_settings[test_secret_key]', __( 'Test Secret Key', 'workinvirtual-job-board-payments' ), '', 'password', '', 'sk_test_…' );
		self::row( 'wivja_settings[webhook_secret]', __( 'Webhook Secret', 'workinvirtual-job-board-payments' ), '', 'password', '', 'whsec_…' );
		self::page_row( 'packages_page_id', __( 'Packages page', 'workinvirtual-job-board-payments' ), $s['packages_page_id'] );
		self::page_row( 'submit_job_page_id', __( 'WP Job Manager Submit Job page', 'workinvirtual-job-board-payments' ), $s['submit_job_page_id'] );
		self::page_row( 'success_page_id', __( 'Payment result page', 'workinvirtual-job-board-payments' ), $s['success_page_id'] );
		echo '</table><p><strong>' . esc_html__( 'Webhook endpoint:', 'workinvirtual-job-board-payments' ) . '</strong> <code>' . esc_html( rest_url( 'wiv-job-ads/v1/stripe-webhook' ) ) . '</code></p>';
		submit_button();
		echo '</form></div>';
	}

	public static function system_status() {
		self::guard();
		echo '<div class="wrap"><h1>' . esc_html__( 'System Status', 'workinvirtual-job-board-payments' ) . '</h1><table class="widefat striped"><tbody>';
		foreach ( WIVJA_System_Status::checks() as $label => $ok ) {
			echo '<tr><th>' . esc_html( $label ) . '</th><td>' . ( $ok ? '<span style="color:#08783e">✓ ' . esc_html__( 'Ready', 'workinvirtual-job-board-payments' ) . '</span>' : '<span style="color:#b32d2e">✕ ' . esc_html__( 'Needs attention', 'workinvirtual-job-board-payments' ) . '</span>' ) . '</td></tr>';
		}
		echo '</tbody></table></div>';
	}

	public static function help() {
		self::guard();
		echo '<div class="wrap"><h1>' . esc_html__( 'Help', 'workinvirtual-job-board-payments' ) . '</h1><p><a class="button button-primary" href="https://workinvirtual.com/docs/stripe-job-board-payments/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'workinvirtual-job-board-payments' ) . '</a> <a class="button" href="https://workinvirtual.com/contact-us/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Contact Support', 'workinvirtual-job-board-payments' ) . '</a></p><p>' . esc_html__( 'Support email:', 'workinvirtual-job-board-payments' ) . ' <a href="mailto:support@workinvirtual.com">support@workinvirtual.com</a></p></div>';
	}

	public static function create_pages() {
		self::guard();
		check_admin_referer( 'wivja_create_free_pages' );
		$settings = WIVJA_Stripe::settings();
		$settings['packages_page_id'] = self::ensure_page( 'job-board-test-packages', 'Job Board Test Packages', '[wiv_job_ad_packages]' );
		$settings['success_page_id']  = self::ensure_page( 'job-board-test-payment', 'Job Board Test Payment', '[wiv_job_ad_success]' );
		if ( empty( $settings['submit_job_page_id'] ) ) {
			$settings['submit_job_page_id'] = absint( get_option( 'job_manager_submit_job_form_page_id' ) );
		}
		update_option( 'wivja_settings', $settings, false );
		wp_safe_redirect( admin_url( 'admin.php?page=wivja-settings&pages=ready' ) );
		exit;
	}

	private static function ensure_page( $slug, $title, $content ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			return (int) $page->ID;
		}
		return (int) wp_insert_post( array( 'post_title' => $title, 'post_name' => $slug, 'post_content' => $content, 'post_status' => 'publish', 'post_type' => 'page' ) );
	}

	private static function row( $name, $label, $value, $type = 'text', $step = '', $placeholder = '' ) {
		echo '<tr><th scope="row"><label for="' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label></th><td><input class="regular-text" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" type="' . esc_attr( $type ) . '" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr( $placeholder ) . '"' . ( $step ? ' step="' . esc_attr( $step ) . '"' : '' ) . ' autocomplete="off"></td></tr>';
	}

	private static function page_row( $key, $label, $selected ) {
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>';
		$dropdown = wp_dropdown_pages( array( 'name' => 'wivja_settings[' . sanitize_key( $key ) . ']', 'selected' => absint( $selected ), 'show_option_none' => esc_html__( '— Select —', 'workinvirtual-job-board-payments' ), 'option_none_value' => 0, 'echo' => 0 ) );
		echo wp_kses( $dropdown, array( 'select' => array( 'name' => true, 'id' => true, 'class' => true ), 'option' => array( 'value' => true, 'selected' => true ) ) );
		echo '</td></tr>';
	}

	private static function guard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'workinvirtual-job-board-payments' ) );
		}
	}
}
