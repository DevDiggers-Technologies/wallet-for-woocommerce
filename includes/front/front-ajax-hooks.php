<?php
/**
 * Front ajax hooks class
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes\Front;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Front_Ajax_Hooks' ) ) {
	/**
	 * Front ajax hooks class
	 */
	class DDWCWM_Front_Ajax_Hooks extends DDWCWM_Front_Ajax_Functions {
		/**
		 * Construct
		 */
		public function __construct() {
			add_action( 'wp_ajax_ddwcwm_get_transaction_rows', [ $this, 'ddwcwm_get_transaction_rows' ] );
			add_action( 'wp_ajax_ddwcwm_send_money_to_user', [ $this, 'ddwcwm_send_money_to_user' ] );
		}
	}
}
