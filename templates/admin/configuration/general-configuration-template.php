<?php
/**
 * General configuration template class
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_General_Configuration_Template' ) ) {
	/**
	 * General configuration template class
	 */
	class DDWCWM_General_Configuration_Template {
		/**
		 * Construct
		 * 
		 * @param array $ddwcwm_wallet
		 */
		public function __construct( $ddwcwm_wallet ) {
			$gateways_options = [];
			$gateways         = WC()->payment_gateways->get_available_payment_gateways();
			if ( ! empty( $gateways ) ) {
				foreach ( $gateways as $key => $gateway ) {
					if ( $gateway->enabled == 'yes' && $gateway->id != 'ddwcwm_wallet' ) {
						$gateways_options[ $key ] = $gateway->method_title;
					}
				}
			}

			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'General Settings', 'devdiggers-wallet-for-woocommerce' ),
						'description' => esc_html__( 'Configure the core functionalities of the wallet system, including registration rewards and top-up defaults.', 'devdiggers-wallet-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Enable/Disable', 'devdiggers-wallet-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Enable DevDiggers Wallet for WooCommerce', 'devdiggers-wallet-for-woocommerce' ),
							'description'    => esc_html__( 'Activate or deactivate the entire wallet system and its features for all customers.', 'devdiggers-wallet-for-woocommerce' ),
							'id'             => 'ddwcwm-enable',
							'name'           => '_ddwcwm_enabled',
							'value'          => $ddwcwm_wallet['enabled'],
						],
						[
							'type'        => 'number',
							'label'       => esc_html__( 'Registration Credit', 'devdiggers-wallet-for-woocommerce' ),
							'description' => esc_html__( 'The amount credited to a customer\'s wallet upon successful account registration. Leave blank to disable registration rewards.', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-registration-credit',
							'name'        => '_ddwcwm_registration_credit',
							'value'       => $ddwcwm_wallet['registration_credit'],
							'custom_attributes' => [ 'step' => '0.01' ],
							'after_field_text'  => '<strong>' . get_woocommerce_currency_symbol() . '</strong>',
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Wallet Topup Order Status', 'devdiggers-wallet-for-woocommerce' ),
							'description' => esc_html__( 'Choose the order status that triggers the balance update for top-up purchases. \'Completed\' is recommended for security.', 'devdiggers-wallet-for-woocommerce' ),
							'options'     => [
								'default'   => esc_html__( 'Default', 'devdiggers-wallet-for-woocommerce' ),
								'completed' => esc_html__( 'Completed', 'devdiggers-wallet-for-woocommerce' ),
							],
							'id'          => 'ddwcwm-topup-order-status',
							'name'        => '_ddwcwm_topup_order_status',
							'value'       => $ddwcwm_wallet['topup_order_status'],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Top-up Presets', 'devdiggers-wallet-for-woocommerce' ),
							'description' => esc_html__( 'Define quick-select amount buttons (comma-separated) to simplify the top-up process for customers (e.g., 10, 20, 50, 100). Available in the Pro version.', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-topup-presets',
							'name'        => '_ddwcwm_topup_presets',
							'value'       => '10, 20, 50, 100',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Limits Settings', 'devdiggers-wallet-for-woocommerce' ),
						'description' => esc_html__( 'Set restrictions on how users can top up or spend their wallet balance.', 'devdiggers-wallet-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'             => 'number',
							'label'            => esc_html__( 'Min Topup Limit', 'devdiggers-wallet-for-woocommerce' ),
							'description'      => esc_html__( 'The lowest allowable amount for a single top-up transaction.', 'devdiggers-wallet-for-woocommerce' ),
							'id'               => 'ddwcwm-min-topup-limit',
							'name'             => '_ddwcwm_min_topup_limit',
							'value'            => '',
							'after_field_text' => '<strong>' . get_woocommerce_currency_symbol() . '</strong>',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'             => 'number',
							'label'            => esc_html__( 'Max Topup Limit', 'devdiggers-wallet-for-woocommerce' ),
							'description'      => esc_html__( 'The highest allowable amount for a single top-up transaction.', 'devdiggers-wallet-for-woocommerce' ),
							'id'               => 'ddwcwm-max-topup-limit',
							'name'             => '_ddwcwm_max_topup_limit',
							'value'            => '',
							'after_field_text' => '<strong>' . get_woocommerce_currency_symbol() . '</strong>',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Debit Limit Type', 'devdiggers-wallet-for-woocommerce' ),
							'description' => esc_html__( 'Choose whether the debit limit is a fixed value or a percentage of the order total.', 'devdiggers-wallet-for-woocommerce' ),
							'options'     => [
								'fixed'      => esc_html__( 'Fixed', 'devdiggers-wallet-for-woocommerce' ),
								'percentage' => esc_html__( 'Percentage', 'devdiggers-wallet-for-woocommerce' ),
							],
							'id'          => 'ddwcwm-debit-limit-type',
							'name'        => '_ddwcwm_debit_limit_type',
							'value'       => '',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'             => 'number',
							'label'            => esc_html__( 'Min Debit Limit', 'devdiggers-wallet-for-woocommerce' ),
							'description'      => esc_html__( 'The minimum wallet balance required to use the wallet at checkout.', 'devdiggers-wallet-for-woocommerce' ),
							'id'               => 'ddwcwm-min-debit-limit',
							'name'             => '_ddwcwm_min_debit_limit',
							'value'            => '',
							'after_field_text' => '<strong>' . get_woocommerce_currency_symbol() . '</strong>',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'             => 'number',
							'label'            => esc_html__( 'Max Debit Limit', 'devdiggers-wallet-for-woocommerce' ),
							'description'      => esc_html__( 'The maximum amount that can be debited from the wallet for a single order.', 'devdiggers-wallet-for-woocommerce' ),
							'id'               => 'ddwcwm-max-debit-limit',
							'name'             => '_ddwcwm_max_debit_limit',
							'value'            => '',
							'after_field_text' => '<strong>' . get_woocommerce_currency_symbol() . '</strong>',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'             => 'number',
							'label'            => esc_html__( 'Min Transfer Limit', 'devdiggers-wallet-for-woocommerce' ),
							'description'      => esc_html__( 'The minimum amount a customer can send in a single wallet transfer.', 'devdiggers-wallet-for-woocommerce' ),
							'id'               => 'ddwcwm-min-transfer-limit',
							'value'            => '',
							'after_field_text' => '<strong>' . get_woocommerce_currency_symbol() . '</strong>',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'             => 'number',
							'label'            => esc_html__( 'Max Transfer Limit', 'devdiggers-wallet-for-woocommerce' ),
							'description'      => esc_html__( 'The maximum amount a customer can send in a single wallet transfer.', 'devdiggers-wallet-for-woocommerce' ),
							'id'               => 'ddwcwm-max-transfer-limit',
							'value'            => '',
							'after_field_text' => '<strong>' . get_woocommerce_currency_symbol() . '</strong>',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Checkout Settings', 'devdiggers-wallet-for-woocommerce' ),
						'description' => esc_html__( 'Configure how the wallet integrates with the WooCommerce checkout process and manage payment methods.', 'devdiggers-wallet-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Partial Payments', 'devdiggers-wallet-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Enable/Disable Partial Payments', 'devdiggers-wallet-for-woocommerce' ),
							'description'    => esc_html__( 'Allow customers to pay a portion of their order with their wallet balance and the rest with another payment method. Available in the Pro version.', 'devdiggers-wallet-for-woocommerce' ),
							'id'             => 'ddwcwm-partial-payments-enabled',
							'value'          => '',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Enabled Payment Gateways for Topup', 'devdiggers-wallet-for-woocommerce' ),
							'description' => esc_html__( 'Restrict the payment methods available specifically for wallet top-up transactions.', 'devdiggers-wallet-for-woocommerce' ),
							'options'     => $gateways_options,
							'id'          => 'ddwcwm-enabled-payment-gateways',
							'name'        => '_ddwcwm_enabled_payment_gateways[]',
							'value'       => $ddwcwm_wallet['enabled_payment_gateways'],
							'custom_attributes' => [
								'multiple'         => 'multiple',
								'data-placeholder' => esc_attr__( 'Select Payment Gateways', 'devdiggers-wallet-for-woocommerce' ),
							],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Redirect to Checkout on Top-up', 'devdiggers-wallet-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Redirect to checkout after adding top-up', 'devdiggers-wallet-for-woocommerce' ),
							'description'    => esc_html__( 'Automatically redirect customers to the checkout page after they add a top-up amount to their wallet.', 'devdiggers-wallet-for-woocommerce' ),
							'id'             => 'ddwcwm-redirect-to-checkout-on-topup',
							'name'           => '_ddwcwm_redirect_to_checkout_on_topup',
							'value'          => $ddwcwm_wallet['redirect_to_checkout_on_topup'],
						],
						[
							'type'  => 'field_html',
							'label' => esc_html__( 'Configure Payment Gateway', 'devdiggers-wallet-for-woocommerce' ),
							'html'  => '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ddwcwm_wallet' ) ) . '" class="button ddwcwm-margin-top-10" target="__blank">' . esc_html__( 'Configure', 'devdiggers-wallet-for-woocommerce' ) . '</a>',
							'description' => esc_html__( 'Navigate to the core WooCommerce settings to configure the Wallet payment method.', 'devdiggers-wallet-for-woocommerce' ),
						],
					],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcwm-general-configuration-fields' );
		}
	}
}
