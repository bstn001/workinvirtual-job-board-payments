<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIVJA_DB {
	public static function table( $name ) {
		global $wpdb;
		$allowed = array( 'packages', 'orders', 'logs' );
		if ( ! in_array( $name, $allowed, true ) ) {
			return '';
		}
		return $wpdb->prefix . 'wivja_' . $name;
	}

	public static function create_tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		dbDelta( 'CREATE TABLE ' . self::table( 'packages' ) . " (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(190) NOT NULL,
			slug VARCHAR(190) NOT NULL,
			type VARCHAR(30) NOT NULL DEFAULT 'one_time',
			price DECIMAL(10,2) NOT NULL DEFAULT 0,
			currency VARCHAR(10) NOT NULL DEFAULT 'usd',
			duration_days INT UNSIGNED NOT NULL DEFAULT 30,
			job_limit INT UNSIGNED NOT NULL DEFAULT 1,
			featured_limit INT UNSIGNED NOT NULL DEFAULT 0,
			stripe_price_id VARCHAR(190) NULL,
			stripe_test_price_id VARCHAR(190) NULL,
			stripe_live_price_id VARCHAR(190) NULL,
			is_featured TINYINT(1) NOT NULL DEFAULT 0,
			is_active TINYINT(1) NOT NULL DEFAULT 1,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (id), UNIQUE KEY slug (slug), KEY type (type), KEY active (is_active)
		) $charset;" );

		dbDelta( 'CREATE TABLE ' . self::table( 'orders' ) . " (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			job_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			package_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
			stripe_session_id VARCHAR(190) NULL,
			stripe_customer_id VARCHAR(190) NULL,
			stripe_payment_intent_id VARCHAR(190) NULL,
			stripe_subscription_id VARCHAR(190) NULL,
			stripe_invoice_id VARCHAR(190) NULL,
			stripe_invoice_url TEXT NULL,
			amount DECIMAL(10,2) NOT NULL DEFAULT 0,
			currency VARCHAR(10) NOT NULL DEFAULT 'usd',
			status VARCHAR(30) NOT NULL DEFAULT 'pending',
			payment_type VARCHAR(30) NOT NULL DEFAULT 'one_time',
			created_at DATETIME NOT NULL,
			paid_at DATETIME NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (id), KEY user_id (user_id), KEY job_id (job_id), KEY session_id (stripe_session_id), KEY status (status)
		) $charset;" );

		dbDelta( 'CREATE TABLE ' . self::table( 'logs' ) . " (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id VARCHAR(190) NULL,
			event_type VARCHAR(190) NULL,
			source VARCHAR(50) NOT NULL DEFAULT 'system',
			status VARCHAR(30) NOT NULL DEFAULT 'info',
			message TEXT NULL,
			payload LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY (id), KEY event_id (event_id), KEY event_type (event_type), KEY status (status)
		) $charset;" );
	}

	public static function seed_free_package() {
		global $wpdb;
		$table = self::table( 'packages' );
		$count = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $table ) );
		if ( $count > 0 ) {
			return;
		}
		$now = current_time( 'mysql' );
		$wpdb->insert(
			$table,
			array(
				'name'                 => 'Standard Job Listing',
				'slug'                 => 'standard-job-listing',
				'type'                 => 'one_time',
				'price'                => 49,
				'currency'             => 'usd',
				'duration_days'        => 30,
				'job_limit'            => 1,
				'featured_limit'       => 0,
				'stripe_price_id'      => '',
				'stripe_test_price_id' => '',
				'stripe_live_price_id' => '',
				'is_featured'          => 0,
				'is_active'            => 1,
				'created_at'           => $now,
				'updated_at'           => $now,
			),
			array( '%s', '%s', '%s', '%f', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
		);
	}

	public static function get_package() {
		global $wpdb;
		$table = self::table( 'packages' );
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %i WHERE type=%s AND is_active=%d ORDER BY id ASC LIMIT 1', $table, 'one_time', 1 ) );
	}

	public static function create_order( $user_id, $package ) {
		global $wpdb;
		$now = current_time( 'mysql' );
		$wpdb->insert(
			self::table( 'orders' ),
			array(
				'user_id'     => absint( $user_id ),
				'package_id'  => absint( $package->id ),
				'amount'      => (float) $package->price,
				'currency'    => sanitize_key( $package->currency ),
				'status'      => 'pending',
				'payment_type'=> 'one_time',
				'created_at'  => $now,
				'updated_at'  => $now,
			),
			array( '%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s' )
		);
		return (int) $wpdb->insert_id;
	}

	public static function get_order( $order_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %i WHERE id=%d', self::table( 'orders' ), absint( $order_id ) ) );
	}

	public static function attach_session( $order_id, $session_id ) {
		global $wpdb;
		return false !== $wpdb->update(
			self::table( 'orders' ),
			array( 'stripe_session_id' => sanitize_text_field( $session_id ), 'updated_at' => current_time( 'mysql' ) ),
			array( 'id' => absint( $order_id ) ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}

	public static function mark_paid( $order_id, $session ) {
		global $wpdb;
		$order = self::get_order( $order_id );
		if ( ! $order || 'paid' === $order->status ) {
			return false;
		}
		$updated = $wpdb->update(
			self::table( 'orders' ),
			array(
				'status'                   => 'paid',
				'stripe_session_id'        => sanitize_text_field( $session['id'] ?? '' ),
				'stripe_customer_id'       => sanitize_text_field( $session['customer'] ?? '' ),
				'stripe_payment_intent_id' => sanitize_text_field( $session['payment_intent'] ?? '' ),
				'paid_at'                  => current_time( 'mysql' ),
				'updated_at'               => current_time( 'mysql' ),
			),
			array( 'id' => absint( $order_id ), 'status' => 'pending' ),
			array( '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d', '%s' )
		);
		if ( 1 === (int) $updated ) {
			$current = absint( get_user_meta( $order->user_id, '_wivja_free_job_credits', true ) );
			update_user_meta( $order->user_id, '_wivja_free_job_credits', $current + 1 );
			return true;
		}
		return false;
	}

	public static function consume_credit( $user_id ) {
		$user_id = absint( $user_id );
		$current = absint( get_user_meta( $user_id, '_wivja_free_job_credits', true ) );
		if ( $current < 1 ) {
			return false;
		}
		update_user_meta( $user_id, '_wivja_free_job_credits', $current - 1 );
		return true;
	}

	public static function log( $event_type, $status, $message, $payload = array(), $event_id = '' ) {
		global $wpdb;
		$wpdb->insert(
			self::table( 'logs' ),
			array(
				'event_id'   => sanitize_text_field( $event_id ),
				'event_type' => sanitize_text_field( $event_type ),
				'source'     => 'stripe',
				'status'     => sanitize_key( $status ),
				'message'    => sanitize_text_field( $message ),
				'payload'    => wp_json_encode( $payload ),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
	}
}
