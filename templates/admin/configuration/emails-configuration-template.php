<?php
/**
 * Emails Configuration template class
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Configuration;

use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Emails_Configuration_Template' ) ) {
	/**
	 * Emails Configuration template class
	 */
	class DDWCWM_Emails_Configuration_Template {
		/**
		 * Construct
		 * 
		 * @param array $ddwcwm_wallet
		 */
		public function __construct( $ddwcwm_wallet ) {
			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Wallet Credited Notification', 'devdiggers-wallet-for-woocommerce' ),
						'description' => esc_html__( 'Automated notification sent to customers when funds are added to their wallet via registration, cashback, top-ups, or manual adjustments.', 'devdiggers-wallet-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Heading', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Heading', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-email-settings-wallet-credited-heading',
							'name'        => '_ddwcwm_email_settings[wallet_credited][heading]',
							'value'       => isset( $ddwcwm_wallet['email_settings']['wallet_credited']['heading'] ) ? $ddwcwm_wallet['email_settings']['wallet_credited']['heading'] : '',
							'description' => esc_html__( 'The main title that appears at the top of the email sent to customers when they receive funds.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Subject', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter subject', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-email-settings-wallet-credited-subject',
							'name'        => '_ddwcwm_email_settings[wallet_credited][subject]',
							'value'       => isset( $ddwcwm_wallet['email_settings']['wallet_credited']['subject'] ) ? $ddwcwm_wallet['email_settings']['wallet_credited']['subject'] : '',
							'description' => esc_html__( 'The subject line of the email notification that appears in the customer\'s inbox when they receive funds.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'          => 'editor',
							'label'         => esc_html__( 'Email Message', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder'   => esc_html__( 'Enter Email Message', 'devdiggers-wallet-for-woocommerce' ),
							'id'            => 'ddwcwm-email-settings-wallet-credited-message',
							'name'          => '_ddwcwm_email_settings[wallet_credited][message]',
							'value'         => isset( $ddwcwm_wallet['email_settings']['wallet_credited']['message'] ) ? $ddwcwm_wallet['email_settings']['wallet_credited']['message'] : '',
							'textarea_rows' => 6,
							/* translators: 1: amount, 2: reason, 3: balance, 4: user name, 5: user email, 6: display name, 7: site title. */
							'description'   => sprintf( esc_html__( "The body of the email that informs the customer about the amount credited, along with details like the new balance. \n\n Available Placeholders: %1\$s, %2\$s, %3\$s, %4\$s, %5\$s, %6\$s and %7\$s.", 'devdiggers-wallet-for-woocommerce' ), '<strong>{amount}</strong>', '<strong>{reason}</strong>', '<strong>{balance}</strong>', '<strong>{user_name}</strong>', '<strong>{user_email}</strong>', '<strong>{user_display_name}</strong>', '<strong>{site_title}</strong>' ),
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Credit Reason Labels', 'devdiggers-wallet-for-woocommerce' ),
						/* translators: %s: the {reason} placeholder tag. */
						'description' => sprintf( esc_html__( 'Define the human-readable text for the %s placeholder used in credit notifications. This clarifies the source of funds for the customer.', 'devdiggers-wallet-for-woocommerce' ), '<strong>{reason}</strong>' ),
					],
					'fields' => [
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Registration', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-credit-reason-registration',
							'name'        => '_ddwcwm_credit_reason[registration]',
							'value'       => isset( $ddwcwm_wallet['credit_reason']['registration'] ) ? $ddwcwm_wallet['credit_reason']['registration'] : '',
							'description' => esc_html__( 'Enter the reason text for funds earned when a user successfully registers an account.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'General Cashback', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-credit-reason-cashback',
							'name'        => '_ddwcwm_credit_reason[cashback]',
							'value'       => isset( $ddwcwm_wallet['credit_reason']['cashback'] ) ? $ddwcwm_wallet['credit_reason']['cashback'] : '',
							'description' => esc_html__( 'Enter the reason text for funds earned from general cashbacks.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Cart Cashback', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-credit-reason-cart-cashback',
							'name'        => '_ddwcwm_credit_reason[cart_cashback]',
							'value'       => isset( $ddwcwm_wallet['credit_reason']['cart_cashback'] ) ? $ddwcwm_wallet['credit_reason']['cart_cashback'] : '',
							'description' => esc_html__( 'Reason text for cashback earned on the entire cart.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Refund', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-credit-reason-refund',
							'name'        => '_ddwcwm_credit_reason[refund]',
							'value'       => isset( $ddwcwm_wallet['credit_reason']['refund'] ) ? $ddwcwm_wallet['credit_reason']['refund'] : '',
							'description' => esc_html__( "Enter the reason text for funds returned via an order refund.", 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Topup', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-credit-reason-topup',
							'name'        => '_ddwcwm_credit_reason[topup]',
							'value'       => isset( $ddwcwm_wallet['credit_reason']['topup'] ) ? $ddwcwm_wallet['credit_reason']['topup'] : '',
							'description' => esc_html__( 'Enter the reason text for funds added via a manual topup purchase.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Transfer Received', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-credit-reason-transfer-received',
							'name'        => '_ddwcwm_credit_reason[transfer_received]',
							'value'       => isset( $ddwcwm_wallet['credit_reason']['transfer_received'] ) ? $ddwcwm_wallet['credit_reason']['transfer_received'] : '',
							'description' => esc_html__( 'Reason text for funds received from another user via Send Money.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Referral', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-credit-reason-referral',
							'name'        => '_ddwcwm_credit_reason[referral]',
							'description' => esc_html__( 'Reason text for funds earned from the referral program.', 'devdiggers-wallet-for-woocommerce' ),
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Product Cashback', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-credit-reason-product-cashback',
							'name'        => '_ddwcwm_credit_reason[product_cashback]',
							'description' => esc_html__( 'Reason text for cashback earned on specific products or categories.', 'devdiggers-wallet-for-woocommerce' ),
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Topup Cashback', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-credit-reason-topup-cashback',
							'name'        => '_ddwcwm_credit_reason[topup_cashback]',
							'description' => esc_html__( 'Reason text for bonus cashback earned on wallet top-ups.', 'devdiggers-wallet-for-woocommerce' ),
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'First Order Cashback', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-credit-reason-first-order-cashback',
							'name'        => '_ddwcwm_credit_reason[first_order_cashback]',
							'description' => esc_html__( 'Reason text for the one-time first-order cashback bonus.', 'devdiggers-wallet-for-woocommerce' ),
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'User Role Cashback', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-credit-reason-user-role-cashback',
							'name'        => '_ddwcwm_credit_reason[user_role_cashback]',
							'description' => esc_html__( 'Reason text for cashback earned based on the customer user role.', 'devdiggers-wallet-for-woocommerce' ),
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Payment Method Cashback', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-credit-reason-payment-method-cashback',
							'name'        => '_ddwcwm_credit_reason[payment_method_cashback]',
							'description' => esc_html__( 'Reason text for cashback earned by paying with a specific gateway.', 'devdiggers-wallet-for-woocommerce' ),
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Wallet Debited Notification', 'devdiggers-wallet-for-woocommerce' ),
						'description' => esc_html__( 'Automated notification sent to customers when funds are deducted for purchases, transfers, or balance expirations.', 'devdiggers-wallet-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Heading', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Heading', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-email-settings-wallet-debited-heading',
							'name'        => '_ddwcwm_email_settings[wallet_debited][heading]',
							'value'       => isset( $ddwcwm_wallet['email_settings']['wallet_debited']['heading'] ) ? $ddwcwm_wallet['email_settings']['wallet_debited']['heading'] : '',
							'description' => esc_html__( 'The main title that appears at the top of the email when funds are debited.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Subject', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter subject', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-email-settings-wallet-debited-subject',
							'name'        => '_ddwcwm_email_settings[wallet_debited][subject]',
							'value'       => isset( $ddwcwm_wallet['email_settings']['wallet_debited']['subject'] ) ? $ddwcwm_wallet['email_settings']['wallet_debited']['subject'] : '',
							'description' => esc_html__( 'The subject line for the funds debit notification email.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'          => 'editor',
							'label'         => esc_html__( 'Email Message', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder'   => esc_html__( 'Enter Email Message', 'devdiggers-wallet-for-woocommerce' ),
							'id'            => 'ddwcwm-email-settings-wallet-debited-message',
							'name'          => '_ddwcwm_email_settings[wallet_debited][message]',
							'value'         => isset( $ddwcwm_wallet['email_settings']['wallet_debited']['message'] ) ? $ddwcwm_wallet['email_settings']['wallet_debited']['message'] : '',
							'textarea_rows' => 6,
							/* translators: 1: amount, 2: reason, 3: balance, 4: user name, 5: user email, 6: display name, 7: site title. */
							'description'   => sprintf( esc_html__( "The body of the email that informs the customer about the amount debited, along with details like the new balance. \n\n Available Placeholders: %1\$s, %2\$s, %3\$s, %4\$s, %5\$s, %6\$s and %7\$s.", 'devdiggers-wallet-for-woocommerce' ), '<strong>{amount}</strong>', '<strong>{reason}</strong>', '<strong>{balance}</strong>', '<strong>{user_name}</strong>', '<strong>{user_email}</strong>', '<strong>{user_display_name}</strong>', '<strong>{site_title}</strong>' ),
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Debit Reason Labels', 'devdiggers-wallet-for-woocommerce' ),
						/* translators: %s: the {reason} placeholder tag. */
						'description' => sprintf( esc_html__( 'Define the human-readable text for the %s placeholder used in debit notifications. This clarifies the reason for the deduction.', 'devdiggers-wallet-for-woocommerce' ), '<strong>{reason}</strong>' ),
					],
					'fields' => [
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Order Payment', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-debit-reason-order-payment',
							'name'        => '_ddwcwm_debit_reason[order_payment]',
							'value'       => isset( $ddwcwm_wallet['debit_reason']['order_payment'] ) ? $ddwcwm_wallet['debit_reason']['order_payment'] : '',
							'description' => esc_html__( 'Enter the reason text for funds debited for paying an order.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Transfer Sent', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-debit-reason-transfer-sent',
							'name'        => '_ddwcwm_debit_reason[transfer_sent]',
							'value'       => isset( $ddwcwm_wallet['debit_reason']['transfer_sent'] ) ? $ddwcwm_wallet['debit_reason']['transfer_sent'] : '',
							'description' => esc_html__( 'Reason text for funds sent to another user via Send Money.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Cashback Expired', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-debit-reason-cashback-expired',
							'name'        => '_ddwcwm_debit_reason[cashback_expired]',
							'value'       => isset( $ddwcwm_wallet['debit_reason']['cashback_expired'] ) ? $ddwcwm_wallet['debit_reason']['cashback_expired'] : '',
							'description' => esc_html__( 'Reason text for cashback removed when it expires.', 'devdiggers-wallet-for-woocommerce' ),
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Withdrawal', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Reason', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-debit-reason-withdrawal',
							'name'        => '_ddwcwm_debit_reason[withdrawal]',
							'value'       => isset( $ddwcwm_wallet['debit_reason']['withdrawal'] ) ? $ddwcwm_wallet['debit_reason']['withdrawal'] : '',
							'description' => esc_html__( 'Reason text for funds debited for a withdrawal payout.', 'devdiggers-wallet-for-woocommerce' ),
							'field_class'       => [ 'ddfw-upgrade-to-pro-tag-wrapper' ],
							'custom_attributes' => [ 'disabled' => 'disabled' ],
						],
					],
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Manual Adjustment Notification', 'devdiggers-wallet-for-woocommerce' ),
						'description' => esc_html__( 'Notification sent when an administrator manually modifies a user\'s wallet balance.', 'devdiggers-wallet-for-woocommerce' ),
					],
					'fields' => [
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Heading', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter Heading', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-email-settings-manual-adjustment-heading',
							'name'        => '_ddwcwm_email_settings[manual_adjustment][heading]',
							'value'       => isset( $ddwcwm_wallet['email_settings']['manual_adjustment']['heading'] ) ? $ddwcwm_wallet['email_settings']['manual_adjustment']['heading'] : '',
							'description' => esc_html__( 'The title of the email that notifies users of manual adjustments made to their wallet balance by an admin.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'        => 'text',
							'label'       => esc_html__( 'Subject', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder' => esc_html__( 'Enter subject', 'devdiggers-wallet-for-woocommerce' ),
							'id'          => 'ddwcwm-email-settings-manual-adjustment-subject',
							'name'        => '_ddwcwm_email_settings[manual_adjustment][subject]',
							'value'       => isset( $ddwcwm_wallet['email_settings']['manual_adjustment']['subject'] ) ? $ddwcwm_wallet['email_settings']['manual_adjustment']['subject'] : '',
							'description' => esc_html__( 'The subject line of the email notifying users of manual adjustments to their balance.', 'devdiggers-wallet-for-woocommerce' ),
						],
						[
							'type'          => 'editor',
							'label'         => esc_html__( 'Email Message', 'devdiggers-wallet-for-woocommerce' ),
							'placeholder'   => esc_html__( 'Enter Email Message', 'devdiggers-wallet-for-woocommerce' ),
							'id'            => 'ddwcwm-email-settings-manual-adjustment-message',
							'name'          => '_ddwcwm_email_settings[manual_adjustment][message]',
							'value'         => isset( $ddwcwm_wallet['email_settings']['manual_adjustment']['message'] ) ? $ddwcwm_wallet['email_settings']['manual_adjustment']['message'] : '',
							'textarea_rows' => 6,
							/* translators: 1: amount, 2: reason, 3: balance, 4: user name, 5: user email, 6: display name, 7: site title. */
							'description'   => sprintf( esc_html__( "The body content of the email informing users about the manual adjustment of their balance.\n\n Available Placeholders: %1\$s, %2\$s, %3\$s, %4\$s, %5\$s, %6\$s and %7\$s.", 'devdiggers-wallet-for-woocommerce' ), '<strong>{amount}</strong>', '<strong>{reason}</strong>', '<strong>{balance}</strong>', '<strong>{user_name}</strong>', '<strong>{user_email}</strong>', '<strong>{user_display_name}</strong>', '<strong>{site_title}</strong>' ),
						],
					],
				],
			];

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout( $args, 'ddwcwm-emails-configuration-fields' );
		}
	}
}
