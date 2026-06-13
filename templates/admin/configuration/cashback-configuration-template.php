<?php
/**
 * Cashback configuration template class
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Cashback_Configuration_Template' ) ) {
	/**
	 * Cashback configuration template class
	 */
	class DDWCWM_Cashback_Configuration_Template {
		/**
		 * Construct
		 * 
		 * @param array $ddwcwm_wallet
		 */
		public function __construct( $ddwcwm_wallet ) {
			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Global Cashback Settings', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Configure the core logic, limits, and eligibility requirements for awarding cashback rewards.', 'wallet-management-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'              => 'number',
							'label'             => esc_html__( 'Cashback Credit Delay', 'wallet-management-for-woocommerce' ),
							'description'       => esc_html__( 'The number of days to wait after order completion before crediting cashback to the wallet. Available in the Pro version.', 'wallet-management-for-woocommerce' ),
							'id'                => 'ddwcwm-cashback-credit-delay',
							'name'              => '_ddwcwm_cashback_credit_delay',
							'value'             => '',
							'after_field_text'  => '<strong>' . esc_html__( 'Days', 'wallet-management-for-woocommerce' ) . '</strong>',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Exclude Sale Products', 'wallet-management-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Exclude products on sale from cashback', 'wallet-management-for-woocommerce' ),
							'description'    => esc_html__( 'When enabled, products already on sale will not contribute to the cashback calculation.', 'wallet-management-for-woocommerce' ),
							'id'             => 'ddwcwm-cashback-exclude-sale-products',
							'name'           => '_ddwcwm_cashback_exclude_sale_products',
							'value'          => $ddwcwm_wallet['cashback_exclude_sale_products'],
						],
						[
							'type'              => 'number',
							'label'             => esc_html__( 'Max Cashback Cap', 'wallet-management-for-woocommerce' ),
							'description'       => esc_html__( 'The maximum total cashback amount allowable per single order. Leave blank for no limit.', 'wallet-management-for-woocommerce' ),
							'id'                => 'ddwcwm-cashback-max-cap',
							'name'              => '_ddwcwm_cashback_max_cap',
							'value'             => $ddwcwm_wallet['cashback_max_cap'],
							'placeholder'       => esc_html__( 'Leave empty for no limit', 'wallet-management-for-woocommerce' ),
							'custom_attributes' => [ 'step' => '0.01', 'min' => '0' ],
							'after_field_text'  => '<strong>' . get_woocommerce_currency_symbol() . '</strong>',
						],
						[
							'type'              => 'number',
							'label'             => esc_html__( 'Minimum Order Value', 'wallet-management-for-woocommerce' ),
							'description'       => esc_html__( 'The minimum subtotal required for an order to qualify for any cashback rewards.', 'wallet-management-for-woocommerce' ),
							'id'                => 'ddwcwm-cashback-min-order-value',
							'name'              => '_ddwcwm_cashback_min_order_value',
							'value'             => $ddwcwm_wallet['cashback_min_order_value'],
							'placeholder'       => esc_html__( 'Leave empty for no limit', 'wallet-management-for-woocommerce' ),
							'custom_attributes' => [ 'step' => '0.01', 'min' => '0' ],
							'after_field_text'  => '<strong>' . get_woocommerce_currency_symbol() . '</strong>',
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Reward Milestones', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Define specific scenarios and purchase milestones that trigger specialized cashback rewards.', 'wallet-management-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'First Order Reward', 'wallet-management-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Enable First Order Cashback', 'wallet-management-for-woocommerce' ),
							'description'    => esc_html__( 'Activate special one-time cashback incentives specifically for a customer\'s first successful purchase.', 'wallet-management-for-woocommerce' ),
							'id'             => 'ddwcwm-first-order-cashback-enabled',
							'name'           => '_ddwcwm_first_order_cashback_enabled',
							'value'          => '',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'select',
							'label'       => esc_html__( 'Reward Type', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Choose whether the first-order bonus is a fixed monetary value or a percentage of the purchase subtotal.', 'wallet-management-for-woocommerce' ),
							'options'     => [
								'fixed'      => esc_html__( 'Fixed', 'wallet-management-for-woocommerce' ),
								'percentage' => esc_html__( 'Percentage', 'wallet-management-for-woocommerce' ),
							],
							'id'          => 'ddwcwm-first-order-cashback-type',
							'name'        => '_ddwcwm_first_order_cashback_type',
							'value'       => '',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'              => 'number',
							'label'             => esc_html__( 'Reward Amount', 'wallet-management-for-woocommerce' ),
							'description'       => esc_html__( 'The specific value or percentage for the first-order incentive.', 'wallet-management-for-woocommerce' ),
							'id'                => 'ddwcwm-first-order-cashback-amount',
							'name'              => '_ddwcwm_first_order_cashback_amount',
							'value'             => '',
							'custom_attributes' => [ 'step' => '0.01' ],
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Expiry', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Set expiration rules and automated email reminders to encourage users to spend their cashback balances.', 'wallet-management-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Enable Expiry', 'wallet-management-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Enable expiration for awarded cashback', 'wallet-management-for-woocommerce' ),
							'description'    => esc_html__( 'Toggle the automatic expiration of awarded cashback balances after a defined period.', 'wallet-management-for-woocommerce' ),
							'id'             => 'ddwcwm-cashback-expiry-enabled',
							'name'           => '_ddwcwm_cashback_expiry_enabled',
							'value'          => '',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'              => 'number',
							'label'             => esc_html__( 'Expiry Duration', 'wallet-management-for-woocommerce' ),
							'description'       => esc_html__( 'The lifespan of awarded cashback (in days) before it is automatically deducted from the user\'s balance.', 'wallet-management-for-woocommerce' ),
							'id'                => 'ddwcwm-cashback-expiry-days',
							'name'              => '_ddwcwm_cashback_expiry_days',
							'value'             => '',
							'placeholder'       => esc_html__( 'Enter number of days', 'wallet-management-for-woocommerce' ),
							'after_field_text'  => '<strong>' . esc_html__( 'Days', 'wallet-management-for-woocommerce' ) . '</strong>',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Enable Expiry Reminder', 'wallet-management-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Enable reminder email for expiring cashback', 'wallet-management-for-woocommerce' ),
							'description'    => esc_html__( 'Send an automated notification to customers before their cashback balance is scheduled to expire.', 'wallet-management-for-woocommerce' ),
							'id'             => 'ddwcwm-cashback-expiry-reminder-enabled',
							'name'           => '_ddwcwm_cashback_expiry_reminder_enabled',
							'value'          => '',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'              => 'number',
							'label'             => esc_html__( 'Reminder Before Expiry (in days)', 'wallet-management-for-woocommerce' ),
							'description'       => esc_html__( 'Number of days prior to expiration to trigger the reminder notification.', 'wallet-management-for-woocommerce' ),
							'id'                => 'ddwcwm-cashback-expiry-reminder-days',
							'name'              => '_ddwcwm_cashback_expiry_reminder_days',
							'value'             => '',
							'placeholder'       => esc_html__( 'Enter number of days', 'wallet-management-for-woocommerce' ),
							'after_field_text'  => '<strong>' . esc_html__( 'Days', 'wallet-management-for-woocommerce' ) . '</strong>',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Visuals & Notifications', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Customize how cashback opportunities and earned rewards are communicated to customers across your store.', 'wallet-management-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Enable Messages', 'wallet-management-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Display Cashback Banners', 'wallet-management-for-woocommerce' ),
							'description'    => esc_html__( 'Toggle the visibility of cashback information banners across various store pages.', 'wallet-management-for-woocommerce' ),
							'id'             => 'ddwcwm-cashback-messages-enabled',
							'name'           => '_ddwcwm_cashback_messages_enabled',
							'value'          => $ddwcwm_wallet['cashback_messages_enabled'],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Shop Page Message', 'wallet-management-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Shop Page Message', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-cashback-shop-page-message',
							'name'        => '_ddwcwm_cashback_shop_page_message',
							'value'       => $ddwcwm_wallet['cashback_shop_page_message'],
							/* translators: %s: the {cashback_amount} placeholder tag. */
							'description' => sprintf( esc_html__( 'Archive pages highlighting potential earnings. Use placeholder: %s.', 'wallet-management-for-woocommerce' ), '<strong>{cashback_amount}</strong>' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Product Page Message', 'wallet-management-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Product Page Message', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-cashback-product-page-message',
							'name'        => '_ddwcwm_cashback_product_page_message',
							'value'       => $ddwcwm_wallet['cashback_product_page_message'],
							/* translators: %s: the {cashback_amount} placeholder tag. */
							'description' => sprintf( esc_html__( 'Individual item pages showing specific cashback. Use placeholder: %s.', 'wallet-management-for-woocommerce' ), '<strong>{cashback_amount}</strong>' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Cart Page Message', 'wallet-management-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Cart Page Message', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-cashback-cart-page-message',
							'name'        => '_ddwcwm_cashback_cart_page_message',
							'value'       => $ddwcwm_wallet['cashback_cart_page_message'],
							/* translators: %s: the {cashback_amount} placeholder tag. */
							'description' => sprintf( esc_html__( 'Cumulative cashback summary on the cart page. Use placeholder: %s.', 'wallet-management-for-woocommerce' ), '<strong>{total_cashback}</strong>' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Checkout Page Message', 'wallet-management-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Checkout Page Message', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-cashback-checkout-page-message',
							'name'        => '_ddwcwm_cashback_checkout_page_message',
							'value'       => $ddwcwm_wallet['cashback_checkout_page_message'],
							/* translators: %s: the {cashback_amount} placeholder tag. */
							'description' => sprintf( esc_html__( 'Final cashback confirmation during checkout. Use placeholder: %s.', 'wallet-management-for-woocommerce' ), '<strong>{total_cashback}</strong>' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'View Order Page Message', 'wallet-management-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter View Order Page Message', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-cashback-view-order-page-message',
							'name'        => '_ddwcwm_cashback_view_order_page_message',
							'value'       => $ddwcwm_wallet['cashback_view_order_page_message'],
							/* translators: %s: the {cashback_amount} placeholder tag. */
							'description' => sprintf( esc_html__( 'Summary of rewards displayed on order details. Use placeholder: %s.', 'wallet-management-for-woocommerce' ), '<strong>{total_cashback}</strong>' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Order Received Message', 'wallet-management-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Order Received Page Message', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-cashback-order-received-page-message',
							'name'        => '_ddwcwm_cashback_order_received_page_message',
							'value'       => $ddwcwm_wallet['cashback_order_received_page_message'],
							/* translators: %s: the {cashback_amount} placeholder tag. */
							'description' => sprintf( esc_html__( 'Confirmation message on the "Thank You" page. Use placeholder: %s.', 'wallet-management-for-woocommerce' ), '<strong>{total_cashback}</strong>' ),
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Message Design', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Fine-tune the appearance of cashback banners to match your store\'s visual theme.', 'wallet-management-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'  => 'colorpicker',
							'label' => esc_html__( 'Text Color', 'wallet-management-for-woocommerce' ),
							'id'    => 'ddwcwm-cashback-message-text-color',
							'name'  => '_ddwcwm_cashback_message_text_color',
							'value' => $ddwcwm_wallet['cashback_message_text_color'],
						],
						[
							'type'  => 'colorpicker',
							'label' => esc_html__( 'Background Color', 'wallet-management-for-woocommerce' ),
							'id'    => 'ddwcwm-cashback-message-bg-color',
							'name'  => '_ddwcwm_cashback_message_bg_color',
							'value' => $ddwcwm_wallet['cashback_message_bg_color'],
						],
						[
							'type'  => 'colorpicker',
							'label' => esc_html__( 'Border Color', 'wallet-management-for-woocommerce' ),
							'id'    => 'ddwcwm-cashback-message-border-color',
							'name'  => '_ddwcwm_cashback_message_border_color',
							'value' => $ddwcwm_wallet['cashback_message_border_color'],
						],
						[
							'type'  => 'number',
							'label' => esc_html__( 'Font Size (px)', 'wallet-management-for-woocommerce' ),
							'id'    => 'ddwcwm-cashback-message-font-size',
							'name'  => '_ddwcwm_cashback_message_font_size',
							'value' => $ddwcwm_wallet['cashback_message_font_size'],
						],
					],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcwm-cashback-configuration-fields' );
		}
	}
}
