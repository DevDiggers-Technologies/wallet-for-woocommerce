<?php
/**
 * Transactions helper
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Helper\Transactions;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Transactions_Helper' ) ) {
	/**
	 * Transactions helper class
	 */
	class DDWCWM_Transactions_Helper {
		/**
		 * Database Object
		 *
		 * @var object
		 */
        protected $wpdb;

		/**
		 * Transactions table Variable
		 *
		 * @var string
		 */
		protected $transaction_table;

        /**
		 * Users table Variable
		 *
		 * @var string
		 */
        protected $users_table;

		/**
		 * Construct
		 */
		public function __construct() {
			global $wpdb;
            $this->wpdb = $wpdb;

            $this->transaction_table = $this->wpdb->prefix . 'ddwcwm_transactions';
			$this->users_table       = $this->wpdb->users;
		}

		/**
		 * Save transaction to DB function
		 *
		 * @param array $data
		 * @return int
		 */
		public function ddwcwm_save_transaction( $data ) {
			$default_data = [
                'order_id'  => NULL,
				'reference' => '',
				'sender_id' => 0,
                'user_id'   => get_current_user_id(),
                'amount'    => 0,
                'type'      => '',
                'date'      => current_time( 'Y-m-d H:i:s' ),
                'expiry_date' => NULL,
                'is_expired'  => 0,
                'note'      => '',
			];

			$data = wp_parse_args( $data, $default_data );
            $this->wpdb->insert(
                $this->transaction_table,
                $data,
                [ '%d', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s' ]
            );

            $transaction_id = $this->wpdb->insert_id;

			$data['transaction_id'] = $transaction_id;
			$this->ddwcwm_send_transaction_email( $data );

            return $transaction_id;
		}

		/**
		 * Send transaction email function
		 * 
		 * @param array $data
		 * @return void
		 */
		public function ddwcwm_send_transaction_email( $data ) {
			global $ddwcwm_wallet;

			if ( empty( $data['user_id'] ) || empty( $data['amount'] ) || empty( $ddwcwm_wallet['email_settings'] ) ) {
				return;
			}

			$user = get_userdata( $data['user_id'] );
			if ( ! $user ) {
				return;
			}

			$user_helper   = new \DDWCWalletManagement\Helper\Users\DDWCWM_Users_Helper();
			$balance       = $user_helper->ddwcwm_get_user_wallet_balance( $data['user_id'] );
			$formatted_bal = wc_price( apply_filters( 'ddwcwm_modify_amount_to_multi_currency', $balance ) );
			$formatted_amt = wc_price( apply_filters( 'ddwcwm_modify_amount_to_multi_currency', abs( $data['amount'] ) ) );
			
			$email_heading = '';
			$email_subject = '';
			$email_message = '';
			$reason        = '';
			$status        = '';

			if ( in_array( $data['reference'], [ 'manual_adjustment', 'manual_credit', 'manual_debit' ] ) ) {
				if ( ! empty( $ddwcwm_wallet['email_settings']['manual_adjustment']['message'] ) ) {
					$email_heading = $ddwcwm_wallet['email_settings']['manual_adjustment']['heading'];
					$email_subject = $ddwcwm_wallet['email_settings']['manual_adjustment']['subject'];
					$email_message = wpautop( $ddwcwm_wallet['email_settings']['manual_adjustment']['message'] );
					$reason        = ! empty( $data['note'] ) ? $data['note'] : esc_html__( 'Manual adjustment by admin', 'wallet-management-for-woocommerce' );
				}
			} elseif ( 'credit' === $data['type'] ) {
				if ( ! empty( $ddwcwm_wallet['email_settings']['wallet_credited']['message'] ) ) {
					$email_heading = $ddwcwm_wallet['email_settings']['wallet_credited']['heading'];
					$email_subject = $ddwcwm_wallet['email_settings']['wallet_credited']['subject'];
					$email_message = wpautop( $ddwcwm_wallet['email_settings']['wallet_credited']['message'] );

					$reason = ! empty( $ddwcwm_wallet['credit_reason'][ $data['reference'] ] ) ? $ddwcwm_wallet['credit_reason'][ $data['reference'] ] : $data['reference'];
					if ( empty( $reason ) && ! empty( $data['note'] ) ) {
						$reason = $data['note'];
					}
				}
			} elseif ( 'debit' === $data['type'] ) {
				if ( ! empty( $ddwcwm_wallet['email_settings']['wallet_debited']['message'] ) ) {
					$email_heading = $ddwcwm_wallet['email_settings']['wallet_debited']['heading'];
					$email_subject = $ddwcwm_wallet['email_settings']['wallet_debited']['subject'];
					$email_message = wpautop( $ddwcwm_wallet['email_settings']['wallet_debited']['message'] );

					$reason = ! empty( $ddwcwm_wallet['debit_reason'][ $data['reference'] ] ) ? $ddwcwm_wallet['debit_reason'][ $data['reference'] ] : $data['reference'];
					if ( empty( $reason ) && ! empty( $data['note'] ) ) {
						$reason = $data['note'];
					}
				}
			} elseif ( 'transfer' === $data['type'] ) {
				// The sender gets debited, receiver gets credited.
				// Sender email:
				if ( ! empty( $data['sender_id'] ) && ! empty( $ddwcwm_wallet['email_settings']['wallet_debited']['message'] ) ) {
					$sender = get_userdata( $data['sender_id'] );
					if ( $sender ) {
						$sender_balance = $user_helper->ddwcwm_get_user_wallet_balance( $data['sender_id'] );
						$sender_bal_fmt = wc_price( apply_filters( 'ddwcwm_modify_amount_to_multi_currency', $sender_balance ) );
						
						$sender_heading = $ddwcwm_wallet['email_settings']['wallet_debited']['heading'];
						$sender_subject = $ddwcwm_wallet['email_settings']['wallet_debited']['subject'];
						$sender_message = wpautop( $ddwcwm_wallet['email_settings']['wallet_debited']['message'] );
						
						$sender_reason  = ! empty( $ddwcwm_wallet['debit_reason']['transfer_sent'] ) ? $ddwcwm_wallet['debit_reason']['transfer_sent'] : 'Transfer Sent';
						if ( ! empty( $data['note'] ) ) {
							$sender_reason .= ' - ' . esc_html__( 'Note:', 'wallet-management-for-woocommerce' ) . ' ' . $data['note'];
						}

						$sender_replace = [
							'{amount}'  => $formatted_amt,
							'{reason}'  => $sender_reason,
							'{balance}' => $sender_bal_fmt,
						];

						$sender_email_data = [
							'email'   => $sender->user_email,
							'user_id' => $sender->ID,
							'heading' => $sender_heading,
							'subject' => $sender_subject,
							'message' => $sender_message,
                            'replace' => $sender_replace,
						];
						do_action( 'ddwcwm_mail', $sender_email_data );
					}
				}

				// Receiver gets credited, reuse the standard logic below!
				if ( ! empty( $ddwcwm_wallet['email_settings']['wallet_credited']['message'] ) ) {
					$email_heading = $ddwcwm_wallet['email_settings']['wallet_credited']['heading'];
					$email_subject = $ddwcwm_wallet['email_settings']['wallet_credited']['subject'];
					$email_message = wpautop( $ddwcwm_wallet['email_settings']['wallet_credited']['message'] );

					$reason = ! empty( $ddwcwm_wallet['credit_reason']['transfer_received'] ) ? $ddwcwm_wallet['credit_reason']['transfer_received'] : 'Transfer Received';
					if ( ! empty( $data['note'] ) ) {
						$reason .= ' - ' . esc_html__( 'Note:', 'wallet-management-for-woocommerce' ) . ' ' . $data['note'];
					}
				}
			} elseif ( 'withdraw' === $data['type'] ) {
				if ( ! empty( $ddwcwm_wallet['email_settings']['withdrawal_status']['message'] ) ) {
					$email_heading = $ddwcwm_wallet['email_settings']['withdrawal_status']['heading'];
					$email_subject = $ddwcwm_wallet['email_settings']['withdrawal_status']['subject'];
					$email_message = wpautop( $ddwcwm_wallet['email_settings']['withdrawal_status']['message'] );
					
					$status = $this->ddwcwm_get_transactions_translation( $data['reference'] );
					$reason = ! empty( $ddwcwm_wallet['debit_reason']['withdrawal'] ) ? $ddwcwm_wallet['debit_reason']['withdrawal'] : 'Withdrawal';
					if ( ! empty( $data['note'] ) ) {
						$reason .= ' - ' . esc_html__( 'Note:', 'wallet-management-for-woocommerce' ) . ' ' . $data['note'];
					}
				}
			}

			if ( ! empty( $email_message ) ) {
				$replace = [
					'{amount}'            => $formatted_amt,
					'{reason}'            => $reason,
					'{balance}'           => $formatted_bal,
					'{user_name}'         => $user->user_login,
					'{user_display_name}' => $user->display_name,
					'{user_email}'        => $user->user_email,
					'{site_title}'        => get_bloginfo( 'name' ),
					'{status}'            => $status,
				];

				$email_data = [
					'email'   => $user->user_email,
					'user_id' => $user->ID,
					'heading' => $email_heading,
					'subject' => $email_subject,
					'message' => $email_message,
                    'replace' => $replace,
				];

				do_action( 'ddwcwm_mail', $email_data );
			}
		}

		/**
		 * Build the WHERE conditions for the admin transactions list from the
		 * request filters. Every value is sanitized and bound via $wpdb->prepare().
		 *
		 * @return string Prepared SQL conditions fragment.
		 */
		private function ddwcwm_build_transaction_filter_conditions() {
			global $wpdb;

			$conditions = '';

			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only admin list filters, no state change.
			if ( ! empty( $_GET['ddwcwm-transaction-type'] ) ) {
				$conditions .= $wpdb->prepare( ' AND transactions.type=%s', sanitize_text_field( wp_unslash( $_GET['ddwcwm-transaction-type'] ) ) );
			}
			if ( ! empty( $_GET['ddwcwm-transaction-from-date'] ) ) {
				$from = sanitize_text_field( wp_unslash( $_GET['ddwcwm-transaction-from-date'] ) );
				$to   = ! empty( $_GET['ddwcwm-transaction-to-date'] ) ? sanitize_text_field( wp_unslash( $_GET['ddwcwm-transaction-to-date'] ) ) : $from;
				$conditions .= $wpdb->prepare( ' AND DATE(date) BETWEEN %s AND %s', $from, $to );
			}
			if ( ! empty( $_GET['customer-id'] ) ) {
				$conditions .= $wpdb->prepare( ' AND transactions.user_id=%d', absint( wp_unslash( $_GET['customer-id'] ) ) );
			}
			if ( ! empty( $_GET['s'] ) ) {
				$search      = '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) . '%';
				$conditions .= $wpdb->prepare( ' AND transactions.id LIKE %s', $search );
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			return $conditions;
		}

		/**
		 * Get All transactions function
		 *
		 * @param int $per_page
		 * @param int $offset
		 * @return array
		 */
		public function ddwcwm_get_all_transactions( $per_page, $offset ) {
			global $wpdb;

			$conditions = $this->ddwcwm_build_transaction_filter_conditions();

			$query  = $wpdb->prepare(
				"SELECT DISTINCT transactions.* FROM %i as transactions LEFT JOIN %i as users ON transactions.user_id=users.ID WHERE 1=1",
				$this->transaction_table,
				$this->users_table
			);
			$query .= $conditions;
			$query .= ' GROUP BY transactions.id ORDER BY transactions.id DESC LIMIT ' . absint( $per_page ) . ' OFFSET ' . absint( $offset );

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table names use %i, $conditions is built via $wpdb->prepare(), LIMIT/OFFSET cast with absint(); ledger query, no caching.
			$transactions = $wpdb->get_results( $query, ARRAY_A );

			return apply_filters( 'ddwcwm_modify_transactions_data', $transactions, $per_page, $offset );

		}

		/**
		 * Get All transactions count function
		 *
		 * @return int
		 */
		public function ddwcwm_get_all_transactions_count() {
			global $wpdb;

			$conditions = $this->ddwcwm_build_transaction_filter_conditions();

			$query  = $wpdb->prepare(
				"SELECT count(DISTINCT transactions.id) FROM %i as transactions JOIN %i as users ON transactions.user_id=users.ID WHERE 1=1",
				$this->transaction_table,
				$this->users_table
			);
			$query .= $conditions;

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table names use %i, $conditions is built via $wpdb->prepare(); ledger count query, no caching.
			$total_transactions_count = $wpdb->get_var( $query );

			return apply_filters( 'ddwcwm_modify_total_transactions_count', $total_transactions_count );

		}

		/**
		 * Return translations for static words function
		 *
		 * @param string $static_word
		 * @return string
		 */
		public static function ddwcwm_get_transactions_translation( $static_word ) {
            $transaction_translation = [
				'credit'                 => esc_html__( 'Credit', 'wallet-management-for-woocommerce' ),
				'debit'                  => esc_html__( 'Debit', 'wallet-management-for-woocommerce' ),
				'manual_adjustment'      => esc_html__( 'Manual Adjustment', 'wallet-management-for-woocommerce' ),
				'manual_debit'           => esc_html__( 'Manual Debit', 'wallet-management-for-woocommerce' ),
				'manual_credit'          => esc_html__( 'Manual Credit', 'wallet-management-for-woocommerce' ),
				'order_credit'           => esc_html__( 'Order Credit', 'wallet-management-for-woocommerce' ),
				'order_debit'            => esc_html__( 'Order Payment', 'wallet-management-for-woocommerce' ),
				'order_payment'          => esc_html__( 'Order Payment', 'wallet-management-for-woocommerce' ),
				'order_cancelled_credit' => esc_html__( 'Order Cancelled Credit', 'wallet-management-for-woocommerce' ),
				'order_cancelled_debit'  => esc_html__( 'Order Cancelled Debit', 'wallet-management-for-woocommerce' ),
				'order_refund'           => esc_html__( 'Order Refund', 'wallet-management-for-woocommerce' ),
				'wallet_topup'           => esc_html__( 'Wallet Topup', 'wallet-management-for-woocommerce' ),
				'cart_cashback'          => esc_html__( 'Cart Cashback', 'wallet-management-for-woocommerce' ),
				'topup_cashback'         => esc_html__( 'Topup Cashback', 'wallet-management-for-woocommerce' ),
				'transfer'               => esc_html__( 'Transfer', 'wallet-management-for-woocommerce' ),
				'wallet_transfer'        => esc_html__( 'Wallet Transfer', 'wallet-management-for-woocommerce' ),
				'registration_credit'    => esc_html__( 'Registration Credit', 'wallet-management-for-woocommerce' ),
			];

			$transaction_translation = apply_filters( 'ddwcwm_modify_transaction_translations', $transaction_translation );

			$result_translation = ! empty( $transaction_translation[ $static_word ] ) ? $transaction_translation[ $static_word ] : $static_word;

			return apply_filters( 'ddwcwm_modify_result_transaction_translation', $result_translation );
		}

		/**
		 * Delete Transaction function
		 * 
		 * @param int $transaction_id
		 * @return int|bool
		 */
		public function ddwcwm_delete_transaction( $transaction_id ) {
			return $this->wpdb->delete(
				$this->transaction_table,
				[
					'id' => $transaction_id
				],
                [ '%d' ]
            );
		}
	}
}
