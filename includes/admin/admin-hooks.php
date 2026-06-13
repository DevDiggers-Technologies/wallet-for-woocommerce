<?php
/**
 * Admin hooks class
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes\Admin;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Admin_Hooks' ) ) {
	/**
	 * Admin hooks class
	 */
	class DDWCWM_Admin_Hooks extends DDWCWM_Admin_Functions {
		/**
		 * Construct
		 */
		public function __construct() {
			add_action( 'admin_init', [ $this, 'ddwcwm_register_settings' ] );

			// add_action( 'woocommerce_order_status_cancelled', [ $this, 'ddwcwm_handle_wallet_on_order_cancelled' ] );

			// add_action( 'woocommerce_order_status_refunded', [ $this, 'ddwcwm_handle_wallet_on_order_fully_refunded' ] );
		}
	}
}
