<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIVJA_Shortcodes {
	public static function init() {
		add_shortcode( 'wiv_job_ad_packages', array( __CLASS__, 'packages' ) );
		add_shortcode( 'wiv_job_ad_success', array( __CLASS__, 'success' ) );
		add_action( 'admin_post_wivja_free_checkout', array( __CLASS__, 'checkout' ) );
		add_action( 'admin_post_nopriv_wivja_free_checkout', array( __CLASS__, 'login_required' ) );
	}

	public static function packages() {
		wp_enqueue_style( 'wivja-public' );
		$package = WIVJA_DB::get_package();
		if ( ! $package ) {
			return '<div class="wivja-wrap"><p>' . esc_html__( 'No Test-mode package is currently available.', 'workinvirtual-job-board-payments' ) . '</p></div>';
		}
		$settings = WIVJA_Stripe::settings();
		$login_url = wp_login_url( get_permalink() );
		ob_start();
		?>
		<div class="wivja-wrap wivja-free-packages">
			<div class="wivja-package-card">
				<p class="wivja-badge"><?php esc_html_e( 'Stripe Test Mode', 'workinvirtual-job-board-payments' ); ?></p>
				<h2><?php echo esc_html( $package->name ); ?></h2>
				<p class="wivja-price"><?php echo esc_html( strtoupper( $package->currency ) . ' ' . number_format_i18n( (float) $package->price, 2 ) ); ?></p>
				<p><?php esc_html_e( 'Includes one standard WP Job Manager listing credit. Test cards only; no real charge is created.', 'workinvirtual-job-board-payments' ); ?></p>
				<?php if ( is_user_logged_in() ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="wivja_free_checkout">
						<?php wp_nonce_field( 'wivja_free_checkout', 'wivja_nonce' ); ?>
						<button class="wivja-btn" type="submit"><?php esc_html_e( 'Open Stripe Test Checkout', 'workinvirtual-job-board-payments' ); ?></button>
					</form>
				<?php else : ?>
					<a class="wivja-btn" href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Log in to test checkout', 'workinvirtual-job-board-payments' ); ?></a>
				<?php endif; ?>
			</div>
			<p><a href="<?php echo esc_url( WIVJA_UPGRADE_URL ); ?>"><?php esc_html_e( 'Need Live payments, subscriptions, featured credits, or Customer Portal access? View Professional.', 'workinvirtual-job-board-payments' ); ?></a></p>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function checkout() {
		if ( ! is_user_logged_in() || ! isset( $_POST['wivja_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wivja_nonce'] ) ), 'wivja_free_checkout' ) ) {
			wp_die( esc_html__( 'Checkout request could not be verified.', 'workinvirtual-job-board-payments' ), '', array( 'response' => 403 ) );
		}
		$package = WIVJA_DB::get_package();
		if ( ! $package ) {
			wp_die( esc_html__( 'No package is available.', 'workinvirtual-job-board-payments' ) );
		}
		$order_id = WIVJA_DB::create_order( get_current_user_id(), $package );
		$session  = WIVJA_Stripe::create_checkout( $order_id, $package );
		if ( is_wp_error( $session ) ) {
			wp_die( esc_html( $session->get_error_message() ) );
		}
		$checkout_url = esc_url_raw( $session['url'] );
		$checkout_host = (string) wp_parse_url( $checkout_url, PHP_URL_HOST );
		$is_stripe_host = 'checkout.stripe.com' === $checkout_host || '.checkout.stripe.com' === substr( $checkout_host, -20 );
		if ( 'https' !== wp_parse_url( $checkout_url, PHP_URL_SCHEME ) || ! $is_stripe_host ) {
			wp_die( esc_html__( 'Stripe returned an invalid Checkout URL.', 'workinvirtual-job-board-payments' ) );
		}
		wp_redirect( $checkout_url, 303, 'Stripe Job Board Payments' ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit;
	}

	public static function login_required() {
		wp_safe_redirect( wp_login_url( wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	public static function success() {
		wp_enqueue_style( 'wivja-public' );
		$payment_result = filter_input( INPUT_GET, 'wivja_payment', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( 'success' !== sanitize_key( (string) $payment_result ) ) {
			return '';
		}
		return '<div class="wivja-wrap"><div class="wivja-alert"><strong>' . esc_html__( 'Test checkout returned successfully.', 'workinvirtual-job-board-payments' ) . '</strong> ' . esc_html__( 'Stripe will grant the job credit after the signed webhook is processed.', 'workinvirtual-job-board-payments' ) . '</div></div>';
	}
}
