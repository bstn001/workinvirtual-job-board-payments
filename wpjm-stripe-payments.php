<?php
/**
 * Plugin Name: WorkinVirtual Job Board Payments – Stripe Test Checkout
 * Description: Accept Stripe Test-mode payments for a basic WP Job Manager listing package without WooCommerce.
 * Version: 1.1.3
 * Author: WorkinVirtual
 * Author URI: https://workinvirtual.com/
 * Requires Plugins: wp-job-manager
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: workinvirtual-job-board-payments
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'wsp_fs' ) ) {
	wsp_fs()->set_basename( true, __FILE__ );
} elseif ( ! function_exists( 'wsp_fs' ) ) {
	function wsp_fs() {
		global $wsp_fs;
		if ( ! isset( $wsp_fs ) ) {
			require_once __DIR__ . '/vendor/freemius/start.php';
			$wsp_fs = fs_dynamic_init(
				array(
					'id'                  => '36287',
					'slug'                => 'wpjm-stripe-payments',
					'type'                => 'plugin',
					'public_key'          => 'pk_b6b93a383f23edce606963f654df5',
					'is_premium'          => false,
					'premium_suffix'      => 'Professional',
					'has_premium_version' => true,
					'has_addons'          => false,
					'has_paid_plans'      => true,
					'is_org_compliant'    => true,
					'wp_org_gatekeeper'   => 'OA7#BoRiBNqdf52FvzEf!!074aRLPs8fspif$7K1#4u4Csys1fQlCecVcUTOs2mcpeVHi#C2j9d09fOTvbC0HloPT7fFee5WdS3G',
					'menu'                => array( 'support' => false ),
				)
			);
		}
		return $wsp_fs;
	}
	wsp_fs();
	do_action( 'wsp_fs_loaded' );
}

define( 'WIVJA_VERSION', '1.1.3' );
define( 'WIVJA_FILE', __FILE__ );
define( 'WIVJA_PATH', plugin_dir_path( __FILE__ ) );
define( 'WIVJA_URL', plugin_dir_url( __FILE__ ) );
define( 'WIVJA_UPGRADE_URL', 'https://workinvirtual.com/products/stripe-job-board-payments/' );

require_once WIVJA_PATH . 'includes/class-wivja-db.php';
require_once WIVJA_PATH . 'includes/class-wivja-stripe.php';
require_once WIVJA_PATH . 'includes/class-wivja-webhook.php';
require_once WIVJA_PATH . 'includes/class-wivja-wpjm.php';
require_once WIVJA_PATH . 'includes/class-wivja-shortcodes.php';
require_once WIVJA_PATH . 'includes/class-wivja-system-status.php';
require_once WIVJA_PATH . 'includes/class-wivja-activator.php';
require_once WIVJA_PATH . 'admin/class-wivja-admin.php';

final class WIVJA_Plugin {
	public static function boot() {
		WIVJA_WPJM::init();
		WIVJA_Shortcodes::init();
		WIVJA_Webhook::init();
		if ( is_admin() ) {
			WIVJA_Admin::init();
		}
	}

	public static function register_assets() {
		wp_register_style( 'wivja-public', WIVJA_URL . 'assets/css/public.css', array(), WIVJA_VERSION );
	}
}

add_action( 'plugins_loaded', array( 'WIVJA_Plugin', 'boot' ) );
add_action( 'wp_enqueue_scripts', array( 'WIVJA_Plugin', 'register_assets' ) );
register_activation_hook( __FILE__, array( 'WIVJA_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WIVJA_Activator', 'deactivate' ) );
