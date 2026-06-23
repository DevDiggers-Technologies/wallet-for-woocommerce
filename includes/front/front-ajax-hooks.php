<?php
/**
 * Front ajax hooks class
 *
 * @package Wallet Management for WooCommerce
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
			// Pro front operations (partial payment, send/request/withdraw money, OTP,
			// referrals) are intentionally not registered in Free.
			// Transactions are user-specific, so this endpoint is for logged-in users only.
			add_action( 'wp_ajax_ddwcwm_get_transaction_rows', [ $this, 'ddwcwm_get_transaction_rows' ] );

			// Send Balance (peer-to-peer transfer) is available in Free for logged-in users only.
			add_action( 'wp_ajax_ddwcwm_send_money_to_user', [ $this, 'ddwcwm_send_money_to_user' ] );
		}
	}
}
