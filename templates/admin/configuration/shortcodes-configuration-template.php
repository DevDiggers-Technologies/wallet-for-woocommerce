<?php
/**
 * Shortcodes configuration template class
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Shortcodes_Configuration_Template' ) ) {
	/**
	 * Shortcodes configuration template class
	 */
	class DDWCWM_Shortcodes_Configuration_Template {
		/**
		 * Construct
		 * 
		 * @param array $ddwcwm_wallet
		 */
		public function __construct( $ddwcwm_wallet ) {
			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Custom Shortcodes', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Deploy wallet functionality anywhere on your site using custom shortcodes. These tags automatically render user-specific data for the currently logged-in customer.', 'wallet-management-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Balance Only', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Displays the numerical wallet balance of the current user.', 'wallet-management-for-woocommerce' ),
							/* translators: %s: the default shortcode example. */
							'placeholder' => sprintf( esc_attr__( 'Default: %s', 'wallet-management-for-woocommerce' ), '[ddwcwm_wallet_balance_shortcode]' ),
							'id'          => 'ddwcwm-wallet-balance-only-shortcode',
							'name'        => '_ddwcwm_wallet_balance_only_shortcode',
							'value'       => $ddwcwm_wallet['wallet_balance_only_shortcode'],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Balance Layout', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Renders the stylized balance card layout, including icons and labels.', 'wallet-management-for-woocommerce' ),
							/* translators: %s: the default shortcode example. */
							'placeholder' => sprintf( esc_attr__( 'Default: %s', 'wallet-management-for-woocommerce' ), '[ddwcwm_wallet_balance_layout_shortcode]' ),
							'id'          => 'ddwcwm-wallet-balance-shortcode',
							'name'        => '_ddwcwm_wallet_balance_shortcode',
							'value'       => $ddwcwm_wallet['wallet_balance_shortcode'],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Operations Hub', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Displays the interactive action buttons for adding, sending, requesting, and withdrawing funds.', 'wallet-management-for-woocommerce' ),
							/* translators: %s: the default shortcode example. */
							'placeholder' => sprintf( esc_attr__( 'Default: %s', 'wallet-management-for-woocommerce' ), '[ddwcwm_wallet_operations_shortcode]' ),
							'id'          => 'ddwcwm-wallet-operations-shortcode',
							'name'        => '_ddwcwm_wallet_operations_shortcode',
							'value'       => $ddwcwm_wallet['wallet_operations_shortcode'],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Integrated Dashboard', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Combines the balance layout and operation buttons into a single cohesive interface.', 'wallet-management-for-woocommerce' ),
							/* translators: %s: the default shortcode example. */
							'placeholder' => sprintf( esc_attr__( 'Default: %s', 'wallet-management-for-woocommerce' ), '[ddwcwm_wallet_balance_operations_shortcode]' ),
							'id'          => 'ddwcwm-wallet-balance-operations-shortcode',
							'name'        => '_ddwcwm_wallet_balance_operations_shortcode',
							'value'       => $ddwcwm_wallet['wallet_balance_operations_shortcode'],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Transaction History', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Renders a detailed table of the user\'s recent wallet activity and transaction logs.', 'wallet-management-for-woocommerce' ),
							/* translators: %s: the default shortcode example. */
							'placeholder' => sprintf( esc_attr__( 'Default: %s', 'wallet-management-for-woocommerce' ), '[ddwcwm_wallet_transactions_shortcode]' ),
							'id'          => 'ddwcwm-wallet-transactions-shortcode',
							'name'        => '_ddwcwm_wallet_transactions_shortcode',
							'value'       => $ddwcwm_wallet['wallet_transactions_shortcode'],
						],
					],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcwm-shortcodes-configuration-fields' );
		}
	}
}
