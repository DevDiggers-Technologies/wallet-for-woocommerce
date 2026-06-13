<?php
/**
 * Common functions class
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes\Common;

use DDWCWalletManagement\Helper\Users\DDWCWM_Users_Helper;
use DDWCWalletManagement\Helper\Transactions\DDWCWM_Transactions_Helper;
use DDWCWalletManagement\Includes\DDWCWM_Email_Notification_Handler;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Common_Functions' ) ) {
	/**
	 * Common functions
	 */
	class DDWCWM_Common_Functions {

		/**
         * Register new endpoint to use inside My Account page.
		 *
		 * @return void
		 */
        public function ddwcwm_add_endpoints() {
			global $ddwcwm_wallet;
			add_rewrite_endpoint( $ddwcwm_wallet[ 'my_account_endpoint' ], EP_ROOT | EP_PAGES );
		}

		/**
		 * Add Email Notification function
		 *
		 * @param array $email_classes
		 * @return array
		 */
		public function ddwcwm_add_new_email_notification( $email_classes ) {
			$email_classes[ 'WC_Email_Wallet_Management_Notification' ] = new DDWCWM_Email_Notification_Handler();
            return $email_classes;
		}

		/**
		 * Add Email Notification Action function
		 *
		 * @param array $actions
		 * @return array
		 */
		public function ddwcwm_add_notification_actions( $actions ) {
			$actions[] = 'ddwcwm_mail';
            return $actions;
        }

        /**
         * Add the gateway to woocommerce
         *
         * @param array $methods All payment methods.
         */
		public function ddwcwm_add_payment_gateway( $methods ) {
			$methods[] = 'DDWCWalletManagement\Includes\DDWCWM_Wallet_Gateway';
            return $methods;
		}

		/**
		 * Add whatsapp automation function
		 *
		 * @param string $whatsapp_message
		 * @param object $response
		 * @return string
		 */
		public function ddwcwm_add_whatsapp_automation( $whatsapp_message, $response ) {
			$twilio_request    = $response->get_params();
			$user_message_body = $twilio_request[ 'Body' ];

			if ( strpos( strtolower( $user_message_body ), 'wallet' ) !== false ) {
				$whatsapp_from    = str_replace( 'whatsapp:', '', $twilio_request[ 'From' ] );

				$user_id = $this->ddwcwm_get_user_id_by_billing_phone( $whatsapp_from );

				if ( ! empty( $user_id ) ) {
					$user_helper      = new DDWCWM_Users_Helper();
					$wallet_balance   = $user_helper->ddwcwm_get_user_wallet_balance( $user_id );
					/* translators: %s: customer wallet balance amount. */
					$whatsapp_message = sprintf( esc_html__( 'Your current wallet balance is %s', 'wallet-management-for-woocommerce' ), $this->ddwcwm_whatsapp_price( $wallet_balance ) );
				} else {
                    $permalink = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . '/' . get_option( 'woocommerce_myaccount_edit_account_endpoint', 'edit-account' ) . '/billing';

					/* translators: %s: My Account page URL. */
					$whatsapp_message = sprintf( esc_html__( 'Sorry, we are not able to find your number saved on your account, kindly save your phone number on the billing phone field from here - %s', 'wallet-management-for-woocommerce' ), esc_url( $permalink ) );
				}
			} else {
				$whatsapp_message .= "\n\n" . html_entity_decode( esc_html__( 'You can also check your wallet balance from here just by typing like "What is my wallet balance?"', 'wallet-management-for-woocommerce' ) );
			}

			return $whatsapp_message;
		}

		/**
         * Get price with currency and without html function
         *
         * @param float|string $amount
         * @return string
         */
        public function ddwcwm_whatsapp_price( $amount ) {
            return html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ) );
        }

		/**
         * Get user id by billing phone function
         *
         * @param string $billing_phone
         * @return integer|null
         */
        public function ddwcwm_get_user_id_by_billing_phone( $billing_phone ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Prepared lookup on usermeta, no caching layer for this one-off resolve.
			return $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key=%s AND meta_value=%s", 'billing_phone', $billing_phone ) );
        }

		/**
		 * Handle removing wallet for whatsapp purchase function
		 *
		 * @param boolean $initial
		 * @param object $gateway
		 * @param object $response
		 * @return boolean
		 */
		public function ddwcwm_handle_removing_wallet_for_whatsapp_purchase( $initial, $gateway, $cart_total, $response ) {
			if ( 'ddwcwm_wallet' === $gateway->id ) {
				global $ddwcwm_wallet;
				$twilio_request    = $response->get_params();
				$user_message_body = $twilio_request[ 'Body' ];
				$whatsapp_from     = str_replace( 'whatsapp:', '', $twilio_request[ 'From' ] );
				$user_id           = $this->ddwcwm_get_user_id_by_billing_phone( $whatsapp_from );

				if ( ! empty( $user_id ) ) {
					$user_helper    = new DDWCWM_Users_Helper();
					$wallet_balance = $user_helper->ddwcwm_get_user_wallet_balance( $user_id );

					if ( $wallet_balance <= 0 || $wallet_balance < $cart_total ) {
						return false;
					}

					return true;
				} else {
                    return false;
				}
			}

			return $initial;
		}

		/**
		 * Check if dues product exist in cart function
		 * 
		 * @param mixed
		 * @return mixed
		 */
		public function ddwcwm_check_if_wallet_topup_product_exists_in_cart( $passed = true, $product_id = 0, $quantity = 1 ) {
			// If called as an action (like woocommerce_cart_loaded_from_session), $passed might be the cart object.
			// We should ignore it and ensure we return the correct type if it was a filter.
			$is_filter = is_bool( $passed );

			$wallet_topup_pro = get_page_by_path( 'ddwcwm-wallet-topup' , OBJECT, 'product' );
			if ( ! $wallet_topup_pro ) {
				return $passed;
			}
			$wallet_pro_id = $wallet_topup_pro->ID;

			if ( ! empty( WC()->cart ) ) {
				$cart_contents = WC()->cart->get_cart();
				$wallet_product_in_cart = false;
				foreach ( $cart_contents as $cart_item ) {
					if ( $cart_item['product_id'] == $wallet_pro_id ) {
						$wallet_product_in_cart = true;
						break;
					}
				}

				if ( $product_id ) {
					// We are validating an add-to-cart action
					if ( $product_id == $wallet_pro_id ) {
						// Adding a wallet product - remove all others
						foreach ( $cart_contents as $cart_item_key => $cart_item ) {
							if ( $cart_item['product_id'] != $wallet_pro_id ) {
								WC()->cart->remove_cart_item( $cart_item_key );
							}
						}
					} else if ( $wallet_product_in_cart ) {
						// Adding a non-wallet product while wallet is in cart - block it
						wc_add_notice( __( 'Your cart contains a Wallet top-up. Please remove it before adding other products.', 'wallet-management-for-woocommerce' ), 'error' );
						return false;
					}
				} else {
					// Maintenance sweep (called as action or on cart load)
					if ( $wallet_product_in_cart ) {
						foreach ( $cart_contents as $cart_item_key => $cart_item ) {
							if ( $cart_item['product_id'] != $wallet_pro_id ) {
								WC()->cart->remove_cart_item( $cart_item_key );
							}
						}
					}
				}
			}

			return $passed;
		}

		/**
		 * Handle wallet on order cancel function
		 *
		 * @param int $order_id
		 * @return void
		 */
		public function ddwcwm_handle_wallet_on_order_cancelled( $order_id ) {
			$order                      = wc_get_order( $order_id );
			$order_cancellation_handled = $order->get_meta( '_ddwcwm_order_cancellation_handled', true );

			if ( empty( $order_cancellation_handled ) ) {
				$wallet_amount_used = $order->get_meta( '_ddwcwm_wallet_amount_used', true );

				$user_helper = new DDWCWM_Users_Helper();
				$transaction_helper = new DDWCWM_Transactions_Helper();

				$user_id = $order->get_user_id();

				$wallet_balance = $user_helper->ddwcwm_get_user_wallet_balance( $user_id );

				if ( ! empty( $wallet_amount_used ) ) {
					do_action( 'ddwcwm_before_credit_wallet_amount_on_order_cancelled', $wallet_amount_used, $user_id, $order_id );

					$wallet_balance += $wallet_amount_used;

					$data = [
						'type'      => 'credit',
						'amount'    => $wallet_amount_used,
						'user_id'   => $user_id,
						'order_id'  => $order_id,
						'note'      => esc_html__( 'Credited wallet amount on order cancel.', 'wallet-management-for-woocommerce' ),
						'date'      => current_time( 'Y-m-d H:i:s' ),
						'reference' => 'order_cancelled_credit',
					];

					$transaction_helper->ddwcwm_save_transaction( $data );
				}

				$cashback_amount       = $order->get_meta( '_ddwcwm_cashback_amount', true );
				$awarded_cashback_data = $order->get_meta( '_ddwcwm_awarded_cashback_data', true );

				if ( ! empty( $awarded_cashback_data ) && ! empty( $cashback_amount ) ) {
					$cashback_amount = 0;

					if ( ! empty( $awarded_cashback_data[ 'cart' ] ) ) {
						$cashback_amount += $awarded_cashback_data[ 'cart' ];

						$wallet_balance -= $awarded_cashback_data[ 'cart' ];

						$data = [
							'type'      => 'debit',
							'amount'    => $awarded_cashback_data[ 'cart' ],
							'user_id'   => $user_id,
							'order_id'  => $order_id,
							'note'      => esc_html__( 'Debit wallet cashback for cart due to order cancel.', 'wallet-management-for-woocommerce' ),
							'date'      => current_time( 'Y-m-d H:i:s' ),
							'reference' => 'cart_cashback',
						];

						$transaction_helper->ddwcwm_save_transaction( $data );
					}

					if ( ! empty( $awarded_cashback_data[ 'topup' ] ) ) {
						$cashback_amount += $awarded_cashback_data[ 'topup' ];

						$wallet_balance -= $awarded_cashback_data[ 'topup' ];

						$data = [
							'type'      => 'debit',
							'amount'    => $awarded_cashback_data[ 'topup' ],
							'user_id'   => $user_id,
							'order_id'  => $order_id,
							'note'      => esc_html__( 'Debit wallet cashback for topup due to order cancel.', 'wallet-management-for-woocommerce' ),
							'date'      => current_time( 'Y-m-d H:i:s' ),
							'reference' => 'topup_cashback',
						];

						$transaction_helper->ddwcwm_save_transaction( $data );
					}

					if ( ! empty( $cashback_amount ) ) {
					}
				}

				$user_helper->ddwcwm_set_user_wallet_balance( $wallet_balance, $user_id );

				$order->update_meta_data( '_ddwcwm_order_cancellation_handled', 'yes' );

				$order->save();
			}
		}

		/**
		 * Handle wallet on order fully refunded function
		 *
		 * @param int $order_id
		 * @return void
		 */
		public function ddwcwm_handle_wallet_on_order_fully_refunded( $order_id ) {
			$order                  = wc_get_order( $order_id );
			$order_refunded_handled = $order->get_meta( '_ddwcwm_order_fully_refund_handled', true );

			if ( empty( $order_refunded_handled ) ) {
				$wallet_amount_used = $order->get_meta( '_ddwcwm_wallet_amount_used', true );

				$user_helper        = new DDWCWM_Users_Helper();
				$transaction_helper = new DDWCWM_Transactions_Helper();

				$user_id = $order->get_user_id();

				$wallet_balance = $user_helper->ddwcwm_get_user_wallet_balance( $user_id );

				if ( ! empty( $wallet_amount_used ) ) {
					do_action( 'ddwcwm_before_credit_wallet_amount_on_order_refunded', $wallet_amount_used, $user_id, $order_id );

					$wallet_balance += $wallet_amount_used;

					$data = [
						'type'      => 'credit',
						'amount'    => $wallet_amount_used,
						'user_id'   => $user_id,
						'order_id'  => $order_id,
						'note'      => esc_html__( 'Credited wallet amount on order fully refund.', 'wallet-management-for-woocommerce' ),
						'date'      => current_time( 'Y-m-d H:i:s' ),
						'reference' => 'order_refund',
					];

					$transaction_helper->ddwcwm_save_transaction( $data );

				}

				$cashback_amount       = $order->get_meta( '_ddwcwm_cashback_amount', true );
				$awarded_cashback_data = $order->get_meta( '_ddwcwm_awarded_cashback_data', true );

				if ( ! empty( $awarded_cashback_data ) && ! empty( $cashback_amount ) ) {
					$cashback_amount = 0;

					if ( ! empty( $awarded_cashback_data[ 'cart' ] ) ) {
						$cashback_amount += $awarded_cashback_data[ 'cart' ];

						$wallet_balance -= $awarded_cashback_data[ 'cart' ];

						$data = [
							'type'      => 'debit',
							'amount'    => $awarded_cashback_data[ 'cart' ],
							'user_id'   => $user_id,
							'order_id'  => $order_id,
							'note'      => esc_html__( 'Debit wallet cashback for cart due to order fully refund.', 'wallet-management-for-woocommerce' ),
							'date'      => current_time( 'Y-m-d H:i:s' ),
							'reference' => 'cart_cashback',
						];

						$transaction_helper->ddwcwm_save_transaction( $data );
					}

					if ( ! empty( $awarded_cashback_data[ 'topup' ] ) ) {
						$cashback_amount += $awarded_cashback_data[ 'topup' ];

						$wallet_balance -= $awarded_cashback_data[ 'topup' ];

						$data = [
							'type'      => 'debit',
							'amount'    => $awarded_cashback_data[ 'topup' ],
							'user_id'   => $user_id,
							'order_id'  => $order_id,
							'note'      => esc_html__( 'Debit wallet cashback for topup due to order fully refund.', 'wallet-management-for-woocommerce' ),
							'date'      => current_time( 'Y-m-d H:i:s' ),
							'reference' => 'topup_cashback',
						];

						$transaction_helper->ddwcwm_save_transaction( $data );
					}

					if ( ! empty( $cashback_amount ) ) {
					}
				}

				$user_helper->ddwcwm_set_user_wallet_balance( $wallet_balance, $user_id );

				$order->update_meta_data( '_ddwcwm_order_fully_refund_handled', 'yes' );

				$order->save();
			}
		}
		/**
		 * Handle wallet on order partially refunded
		 *
		 * @param int $order_id
		 * @param int $refund_id
		 * @return void
		 */
		public function ddwcwm_handle_wallet_on_order_partially_refunded( $order_id, $refund_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				return;
			}

			$awarded_cashback_data = $order->get_meta( '_ddwcwm_awarded_cashback_data', true );
			if ( empty( $awarded_cashback_data ) ) {
				return;
			}

			$refund = wc_get_order( $refund_id );
			if ( ! $refund ) {
				return;
			}

			$refund_amount = abs( floatval( $refund->get_total() ) );
			// Current total is already reduced by refund amount in some WC flows, 
			// but at this hook, we usually have the new total or can reconstruct original.
			$current_total = floatval( $order->get_total() );
			$original_total = $current_total + $refund_amount;

			if ( $original_total <= 0 || $refund_amount <= 0 ) {
				return;
			}

			$ratio   = $refund_amount / $original_total;
			$user_id = $order->get_customer_id();
			if ( ! $user_id ) {
				return;
			}

			$user_helper        = new DDWCWM_Users_Helper();
			$transaction_helper = new DDWCWM_Transactions_Helper();
			$wallet_balance     = $user_helper->ddwcwm_get_user_wallet_balance( $user_id );
			$total_debit        = 0;

			foreach ( $awarded_cashback_data as $key => $amount ) {
				if ( $amount > 0 ) {
					$debit_amount = $amount * $ratio;
					$wallet_balance -= $debit_amount;
					$total_debit    += $debit_amount;
					
					$awarded_cashback_data[ $key ] -= $debit_amount;

					$data = [
						'type'      => 'debit',
						'amount'    => $debit_amount,
						'user_id'   => $user_id,
						'order_id'  => $order_id,
						/* translators: %s: cashback type label. */
						'note'      => sprintf( esc_html__( 'Debit wallet cashback for %s due to order partial refund.', 'wallet-management-for-woocommerce' ), str_replace( '_', ' ', $key ) ),
						'date'      => current_time( 'Y-m-d H:i:s' ),
						'reference' => 'cashback_partial_reversal',
					];
					$transaction_helper->ddwcwm_save_transaction( $data );
				}
			}

			if ( $total_debit > 0 ) {
				$user_helper->ddwcwm_set_user_wallet_balance( $wallet_balance, $user_id );
				$order->update_meta_data( '_ddwcwm_awarded_cashback_data', $awarded_cashback_data );
				$order->save();

			}
		}

		/**
		 * Add SVG icons function
		 *
		 * @param array $default_svg_icons
		 * @param array $args
		 * @return array
		 */
		public function ddwcwm_add_svg_icons( $default_svg_icons, $args ) {
			$size         = ! empty( $args['size'] ) ? $args['size'] : '24';
			$size_attr    = 'width="' . $size . '" height="' . $size . '"';
			$stroke_color = ! empty( $args['stroke_color'] ) ? $args['stroke_color'] : 'currentColor';
			$stroke_width = isset( $args['stroke_width'] ) ? $args['stroke_width'] : '2';
			$fill         = ! empty( $args['fill'] ) ? $args['fill'] : 'none';

			$svg_icons = [
				'wallet_balance'     => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path></svg>',
				'wallet'             => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path></svg>',
				'transactions'       => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>',
				'users'              => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
				'withdrawals'        => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><path d="M3 10h18"></path><path d="M5 6l1.3-3.6C6.5 2.1 6.8 2 7 2h10c.2 0 .5.1.7.4L19 6"></path><path d="M4 10v11"></path><path d="M20 10v11"></path><path d="M8 14v3"></path><path d="M12 14v3"></path><path d="M16 14v3"></path></svg>',
				'endpoints'          => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
				'otp'                => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path></svg>',
				'referrals'          => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="16" y1="11" x2="22" y2="11"></line></svg>',
				'wallet_spent'       => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.71a2 2 0 0 0 2-1.61l1.71-8.39H5.05"></path></svg>',
				'layout'             => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"></rect><path d="M3 9h18"></path><path d="M9 21V9"></path></svg>',
				'shortcodes'         => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
				'cashback'           => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
				'emails'             => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
				'cloud-upload'       => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 56 56" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '"><path xmlns="http://www.w3.org/2000/svg" d="M52 29.3998C52 36.4559 46.2561 42.1998 39.2 42.1998H37.6C36.72 42.1998 36 41.4798 36 40.5998C36 39.7198 36.72 38.9998 37.6 38.9998H39.2C44.4961 38.9998 48.8 34.6959 48.8 29.3998C48.8 25.1437 45.9522 21.3519 41.8878 20.1838C41.2639 20.0077 40.8 19.4638 40.736 18.8077C40.256 13.8959 36.1599 10.1999 31.2 10.1999C27.4561 10.1999 24.0478 12.3759 22.4961 15.7677C22.1921 16.4398 21.4722 16.8077 20.7361 16.6798C20.4961 16.6159 20.2561 16.5998 20 16.5998C17.36 16.5998 15.2 18.7598 15.2 21.3998C15.2 22.1837 14.64 22.9998 13.8561 23.1276C10 23.7676 7.2 27.0798 7.2 30.9998C7.2 35.4159 10.7839 38.9998 15.2 38.9998H18.4C19.28 38.9998 20 39.7198 20 40.5998C20 41.4798 19.28 42.1998 18.4 42.1998H15.2C9.02391 42.1998 4 37.1759 4 30.9998C4 25.9276 7.34391 21.5918 12.0961 20.2478C12.7039 16.3278 16.0161 13.4 20 13.4H20.1121C22.3682 9.48 26.5921 7 31.1997 7C37.4558 7 42.6877 11.4161 43.7757 17.4475C48.6718 19.3197 52 24.0873 52 29.3998ZM29.1364 28.6637C28.9925 28.5197 28.8325 28.4237 28.6564 28.3437C28.6086 28.3276 28.5604 28.3115 28.4964 28.2958C28.3686 28.248 28.2404 28.2319 28.0964 28.2158C28.0486 28.2158 27.9843 28.1997 27.9364 28.1997C27.7604 28.1997 27.6004 28.2319 27.4243 28.2958C27.4082 28.2958 27.3922 28.2958 27.3764 28.3119C27.2004 28.3919 27.0404 28.504 26.9125 28.6319C26.8964 28.648 26.8804 28.648 26.8647 28.664L22.0647 33.464C21.4407 34.088 21.4407 35.0962 22.0647 35.7201C22.3843 36.0398 22.7843 36.1998 23.2004 36.1998C23.6165 36.1998 24.0165 36.0398 24.3365 35.7358L26.4004 33.6558V47.3994C26.4004 48.2794 27.1204 48.9994 28.0004 48.9994C28.8804 48.9994 29.6004 48.2794 29.6004 47.3994V33.6558L31.6643 35.7197C32.2882 36.3437 33.2965 36.3437 33.9204 35.7197C34.5443 35.0958 34.5443 34.0876 33.9204 33.4637L29.1364 28.6637Z"/></svg>',
				'checkmark-circle'   => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
				'money-bag'          => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="8" r="6"></circle><path d="M18.09 10.37A6 6 0 1 1 10.34 18"></path><path d="M7 6h1v4"></path><path d="m16.71 13.88.7.71-2.82 2.82"></path></svg>',
				'send'               => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>',
				'request'            => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v10m0 0l-3-3m3 3l3-3"></path><path d="M6 16h12a2 2 0 1 1 0 4H6a2 2 0 1 1 0-4z"></path></svg>',
				'topup'              => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line><line x1="15" y1="15" x2="19" y2="15"></line><line x1="17" y1="13" x2="17" y2="17"></line></svg>',
				'basic-info'         => '<svg xmlns="http://www.w3.org/2000/svg" ' . $size_attr . ' viewBox="0 0 24 24" fill="' . $fill . '" stroke="' . $stroke_color . '" stroke-width="' . $stroke_width . '" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
			];

			return array_merge( $default_svg_icons, $svg_icons );
		}
		/**
		 * Handle wallet on order completed function
		 *
		 * @param int $order_id
		 * @return void
		 */
		public function ddwcwm_handle_wallet_on_order_completed( $order_id ) {
			$order                    = wc_get_order( $order_id );
			$order_completion_handled = $order->get_meta( '_ddwcwm_order_completion_handled', true );

			if ( empty( $order_completion_handled ) ) {
				$user_id = $order->get_customer_id();

				if ( ! $user_id ) {
					return;
				}

				$this->ddwcwm_process_wallet_credits( $order_id, $user_id );
			}
		}

		/**
		 * Process wallet credits (immediate or called via cron)
		 *
		 * @param int $order_id
		 * @param int $user_id
		 * @param int $delay
		 * @return void
		 */
		public function ddwcwm_process_wallet_credits( $order_id, $user_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order || ! $order->has_status( 'completed' ) ) {
				return;
			}

			$order_items        = $order->get_items();
			$order_subtotal     = $order->get_subtotal();
			$wallet_topup_pro   = get_page_by_path( 'ddwcwm-wallet-topup' , OBJECT, 'product' );
			$user_helper        = new DDWCWM_Users_Helper();
			$wallet_balance     = $user_helper->ddwcwm_get_user_wallet_balance( $user_id );
			$transaction_helper = new DDWCWM_Transactions_Helper();

			// Handle Topup
			foreach ( $order_items as $key => $order_item ) {
				if ( $wallet_topup_pro && $order_item->get_data()[ 'product_id' ] == $wallet_topup_pro->ID ) {
					// Topup logic should only run once.
					$topup_handled = $order->get_meta( '_ddwcwm_topup_handled', true );
					if ( ! empty( $topup_handled ) ) {
						continue;
					}

					$order_subtotal = apply_filters( 'ddwcwm_modify_order_subtotal_for_topup', $order_subtotal, $order_item, $order );
					$wallet_balance += $order_subtotal;

					$data = [
						'type'      => 'credit',
						'amount'    => $order_subtotal,
						'user_id'   => $user_id,
						'order_id'  => $order_id,
						'note'      => esc_html__( 'Wallet Topup', 'wallet-management-for-woocommerce' ),
						'date'      => current_time( 'Y-m-d H:i:s' ),
						'reference' => 'wallet_topup',
					];

					$transaction_helper->ddwcwm_save_transaction( $data );
					$user_helper->ddwcwm_set_user_wallet_balance( $wallet_balance, $user_id );
					
					$order->update_meta_data( '_ddwcwm_topup_handled', 'yes' );
				}
			}

			$awarded_cashback_data = $order->get_meta( '_ddwcwm_awarded_cashback_data', true );

			if ( ! empty( $awarded_cashback_data ) ) {
				global $ddwcwm_wallet;
				$cashback_amount = 0;


				if ( ! empty( $awarded_cashback_data[ 'cart' ] ) ) {
					$cashback_amount += $awarded_cashback_data[ 'cart' ];

					$wallet_balance += $awarded_cashback_data[ 'cart' ];

					$data = [
						'type'      => 'credit',
						'amount'    => $awarded_cashback_data[ 'cart' ],
						'user_id'   => $user_id,
						'order_id'  => $order_id,
						'note'      => esc_html__( 'Wallet Cashback for Cart', 'wallet-management-for-woocommerce' ),
						'date'        => current_time( 'Y-m-d H:i:s' ),
						'reference'   => 'cart_cashback',
					];

					$transaction_helper->ddwcwm_save_transaction( $data );
				}

				if ( ! empty( $awarded_cashback_data[ 'topup' ] ) ) {
					$cashback_amount += $awarded_cashback_data[ 'topup' ];

					$wallet_balance += $awarded_cashback_data[ 'topup' ];

					$data = [
						'type'      => 'credit',
						'amount'    => $awarded_cashback_data[ 'topup' ],
						'user_id'   => $user_id,
						'order_id'  => $order_id,
						'note'      => esc_html__( 'Wallet Cashback for Topup', 'wallet-management-for-woocommerce' ),
						'date'        => current_time( 'Y-m-d H:i:s' ),
						'reference'   => 'topup_cashback',
					];

					$transaction_helper->ddwcwm_save_transaction( $data );
				}

				if ( ! empty( $cashback_amount ) ) {
					$order->update_meta_data( '_ddwcwm_cashback_amount', $cashback_amount );
				}
			}

			$user_helper->ddwcwm_set_user_wallet_balance( $wallet_balance, $user_id );

			$order->update_meta_data( '_ddwcwm_order_completion_handled', 'yes' );

			$order->save();
		}

		/**
		 * Add registration fields function
		 *
		 * @return void
		 */
		public function ddwcwm_add_registration_fields() {
			// The referral registration field is part of the Pro referral program and is
			// not rendered in Free.
		}

		/**
		 * Display registration wallet credit notice for guest function
		 *
		 * @return void
		 */
		public function ddwcwm_display_registration_credit_notice_for_guest() {
			if ( is_user_logged_in() ) {
				return;
			}

			global $ddwcwm_wallet;

			if ( ! empty( $ddwcwm_wallet[ 'registration_credit' ] ) && is_account_page() ) {
				/* translators: %s: registration credit amount. */
				wc_add_notice( sprintf ( esc_html__( 'You\'ll receive %s in your wallet after sucessful registration.', 'wallet-management-for-woocommerce' ), wc_price( apply_filters( 'ddwcwm_modify_amount_to_multi_currency', $ddwcwm_wallet[ 'registration_credit' ] ) ) ), 'notice' );
			}
		}

		/**
         * Credit Wallet on registration for customer function
         *
         * @param int $user_id
         * @return void
         */
		public function ddwcwm_credit_wallet_on_registration( $user_id ) {
			if ( is_user_logged_in() ) {
				return;
			}

			global $ddwcwm_wallet;

			if ( ! empty( $ddwcwm_wallet[ 'registration_credit' ] ) ) {
				$registration_credit = floatval( $ddwcwm_wallet[ 'registration_credit' ] );

				$user_helper = new DDWCWM_Users_Helper();
				$user_helper->ddwcwm_set_user_wallet_balance( $registration_credit, $user_id );

				$data = [
					'type'      => 'credit',
					'amount'    => $registration_credit,
					'user_id'   => $user_id,
					'note'      => '',
					'date'      => current_time( 'Y-m-d H:i:s' ),
					'reference' => 'registration_credit',
				];

				$transaction_helper = new DDWCWM_Transactions_Helper();

				$transaction_helper->ddwcwm_save_transaction( $data );
			}

			// Referral rewards on registration are a Pro feature and are not processed in Free.
		}

		/**
		 * Check if wallet topup product exists in cart function
		 * 
		 * @return boolean
		 */
		public static function ddwcwm_is_wallet_topup_pro_in_cart() {
			$wallet_topup_pro = get_page_by_path( 'ddwcwm-wallet-topup' , OBJECT, 'product' );

			if ( ! $wallet_topup_pro ) {
				return false;
			}

			$wallet_pro_id = $wallet_topup_pro->ID;
			$cart_contents = ! empty( WC()->cart ) ? WC()->cart->get_cart() : [];

			if ( ! empty( $cart_contents ) ) {
				foreach ( $cart_contents as $cart_content ) {
					$id = ! empty( $cart_content[ 'variation_id' ] ) ? $cart_content[ 'variation_id' ] : $cart_content[ 'product_id' ];

					if ( $id == $wallet_pro_id ) {
						return true;
					}
				}
			}

			return false;
		}
	}
}
