<?php
/**
 * File handler
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes;

use DDWCWalletManagement\Includes\Admin;
use DDWCWalletManagement\Includes\Front;
use DDWCWalletManagement\Includes\Common;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_File_Handler' ) ) {

	/**
	 * File handler class
	 */
	class DDWCWM_File_Handler {

		/**
		 * Construct
		 */
		public function __construct() {
			$ddwcwm_wallet = $this->ddwcwm_set_globals();

			add_action( 'init', [ $this, 'ddwcwm_set_globals' ] );

			new Common\DDWCWM_Common_Hooks();

			if ( ! is_admin() ) {
				if ( ! empty( $ddwcwm_wallet[ 'enabled' ] ) ) {
					new Front\DDWCWM_Front_Hooks();
				}
			}

			if ( is_user_logged_in() ) {
				new Front\DDWCWM_Front_Ajax_Hooks();

				if ( is_admin() ) {
					new DDWCWM_Admin_Dashboard();
					new Admin\DDWCWM_Admin_Hooks();
					new Admin\DDWCWM_Admin_Ajax_Hooks();
				}
			}
		}

		/**
		 * Set globals function
		 *
		 * @return void
		 */
		public function ddwcwm_set_globals() {
			global $ddwcwm_wallet;

			$wallet_balance_only_shortcode       = get_option( '_ddwcwm_wallet_balance_only_shortcode' );
			$wallet_balance_shortcode            = get_option( '_ddwcwm_wallet_balance_shortcode' );
			$wallet_operations_shortcode         = get_option( '_ddwcwm_wallet_operations_shortcode' );
			$wallet_balance_operations_shortcode = get_option( '_ddwcwm_wallet_balance_operations_shortcode' );
			$wallet_transactions_shortcode       = get_option( '_ddwcwm_wallet_transactions_shortcode' );
			$enabled_payment_gateways            = get_option( '_ddwcwm_enabled_payment_gateways' );
			$enabled_otp_operations              = get_option( '_ddwcwm_enabled_otp_operations' );
			$otp_length                          = get_option( '_ddwcwm_otp_length', 6 );
			$my_account_endpoint                 = get_option( '_ddwcwm_my_account_endpoint' );
			$my_account_endpoint_title           = get_option( '_ddwcwm_my_account_endpoint_title' );

			if ( empty( $wallet_balance_only_shortcode ) ) {
				$wallet_balance_only_shortcode = '[ddwcwm_wallet_balance_shortcode]';
			}

			if ( empty( $wallet_balance_shortcode ) ) {
				$wallet_balance_shortcode = '[ddwcwm_wallet_balance_layout_shortcode]';
			}

			if ( empty( $wallet_operations_shortcode ) ) {
				$wallet_operations_shortcode = '[ddwcwm_wallet_operations_shortcode]';
			}

			if ( empty( $wallet_balance_operations_shortcode ) ) {
				$wallet_balance_operations_shortcode = '[ddwcwm_wallet_balance_operations_shortcode]';
			}

			if ( empty( $wallet_transactions_shortcode ) ) {
				$wallet_transactions_shortcode = '[ddwcwm_wallet_transactions_shortcode]';
			}

			$credit_reason = [
				'registration'            => esc_html__( 'creating a new account', 'wallet-management-for-woocommerce' ),
				'referral'                => esc_html__( 'referring a friend to our store', 'wallet-management-for-woocommerce' ),
				'cashback'                => esc_html__( 'receiving cashback from your purchase', 'wallet-management-for-woocommerce' ),
				'cart_cashback'           => esc_html__( 'receiving cart cashback from your purchase', 'wallet-management-for-woocommerce' ),
				'product_cashback'        => esc_html__( 'receiving product cashback from your purchase', 'wallet-management-for-woocommerce' ),
				'topup_cashback'          => esc_html__( 'receiving topup cashback from your purchase', 'wallet-management-for-woocommerce' ),
				'first_order_cashback'    => esc_html__( 'receiving first order cashback from your purchase', 'wallet-management-for-woocommerce' ),
				'user_role_cashback'      => esc_html__( 'receiving user role cashback from your purchase', 'wallet-management-for-woocommerce' ),
				'payment_method_cashback' => esc_html__( 'receiving payment method cashback from your purchase', 'wallet-management-for-woocommerce' ),
				'refund'                  => esc_html__( 'refund processing', 'wallet-management-for-woocommerce' ),
				'transfer_received'       => esc_html__( 'wallet transfer received', 'wallet-management-for-woocommerce' ),
				'topup'                   => esc_html__( 'wallet topup', 'wallet-management-for-woocommerce' ),
			];

			$debit_reason = [
				'order_payment'    => esc_html__( 'order payment', 'wallet-management-for-woocommerce' ),
				'transfer_sent'    => esc_html__( 'wallet transfer sent', 'wallet-management-for-woocommerce' ),
				'cashback_expired' => esc_html__( 'cashback expiration', 'wallet-management-for-woocommerce' ),
				'withdrawal'       => esc_html__( 'wallet withdrawal', 'wallet-management-for-woocommerce' ),
			];

			$email_settings = [
				'wallet_credited' => [
					'heading' => 'Your Wallet Has Been Credited!',
					'subject' => 'Wallet Credited with {amount}',
					'message' => "<p>Hi {user_display_name},</p>
<p>Great news! Your wallet has just been credited with {amount} for {reason}. Your new wallet balance is {balance}.</p>
<p>Keep up the great work!</p>
<p>Thank you for being a valued customer.</p>
<p>Best regards</p>",
				],
				'wallet_debited' => [
					'heading' => 'Your Wallet Has Been Debited',
					'subject' => 'Wallet Debited by {amount}',
					'message' => "<p>Hi {user_display_name},</p>
<p>We wanted to inform you that your wallet balance has been debited by {amount} due to {reason}. Please review your updated balance below:</p>
<p>Current wallet balance: {balance}</p>
<p>Thank you for understanding.</p>
<p>Best regards</p>",
				],
				'manual_adjustment' => [
					'heading' => 'Wallet Balance Adjustment Notification',
					'subject' => 'Your Wallet Balance Has Been Updated',
					'message' => "<p>Hi {user_display_name},</p>
<p>We wanted to inform you that your wallet balance has been adjusted by {amount} due to {reason}. Please review your updated balance below:</p>
<p>Current wallet balance: {balance}</p>
<p>Thank you for understanding.</p>
<p>Best regards</p>",
				],
			];

			$ddwcwm_wallet = apply_filters( 'ddwcwm_modify_global_configuration', [
				'enabled'                             => get_option( '_ddwcwm_enabled' ),
				// Pro: peer transfer limits are locked off in Free.
				'min_transfer_limit'                  => '',
				'max_transfer_limit'                  => '',
				'registration_credit'                 => get_option( '_ddwcwm_registration_credit' ),
				'topup_order_status'                  => get_option( '_ddwcwm_topup_order_status', 'completed' ),
				// Pro: partial payments are locked off in Free.
				'partial_payments_enabled'            => '',
				'redirect_to_checkout_on_topup'       => get_option( '_ddwcwm_redirect_to_checkout_on_topup', 'yes' ),
				'enabled_payment_gateways'            => ! empty( $enabled_payment_gateways ) ? $enabled_payment_gateways : [],
				// Pro: OTP verification is locked off in Free.
				'enabled_otp_operations'              => [],
				'otp_expiry'                          => '',
				'otp_length'                          => 6,
				// Pro: withdrawals are locked off in Free.
				'withdrawals_enabled'                 => '',
				'withdraw_charges_type'               => '',
				'withdraw_charges_amount'             => '',
				'min_withdrawal_limit'                => '',
				'max_withdrawal_limit'                => '',
				// Pro: referrals are locked off in Free.
				'referrals_enabled'                   => '',
				'referral_earning_amount'             => '',
				'referral_email_content'              => '',
				'my_account_endpoint'                 => ! empty( $my_account_endpoint ) ? $my_account_endpoint : 'my-wallet',
				'my_account_endpoint_title'           => ! empty( $my_account_endpoint_title ) ? $my_account_endpoint_title : esc_html__( 'My Wallet', 'wallet-management-for-woocommerce' ),
				'enable_widgets_my_account_endpoint'  => get_option( '_ddwcwm_enable_widgets_my_account_endpoint' ),
				'wallet_balance_only_shortcode'       => $wallet_balance_only_shortcode,
				'wallet_balance_shortcode'            => $wallet_balance_shortcode,
				'wallet_operations_shortcode'         => $wallet_operations_shortcode,
				'wallet_balance_operations_shortcode' => $wallet_balance_operations_shortcode,
				'wallet_transactions_shortcode'       => $wallet_transactions_shortcode,
				'details_icons_enabled'               => get_option( '_ddwcwm_details_icons_enabled', 'yes' ),
				'details_icons_wrapper_enabled'       => get_option( '_ddwcwm_details_icons_wrapper_enabled', 'yes' ),
				'details_icon_size'                   => get_option( '_ddwcwm_details_icon_size', 24 ),
				'theme_color'                         => get_option( '_ddwcwm_theme_color', '#0256ff' ),
				'details_icon_color'                  => get_option( '_ddwcwm_details_icon_color', '#0256ff' ),
				'details_icon_wrapper_background_color' => get_option( '_ddwcwm_details_icon_wrapper_background_color', '#EEF3FF' ),
				'details_card_background_color'       => get_option( '_ddwcwm_details_card_background_color', '#ffffff' ),
				// #e5e7eb
				'details_card_border_color'           => get_option( '_ddwcwm_details_card_border_color', '#dce6ff' ),
				'details_card_text_color'             => get_option( '_ddwcwm_details_card_text_color', '#000000' ),
				'details_card_value_color'            => get_option( '_ddwcwm_details_card_value_color', '#111827' ),
				'layout_table_header_text_color'      => get_option( '_ddwcwm_layout_table_header_text_color', '#000000' ),
				'layout_table_header_background_color'=> get_option( '_ddwcwm_layout_table_header_background_color', '#f9fafb' ),
				'success_message_text_color'          => get_option( '_ddwcwm_success_message_text_color', '#065f46' ),
				'success_message_background_color'    => get_option( '_ddwcwm_success_message_background_color', '#ecfdf5' ),
				'error_message_text_color'            => get_option( '_ddwcwm_error_message_text_color', '#991b1b' ),
				'error_message_background_color'      => get_option( '_ddwcwm_error_message_background_color', '#fef2f2' ),
				'info_message_text_color'             => get_option( '_ddwcwm_info_message_text_color', '#0256ff' ),
				'info_message_background_color'       => get_option( '_ddwcwm_info_message_background_color', '#eef3ff' ),
				'cashback_exclude_sale_products'      => get_option( '_ddwcwm_cashback_exclude_sale_products' ),
				'cashback_max_cap'                    => get_option( '_ddwcwm_cashback_max_cap', '' ),
				'cashback_min_order_value'            => get_option( '_ddwcwm_cashback_min_order_value', '' ),
				'email_settings'                      => get_option( '_ddwcwm_email_settings', $email_settings ),
				'credit_reason'                       => get_option( '_ddwcwm_credit_reason', $credit_reason ),
				'debit_reason'                        => get_option( '_ddwcwm_debit_reason', $debit_reason ),
				'cashback_messages_enabled'           => get_option( '_ddwcwm_cashback_messages_enabled', 'yes' ),
				'cashback_cart_page_message'          => get_option( '_ddwcwm_cashback_cart_page_message', 'You will earn {total_cashback} total cashback on this order.' ),
				'cashback_checkout_page_message'      => get_option( '_ddwcwm_cashback_checkout_page_message', 'You will earn {total_cashback} total cashback on this order.' ),
				'cashback_view_order_page_message'     => get_option( '_ddwcwm_cashback_view_order_page_message', 'You have earned {total_cashback} cashback on this order.' ),
				'cashback_order_received_page_message' => get_option( '_ddwcwm_cashback_order_received_page_message', 'You have earned {total_cashback} cashback on this order.' ),
				'cashback_message_text_color'          => get_option( '_ddwcwm_cashback_message_text_color', '#1f2937' ),
				'cashback_message_bg_color'            => get_option( '_ddwcwm_cashback_message_bg_color', '#f3f4f6' ),
				'cashback_message_border_color'        => get_option( '_ddwcwm_cashback_message_border_color', '#0256ff' ),
				'cashback_message_font_size'           => get_option( '_ddwcwm_cashback_message_font_size', '14' ),
			] );

			return $ddwcwm_wallet;
		}
	}
}
