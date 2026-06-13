<?php
/**
 * Referrals configuration template class
 *
 * In the Free plugin the referral program is a locked Pro feature. This tab only
 * renders an upgrade prompt and saves no settings.
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Configuration;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Referrals_Configuration_Template' ) ) {
	/**
	 * Referrals configuration template class
	 */
	class DDWCWM_Referrals_Configuration_Template {
		/**
		 * Construct
		 *
		 * @param array $ddwcwm_wallet Configuration.
		 */
		public function __construct( $ddwcwm_wallet ) {
			ddfw_upgrade_to_pro_section( [
				'image_url'     => DDWCWM_PLUGIN_URL . 'assets/images/pro-pages/referral-configuration.webp',
				'heading'       => esc_html__( 'Referral Program is a Pro feature', 'wallet-management-for-woocommerce' ),
				'description'   => esc_html__( 'Reward both the referrer and the new customer with wallet credit on successful registration.', 'wallet-management-for-woocommerce' ),
				'list_features' => [
					esc_html__( 'Reward referrer and new user automatically', 'wallet-management-for-woocommerce' ),
					esc_html__( 'Configurable reward amount', 'wallet-management-for-woocommerce' ),
					esc_html__( 'Customizable referral invitation email', 'wallet-management-for-woocommerce' ),
				],
				'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-wallet-management/',
			] );
		}
	}
}
