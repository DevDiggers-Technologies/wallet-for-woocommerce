<?php
/**
 * Admin functions class
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes\Admin;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Admin_Functions' ) ) {
	/**
	 * Admin functions
	 */
	class DDWCWM_Admin_Functions {

		/**
		 * Register Option settings
		 *
		 * @return void
		 */
		public function ddwcwm_register_settings() {
			$sanitize = [
				'sanitize_callback' => [ $this, 'ddwcwm_sanitize_option_value' ],
			];

			$sanitize_array = [
				'sanitize_callback' => [ $this, 'ddwcwm_sanitize_array_option' ],
			];

			register_setting( 'ddwcwm-general-configuration-fields', '_ddwcwm_enabled', $sanitize );
			register_setting( 'ddwcwm-general-configuration-fields', '_ddwcwm_registration_credit', $sanitize );
			register_setting( 'ddwcwm-general-configuration-fields', '_ddwcwm_topup_order_status', $sanitize );
			register_setting( 'ddwcwm-general-configuration-fields', '_ddwcwm_enabled_payment_gateways', $sanitize_array );
			register_setting( 'ddwcwm-general-configuration-fields', '_ddwcwm_redirect_to_checkout_on_topup', $sanitize );

			// Pro-only configuration groups (OTP, withdrawals, referrals, transfer limits,
			// partial payments) are intentionally NOT registered in Free. Their UI is shown
			// locked and submits no meaningful value.

			register_setting( 'ddwcwm-endpoints-configuration-fields', '_ddwcwm_my_account_endpoint', $sanitize );
			register_setting( 'ddwcwm-endpoints-configuration-fields', '_ddwcwm_my_account_endpoint_title', $sanitize );
			register_setting( 'ddwcwm-endpoints-configuration-fields', '_ddwcwm_enable_widgets_my_account_endpoint', $sanitize );

			register_setting( 'ddwcwm-shortcodes-configuration-fields', '_ddwcwm_wallet_balance_only_shortcode', $sanitize );
			register_setting( 'ddwcwm-shortcodes-configuration-fields', '_ddwcwm_wallet_balance_shortcode', $sanitize );
			register_setting( 'ddwcwm-shortcodes-configuration-fields', '_ddwcwm_wallet_operations_shortcode', $sanitize );
			register_setting( 'ddwcwm-shortcodes-configuration-fields', '_ddwcwm_wallet_balance_operations_shortcode', $sanitize );
			register_setting( 'ddwcwm-shortcodes-configuration-fields', '_ddwcwm_wallet_transactions_shortcode', $sanitize );

			// Pro-only: cashback credit delay is not registered in Free.
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_exclude_sale_products', $sanitize );
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_max_cap', $sanitize );
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_min_order_value', $sanitize );
			// Pro-only cashback controls (first-order cashback, expiry, expiry reminders)
			// are intentionally NOT registered in Free.

			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_messages_enabled', $sanitize );
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_shop_page_message', $sanitize );
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_product_page_message', $sanitize );
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_cart_page_message', $sanitize );
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_checkout_page_message', $sanitize );
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_view_order_page_message', $sanitize );
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_order_received_page_message', $sanitize );
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_message_text_color', $sanitize );
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_message_bg_color', $sanitize );
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_message_border_color', $sanitize );
			register_setting( 'ddwcwm-cashback-configuration-fields', '_ddwcwm_cashback_message_font_size', $sanitize );

			register_setting( 'ddwcwm-emails-configuration-fields', '_ddwcwm_email_settings', $sanitize_array );
			register_setting( 'ddwcwm-emails-configuration-fields', '_ddwcwm_credit_reason', $sanitize_array );
			register_setting( 'ddwcwm-emails-configuration-fields', '_ddwcwm_debit_reason', $sanitize_array );

			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_details_icons_enabled', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_details_icons_wrapper_enabled', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_details_icon_size', $sanitize );
			// Pro-only: custom component icon uploads (available balance, send, request,
			// withdraw, refer, top-up) are not registered in Free.
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_theme_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_details_icon_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_details_icon_wrapper_background_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_details_card_background_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_details_card_border_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_details_card_text_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_details_card_value_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_layout_table_header_text_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_layout_table_header_background_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_success_message_text_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_success_message_background_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_error_message_text_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_error_message_background_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_info_message_text_color', $sanitize );
			register_setting( 'ddwcwm-layout-configuration-fields', '_ddwcwm_info_message_background_color', $sanitize );
		}

		/**
		 * Sanitize and Unslash Option Input
		 *
		 * @param string $input Input String.
		 * @return $input
		 */
		public function ddwcwm_sanitize_option_value( $input ) {
			$input = sanitize_text_field( wp_unslash( $input ) );
			return $input;
		}

		/**
		 * Sanitize an array option (recursively) before saving.
		 *
		 * @param mixed $input Option value.
		 * @return mixed
		 */
		public function ddwcwm_sanitize_array_option( $input ) {
			if ( ! is_array( $input ) ) {
				return sanitize_text_field( wp_unslash( $input ) );
			}

			return map_deep( wp_unslash( $input ), 'wp_kses_post' );
		}
	}
}
