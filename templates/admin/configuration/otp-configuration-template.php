<?php
/**
 * OTP configuration template class
 *
 * In the Free plugin OTP verification is a locked Pro feature. This tab only
 * renders an upgrade prompt and saves no settings.
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Configuration;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_OTP_Configuration_Template' ) ) {
	/**
	 * OTP configuration template class
	 */
	class DDWCWM_OTP_Configuration_Template {
		/**
		 * Construct
		 *
		 * @param array $ddwcwm_wallet Configuration.
		 */
		public function __construct( $ddwcwm_wallet ) {
			ddfw_upgrade_to_pro_section( [
				'image_url'     => DDWCWM_PLUGIN_URL . 'assets/images/pro-pages/otp-configuration.webp',
				'heading'       => esc_html__( 'OTP Verification is a Pro feature', 'wallet-management-for-woocommerce' ),
				'description'   => esc_html__( 'Add an email based One-Time Password layer to secure sensitive wallet operations.', 'wallet-management-for-woocommerce' ),
				'list_features' => [
					esc_html__( 'Require OTP for send, request and withdraw operations', 'wallet-management-for-woocommerce' ),
					esc_html__( 'Configurable OTP expiry time', 'wallet-management-for-woocommerce' ),
					esc_html__( 'Configurable OTP length', 'wallet-management-for-woocommerce' ),
				],
				'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-wallet-management/',
			] );
		}
	}
}
