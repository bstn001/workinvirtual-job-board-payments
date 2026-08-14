<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIVJA_Webhook {
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_route' ) );
	}

	public static function register_route() {
		register_rest_route(
			'wiv-job-ads/v1',
			'/stripe-webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'receive' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function receive( WP_REST_Request $request ) {
		$payload   = $request->get_body();
		$signature = (string) $request->get_header( 'stripe-signature' );
		$settings  = WIVJA_Stripe::settings();
		$secret    = trim( $settings['webhook_secret'] );
		if ( 0 !== strpos( $secret, 'whsec_' ) || ! self::valid_signature( $payload, $signature, $secret ) ) {
			return new WP_REST_Response( array( 'error' => 'invalid_signature' ), 400 );
		}
		$event = json_decode( $payload, true );
		if ( ! is_array( $event ) || empty( $event['id'] ) || empty( $event['type'] ) ) {
			return new WP_REST_Response( array( 'error' => 'invalid_payload' ), 400 );
		}
		if ( 'checkout.session.completed' !== $event['type'] ) {
			WIVJA_DB::log( $event['type'], 'ignored', 'Event is outside the free one-time workflow.', array(), $event['id'] );
			return new WP_REST_Response( array( 'received' => true ), 200 );
		}
		$session  = $event['data']['object'] ?? array();
		$order_id = absint( $session['metadata']['order_id'] ?? 0 );
		$order    = WIVJA_DB::get_order( $order_id );
		$valid    = $order
			&& 'payment' === ( $session['mode'] ?? '' )
			&& 'paid' === ( $session['payment_status'] ?? '' )
			&& absint( $session['metadata']['user_id'] ?? 0 ) === (int) $order->user_id
			&& absint( $session['metadata']['package_id'] ?? 0 ) === (int) $order->package_id
			&& hash_equals( (string) $order->stripe_session_id, (string) ( $session['id'] ?? '' ) );
		if ( ! $valid ) {
			WIVJA_DB::log( $event['type'], 'rejected', 'Checkout metadata did not match the pending order.', array(), $event['id'] );
			return new WP_REST_Response( array( 'error' => 'order_mismatch' ), 400 );
		}
		WIVJA_DB::mark_paid( $order_id, $session );
		WIVJA_DB::log( $event['type'], 'processed', 'One-time Test payment recorded.', array( 'order_id' => $order_id ), $event['id'] );
		return new WP_REST_Response( array( 'received' => true ), 200 );
	}

	private static function valid_signature( $payload, $header, $secret ) {
		$timestamp = 0;
		$signatures = array();
		foreach ( explode( ',', $header ) as $part ) {
			$pieces = array_map( 'trim', explode( '=', $part, 2 ) );
			if ( 2 !== count( $pieces ) ) {
				continue;
			}
			if ( 't' === $pieces[0] ) {
				$timestamp = absint( $pieces[1] );
			} elseif ( 'v1' === $pieces[0] ) {
				$signatures[] = $pieces[1];
			}
		}
		if ( ! $timestamp || abs( time() - $timestamp ) > 300 || empty( $signatures ) ) {
			return false;
		}
		$expected = hash_hmac( 'sha256', $timestamp . '.' . $payload, $secret );
		foreach ( $signatures as $candidate ) {
			if ( hash_equals( $expected, $candidate ) ) {
				return true;
			}
		}
		return false;
	}
}
