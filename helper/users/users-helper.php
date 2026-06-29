<?php
/**
 * Users helper
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Helper\Users;

use DDWCWalletManagement\Helper\Error\DDWCWM_Error_Helper;
use DDWCWalletManagement\Helper\Transactions\DDWCWM_Transactions_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Users_Helper' ) ) {
	/**
	 * Users helper class
	 */
	class DDWCWM_Users_Helper {
		/**
		 * Error Trait
		 * 
		 * @var object
		 */
		use DDWCWM_Error_Helper;

		/**
		 * Database object
		 *
		 * @var object
		 */
		protected $wpdb;

		/**
		 * Global Wallet variable
		 *
		 * @var array
		 */
		protected $ddwcwm_wallet;

		/**
		 * Current user ID variable
		 *
		 * @var int
		 */
		protected $user_id;

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
			global $wpdb, $ddwcwm_wallet;
			$this->wpdb = $wpdb;
			$this->ddwcwm_wallet = $ddwcwm_wallet;
			$this->user_id = get_current_user_ID();

			$this->transaction_table = $this->wpdb->prefix . 'ddwcwm_transactions';
			$this->users_table = $this->wpdb->users;
		}

		/**
		 * Update user wallet amount
		 * 
		 * @param array $post_data
		 * @return void
		 */
		public function ddwcwm_update_user_wallet_amount( $post_data ) {
			$user_ids         = ! empty( $post_data[ 'ddwcwm_users' ] ) ? array_map( 'sanitize_text_field', $post_data[ 'ddwcwm_users' ] ) : [];
			$wallet_amount    = ! empty( $post_data[ 'ddwcwm_wallet_amount' ] ) ? sanitize_text_field( $post_data[ 'ddwcwm_wallet_amount' ] ) : 0;
			$action_type      = ! empty( $post_data[ 'ddwcwm_action_type' ] ) ? sanitize_text_field( $post_data[ 'ddwcwm_action_type' ] ) : '';
			$transaction_reason = ! empty( $post_data[ 'ddwcwm_reason' ] ) ? sanitize_text_field( $post_data[ 'ddwcwm_reason' ] ) : '';

			if ( ! empty( $user_ids ) && ! empty( $action_type ) && ! empty( $wallet_amount ) && $wallet_amount > 0 ) {
				$transaction_helper = new DDWCWM_Transactions_Helper();

				foreach ( $user_ids as $key => $user_id ) {
					$reference = '';
					$old_wallet_balance = $this->ddwcwm_get_user_wallet_balance( $user_id );

					if ( 'credit' === $action_type ) {
						$new_wallet_amount = $old_wallet_balance + $wallet_amount;
						$this->ddwcwm_set_user_wallet_balance( $new_wallet_amount, $user_id );

						$reference = 'manual_adjustment';

						$message = wc_price( $wallet_amount ) . ' ' . esc_html__( 'amount is credited to selected users(s) wallet balance.', 'devdiggers-wallet-for-woocommerce' );
					} else if ( 'debit' === $action_type && $old_wallet_balance >= $wallet_amount ) {
						$new_wallet_amount = $old_wallet_balance - $wallet_amount;
						$this->ddwcwm_set_user_wallet_balance( $new_wallet_amount, $user_id );

						$reference = 'manual_adjustment';

						$message = wc_price( $wallet_amount ) . ' ' . esc_html__( 'amount is debited from selected users(s) wallet balance.', 'devdiggers-wallet-for-woocommerce' );
					} else {
						$error = 1;
						$message = esc_html__( 'Insufficient amount to debit.', 'devdiggers-wallet-for-woocommerce' );
						$this->ddwcwm_print_notification( $message, 'error' );
					}

					if ( empty( $error ) ) {

						$data = [
							'type'      => $action_type,
							'amount'    => $wallet_amount,
							// 'sender_id' => get_current_user_id(),
							'user_id'   => $user_id,
							'note'      => $transaction_reason,
							'date'      => current_time( 'Y-m-d H:i:s' ),
							'reference' => $reference,
						];

						$transaction_helper->ddwcwm_save_transaction( $data );
					}

					do_action( 'ddwcwm_after_single_user_manual_credit_debit', $user_id, $post_data );
				}
			} else {
				$error = 1;
				$message = esc_html__( 'Some fields are empty or not valid.', 'devdiggers-wallet-for-woocommerce' );
				$this->ddwcwm_print_notification( $message, 'error' );
			}

			if ( empty( $error ) ) {
				$this->ddwcwm_print_notification( $message, 'success' );
			} else {
				$this->ddwcwm_print_notification( $message, 'error' );
			}
		}

		/**
		 * Get user wallet balance function
		 * 
		 * @param mixed $user_id
		 * @return float
		 */
		public function ddwcwm_get_user_wallet_balance( $user_id = '' ) {
			if ( ! empty( $user_id ) ) {
				$this->user_id = $user_id;
			}

			return apply_filters( 'ddwcwm_modify_user_wallet_balance', floatval( get_user_meta( $this->user_id, '_ddwcwm_wallet_balance', true ) ), $this->user_id );
		}

		/**
		 * Set user wallet balance function
		 * 
		 * @param mixed $user_id
		 * @return float
		 */
		public function ddwcwm_set_user_wallet_balance( $balance, $user_id = '' ) {
			if ( ! empty( $user_id ) ) {
				$this->user_id = $user_id;
			}

			$balance = apply_filters( 'ddwcwm_modify_wallet_balance_before_updating', $balance, $this->user_id );

			update_user_meta( $this->user_id, '_ddwcwm_wallet_balance', $balance );
		}

		/**
		 * Get User Transactions function
		 *
		 * @param int $per_page
		 * @param int $offset
		 * @param mixed $user_id
		 * @return array
		 */
		public function ddwcwm_get_user_transactions( $per_page, $offset, $user_id = '' ) {
			global $wpdb;

			if ( ! empty( $user_id ) ) {
				$this->user_id = $user_id;
			}

			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i as transactions LEFT JOIN %i as users ON (transactions.user_id=users.ID OR transactions.sender_id=users.ID) WHERE (transactions.user_id=%d OR transactions.sender_id=%d) GROUP BY transactions.id ORDER BY transactions.id DESC LIMIT %d OFFSET %d", $this->transaction_table, $this->users_table, $this->user_id, $this->user_id, absint( $per_page ), absint( $offset ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Get User Today Transaction amount function
		 *
		 * @param mixed $user_id
		 * @return array
		 */
		public function ddwcwm_get_user_today_transaction_amount( $user_id = '', $day = '' ) {
			global $wpdb;

			if ( ! empty( $user_id ) ) {
				$this->user_id = $user_id;
			}

			if ( empty( $day ) ) {
				$day = current_time( 'Y-m-d' );
			}

			return $wpdb->get_row( $wpdb->prepare( "SELECT SUM( CASE WHEN (type='credit' OR (type='transfer') AND user_id=%d) THEN amount ELSE 0 END ) as credit, SUM( CASE WHEN (type='debit' OR (type='transfer') AND sender_id=%d) THEN amount ELSE 0 END ) as debit FROM %i WHERE (sender_id=%d OR user_id=%d) AND DATE(date)=%s", $this->user_id, $this->user_id, $this->transaction_table, $this->user_id, $this->user_id, $day ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Get User Total Transactions count function
		 *
		 * @param mixed $user_id
		 * @return array
		 */
		public function ddwcwm_get_user_total_transactions_count( $user_id = '' ) {
			global $wpdb;

			if ( ! empty( $user_id ) ) {
				$this->user_id = $user_id;
			}

			return $wpdb->get_var( $wpdb->prepare( "SELECT DISTINCT count(transactions.id) FROM %i as transactions LEFT JOIN %i as users ON (transactions.user_id=users.ID OR transactions.sender_id=users.ID) WHERE (transactions.user_id=%d OR transactions.sender_id=%d)", $this->transaction_table, $this->users_table, $this->user_id, $this->user_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

	}
}
