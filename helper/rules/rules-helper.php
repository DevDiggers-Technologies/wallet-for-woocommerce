<?php
/**
 * Rules helper
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Helper\Rules;

use DDWCWalletManagement\Helper\Error\DDWCWM_Error_Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'DDWCWM_Rules_Helper' ) ) {
	/**
	 * Rules helper class
	 */
	class DDWCWM_Rules_Helper {

		/**
		 * Error trait
		 * 
		 * @var object
		 */
		use DDWCWM_Error_Helper;

		/**
		 * Database Object
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Rules table Variable
		 *
		 * @var string
		 */
        protected $rules_table;

		/**
		 * Postmeta table Variable
		 *
		 * @var string
		 */
        protected $postmeta_table;

		/**
		 * Termmeta table Variable
		 *
		 * @var string
		 */
        protected $termmeta_table;

		/**
		 * Construct
		 */
		public function __construct() {
			global $wpdb;
            $this->wpdb           = $wpdb;
            $this->rules_table    = $this->wpdb->prefix . 'ddwcwm_cashback_rules';
            $this->postmeta_table = $this->wpdb->prefix . 'postmeta';
            $this->termmeta_table = $this->wpdb->prefix . 'termmeta';
		}

        /**
		 * Prepare cashback rule data and save to DB function.
		 *
		 * Free supports cart-total cashback only, so only the general (cart) rule
		 * set is processed here.
		 *
		 * @param array $data               Submitted POST data.
		 * @param array $old_cashback_rules Existing cart cashback rules.
		 * @return bool
		 */
		public function ddwcwm_prepare_cashback_rule_data_and_save( $data, $old_cashback_rules ) {
			$error = 0;

			// Cart cashback rule args. Free supports cart-total cashback only, so there is
			// no rule "basis" dimension; every rule applies to the cart subtotal.
            $amount_from     = ! empty( $data[ 'ddwcwm_amount_from' ] ) ? array_map( 'sanitize_text_field', wp_unslash( $data[ 'ddwcwm_amount_from' ] ) ) : [];
            $amount_to       = ! empty( $data[ 'ddwcwm_amount_to' ] ) ? array_map( 'sanitize_text_field', wp_unslash( $data[ 'ddwcwm_amount_to' ] ) ) : [];
            $cashback_type   = ! empty( $data[ 'ddwcwm_cashback_type' ] ) ? array_map( 'sanitize_text_field', wp_unslash( $data[ 'ddwcwm_cashback_type' ] ) ) : [];
            $cashback_amount = ! empty( $data[ 'ddwcwm_cashback_amount' ] ) ? array_map( 'sanitize_text_field', wp_unslash( $data[ 'ddwcwm_cashback_amount' ] ) ) : [];
            $rule_status     = ! empty( $data[ 'ddwcwm_rule_status' ] ) ? array_map( 'sanitize_text_field', wp_unslash( $data[ 'ddwcwm_rule_status' ] ) ) : [];

			if ( ! empty( $amount_from ) && ! empty( $amount_to ) && ! empty( $cashback_type ) && ! empty( $cashback_amount ) && ! empty( $rule_status ) ) {
				$number_of_cashback_rules = count( $amount_from );

				if ( ( count( $amount_from ) + count( $amount_to ) + count( $cashback_type ) + count( $cashback_amount ) + count( $rule_status ) ) === ( $number_of_cashback_rules * 5 ) ) {
					foreach ( $amount_from as $amount_from_id => $amount_from_value ) {
						if ( $amount_from[ $amount_from_id ] > $amount_to[ $amount_from_id ] ) {
							$this->error_helper->set_error_code( 1 );
							$error = 1;
							break;
						}

						if ( ! is_numeric( $cashback_amount[ $amount_from_id ] ) ) {
							$error = 1;
							break;
						}

						foreach ( $amount_from as $key => $amount_from_val ) {
							if ( $amount_from_id === $key ) {
								continue;
							}

							if ( $amount_from_value == $amount_from[ $key ] ) {
								$error = 1;
								break 2;
							}

							if ( $amount_from[ $amount_from_id ] > $amount_to[ $amount_from_id ] ) {
								$error = 1;
								break 2;
							}

							if ( $amount_from[ $key ] <= $amount_from[ $amount_from_id ] && $amount_from[ $amount_from_id ] <= $amount_to[ $key ] ) {
								$error = 1;
								break 2;
							}

							if ( $amount_from[ $key ] <= $amount_to[ $amount_from_id ] && $amount_to[ $amount_from_id ] <= $amount_to[ $key ] ) {
								$error = 1;
								break 2;
							}
						}
					}
				} else {
					$error = 1;
				}

				if ( empty( $error ) ) {
					$old_ids = wp_list_pluck( $old_cashback_rules, 'id' );

					if( ! empty( $old_cashback_rules ) ) {
						foreach ( $old_cashback_rules as $key => $old_cashback_rule ) {
							if ( ! array_key_exists( $old_cashback_rule[ 'id' ], $amount_from ) ) {
								$this->ddwcwm_delete_cashback_rule( $old_cashback_rule[ 'id' ] );
							}
						}
					}

					foreach ( $amount_from as $cashback_id => $amount_from_value ) {
						$cashback_rule_data = [
							'amount_from'     => $amount_from[ $cashback_id ],
							'amount_to'       => $amount_to[ $cashback_id ],
							'cashback_type'   => $cashback_type[ $cashback_id ],
							'cashback_amount' => $cashback_amount[ $cashback_id ],
							'status'          => $rule_status[ $cashback_id ],
						];

						$real_id = in_array( $cashback_id, $old_ids ) ? $cashback_id : 0;
						$this->ddwcwm_save_cashback_rule( $cashback_rule_data, $real_id );
					}
				} else {
					$this->ddwcwm_print_notification( esc_html__( 'General cashback rules having empty or invalid fields.', 'devdiggers-wallet-for-woocommerce' ), 'error' );
				}
			} else {
				if ( ! empty( $old_cashback_rules ) ) {
					foreach ( $old_cashback_rules as $key => $old_cashback_rule ) {
						if ( ! array_key_exists( $old_cashback_rule[ 'id' ], $amount_from ) ) {
							$this->ddwcwm_delete_cashback_rule( $old_cashback_rule[ 'id' ] );
						}
					}
				}
			}

			// Product, category, user-role and payment-method cashback rules are Pro-only
			// features and are intentionally not processed in Free.

			if ( empty( $error ) ) {
				$this->ddwcwm_print_notification( esc_html__( 'Cashback Rules has been saved successfully.', 'devdiggers-wallet-for-woocommerce' ), 'success' );

				return true;
			}

			return false;
		}

		/**
		 * Save cashback rule to DB function
		 *
		 * @param array $rules_data
		 * @param array $rule_id
		 * @return int
		 */
		public function ddwcwm_save_cashback_rule( $rules_data, $rule_id ) {
            if ( ! empty( $rule_id ) ) {
				$this->wpdb->update(
					$this->rules_table,
					$rules_data,
					[ 'id' => $rule_id ]
				);
			} else {
				$this->wpdb->insert(
					$this->rules_table,
					$rules_data
				);

				$rule_id = $this->wpdb->insert_id;
			}

            return $rule_id;
		}

		/**
		 * Get All Cashback Rules function
		 *
		 * @return array
		 */
		public function ddwcwm_get_all_cashback_rules() {
			global $wpdb;

			$rules = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i", $this->rules_table ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

			return apply_filters( 'ddwcwm_modify_rules_data', $rules );
		}

		/**
		 * Delete Cashback Rule function
		 * 
		 * @param int $rule_id
		 * @return int|bool
		 */
		public function ddwcwm_delete_cashback_rule( $rule_id ) {

			return $this->wpdb->delete(
				$this->rules_table,
				[
					'id' => $rule_id
				],
                [ '%d' ]
			);

		}

		/**
		 * Calculate Cashbacks with cart function
		 *
		 * @return array
		 */
		public function ddwcwm_calculate_cashbacks_with_cart() {
			$wallet_topup_pro = get_page_by_path( 'ddwcwm-wallet-topup' , OBJECT, 'product' );

			// Free supports cart-total cashback only.
			$cashbacks = [ 'cart' => 0 ];

			global $wpdb, $ddwcwm_wallet;

			$cart_subtotal = apply_filters( 'ddwcwm_modify_amount_to_base_currency', floatval( ! empty( WC()->cart ) ? WC()->cart->get_subtotal() : 0 ) );
			$exclude_sale  = ( ! empty( $ddwcwm_wallet['cashback_exclude_sale_products'] ) && $ddwcwm_wallet['cashback_exclude_sale_products'] === 'on' );

			$min_order_value = ! empty( $ddwcwm_wallet['cashback_min_order_value'] ) ? $ddwcwm_wallet['cashback_min_order_value'] : 0;
			if ( ! empty( $min_order_value ) && $cart_subtotal < $min_order_value ) {
				return $cashbacks;
			}

			$cart_contents     = ! empty( WC()->cart ) ? WC()->cart->get_cart_contents() : [];
			$wallet_pro_exists = false;

			if ( ! empty( $cart_contents ) ) {
				foreach ( $cart_contents as $key => $cart_content ) {
					$product_id = ! empty( $cart_content[ 'variation_id' ] ) ? $cart_content[ 'variation_id' ] : $cart_content[ 'product_id' ];
					$product    = wc_get_product( $product_id );

					if ( $exclude_sale && $product && $product->is_on_sale() ) {
						$cart_subtotal -= $cart_content[ 'line_total' ];
						continue;
					}

					if ( $wallet_topup_pro && $product_id == $wallet_topup_pro->ID ) {
						$wallet_pro_exists = true;
					}
				}

				// General Cart Rule (the only cashback type available in Free).
				if ( ! $wallet_pro_exists ) {
					$cashback_data = $wpdb->get_row( $wpdb->prepare( "SELECT cashback_amount, cashback_type FROM %i WHERE status=%s AND %f >= amount_from AND amount_to >= %f", $this->rules_table, 'enabled', $cart_subtotal, $cart_subtotal ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

					if ( ! empty( $cashback_data ) ) {
						if ( $cashback_data[ 'cashback_type' ] == 'fixed' ) {
							$cashbacks[ 'cart' ] = $cashback_data[ 'cashback_amount' ];
						} else {
							$cashbacks[ 'cart' ] = $cart_subtotal * $cashback_data[ 'cashback_amount' ] / 100;
						}
					}
				}
			}

			$total_cashback = array_sum( $cashbacks );
			$max_cap = ! empty( $ddwcwm_wallet['cashback_max_cap'] ) ? $ddwcwm_wallet['cashback_max_cap'] : 0;
			if ( ! empty( $max_cap ) && $total_cashback > $max_cap ) {
				$ratio = $max_cap / $total_cashback;
				foreach ( $cashbacks as $key => $val ) {
					$cashbacks[ $key ] = $val * $ratio;
				}
			}

			return $cashbacks;
		}

		/**
		 * Calculate Cashbacks with order function
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function ddwcwm_calculate_cashbacks_with_order( $order_id ) {
			$wallet_topup_pro = get_page_by_path( 'ddwcwm-wallet-topup' , OBJECT, 'product' );

			// Free supports cart-total cashback only.
			$cashbacks = [ 'cart' => 0 ];

			$order = new \WC_Order( $order_id );
			global $wpdb, $ddwcwm_wallet;

			$order_subtotal = apply_filters( 'ddwcwm_modify_amount_to_base_currency', floatval( $order->get_subtotal() ), $order->get_id() );
			$exclude_sale   = ( ! empty( $ddwcwm_wallet['cashback_exclude_sale_products'] ) && $ddwcwm_wallet['cashback_exclude_sale_products'] === 'on' );

			$min_order_value = ! empty( $ddwcwm_wallet['cashback_min_order_value'] ) ? $ddwcwm_wallet['cashback_min_order_value'] : 0;
			if ( ! empty( $min_order_value ) && $order_subtotal < $min_order_value ) {
				return $cashbacks;
			}

			$order_items       = $order->get_items();
			$wallet_pro_exists = false;

			if ( ! empty( $order_items ) ) {
				foreach ( $order_items as $key => $cart_content ) {
					$product_id = ! empty( $cart_content[ 'variation_id' ] ) ? $cart_content[ 'variation_id' ] : $cart_content[ 'product_id' ];
					$product    = $cart_content->get_product();

					if ( $exclude_sale && $product && $product->is_on_sale() ) {
						$order_subtotal -= $cart_content->get_total();
						continue;
					}

					if ( $wallet_topup_pro && $product_id == $wallet_topup_pro->ID ) {
						$wallet_pro_exists = true;
					}
				}

				// General Cart Rule (the only cashback type available in Free).
				if ( ! $wallet_pro_exists ) {
					$cashback_data = $wpdb->get_row( $wpdb->prepare( "SELECT cashback_amount, cashback_type FROM %i WHERE status=%s AND %f >= amount_from AND amount_to >= %f", $this->rules_table, 'enabled', $order_subtotal, $order_subtotal ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

					if ( ! empty( $cashback_data ) ) {
						if ( $cashback_data[ 'cashback_type' ] == 'fixed' ) {
							$cashbacks[ 'cart' ] = $cashback_data[ 'cashback_amount' ];
						} else {
							$cashbacks[ 'cart' ] = $order_subtotal * $cashback_data[ 'cashback_amount' ] / 100;
						}
					}
				}
			}

			$total_cashback = array_sum( $cashbacks );
			$max_cap = ! empty( $ddwcwm_wallet['cashback_max_cap'] ) ? $ddwcwm_wallet['cashback_max_cap'] : 0;
			if ( ! empty( $max_cap ) && $total_cashback > $max_cap ) {
				$ratio = $max_cap / $total_cashback;
				foreach ( $cashbacks as $key => $val ) {
					$cashbacks[ $key ] = $val * $ratio;
				}
			}

			return $cashbacks;
		}
	}
}
