<?php
/**
 * Endpoints configuration template class
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Endpoints_Configuration_Template' ) ) {
	/**
	 * Endpoints configuration template class
	 */
	class DDWCWM_Endpoints_Configuration_Template {
		/**
		 * Construct
		 * 
		 * @param array $ddwcwm_wallet
		 */
		public function __construct( $ddwcwm_wallet ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check of core Settings API 'settings-updated' flag.
			if ( ! empty( $_GET['settings-updated'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) ) {
				flush_rewrite_rules();
			}

			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'My Account Section', 'devdiggers-wallet-for-woocommerce' ),
						'description' => esc_html__( 'Configure the endpoint slug, menu title, and sidebar presence for the wallet dashboard within the WooCommerce My Account area.', 'devdiggers-wallet-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Endpoint Slug', 'devdiggers-wallet-for-woocommerce' ),
							'description' => esc_html__( 'The unique URL identifier for the wallet section (e.g., "my-wallet"). Remember to save permalinks after changing this.', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_attr__( 'Default: devdiggers-wallet-for-woocommerce', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-my-account-endpoint',
							'name'        => '_ddwcwm_my_account_endpoint',
							'value'       => $ddwcwm_wallet['my_account_endpoint'],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Menu Title', 'devdiggers-wallet-for-woocommerce' ),
							'description' => esc_html__( 'The label displayed in the My Account sidebar navigation menu.', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_attr__( 'Default: My Wallet', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-my-account-endpoint-title',
							'name'        => '_ddwcwm_my_account_endpoint_title',
							'value'       => $ddwcwm_wallet['my_account_endpoint_title'],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Sidebar Widgets', 'devdiggers-wallet-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Enable wallet widgets in account sidebar', 'devdiggers-wallet-for-woocommerce' ),
							'description'    => esc_html__( 'Toggle the visibility of supplementary wallet information widgets when viewing the wallet endpoint.', 'devdiggers-wallet-for-woocommerce' ),
							'id'             => 'ddwcwm-enable-widgets-my-account-endpoint',
							'name'           => '_ddwcwm_enable_widgets_my_account_endpoint',
							'value'          => $ddwcwm_wallet['enable_widgets_my_account_endpoint'],
						],
					],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcwm-endpoints-configuration-fields' );
		}
	}
}
