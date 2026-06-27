<?php
/**
 * Wallet Gateway.
 *
 * @class DDWCWM_Wallet_Gateway
 * @extends WC_Payment_Gateway
 * @version 1.0.0
 * @package DevDiggers Wallet for WooCommerce
 */

namespace DDWCWalletManagement\Includes;

use DDWCWalletManagement\Helper\Transactions\DDWCWM_Transactions_Helper;
use DDWCWalletManagement\Helper\Users\DDWCWM_Users_Helper;

defined( 'ABSPATH' ) || exit();

class DDWCWM_Wallet_Gateway extends \WC_Payment_Gateway {
	/**
	 * Instructions for the gateway.
	 *
	 * @var string
	 */
	public $instructions;

	/**
	 * Shipping methods this gateway is enabled for.
	 *
	 * @var array
	 */
	public $enable_for_methods;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'ddwcwm_wallet';
		$this->icon               = apply_filters( 'woocommerce_cod_icon', '' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Reusing core WooCommerce COD icon filter.
		$this->method_title       = esc_html__( 'Wallet', 'devdiggers-wallet-for-woocommerce' );
		$this->method_description = esc_html__( 'Have your customers pay with Wallet.', 'devdiggers-wallet-for-woocommerce' );
		$this->has_fields         = false;

		// Load the settings
		$this->init_form_fields();
		$this->init_settings();

		// Get settings
		$this->title              = $this->get_option( 'title' );
		$this->description        = apply_filters( 'ddwcwm_modify_payment_gateway_description', $this->get_option( 'description' ) );
		$this->instructions       = $this->get_option( 'instructions' );
		$this->enable_for_methods = $this->get_option( 'enable_for_methods', [] );
		$this->supports = [
			'products',
			'refunds'
		];

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		add_action( 'woocommerce_thankyou_ddwcwm_wallet', [ $this, 'ddwcwm_add_instructions_in_thankyou_page' ] );

		// Customer Emails
		add_action( 'woocommerce_email_before_order_table', [ $this, 'ddwcwm_add_email_instructions' ], 10, 3 );
	}

	/**
	 * Add current balance in payment gateway in checkout function
	 *
	 * @return void
	 */
	public function get_icon() {
		if ( apply_filters( 'ddwcwm_show_available_balance_in_payment_gateway', true ) ) {
			$user_helper    = new DDWCWM_Users_Helper();
			$wallet_balance = $user_helper->ddwcwm_get_user_wallet_balance();
			$wallet_balance = apply_filters( 'ddwcwm_modify_amount_to_multi_currency', $wallet_balance );
			$wallet_balance = apply_filters( 'ddwcwm_modify_available_wallet_amount_displayed_in_payment_gateway', $wallet_balance );

			/* translators: %s: available wallet balance amount. */
			return apply_filters( 'ddwcwm_modify_available_balance_text_in_payment_gateway', ' (' . sprintf( esc_html__( 'Available Balance: %s', 'devdiggers-wallet-for-woocommerce' ), '<strong>' . wc_price( $wallet_balance ) . '</strong>' ) . ')' );
		}
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 * 
	 * @return void
	 */
	public function init_form_fields() {
		$shipping_methods = [];

		if ( is_admin() ) {
			foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
				$shipping_methods[ $method->id ] = $method->get_method_title();
			}
		}

		$this->form_fields = [
			'enabled' => [
				'title'       => esc_html__( 'Enable/Disable', 'devdiggers-wallet-for-woocommerce' ),
				'label'       => esc_html__( 'Enable Wallet', 'devdiggers-wallet-for-woocommerce' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
			],
			'title' => [
				'title'       => esc_html__( 'Title', 'devdiggers-wallet-for-woocommerce' ),
				'type'        => 'text',
				'description' => esc_html__( 'Payment method title that the customer will see on your checkout.', 'devdiggers-wallet-for-woocommerce' ),
				'default'     => esc_html__( 'Wallet', 'devdiggers-wallet-for-woocommerce' ),
				'desc_tip'    => true,
			],
			'description' => [
				'title'       => esc_html__( 'Description', 'devdiggers-wallet-for-woocommerce' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'Payment method description that the customer will see on your website.', 'devdiggers-wallet-for-woocommerce' ),
				'default'     => esc_html__( 'Pay with Wallet.', 'devdiggers-wallet-for-woocommerce' ),
				'desc_tip'    => true,
			],
			'instructions' => [
				'title'       => esc_html__( 'Instructions', 'devdiggers-wallet-for-woocommerce' ),
				'type'        => 'textarea',
				'description' => esc_html__( 'Instructions that will be added to the thank you page.', 'devdiggers-wallet-for-woocommerce' ),
				'default'     => esc_html__( 'Your order is placed with Wallet, please keep your wallet recharged in order to use its services.', 'devdiggers-wallet-for-woocommerce' ),
				'desc_tip'    => true,
			],
			'enable_for_methods' => [
				'title'             => esc_html__( 'Enable for shipping methods', 'devdiggers-wallet-for-woocommerce' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select',
				'default'           => '',
				'description'       => esc_html__( 'If Wallet is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'devdiggers-wallet-for-woocommerce' ),
				'options'           => $shipping_methods,
				'desc_tip'          => true,
				'custom_attributes' => [
					'data-placeholder' => esc_html__( 'Select shipping methods', 'devdiggers-wallet-for-woocommerce' )
				],
			],
		];
	}

	/**
	 * Check If The Gateway Is Available For Use.
	 *
	 * @return bool
	 */
	public function is_available() {
		$order          = null;
		$needs_shipping = false;

		// Test if shipping is needed first
		if ( WC()->cart && WC()->cart->needs_shipping() ) {
			$needs_shipping = true;
		} elseif ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
			$order_id = absint( get_query_var( 'order-pay' ) );
			$order    = wc_get_order( $order_id );

			// Test if order needs shipping.
			if ( 0 < sizeof( $order->get_items() ) ) {
				foreach ( $order->get_items() as $item ) {
					$_product = $order->get_product_from_item( $item );
					if ( $_product && $_product->needs_shipping() ) {
						$needs_shipping = true;
						break;
					}
				}
			}
		}

		$needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Reusing core WooCommerce cart shipping filter.

		// Check methods
		if ( ! empty( $this->enable_for_methods ) && $needs_shipping ) {

			// Only apply if all packages are being shipped via chosen methods, or order is virtual
			$chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

			if ( isset( $chosen_shipping_methods_session ) ) {
				$chosen_shipping_methods = array_unique( $chosen_shipping_methods_session );
			} else {
				$chosen_shipping_methods = [];
			}

			$check_method = false;

			if ( is_object( $order ) ) {
				if ( $order->shipping_method ) {
					$check_method = $order->shipping_method;
				}

			} elseif ( empty( $chosen_shipping_methods ) || sizeof( $chosen_shipping_methods ) > 1 ) {
				$check_method = false;
			} elseif ( sizeof( $chosen_shipping_methods ) == 1 ) {
				$check_method = $chosen_shipping_methods[ 0 ];
			}

			if ( ! $check_method ) {
				return false;
			}

			$found = false;

			foreach ( $this->enable_for_methods as $method_id ) {
				if ( strpos( $check_method, $method_id ) === 0 ) {
					$found = true;
					break;
				}
			}

			if ( ! $found ) {
				return false;
			}
		}

		return parent::is_available();
	}

	/**
	 * Process Refund function
	 *
	 * @param int $order_id
	 * @param float $amount
	 * @param string $reason
	 * @return void
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$amount         = apply_filters( 'ddwcwm_modify_amount_to_base_currency', $amount );
		$order          = wc_get_order( $order_id );
		$user_id        = $order->get_customer_id();
		$user_helper    = new DDWCWM_Users_Helper();
		$wallet_balance = $user_helper->ddwcwm_get_user_wallet_balance( $user_id );

		$wallet_balance += $amount;

		$user_helper->ddwcwm_set_user_wallet_balance( $wallet_balance, $user_id );

		do_action( 'ddwcwm_after_credit_wallet_amount_on_payment_gateway_refund', $amount, $user_id, $order_id );

		$data = [
			'type'      => 'credit',
			'amount'    => $amount,
			'user_id'   => $user_id,
			'order_id'  => $order_id,
			'note'      => esc_html__( 'Credited on refund for purchase.', 'devdiggers-wallet-for-woocommerce' ),
			'date'      => current_time( 'Y-m-d H:i:s' ),
			'reference' => 'order_refund',
		];

		$transaction_helper = new DDWCWM_Transactions_Helper();

		$transaction_helper->ddwcwm_save_transaction( $data );

		if ( $order->get_remaining_refund_amount() <= 0 ) {
			$order->update_meta_data( '_ddwcwm_order_fully_refund_handled', 'yes' );
			$order->save();
		}

		return true;
	}

	/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order          = wc_get_order( $order_id );
		$order_total    = $order->get_total();
		$order_total    = apply_filters( 'ddwcwm_modify_amount_to_base_currency', $order_total );
		$user_id        = $order->get_customer_id();
		$user_helper    = new DDWCWM_Users_Helper();
		$wallet_balance = $user_helper->ddwcwm_get_user_wallet_balance( $user_id );

		$wallet_balance -= $order_total;

		$user_helper->ddwcwm_set_user_wallet_balance( $wallet_balance, $user_id );

		do_action( 'ddwcwm_after_deduct_wallet_amount_with_payment_gateway', $order_total, $user_id, $order_id );

		$data = [
			'type'      => 'debit',
			'amount'    => $order_total,
			'user_id'   => $user_id,
			'order_id'  => $order_id,
			'note'      => esc_html__( 'Debited on using it on purchase.', 'devdiggers-wallet-for-woocommerce' ),
			'date'      => current_time( 'Y-m-d H:i:s' ),
			'reference' => 'order_payment',
		];

		$transaction_helper = new DDWCWM_Transactions_Helper();

		$transaction_helper->ddwcwm_save_transaction( $data );

		$order->update_meta_data( '_ddwcwm_wallet_amount_used', $order_total );

		$order->save();

		// $order->update_status( apply_filters( 'ddwcwm_process_payment_order_status', $order->has_downloadable_item() ? 'on-hold' : 'processing', $order ), esc_html__( 'Payment done for Shopping.', 'devdiggers-wallet-for-woocommerce' ) );

		$order->payment_complete();

		// Remove cart
		WC()->cart->empty_cart();

		// Return thankyou redirect
		return [
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order )
		];
	}

	/**
	 * Output for the order received page.
	 * 
	 * @return void
	 */
	public function ddwcwm_add_instructions_in_thankyou_page() {
		if ( ! empty( $this->instructions ) ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) );
		}
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @access public
	 * @param WC_Order $order
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 * @return void
	 */
	public function ddwcwm_add_email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		if ( $this->instructions && ! $sent_to_admin && 'ddwcwm_wallet' === $order->get_payment_method() ) {
			echo wp_kses_post( wpautop( wptexturize( $this->instructions ) ) ) . PHP_EOL;
		}
	}
}
