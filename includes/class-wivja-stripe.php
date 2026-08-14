<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIVJA_Stripe {
	public static function settings() {
		$stored = get_option( 'wivja_settings', array() );
		return wp_parse_args(
			is_array( $stored ) ? $stored : array(),
			array(
				'mode'                 => 'test',
				'currency'             => 'usd',
				'test_publishable_key' => '',
				'test_secret_key'      => '',
				'webhook_secret'       => '',
				'packages_page_id'     => 0,
				'submit_job_page_id'   => 0,
				'success_page_id'      => 0,
			)
		);
	}

	public static function api_request( $endpoint, $body = array() ) {
		$settings = self::settings();
		$key      = trim( $settings['test_secret_key'] );
		if ( 0 !== strpos( $key, 'sk_test_' ) ) {
			return new WP_Error( 'wivja_test_key_required', __( 'A valid Stripe Test Secret Key is required.', 'workinvirtual-job-board-payments' ) );
		}
		$response = wp_remote_post(
			'https://api.stripe.com/v1/' . ltrim( $endpoint, '/' ),
			array(
				'headers' => array( 'Authorization' => 'Bearer ' . $key ),
				'body'    => $body,
				'timeout' => 30,
			)
		);
		if ( is_wp_error( $response ) ) {
			WIVJA_DB::log( 'stripe.api_error', 'error', $response->get_error_message() );
			return $response;
		}
		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( $code < 200 || $code > 299 ) {
			$message = sanitize_text_field( $data['error']['message'] ?? __( 'Stripe API request failed.', 'workinvirtual-job-board-payments' ) );
			WIVJA_DB::log( 'stripe.api_error', 'error', $message, array( 'endpoint' => $endpoint ) );
			return new WP_Error( 'wivja_stripe_error', $message );
		}
		return is_array( $data ) ? $data : array();
	}

	public static function create_checkout( $order_id, $package ) {
		$settings = self::settings();
		$success  = absint( $settings['success_page_id'] ) ? get_permalink( absint( $settings['success_page_id'] ) ) : home_url( '/' );
		$cancel   = absint( $settings['packages_page_id'] ) ? get_permalink( absint( $settings['packages_page_id'] ) ) : home_url( '/' );
		$success  = add_query_arg( array( 'wivja_payment' => 'success', 'wivja_order' => absint( $order_id ) ), $success );
		$cancel   = add_query_arg( 'wivja_payment', 'cancel', $cancel );
		$amount   = max( 50, (int) round( (float) $package->price * 100 ) );
		$currency = sanitize_key( $package->currency ?: $settings['currency'] );
		$body     = array(
			'mode'                                    => 'payment',
			'success_url'                             => $success,
			'cancel_url'                              => $cancel,
			'client_reference_id'                     => (string) absint( $order_id ),
			'metadata[order_id]'                      => (string) absint( $order_id ),
			'metadata[user_id]'                       => (string) get_current_user_id(),
			'metadata[package_id]'                    => (string) absint( $package->id ),
			'line_items[0][quantity]'                 => 1,
			'line_items[0][price_data][currency]'     => $currency,
			'line_items[0][price_data][unit_amount]'  => $amount,
			'line_items[0][price_data][product_data][name]' => wp_strip_all_tags( $package->name ),
		);
		$session = self::api_request( 'checkout/sessions', $body );
		if ( is_wp_error( $session ) ) {
			return $session;
		}
		if ( empty( $session['id'] ) || empty( $session['url'] ) || 0 !== strpos( $session['id'], 'cs_test_' ) ) {
			return new WP_Error( 'wivja_invalid_session', __( 'Stripe did not return a valid Test-mode Checkout Session.', 'workinvirtual-job-board-payments' ) );
		}
		WIVJA_DB::attach_session( $order_id, $session['id'] );
		return $session;
	}
}

