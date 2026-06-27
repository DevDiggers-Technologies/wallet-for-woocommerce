<?php
/**
 * Front functions
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */


namespace DDWCWalletManagement\Includes\Front;

use DDWCWalletManagement\Helper\Rules\DDWCWM_Rules_Helper;
use DDWCWalletManagement\Helper\Transactions\DDWCWM_Transactions_Helper;
use DDWCWalletManagement\Helper\Users\DDWCWM_Users_Helper;
use DDWCWalletManagement\Templates\Front;
use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Front_Functions' ) ) {
	/**
	 * Front functions class
	 */
	class DDWCWM_Front_Functions {

		/**
		 * Add Wallet Menu in my account page function
		 *
		 * @param array $items
		 * @return array
		 */
		public function ddwcwm_add_woocommerce_menu( $items ) {
			global $ddwcwm_wallet;

			$customer_logout_endpoint = get_option( 'woocommerce_logout_endpoint', 'customer-logout' );

			// Remove the logout menu item.
			if ( ! empty( $items[ $customer_logout_endpoint ] ) ) {
				$logout = $items[ $customer_logout_endpoint ];
				unset( $items[ $customer_logout_endpoint ] );
			}

			if ( apply_filters( 'ddwcwm_display_myaccounts_menu', true ) ) {
				// Insert your custom endpoint.
				$items[ $ddwcwm_wallet[ 'my_account_endpoint' ] ] = $ddwcwm_wallet[ 'my_account_endpoint_title' ];
			}

			// Insert back the logout item.
			if ( ! empty( $logout ) ) {
				$items[ $customer_logout_endpoint ] = $logout;
			}

			return $items;
		}

		/**
		 * Add Query Vars function
		 *
		 * @param array $vars
		 * @return array
		 */
		public function ddwcwm_add_query_vars( $vars ) {
			global $ddwcwm_wallet;
			$vars[] = $ddwcwm_wallet[ 'my_account_endpoint' ];
            return $vars;
		}

		/**
		 * Add Wallet Content on my account page function
		 *
		 * @return void
		 */
		public function ddwcwm_add_wallet_content_on_my_account_page() {
			new Front\MyAccount\DDWCWM_My_Wallet_Template();
		}

		/**
		 * Remove Sidebar from wallet menu function
		 *
		 * @param array $sidebars_widgets
		 * @return array
		 */
		public function ddwcwm_remove_sidebar_from_wallet_page( $sidebars_widgets ) {
            global $wp_query, $ddwcwm_wallet;
			return isset( $wp_query->query_vars[ $ddwcwm_wallet[ 'my_account_endpoint' ] ] ) && empty( $ddwcwm_wallet[ 'enable_widgets_my_account_endpoint' ] ) && is_account_page() ? [ false ] : $sidebars_widgets;
		}

		/**
		 * Register front scripts function
		 *
		 * @return void
		 */
		public function ddwcwm_register_front_scripts() {
			global $ddwcwm_wallet;
			wp_register_style( 'ddwcwm-front-style', DDWCWM_PLUGIN_URL . 'assets/css/front.css', [], filemtime( DDWCWM_PLUGIN_FILE . 'assets/css/front.css' ) );
			wp_register_script( 'ddwcwm-front-script', DDWCWM_PLUGIN_URL . 'assets/js/front.js', [], filemtime( DDWCWM_PLUGIN_FILE . 'assets/js/front.js' ), true );

			wp_localize_script( 'ddwcwm-front-script', 'ddwcwmFrontObj', [
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'ajaxNonce'     => wp_create_nonce( 'ddwcwm-nonce' ),
				'ddwcwm_wallet' => $ddwcwm_wallet,
				'i18n'          => [
					'enterOtp'          => esc_html__( 'Enter OTP first!!', 'devdiggers-wallet-for-woocommerce' ),
					'demoOtp'           => esc_html__( 'Demo OTP', 'devdiggers-wallet-for-woocommerce' ),
					'confirmPayRequest' => esc_html__( 'Are you sure you want to pay this request?', 'devdiggers-wallet-for-woocommerce' ),
					'successIcon'       => DDFW_SVG::get_svg_icon( 'checkmark-circle', true ),
					'errorIcon'         => DDFW_SVG::get_svg_icon( 'basic-info', true ), // Using info icon for now
				],
			] );

			// Add dynamic layout CSS. Each value is a user-configured colour, so it is
			// run through a CSS-colour sanitizer before being interpolated and output.
			$css_vars = [
				'--ddwcwm-theme-color'      => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['theme_color'] ),
				'--ddwcwm-success-text'     => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['success_message_text_color'] ),
				'--ddwcwm-success-bg'       => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['success_message_background_color'] ),
				'--ddwcwm-error-text'       => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['error_message_text_color'] ),
				'--ddwcwm-error-bg'         => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['error_message_background_color'] ),
				'--ddwcwm-info-text'        => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['info_message_text_color'] ),
				'--ddwcwm-info-bg'          => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['info_message_background_color'] ),
				'--ddwcwm-card-bg'          => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['details_card_background_color'] ),
				'--ddwcwm-card-border'      => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['details_card_border_color'] ),
				'--ddwcwm-card-text'        => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['details_card_text_color'] ),
				'--ddwcwm-card-value'       => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['details_card_value_color'] ),
				'--ddwcwm-icon-color'       => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['details_icon_color'] ),
				'--ddwcwm-icon-wrapper-bg'  => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['details_icon_wrapper_background_color'] ),
				'--ddwcwm-table-header-bg'  => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['layout_table_header_background_color'] ),
				'--ddwcwm-table-header-text' => $this->ddwcwm_sanitize_css_color( $ddwcwm_wallet['layout_table_header_text_color'] ),
			];

			$custom_css = ':root {';
			foreach ( $css_vars as $var => $value ) {
				if ( '' !== $value ) {
					$custom_css .= esc_attr( $var ) . ':' . esc_attr( $value ) . ';';
				}
			}
			$custom_css .= '}';

			wp_add_inline_style( 'ddwcwm-front-style', $custom_css );
		}

		/**
		 * Enqueue front scripts function
		 *
		 * @return void
		 */
		public function ddwcwm_enqueue_front_scripts() {
			wp_enqueue_style( 'ddwcwm-front-style' );
			wp_enqueue_script( 'ddwcwm-front-script' );
		}

		/**
		 * Display notice if dues product exists in cart function
		 *
		 * @return void
		 */
		public function ddwcwm_display_notice_if_wallet_topup_added_in_cart() {
			$wallet_topup_pro = get_page_by_path( 'ddwcwm-wallet-topup' , OBJECT, 'product' );

			if ( is_shop() || is_single() ) {
				$cart_contents = WC()->cart->cart_contents;

				if ( ! empty( $cart_contents ) ) {
					foreach( $cart_contents as $key => $cart_content ) {
						if ( $cart_content[ 'product_id' ] == $wallet_topup_pro->ID ) {
							wc_add_notice( esc_html__( 'Cannot add new products now. Either empty cart or process wallet topup first.', 'devdiggers-wallet-for-woocommerce' ), 'notice' );
						}
					}
				}
			}
		}

		/**
         * This function handles checkout payment gateways.
         *
         * @param array $available_gateways All available gateways.
		 * @return array
         */
		public function ddwcwm_remove_payment_gateway( $available_gateways ) {
			global $ddwcwm_wallet, $wp;

			$wallet_topup_pro = get_page_by_path( 'ddwcwm-wallet-topup' , OBJECT, 'product' );
			$user_helper      = new DDWCWM_Users_Helper();
			$wallet_balance   = $user_helper->ddwcwm_get_user_wallet_balance();
			$cart_total       = 0;

			if ( ! empty( $wp->query_vars[ 'order-pay' ] ) ) {
				$order_id    = absint( $wp->query_vars[ 'order-pay' ] );
				$order       = wc_get_order( $order_id );
				$cart_total  = $order->get_total();
				$order_items = $order->get_items();

				foreach ( $order_items as $key => $order_item ) {
					if ( $order_item->get_data()[ 'product_id' ] == $wallet_topup_pro->ID ) {
						foreach ( $available_gateways as $payment_gateway_id => $available_gateway ) {
							if ( ! empty( $ddwcwm_wallet[ 'enabled_payment_gateways' ] ) && ! in_array( $payment_gateway_id, $ddwcwm_wallet[ 'enabled_payment_gateways' ] ) ) {
								unset( $available_gateways[ $payment_gateway_id ] );
							}
						}
						unset( $available_gateways[ 'ddwcwm_wallet' ] );
						break;
					}
				}
			} else {
				if ( ! empty( WC()->cart ) ) {
					$cart_contents = WC()->cart->cart_contents;
					$cart_total    = WC()->cart->total;

					if ( ! empty( $cart_contents ) ) {
						foreach( $cart_contents as $key => $cart_content ) {
							if ( $cart_content[ 'product_id' ] == $wallet_topup_pro->ID ) {
								foreach ( $available_gateways as $payment_gateway_id => $available_gateway ) {
									if ( ! empty( $ddwcwm_wallet[ 'enabled_payment_gateways' ] ) && ! in_array( $payment_gateway_id, $ddwcwm_wallet[ 'enabled_payment_gateways' ] ) ) {
										unset( $available_gateways[ $payment_gateway_id ] );
									}
								}
								unset( $available_gateways[ 'ddwcwm_wallet' ] );
								break;
							}
						}
					}
				}
			}

			$cart_total = apply_filters( 'ddwcwm_modify_amount_to_base_currency', $cart_total );
			$cart_total = apply_filters( 'ddwcwm_modify_cart_total_amount_for_removing_wallet_payment_gateway', $cart_total );

			if ( apply_filters( 'ddwcwm_add_custom_conditions_to_remove_wallet_payment_gateway', true, $available_gateways ) && ( $wallet_balance <= 0 || ( ! empty( WC()->session ) && ! empty( WC()->session->get( 'ddwcwm_wallet_amount' ) ) ) || $wallet_balance < $cart_total ) ) {
				unset( $available_gateways[ 'ddwcwm_wallet' ] );
			}

            return $available_gateways;
		}

		/**
		 * Remove used wallet amount from user meta function
		 *
		 * @param int $order_id
		 * @return void
		 */
		public function ddwcwm_deduct_wallet_amount_on_order_processed( $order_id ) {
			global $ddwcwm_wallet;

			$order = wc_get_order( $order_id );

			// Partial wallet payment deduction (session based) is a Pro feature and is not
			// applied in Free. Full wallet payments are deducted by the wallet gateway
			// ( DDWCWM_Wallet_Gateway::process_payment ). Cashback and topup handling below
			// still run for every order.

			$rules_helper = new DDWCWM_Rules_Helper();

			$awarded_cashback_data = $rules_helper->ddwcwm_calculate_cashbacks_with_order( $order_id );

			if ( ! empty( $awarded_cashback_data ) ) {
				$order->update_meta_data( '_ddwcwm_awarded_cashback_data', $awarded_cashback_data );
			}

			$order_items      = $order->get_items();
			$wallet_topup_pro = get_page_by_path( 'ddwcwm-wallet-topup' , OBJECT, 'product' );

			foreach ( $order_items as $key => $order_item ) {
				if ( $order_item->get_data()[ 'product_id' ] == $wallet_topup_pro->ID && $ddwcwm_wallet[ 'topup_order_status' ] === 'completed' ) {
					$order->update_status( 'completed' );
				}
			}

			$order->save();
		}

		/**
		 * Deduct wallet amount on Store API (Blocks) checkout order processed
		 *
		 * The Store API hook passes the WC_Order object directly,
		 * so we extract the order ID and delegate to the existing method.
		 *
		 * @param \WC_Order $order
		 * @return void
		 */
		public function ddwcwm_deduct_wallet_amount_on_store_api_order_processed( $order ) {
			$this->ddwcwm_deduct_wallet_amount_on_order_processed( $order->get_id() );
		}

		/**
		 * WooCommerce before calculate totals function
		 *
		 * @return void
		 */
		public function ddwcwm_woocommerce_before_calculate_totals() {
			$cart_contents = WC()->cart->get_cart_contents();
			$topup_amount  = WC()->session->get( 'ddwcwm_wallet_topup_amount' );

			if ( ! empty( $cart_contents ) && ! empty( $topup_amount ) && apply_filters( 'ddwcwm_do_normal_topup', true ) ) {
				foreach ( $cart_contents as $key => $cart_content ) {
					$id               = ! empty( $cart_content[ 'variation_id' ] ) ? $cart_content[ 'variation_id' ] : $cart_content[ 'product_id' ];
					$wallet_topup_pro = get_page_by_path( 'ddwcwm-wallet-topup' , OBJECT, 'product' );

					if ( $wallet_topup_pro->ID == $id ) {
						$cart_content[ 'data' ]->set_price( $topup_amount );
					}
				}
			}
		}

		/**
		 * Handle wallet topup function
		 *
		 * @return void
		 */
		public function ddwcwm_handle_wallet_topup() {
			global $ddwcwm_wallet;

			if ( isset( $_POST[ 'ddwcwm_wallet_topup' ] ) && ! empty( $_POST[ 'ddwcwm_wallet_topup_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'ddwcwm_wallet_topup_nonce' ] ) ), 'ddwcwm_wallet_topup_nonce_action' ) ) {
				$topup_amount = ! empty( $_POST[ 'ddwcwm_wallet_topup_amount' ] ) ? floatval( $_POST[ 'ddwcwm_wallet_topup_amount' ] ) : 0;

				if ( ! empty( $topup_amount ) ) {
					$topup_amount = apply_filters( 'ddwcwm_modify_amount_to_base_currency', $topup_amount );

					if ( apply_filters( 'ddwcwm_do_normal_topup', true ) ) {
						if ( ! empty( WC()->session ) && is_object( WC()->session ) ) {
							WC()->session->set( 'ddwcwm_wallet_topup_amount', $topup_amount );
						}
						$wallet_topup_pro = get_page_by_path( 'ddwcwm-wallet-topup', OBJECT, 'product' );
						WC()->cart->empty_cart();
						WC()->cart->add_to_cart( $wallet_topup_pro->ID );
					} else {
						do_action( 'ddwcwm_add_other_topup_functionality', $topup_amount );
					}

					$redirection_url = ! empty( $ddwcwm_wallet['redirect_to_checkout_on_topup'] ) ? wc_get_checkout_url() : wc_get_cart_url();

					wp_safe_redirect( apply_filters( 'ddwcwm_modify_topup_redirection_url', $redirection_url ) );
					exit();
				} else {
					wc_add_notice( esc_html__( 'Enter amount to topup', 'devdiggers-wallet-for-woocommerce' ), 'error' );
					wp_safe_redirect( wp_get_referer() ? wp_get_referer() : wc_get_cart_url() );
					exit();
				}
			}
		}

		/**
		 * Add Wallet balance shortcode content function
		 * 
		 * @return void
		 */
		public function ddwcwm_add_wallet_balance_shortcode_content() {
			$this->ddwcwm_enqueue_front_scripts();
			ob_start();
			require DDWCWM_PLUGIN_FILE . 'templates/shortcodes/wallet-balance.php';
			return ob_get_clean();
		}

		/**
		 * Add Wallet balance layout shortcode content function
		 * 
		 * @return void
		 */
		public function ddwcwm_add_wallet_balance_layout_shortcode_content() {
			$this->ddwcwm_enqueue_front_scripts();
			ob_start();
			require DDWCWM_PLUGIN_FILE . 'templates/shortcodes/wallet-balance-layout.php';
			return ob_get_clean();
		}

		/**
		 * Add Wallet operations shortcode content function
		 * 
		 * @return void
		 */
		public function ddwcwm_add_wallet_operations_shortcode_content() {
			$this->ddwcwm_enqueue_front_scripts();
			ob_start();
			require DDWCWM_PLUGIN_FILE . 'templates/shortcodes/wallet-operations.php';
			return ob_get_clean();
		}

		/**
		 * Add Wallet balance & operations shortcode content function
		 * 
		 * @return void
		 */
		public function ddwcwm_add_wallet_balance_operations_shortcode_content() {
			global $ddwcwm_wallet;
			ob_start();
			?>
			<div class="ddwcwm-wallet-wrapper">
				<?php
                echo do_shortcode( $ddwcwm_wallet[ 'wallet_balance_shortcode' ] );
                echo do_shortcode( $ddwcwm_wallet[ 'wallet_operations_shortcode' ] );
                ?>
			</div>
			<?php
			return ob_get_clean();
		}

		/**
		 * Add Wallet transactions shortcode content function
		 * 
		 * @return void
		 */
		public function ddwcwm_add_wallet_transactions_shortcode_content() {
			$this->ddwcwm_enqueue_front_scripts();
			ob_start();
			require DDWCWM_PLUGIN_FILE . 'templates/shortcodes/wallet-transactions.php';
			return ob_get_clean();
		}

		/**
		 * Sanitize a user-configured CSS colour value before it is interpolated into
		 * inline CSS. Accepts hex, rgb(a) and hsl(a); falls back to an empty string.
		 *
		 * @param string $value Raw colour value.
		 * @return string
		 */
		public function ddwcwm_sanitize_css_color( $value ) {
			$value = trim( (string) $value );
			if ( '' === $value ) {
				return '';
			}

			$hex = sanitize_hex_color( $value );
			if ( ! empty( $hex ) ) {
				return $hex;
			}

			// Allow rgb()/rgba()/hsl()/hsla() and named colours, stripping anything
			// that could break out of the CSS value context.
			if ( preg_match( '/^(?:rgba?|hsla?)\([0-9.,%\s]+\)$/i', $value ) || preg_match( '/^[a-z]+$/i', $value ) ) {
				return $value;
			}

			return '';
		}

		/**
		 * Display cashback message on cart page function
		 *
		 * @return void
		 */
		public function ddwcwm_display_cashback_message_on_cart_page() {
			global $ddwcwm_wallet;
			$message = $ddwcwm_wallet['cashback_cart_page_message'];
			if ( empty( $message ) ) {
				return;
			}

			$rules_helper   = new DDWCWM_Rules_Helper();
			$cashbacks      = $rules_helper->ddwcwm_calculate_cashbacks_with_cart();
			$total_cashback = array_sum( $cashbacks );

			if ( $total_cashback <= 0 ) {
				return;
			}

			$style = $this->ddwcwm_get_cashback_message_style();

			?>
			<div class="ddwcwm-cashback-info-message" style="<?php echo esc_attr( $style ); ?>"><?php echo wp_kses_post( str_replace( '{total_cashback}', wc_price( $total_cashback ), esc_html( $message ) ) ); ?></div>
			<?php
		}

		/**
		 * Display cashback message on checkout page function
		 *
		 * @return void
		 */
		public function ddwcwm_display_cashback_message_on_checkout_page() {
			global $ddwcwm_wallet;
			$message = $ddwcwm_wallet['cashback_checkout_page_message'];
			if ( empty( $message ) ) {
				return;
			}

			$rules_helper = new DDWCWM_Rules_Helper();
			$cashbacks    = $rules_helper->ddwcwm_calculate_cashbacks_with_cart();
			$total_cashback = array_sum( $cashbacks );

			if ( $total_cashback <= 0 ) {
				return;
			}

			$style = $this->ddwcwm_get_cashback_message_style();
			?>
			<div class="ddwcwm-cashback-info-message" style="<?php echo esc_attr( $style ); ?>"><?php echo wp_kses_post( str_replace( '{total_cashback}', wc_price( $total_cashback ), esc_html( $message ) ) ); ?></div>
			<?php
		}

		/**
		 * Display cashback message on view order page function
		 *
		 * @param object $order
		 * @return void
		 */
		public function ddwcwm_display_cashback_message_on_view_order_page( $order ) {
			global $ddwcwm_wallet;
			$message = $ddwcwm_wallet['cashback_view_order_page_message'];
			if ( empty( $message ) ) {
				return;
			}

			$awarded_cashback_data = $order->get_meta( '_ddwcwm_awarded_cashback_data', true );
			if ( empty( $awarded_cashback_data ) ) {
				return;
			}

			$total_cashback = array_sum( $awarded_cashback_data );
			if ( $total_cashback <= 0 ) {
				return;
			}

			$style = $this->ddwcwm_get_cashback_message_style();
			?>
			<div class="ddwcwm-cashback-info-message" style="<?php echo esc_attr( $style ); ?>">
				<?php echo wp_kses_post( str_replace( '{total_cashback}', wc_price( $total_cashback ), esc_html( $message ) ) ); ?>
			</div>
			<?php
		}

		/**
		 * Display cashback message on order received page function
		 *
		 * @param int $order_id
		 * @return void
		 */
		public function ddwcwm_display_cashback_message_on_order_received_page( $order_id ) {
			if ( ! $order_id ) {
				return;
			}

			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}

			global $ddwcwm_wallet;
			$message = $ddwcwm_wallet['cashback_order_received_page_message'];
			if ( empty( $message ) ) {
				return;
			}

			$awarded_cashback_data = $order->get_meta( '_ddwcwm_awarded_cashback_data', true );
			if ( empty( $awarded_cashback_data ) ) {
				return;
			}

			$total_cashback = array_sum( $awarded_cashback_data );
			if ( $total_cashback <= 0 ) {
				return;
			}

			$style = $this->ddwcwm_get_cashback_message_style();
			?>
			<div class="ddwcwm-cashback-info-message" style="<?php echo esc_attr( $style ); ?>"><?php echo wp_kses_post( str_replace( '{total_cashback}', wc_price( $total_cashback ), esc_html( $message ) ) ); ?></div>
			<?php
		}

		/**
		 * Get cashback message style function
		 *
		 * @return string
		 */
		public function ddwcwm_get_cashback_message_style() {
			global $ddwcwm_wallet;
			$styles = [
				'padding'          => '12px 22px',
				'border-radius'    => '6px',
				'font-weight'      => '500',
				'margin'           => '10px 0',
				// 'display'          => 'inline-block',
				'border'           => '1px solid transparent',
				'background-color' => $ddwcwm_wallet['cashback_message_bg_color'],
				'color'            => $ddwcwm_wallet['cashback_message_text_color'],
				'border-color'     => $ddwcwm_wallet['cashback_message_border_color'],
				'font-size'        => $ddwcwm_wallet['cashback_message_font_size'] . 'px',
			];
			$style_str = '';
			foreach ( $styles as $key => $value ) {
				$style_str .= "{$key}: {$value}; ";
			}
			return $style_str;
		}
	}
}
