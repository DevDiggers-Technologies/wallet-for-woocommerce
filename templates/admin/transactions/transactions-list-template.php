<?php
/**
 * Transactions List Template
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Transactions;

use DDWCWalletManagement\Helper\Transactions\DDWCWM_Transactions_Helper;
use DDWCWalletManagement\Helper\Error\DDWCWM_Error_Helper;

defined( 'ABSPATH' ) || exit();

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only admin transactions list: sort, pagination and date/type filter params, no state change.
if ( ! class_exists( 'DDWCWM_Transactions_List_Template' ) ) {
	/**
	 * Transactions list class
	 */
	class DDWCWM_Transactions_List_Template extends \WP_List_table {
		/**
		 * Error Helper Trait
		 */
		use DDWCWM_Error_Helper;

        /**
		 * Transaction Helper Variable
		 *
		 * @var object
		 */
		protected $transaction_helper;

		/**
		 * Class constructor
		 */
		public function __construct() {
			$this->transaction_helper = new DDWCWM_Transactions_Helper();

			parent::__construct( [
				'singular' => esc_html__( 'Transaction List', 'devdiggers-wallet-for-woocommerce' ),
				'plural'   => esc_html__( 'Transactions List', 'devdiggers-wallet-for-woocommerce' ),
				'ajax'     => false,
			] );
		}

		/**
		 * Prepare Items
		 *
		 * @return void
		 */
		public function prepare_items() {
			$this->_column_headers = $this->get_column_info();

			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

            if ( strpos( $request_uri, '_wp_http_referer' ) !== false ) {
                $new_url = remove_query_arg( [ '_wp_http_referer', '_wpnonce' ], $request_uri );
                wp_safe_redirect( $new_url );
                exit();
            }

			$this->process_bulk_action();
			$this->process_row_action();

			$per_page     = $this->get_items_per_page( 'transactions_per_page', 20 );
			$current_page = $this->get_pagenum();
			$total_items  = $this->transaction_helper->ddwcwm_get_all_transactions_count();

			$this->set_pagination_args( [
				'total_items' => $total_items,
				'per_page'    => $per_page,
			] );

			$data = self::ddwcwm_get_transactions( $per_page, $current_page );

			usort( $data, [ $this, 'usort_reorder' ] );

			$this->items = $data;
		}

		/**
		 * Process bulk actions
		 *
		 * @return void
		 */
		public function process_bulk_action() {
			if ( ! empty( $_GET[ 'ddwcwm_transactions_list_nonce' ] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_GET[ 'ddwcwm_transactions_list_nonce' ] ) );
				if ( wp_verify_nonce( $nonce, 'ddwcwm_transactions_list_nonce_action' ) ) {
					if ( 'delete' === $this->current_action() ) {
						if ( ! empty( $_GET[ 'ddwcwm-transaction-id' ] ) ) { // WPCS: input var ok.
							if ( is_array( $_GET[ 'ddwcwm-transaction-id' ] ) ) { // WPCS: input var ok.
								$transaction_ids = array_map( 'sanitize_text_field', wp_unslash( $_GET[ 'ddwcwm-transaction-id' ] ) ); // WPCS: input var ok.

								foreach ( $transaction_ids as $transaction_id ) {
									$this->transaction_helper->ddwcwm_delete_transaction( $transaction_id );
								}

								/* translators: %s: number of deleted transactions. */
								$message = sprintf( esc_html__( '%s Transaction(s) deleted successfully.', 'devdiggers-wallet-for-woocommerce' ), count( $transaction_ids ) );
								$this->ddwcwm_print_notification( $message, 'success' );
							}
						} else {
							$message = esc_html__( 'Select transactions(s) to delete.', 'devdiggers-wallet-for-woocommerce' );
							$this->ddwcwm_print_notification( $message, 'error' );
						}
					}
				} else {
					$message = esc_html__( 'Invalid nonce. Security check failed!!!', 'devdiggers-wallet-for-woocommerce' ) . implode( ',', $error_ids );
					$this->ddwcwm_print_notification( $message, 'error' );
				}
			}
		}

		/**
		 * Process row actions
		 *
		 * @return void
		 */
		public function process_row_action() {
			if ( ! empty( $_GET[ 'ddwcwm_transactions_list_nonce' ] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_GET[ 'ddwcwm_transactions_list_nonce' ] ) );
				if ( wp_verify_nonce( $nonce, 'ddwcwm_transactions_list_nonce_action' ) ) {
					if ( 'delete' === $this->current_action() ) {
						if ( ! empty( $_GET[ 'ddwcwm-transaction-id' ] ) && ! is_array( $_GET[ 'ddwcwm-transaction-id' ] ) ) { // WPCS: input var ok.
							$transaction_id = intval( wp_unslash( $_GET[ 'ddwcwm-transaction-id' ] ) ); // WPCS: input var ok.
							$this->transaction_helper->ddwcwm_delete_transaction( $transaction_id );

							$message = esc_html__( 'Transaction deleted successfully.', 'devdiggers-wallet-for-woocommerce' );
							$this->ddwcwm_print_notification( $message, 'success' );

						}
					}
				} else {
					$message = esc_html__( 'Invalid nonce. Security check failed!!!', 'devdiggers-wallet-for-woocommerce' ) . implode( ',', $error_ids );
					$this->ddwcwm_print_notification( $message, 'error' );
				}
			}
		}

		/**
		 * Usort
		 *
		 * @param int $first First value.
		 * @param int $second Second value.
		 * @return $result
		 */
		public function usort_reorder( $first, $second ){
			$orderby = ! empty( $_GET[ 'orderby' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'orderby' ] ) ) : 'id';
			$order   = ! empty( $_GET[ 'order' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'order' ] ) ) : 'desc';
			$result  = strnatcmp( $first[ $orderby ], $second[ $orderby ] );

			return $order === 'asc' ? $result : -$result;
		}

		/**
		 * Fetch Transactions
		 *
		 * @param int $per_page Per Page.
		 * @param int $current_page Page.
		 * @return array $transactions
		 */
		public function ddwcwm_get_transactions( $per_page, $current_page = 1 ) {
            $data         = [];
            $offset       = ( $current_page - 1 ) * $per_page;
            $transactions = $this->transaction_helper->ddwcwm_get_all_transactions( $per_page, $offset );

            if ( ! empty( $transactions ) ) {
				foreach ( $transactions as $key => $transaction ) {
					$transaction_id        = $transaction[ 'id' ];
					$customer_id           = $transaction[ 'user_id' ];
					$order_id              = $transaction[ 'order_id' ];
					$order                 = wc_get_order( $order_id );
					$order_currency        = $order ? $order->get_currency() : '';
					$order_currency        = apply_filters( 'ddwcwm_modify_transaction_currency', $order_currency, $order_id, $transaction );
					$customer              = get_user_by( 'ID', $customer_id );
					$transaction_type      = $transaction[ 'type' ];
					$transaction_reference = $transaction[ 'reference' ];

					ob_start();
					if ( $transaction_type == 'credit' || $transaction_reference == 'withdraw_cancelled' ) {
						?>
						<span class="ddwcwm-credit">+<?php echo wp_kses_post( wc_price( $transaction[ 'amount' ], [ 'currency' => $order_currency ] ) ) ?></span>
						<?php
					} else if ( $transaction_type == 'transfer' || $transaction_reference == 'withdraw_completed' ) {
						?>
						<span class="ddwcwm-complete"><?php echo wp_kses_post( wc_price( $transaction[ 'amount' ], [ 'currency' => $order_currency ] ) ) ?></span>
						<?php
					} else if ( $transaction_type == 'debit' || $transaction_type == 'withdraw' ) {
						?>
						<span class="ddwcwm-debit">-<?php echo wp_kses_post( wc_price( $transaction[ 'amount' ], [ 'currency' => $order_currency ] ) ) ?></span>
						<?php
					}
					$amount = ob_get_contents();
					ob_end_clean();

					if ( ! empty( $customer ) && is_object( $customer ) ) {
						$data[] = apply_filters( 'ddwcwm_modify_transactions_list_row_data', [
							'id'         => $transaction_id,
							'identification' => [
								'id'          => $transaction_id,
								'date'        => date_i18n( 'F d, Y g:i:s A', strtotime( $transaction[ 'date' ] ) ),
							],
							'customer'   => [
								'id'       => $customer_id,
								'username' => $customer->user_login,
								'email'    => $customer->user_email,
							],
							'amount'     => $amount,
							'transaction_context' => [
								'type'       => $this->transaction_helper->ddwcwm_get_transactions_translation( $transaction_type ),
								'type_slug'  => $transaction_type,
								'reference'  => $this->transaction_helper->ddwcwm_get_transactions_translation( $transaction_reference ),
								'related_id' => $order_id,
								'sender'     => $transaction[ 'sender_id' ],
							],
							'note'       => $transaction[ 'note' ],
						], $transaction );
					}
				}
			}

			return apply_filters( 'ddwcwm_transactions_list_data', $data );
		}

		/**
		 *  No items
		 *
		 * @return void
		 */
		public function no_items() {
			esc_html_e( 'No transactions avaliable.', 'devdiggers-wallet-for-woocommerce' );
		}

		/**
		 * Hidden Columns
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return [];
		}

		/**
		 *  Associative array of columns
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = [
				'cb'                  => '<input type="checkbox" />',
				'identification'      => esc_html__( 'ID & Date', 'devdiggers-wallet-for-woocommerce' ),
				'customer'            => esc_html__( 'Customer', 'devdiggers-wallet-for-woocommerce' ),
				'transaction_context' => esc_html__( 'Transaction Context', 'devdiggers-wallet-for-woocommerce' ),
				'amount'              => esc_html__( 'Amount', 'devdiggers-wallet-for-woocommerce' ),
				'note'                => esc_html__( 'Note', 'devdiggers-wallet-for-woocommerce' ),
			];

			return apply_filters( 'ddwcwm_transactions_list_columns', $columns );
		}

		/**
		 * Render a column when no column specific method exists.
		 *
		 * @param array  $item Items.
		 * @param string $column_name Name.
		 *
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {
			if ( array_key_exists( $column_name, $item ) ) {
				return $item[ $column_name ];
			}

			return '';
		}

		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = [
				'identification' => [ 'id', true ],
				'customer'       => [ 'customer', true ],
				'amount'         => [ 'amount', true ],
			];

			return apply_filters( 'ddwcwm_transactions_list_sortable_columns', $sortable_columns );
		}

		/**
		 * Column Identification
		 *
		 * @param array $item Item.
		 * @return string
		 */
		public function column_identification( $item ) {
			$identification = $item['identification'];
			$actions = [
				'delete' => sprintf( '<a href="%s">%s</a>', wp_nonce_url( 'admin.php?page=' . ( isset( $_REQUEST[ 'page' ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ 'page' ] ) ) : '' ) . '&action=delete&ddwcwm-transaction-id=' . esc_attr( $item[ 'id' ] ), 'ddwcwm_transactions_list_nonce_action', 'ddwcwm_transactions_list_nonce' ), esc_html__( 'Delete', 'devdiggers-wallet-for-woocommerce' ) ),
			];

			return sprintf(
				'<div class="ddwcwm-transaction-id-column">
					<div class="ddwcwm-transaction-id">#%1$d</div>
					<div class="ddwcwm-transaction-date">%2$s</div>
					%3$s
				</div>',
				$identification['id'],
				$identification['date'],
				$this->row_actions( apply_filters( 'ddwcwm_transactions_list_line_actions', $actions ) )
			);
		}

		public function column_transaction_context( $item ) {
			$context = $item['transaction_context'];
			$related_id_html = '-';

			if ( ! empty( $context['sender'] ) ) {
				$sender_id = $context['sender'];
				$sender = get_user_by( 'ID', $sender_id );
				if ( $sender ) {
					$sender_email = $sender->user_email . ' (#' . $sender_id . ')' ;
					$sender_url = admin_url( 'user-edit.php?user_id=' . $sender_id );
					/* translators: %s: linked user or order. */
					$related_id_html = sprintf( esc_html__( 'Sender: %s', 'devdiggers-wallet-for-woocommerce' ), '<a href="' . esc_url( $sender_url ) . '">' . esc_html( $sender_email ) . '</a>' );
				} else {
					/* translators: %d: user ID. */
					$related_id_html = sprintf( esc_html__( 'Sender: #%d', 'devdiggers-wallet-for-woocommerce' ), $sender_id );
				}
			} elseif ( ! empty( $context['related_id'] ) ) {
				$order_id = $context['related_id'];
				$order = wc_get_order( $order_id );
				if ( $order ) {
					$order_url = get_edit_post_link( $order_id );
					/* translators: %s: linked user or order. */
					$related_id_html = sprintf( esc_html__( 'Order: %s', 'devdiggers-wallet-for-woocommerce' ), '<a href="' . esc_url( $order_url ) . '">#' . esc_html( $order_id ) . '</a>' );
				}
			}

			$order_tier_html = '';
			if ( $related_id_html !== '-' ) {
				$order_tier_html = sprintf(
					'<div class="ddwcwm-order-tier">
						<span class="ddwcwm-label-mini">%1$s</span> %2$s
					</div>',
					esc_html__( 'Link:', 'devdiggers-wallet-for-woocommerce' ),
					$related_id_html
				);
			}

			return sprintf(
				'<div class="ddwcwm-transaction-context-column">
					<div class="ddwcwm-type-tier">
						<mark class="ddwcwm-status">%1$s</mark>
					</div>
					<div class="ddwcwm-reference-tier">
						<span class="ddwcwm-label-mini">%2$s</span> <em>%3$s</em>
					</div>
					%4$s
				</div>',
				esc_html( $context['type'] ),
				esc_html__( 'Ref:', 'devdiggers-wallet-for-woocommerce' ),
				! empty( $context['reference'] ) ? esc_html( $context['reference'] ) : '-',
				$order_tier_html
			);
		}

		/**
		 * Column Amount
		 *
		 * @param array $item Item.
		 * @return string
		 */
		public function column_amount( $item ) {
			return sprintf( '<div class="ddwcwm-amount-column">%s</div>', $item['amount'] );
		}

		/**
		 * Column Note
		 *
		 * @param array $item Item.
		 * @return string
		 */
		public function column_note( $item ) {
			return sprintf( '<div class="ddwcwm-note-column"><em>%s</em></div>', $item['note'] ? stripslashes( $item['note'] ) : esc_html__( 'N/A', 'devdiggers-wallet-for-woocommerce' ) );
		}

		/**
		 * Column Customer
		 *
		 * @param array $item Item.
		 * @return string
		 */
		public function column_customer( $item ) {
			$customer      = $item['customer'];
			$customer_id   = $customer['id'];
			$avatar        = get_avatar( $customer_id, 32 );

			return sprintf(
				'<div class="ddwcwm-user-column">%1$s <strong>%2$s</strong><br /><small>%3$s</small></div>',
				$avatar,
				$customer['username'],
				$customer['email']
			);
		}

		/**
		 * Render the bulk edit checkbox
		 *
		 * @param array $item Item.
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="ddwcwm-transaction-id[]" value="%d" />', esc_attr( $item[ 'id' ] ) );
		}

		/**
         * Bulk actions on list.
		 * 
		 * @return array
         */
        public function get_bulk_actions() {
            return apply_filters( 'ddwcwm_modify_bulk_actions_in_transactions', [
                'delete' => esc_html__( 'Delete', 'devdiggers-wallet-for-woocommerce' ),
			] );
        }

        /**
		 * Transactions List Filters
		 *
		 * @param string $which Position of filter.
		 */
		public function extra_tablenav( $which ) {
			$all_transaction_types = [
				''         => esc_html__( 'Transaction Type', 'devdiggers-wallet-for-woocommerce' ),
				'credit'   => esc_html__( 'Credit', 'devdiggers-wallet-for-woocommerce' ),
				'debit'    => esc_html__( 'Debit', 'devdiggers-wallet-for-woocommerce' ),
				'transfer' => esc_html__( 'Transfer', 'devdiggers-wallet-for-woocommerce' ),
				'withdraw' => esc_html__( 'Withdrawal', 'devdiggers-wallet-for-woocommerce' ),
			];

			$all_transaction_types = apply_filters( 'ddwcwm_modify_transaction_types_for_filter' , $all_transaction_types );

			if ( 'top' === $which ) {
                $transaction_type      = ! empty( $_GET[ 'ddwcwm-transaction-type' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'ddwcwm-transaction-type' ] ) ) : '';
                $transaction_from_date = ! empty( $_GET[ 'ddwcwm-transaction-from-date' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'ddwcwm-transaction-from-date' ] ) ) : '';
                $transaction_to_date   = ! empty( $_GET[ 'ddwcwm-transaction-to-date' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'ddwcwm-transaction-to-date' ] ) ) : '';
				?>
				<div class="alignleft actions bulkactions">
					<select name="ddwcwm-transaction-type" class="ddwcwm-transaction-type">
						<?php
						if ( ! empty( $all_transaction_types ) ) {
							foreach ( $all_transaction_types as $key => $value ) {

								$selected = $key == $transaction_type ? 'selected="selected"' : '';
								?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $value ); ?></option>
								<?php
							}
						}
						?>

					</select>

					<label for="ddwcwm-transaction-from-date">&nbsp;<?php esc_html_e( 'From:', 'devdiggers-wallet-for-woocommerce' ); ?></label>
					<input type="date" value="<?php echo esc_attr( $transaction_from_date ); ?>" name="ddwcwm-transaction-from-date" id="ddwcwm-transaction-from-date" class="ddwcwm-datepicker" placeholder="yyyy-mm-dd" autocomplete="off" />

					<label for="ddwcwm-transaction-to-date">&nbsp;<?php esc_html_e( 'To:', 'devdiggers-wallet-for-woocommerce' ); ?></label>
					<input type="date" value="<?php echo esc_attr( $transaction_to_date ); ?>" name="ddwcwm-transaction-to-date" id="ddwcwm-transaction-to-date" class="ddwcwm-datepicker" placeholder="yyyy-mm-dd" autocomplete="off" />

					<select name="customer-id" id="ddwcwm-users" class="regular-text ddfw-users" data-placeholder="<?php esc_attr_e( 'Select Customer', 'devdiggers-wallet-for-woocommerce' ); ?>">
						<?php
						if ( ! empty( $_GET[ 'customer-id' ] ) ) {
							$customer_id = absint( wp_unslash( $_GET[ 'customer-id' ] ) );
							$customer_data = get_userdata( $customer_id );
							$customer_option_value = "(#{$customer_id}) {$customer_data->user_login} <{$customer_data->user_email}>";
							?>
							<option value="<?php echo esc_attr( $customer_id ); ?>"><?php echo esc_html( $customer_option_value ); ?></option>
							<?php
						}
						?>
					</select>

					<input type="submit" value="<?php esc_html_e( 'Filter', 'devdiggers-wallet-for-woocommerce' ); ?>" name="transaction" class="button" />
					
					<?php
					if ( ! empty( $_GET['transaction'] ) || ! empty( $transaction_type ) || ! empty( $transaction_from_date ) || ! empty( $transaction_to_date ) || ! empty( $_GET['customer-id'] ) ) {
						$page = ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
						$menu = ! empty( $_GET['menu'] ) ? sanitize_text_field( wp_unslash( $_GET['menu'] ) ) : '';
						?>
						<a href="<?php echo esc_url( admin_url( "admin.php?page={$page}&menu={$menu}" ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'devdiggers-wallet-for-woocommerce' ); ?></a>
						<?php
					}
					?>
				</div>
				<?php
			}
		}

	}
}
