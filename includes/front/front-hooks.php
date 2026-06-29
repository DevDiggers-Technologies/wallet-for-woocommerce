<?php
/**
 * Front hooks
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes\Front;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Front_Hooks' ) ) {
	/**
	 * Front hooks class
	 */
	class DDWCWM_Front_Hooks extends DDWCWM_Front_Functions {
		/**
		 * Construct
		 */
		public function __construct() {
			global $ddwcwm_wallet;

			add_filter( 'woocommerce_account_menu_items', [ $this, 'ddwcwm_add_woocommerce_menu' ] );

			add_filter( 'query_vars', [ $this, 'ddwcwm_add_query_vars' ] );

			add_action( 'woocommerce_account_' . $ddwcwm_wallet[ 'my_account_endpoint' ] . '_endpoint', [ $this, 'ddwcwm_add_wallet_content_on_my_account_page' ] );

			add_filter( 'sidebars_widgets', [ $this, 'ddwcwm_remove_sidebar_from_wallet_page' ] );

			add_action( 'wp_enqueue_scripts', [ $this, 'ddwcwm_register_front_scripts' ] );

			add_action( 'template_redirect', [ $this, 'ddwcwm_display_notice_if_wallet_topup_added_in_cart' ] );

			add_filter( 'woocommerce_available_payment_gateways', [ $this, 'ddwcwm_remove_payment_gateway' ] );

			// Pro: partial wallet payment at checkout (apply a portion of the balance as a
			// fee alongside another gateway) is not available in Free.

			add_action( 'woocommerce_checkout_order_processed', [ $this, 'ddwcwm_deduct_wallet_amount_on_order_processed' ] );

			add_action( 'woocommerce_store_api_checkout_order_processed', [ $this, 'ddwcwm_deduct_wallet_amount_on_store_api_order_processed' ] );

			add_action( 'woocommerce_before_calculate_totals', [ $this, 'ddwcwm_woocommerce_before_calculate_totals' ] );

			add_action( 'wp_loaded', [ $this, 'ddwcwm_handle_wallet_topup' ] );

			// Shortcodes
			add_shortcode( $this->ddwcwm_validate_shortcode( $ddwcwm_wallet[ 'wallet_balance_only_shortcode' ] ), [ $this, 'ddwcwm_add_wallet_balance_shortcode_content' ] );

			add_shortcode( $this->ddwcwm_validate_shortcode( $ddwcwm_wallet[ 'wallet_balance_shortcode' ] ), [ $this, 'ddwcwm_add_wallet_balance_layout_shortcode_content' ] );

			add_shortcode( $this->ddwcwm_validate_shortcode( $ddwcwm_wallet[ 'wallet_operations_shortcode' ] ), [ $this, 'ddwcwm_add_wallet_operations_shortcode_content' ] );

			add_shortcode( $this->ddwcwm_validate_shortcode( $ddwcwm_wallet[ 'wallet_balance_operations_shortcode' ] ), [ $this, 'ddwcwm_add_wallet_balance_operations_shortcode_content' ] );

			add_shortcode( $this->ddwcwm_validate_shortcode( $ddwcwm_wallet[ 'wallet_transactions_shortcode' ] ), [ $this, 'ddwcwm_add_wallet_transactions_shortcode_content' ] );

			if ( ! empty( $ddwcwm_wallet[ 'cashback_messages_enabled' ] ) ) {
				// Per-product/shop cashback messaging depends on product/category cashback
				// rules, which are a Pro feature, so only cart-based messages are shown in Free.
				add_action( 'woocommerce_before_cart', [ $this, 'ddwcwm_display_cashback_message_on_cart_page' ] );
				add_action( 'woocommerce_before_checkout_form', [ $this, 'ddwcwm_display_cashback_message_on_checkout_page' ] );
				add_action( 'woocommerce_order_details_before_order_table', [ $this, 'ddwcwm_display_cashback_message_on_view_order_page' ] );
				add_action( 'woocommerce_thankyou', [ $this, 'ddwcwm_display_cashback_message_on_order_received_page' ] );
			}
		}

		/**
		 * Validate shortcode function
		 *
		 * @param string $shortcode
		 * @return string
		 */
		public function ddwcwm_validate_shortcode( $shortcode ) {
			$shortcode = str_replace( '[', '', $shortcode );
			$shortcode = str_replace( ']', '', $shortcode );
			$shortcode = str_replace( ' ', '', $shortcode );

			return $shortcode;
		}
	}
}
