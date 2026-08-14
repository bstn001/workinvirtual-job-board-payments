<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIVJA_WPJM {
	public static function init() {
		add_filter( 'job_manager_user_can_post_job', array( __CLASS__, 'allow_paid_credit' ), 20, 2 );
		add_action( 'save_post_job_listing', array( __CLASS__, 'consume_paid_credit' ), 20, 3 );
	}

	public static function allow_paid_credit( $can_post, $user_id ) {
		if ( $can_post ) {
			return true;
		}
		return absint( get_user_meta( absint( $user_id ), '_wivja_free_job_credits', true ) ) > 0;
	}

	public static function consume_paid_credit( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || get_post_meta( $post_id, '_wivja_free_credit_consumed', true ) ) {
			return;
		}
		if ( ! $post instanceof WP_Post || 'job_listing' !== $post->post_type || ! in_array( $post->post_status, array( 'pending', 'publish' ), true ) ) {
			return;
		}
		$user_id = absint( $post->post_author );
		if ( $user_id && WIVJA_DB::consume_credit( $user_id ) ) {
			update_post_meta( $post_id, '_wivja_free_credit_consumed', 1 );
			update_post_meta( $post_id, '_wivja_payment_source', 'stripe_test_free' );
		}
	}
}

