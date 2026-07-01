<?php
/**
 * Common hooks class
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes\Common;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Common_Hooks' ) ) {

	/**
	 * Common hooks class
	 */
	class DDWCWM_Common_Hooks extends DDWCWM_Common_Functions {

		/**
		 * Construct
		 */
		public function __construct() {
			$this->ddwcwm_add_endpoints();

			add_filter( 'ddfw_modify_svg_icons', [ $this, 'ddwcwm_add_svg_icons' ], 10, 2 );

			add_filter( 'woocommerce_payment_gateways', [ $this, 'ddwcwm_add_payment_gateway' ] );

			add_filter( 'woocommerce_email_classes', [ $this, 'ddwcwm_add_new_email_notification' ] );
			add_filter( 'woocommerce_email_actions', [ $this, 'ddwcwm_add_notification_actions' ] );

			add_filter( 'ddwcuws_modify_whatsapp_message_for_other_text', [ $this, 'ddwcwm_add_whatsapp_automation' ], 10, 2 );

			add_filter( 'ddwcuws_remove_payment_gateway_for_whatsapp_purchase', [ $this, 'ddwcwm_handle_removing_wallet_for_whatsapp_purchase' ], 10, 4 );

			add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'ddwcwm_check_if_wallet_topup_product_exists_in_cart' ] );

			add_action( 'woocommerce_before_cart', [ $this, 'ddwcwm_check_if_wallet_topup_product_exists_in_cart' ] );

			add_action( 'woocommerce_before_checkout_form', [ $this, 'ddwcwm_check_if_wallet_topup_product_exists_in_cart' ] );

			add_action( 'woocommerce_cart_loaded_from_session', [ $this, 'ddwcwm_check_if_wallet_topup_product_exists_in_cart' ] );

			add_action( 'woocommerce_order_status_cancelled', [ $this, 'ddwcwm_handle_wallet_on_order_cancelled' ] );
			add_action( 'woocommerce_order_status_refunded', [ $this, 'ddwcwm_handle_wallet_on_order_fully_refunded' ] );
			add_action( 'woocommerce_order_partially_refunded', [ $this, 'ddwcwm_handle_wallet_on_order_partially_refunded' ], 10, 2 );
			add_action( 'woocommerce_order_status_completed', [ $this, 'ddwcwm_handle_wallet_on_order_completed' ] );

			add_action( 'template_redirect', [ $this, 'ddwcwm_display_registration_credit_notice_for_guest' ] );
			add_action( 'woocommerce_created_customer', [ $this, 'ddwcwm_credit_wallet_on_registration' ] );
		}
	}
}
