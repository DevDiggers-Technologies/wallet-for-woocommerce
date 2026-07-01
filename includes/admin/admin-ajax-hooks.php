<?php
/**
 * Admin ajax hooks class
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes\Admin;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Admin_Ajax_Hooks' ) ) {
	/**
	 * Admin ajax hooks class
	 */
	class DDWCWM_Admin_Ajax_Hooks extends DDWCWM_Admin_Ajax_Functions {
		/**
		 * Construct
		 */
		public function __construct() {
			add_action( 'wp_ajax_ddwcwm_get_all_users', [ $this, 'ddwcwm_get_all_users' ] );
			add_action( 'wp_ajax_ddwcwm_batch_manual_transaction', [ $this, 'ddwcwm_batch_manual_transaction' ] );
			add_action( 'wp_ajax_ddwcwm_batch_import_wallets', [ 'DDWCWalletManagement\\Includes\\DDWCWM_Import_Wizard', 'ajax_batch_import_wallets' ] );
		}
	}
}
