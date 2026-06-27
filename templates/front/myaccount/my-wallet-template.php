<?php
/**
 * My Wallet Template Class
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Front\MyAccount;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_My_Wallet_Template' ) ) {
	/**
	 * My Wallet Template Class
	 */
	class DDWCWM_My_Wallet_Template {

		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcwm_wallet;

		/**
		 * Construct
		 */
		public function __construct() {
			global $ddwcwm_wallet;
			$this->ddwcwm_wallet = $ddwcwm_wallet;

			$this->ddwcwm_get_my_wallet_template();
		}

		/**
		 * Get My Wallet Template function
		 *
		 * @return void
		 */
		public function ddwcwm_get_my_wallet_template() {
			do_action( 'ddwcwm_add_content_before_wallet_layout_on_my_account_page' );

			echo do_shortcode( $this->ddwcwm_wallet[ 'wallet_balance_operations_shortcode' ] );
			echo do_shortcode( $this->ddwcwm_wallet[ 'wallet_transactions_shortcode' ] );
		}
	}
}
