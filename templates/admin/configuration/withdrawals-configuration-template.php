<?php
/**
 * Withdrawals configuration template class
 *
 * In the Free plugin withdrawals are a locked Pro feature. This tab only renders
 * an upgrade prompt and saves no settings.
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Configuration;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Withdrawals_Configuration_Template' ) ) {
	/**
	 * Withdrawals configuration template class
	 */
	class DDWCWM_Withdrawals_Configuration_Template {
		/**
		 * Construct
		 *
		 * @param array $ddwcwm_wallet Configuration.
		 */
		public function __construct( $ddwcwm_wallet ) {
			ddfw_upgrade_to_pro_section( [
				'image_url'     => DDWCWM_PLUGIN_URL . 'assets/images/pro-pages/withdrawal-configuration.webp',
				'heading'       => esc_html__( 'Withdrawals is a Pro feature', 'devdiggers-wallet-for-woocommerce' ),
				'description'   => esc_html__( 'Allow customers to request payouts from their wallet balance, with full control over charges and limits.', 'devdiggers-wallet-for-woocommerce' ),
				'list_features' => [
					esc_html__( 'Enable customer withdrawal requests', 'devdiggers-wallet-for-woocommerce' ),
					esc_html__( 'Fixed or percentage based withdrawal charges', 'devdiggers-wallet-for-woocommerce' ),
					esc_html__( 'Minimum and maximum withdrawal limits', 'devdiggers-wallet-for-woocommerce' ),
					esc_html__( 'Approve or cancel requests from the admin dashboard', 'devdiggers-wallet-for-woocommerce' ),
				],
				'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-wallet-management/',
			] );
		}
	}
}
