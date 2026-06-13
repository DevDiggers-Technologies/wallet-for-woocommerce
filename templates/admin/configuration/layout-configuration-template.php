<?php
/**
 * Layout Configuration template class
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Layout_Configuration_Template' ) ) {
	/**
	 * Layout Configuration template class
	 */
	class DDWCWM_Layout_Configuration_Template {
		/**
		 * Construct
		 * 
		 * @param array $ddwcwm_wallet
		 */
		public function __construct( $ddwcwm_wallet ) {
			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Global Interface Settings', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Configure global theme elements, brand colors, and the primary visibility of graphical icons across the wallet dashboard.', 'wallet-management-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Primary Theme Color', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'The core brand color used for action buttons, active highlights, and primary interface elements.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-theme-color',
							'value'       => $ddwcwm_wallet['theme_color'],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Global Icon Visibility', 'wallet-management-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Show icons next to wallet details', 'wallet-management-for-woocommerce' ),
							'description'    => esc_html__( 'Toggle the display of graphical icons alongside balance information and operation cards.', 'wallet-management-for-woocommerce' ),
							'id'             => 'ddwcwm-details-icons-enabled',
							'value'          => $ddwcwm_wallet['details_icons_enabled'],
						],
						[
							'type'           => 'checkbox',
							'label'          => esc_html__( 'Icon Background Wrappers', 'wallet-management-for-woocommerce' ),
							'checkbox_label' => esc_html__( 'Enable decorative wrappers for icons', 'wallet-management-for-woocommerce' ),
							'description'    => esc_html__( 'Adds a circular or square background container behind your dashboard icons.', 'wallet-management-for-woocommerce' ),
							'id'             => 'ddwcwm-details-icons-wrapper-enabled',
							'value'          => $ddwcwm_wallet['details_icons_wrapper_enabled'],
						],
						[
							'type'           => 'number',
							'label'          => esc_html__( 'Base Icon Size (px)', 'wallet-management-for-woocommerce' ),
							'description'    => esc_html__( 'Specify the height and width for dashboard icons. Default is recommended for optimal layout.', 'wallet-management-for-woocommerce' ),
							'id'             => 'ddwcwm-details-icon-size',
							'value'          => $ddwcwm_wallet['details_icon_size'],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Dashboard Component Icons', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Upload custom icons to represent specific wallet operations and account states. Recommended size: 100x100px.', 'wallet-management-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'image',
							'label'       => esc_html__( 'Available Balance Icon', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Represents the current spendable funds in the user\'s wallet.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-details-icon-available-balance',
							'value'       => '',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'image',
							'label'       => esc_html__( 'Send Money Icon', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Icon for the peer-to-peer fund transfer operation.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-details-icon-send-money',
							'value'       => '',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'image',
							'label'       => esc_html__( 'Request Money Icon', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Icon for incoming and outgoing fund requests.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-details-icon-request-money',
							'value'       => '',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'image',
							'label'       => esc_html__( 'Withdrawal Icon', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Icon for payout and withdrawal requests.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-details-icon-withdraw',
							'value'       => '',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'image',
							'label'       => esc_html__( 'Referral System Icon', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Icon for the referral program link and sharing options.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-details-icon-refer',
							'value'       => '',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'image',
							'label'       => esc_html__( 'Top-up (Add Money) Icon', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Icon for the manual balance top-up purchase flow.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-details-icon-topup',
							'value'       => '',
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Card & Table Styling', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Fine-tune the typography and background colors for transaction tables and summary cards.', 'wallet-management-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Icon Graphic Color', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'The fill color applied to the icons if non-colored SVG files are used.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-details-icon-color',
							'value'       => $ddwcwm_wallet['details_icon_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Icon Wrapper Background', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Background color for the decorative icon containers.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-details-icon-wrapper-background-color',
							'value'       => $ddwcwm_wallet['details_icon_wrapper_background_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Card Background', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Background color for all dashboard info cards.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-details-card-background-color',
							'value'       => $ddwcwm_wallet['details_card_background_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Card Border Color', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'The perimeter line color for dashboard sections.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-details-card-border-color',
							'value'       => $ddwcwm_wallet['details_card_border_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Card Label Color', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Typography color for descriptive labels within cards.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-details-card-text-color',
							'value'       => $ddwcwm_wallet['details_card_text_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Card Value Color', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Typography color for numerical balances and main amounts.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-details-card-value-color',
							'value'       => $ddwcwm_wallet['details_card_value_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Table Header Text', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Typography color for the transaction history table headings.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-layout-table-header-text-color',
							'value'       => $ddwcwm_wallet['layout_table_header_text_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Table Header Background', 'wallet-management-for-woocommerce' ),
							'description' => esc_html__( 'Background horizontal bar color for transaction tables.', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-layout-table-header-background-color',
							'value'       => $ddwcwm_wallet['layout_table_header_background_color'],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Feedback & Notification Colors', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Customize the color palette for system alerts, success confirmations, and informational notices.', 'wallet-management-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Success Message Text', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-success-message-text-color',
							'value'       => $ddwcwm_wallet['success_message_text_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Success Background', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-success-message-background-color',
							'value'       => $ddwcwm_wallet['success_message_background_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Error Message Text', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-error-message-text-color',
							'value'       => $ddwcwm_wallet['error_message_text_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Error Background', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-error-message-background-color',
							'value'       => $ddwcwm_wallet['error_message_background_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Info Message Text', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-info-message-text-color',
							'value'       => $ddwcwm_wallet['info_message_text_color'],
						],
						[
							'type'        => 'colorpicker',
							'label'       => esc_html__( 'Info Background', 'wallet-management-for-woocommerce' ),
							'id'          => 'ddwcwm-info-message-background-color',
							'value'       => $ddwcwm_wallet['info_message_background_color'],
						],
					],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcwm-layout-configuration-fields' );
		}
	}
}
