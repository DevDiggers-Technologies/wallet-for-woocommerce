<?php
/**
 * Wallet Gateway Blocks Support class
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes\Blocks;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use DDWCWalletManagement\Helper\Users\DDWCWM_Users_Helper;

defined( 'ABSPATH' ) || exit();

/**
 * Wallet Gateway Blocks Support
 */
final class DDWCWM_Wallet_Gateway_Blocks_Support extends AbstractPaymentMethodType {

	/**
	 * Payment method name
	 *
	 * @var string
	 */
	protected $name = 'ddwcwm_wallet';

	/**
	 * Initialize the payment method type
	 *
	 * @return void
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_ddwcwm_wallet_settings', [] );
	}

	/**
	 * Check if the payment method is active
	 *
	 * @return bool
	 */
	public function is_active() {
		return true;
	}

	/**
	 * Get the integration script handles
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		wp_register_script(
			'ddwcwm-blocks-integration',
			DDWCWM_PLUGIN_URL . 'assets/js/blocks.js',
			[
				'wc-blocks-registry',
				'wc-blocks-checkout',
				'wc-settings',
				'wp-element',
				'wp-html-entities',
				'wp-i18n',
				'wp-components',
			],
			filemtime( DDWCWM_PLUGIN_FILE . 'assets/js/blocks.js' ),
			true
		);

		wp_register_style(
			'ddwcwm-blocks-integration',
			DDWCWM_PLUGIN_URL . 'assets/css/blocks.css',
			[],
			filemtime( DDWCWM_PLUGIN_FILE . 'assets/css/blocks.css' )
		);

		return [ 'ddwcwm-blocks-integration' ];
	}

	/**
	 * Get the integration style handles
	 *
	 * @return array
	 */
	public function get_payment_method_style_handles() {
		return [ 'ddwcwm-blocks-integration' ];
	}

	/**
	 * Get the integration data
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		global $ddwcwm_wallet;
		$user_helper    = new DDWCWM_Users_Helper();
		$wallet_balance = $user_helper->ddwcwm_get_user_wallet_balance();

		$cart_total = 0;
		if ( ! is_null( WC()->cart ) ) {
			$cart_total = WC()->cart->get_total( 'edit' );
		}

		$is_topup_in_cart = \DDWCWalletManagement\Includes\Common\DDWCWM_Common_Functions::ddwcwm_is_wallet_topup_pro_in_cart();

		// Calculate canMakePayment in PHP following the wallet gateway pattern.
		$can_make_payment = ( get_current_user_id() > 0 ) && 
							! $is_topup_in_cart && 
							$wallet_balance >= $cart_total;

		return [
			'title'                   => $this->get_setting( 'title', esc_html__( 'Wallet', 'devdiggers-wallet-for-woocommerce' ) ),
			'description'             => $this->get_setting( 'description', esc_html__( 'Pay with Wallet.', 'devdiggers-wallet-for-woocommerce' ) ),
			/* translators: %s: available wallet balance amount. */
			'available_balance_text'   => sprintf( esc_html__( '(Available Balance: %s)', 'devdiggers-wallet-for-woocommerce' ), wp_strip_all_tags( wc_price( $wallet_balance ) ) ),
			'wallet_balance'          => $wallet_balance,
			'max_debit_limit'         => 0,
			'min_debit_limit'         => 0,
			'total_cashback'          => array_sum( ( new \DDWCWalletManagement\Helper\Rules\DDWCWM_Rules_Helper() )->ddwcwm_calculate_cashbacks_with_cart() ),
			'ajax_url'                => admin_url( 'admin-ajax.php' ),
			'nonce'                   => wp_create_nonce( 'ddwcwm-nonce' ),
			'cashback_message'        => ! empty( $ddwcwm_wallet['ddwcwm_wallet_cashback_message'] ) ? $ddwcwm_wallet['ddwcwm_wallet_cashback_message'] : '',
			'supports'                => [ 'products', 'refunds' ],
			'canMakePayment'          => $can_make_payment,
			'currency_symbol'         => get_woocommerce_currency_symbol(),
			'decimal_separator'       => wc_get_price_decimal_separator(),
			'thousand_separator'      => wc_get_price_thousand_separator(),
			'decimals'                => wc_get_price_decimals(),
		];
	}

	/**
	 * Get setting value
	 *
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	protected function get_setting( $key, $default = '' ) {
		return ! empty( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
	}
}
