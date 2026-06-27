<?php
/**
 * Users List Template
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Users;

use DDWCWalletManagement\Helper\Users\DDWCWM_Users_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Users_List_Template' ) ) {

	/**
	 * Users list class
	 */
	class DDWCWM_Users_List_Template extends \WP_List_table {
		/**
		 * Users Helper Variable
		 *
		 * @var object
		 */
		protected $user_helper;

		/**
		 * Balance Variable
		 *
		 * @var string
		 */
		protected $balance;

		/**
		 * Class constructor
		 */
		public function __construct() {
            $this->user_helper = new DDWCWM_Users_Helper();

			parent::__construct( [
				'singular' => esc_html__( 'User List', 'devdiggers-wallet-for-woocommerce' ),
				'plural'   => esc_html__( 'Users List', 'devdiggers-wallet-for-woocommerce' ),
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

            // phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value, WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Read-only admin list-table; meta_key sort by wallet balance is intentional.
            $search        = ! empty( $_GET[ 's' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 's' ] ) ) : '';
            $this->balance = ! empty( $_GET[ 'balance' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'balance' ] ) ) : '';

			$per_page     = $this->get_items_per_page( 'users_per_page', 20 );
			$current_page = $this->get_pagenum();

			$off  = ( $current_page - 1 ) * $per_page;

			$args = [
				'number'         => $per_page,
				'offset'         => $off,
				'order'          => ! empty( $_GET[ 'order' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'order' ] ) ) : 'DESC',
				'orderby'        => ! empty( $_GET[ 'orderby' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'orderby' ] ) ) : 'ID',
				'search'         => '*' . esc_attr( $search ) . '*',
				'search_columns' => [ 'user_nicename', 'ID', 'user_login', 'user_email' ],
				'fields'         => [ 'ID', 'user_login', 'user_email' ],
			];

			if ( ! empty( $args[ 'orderby' ] ) && 'balance' === $args[ 'orderby' ] ) {
				$args[ 'meta_key' ] = '_ddwcwm_wallet_balance';
				$args[ 'orderby' ]  = 'meta_value_num';
            }

			if ( ! empty( $this->balance ) ) {
				if ( 'yes' === $this->balance ) {
					$args[ 'meta_key' ]     = '_ddwcwm_wallet_balance';
					$args[ 'meta_value' ]   = 0;
					$args[ 'meta_compare' ] = '>';
				} else {
					$args[ 'meta_query' ] = [
						'relation' => 'OR',
						[
							'key'     => '_ddwcwm_wallet_balance',
							'value'   => 0,
							'compare' => '=',
						],
						[
							'key'     => '_ddwcwm_wallet_balance',
							'compare' => 'NOT EXISTS',
						],
					];
				}
			}

			$query = new \WP_User_Query( $args );
			$users = $query->get_results();

			$this->set_pagination_args( [
				'total_items' => $query->get_total(),
				'per_page'    => $per_page,
			] );

			wp_reset_postdata();

			$data = self::ddwcwm_get_users( $users );

			$this->items = $data;
			// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value, WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		/**
		 * Usort
		 *
		 * @param int $first First value.
		 * @param int $second Second value.
		 * @return $result
		 */
		public function usort_reorder( $first, $second ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list sort param.
			$orderby = ! empty( $_GET[ 'orderby' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'orderby' ] ) ) : 'id';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list sort param.
			$order   = ! empty( $_GET[ 'order' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'order' ] ) ) : 'asc';

			$result  = strnatcmp( $first[ $orderby ], $second[ $orderby ] );

			return 'asc' === $order ? $result : -$result;
		}

		/**
		 * Fetch Users
		 *
		 * @param array $users
		 * @return array $data
		 */
		public function ddwcwm_get_users( $users ) {
            $data = [];

            if ( ! empty( $users ) ) {
                foreach ( $users as $user ) {
					$user_id           = $user->ID;
					$balance           = $this->user_helper->ddwcwm_get_user_wallet_balance( $user_id );
					$transaction_count = $this->user_helper->ddwcwm_get_user_total_transactions_count( $user_id );

                    $data[] = [
                        'id'                => $user_id,
                        'username'          => $user->user_login,
                        'email'             => $user->user_email,
                        'balance'           => $balance,
						'transaction_count' => $transaction_count,
					];
                }
            }

			return apply_filters( 'ddwcwm_users_list_data', $data );
		}

		/**
		 *  No items
		 *
		 * @return void
		 */
		public function no_items() {
			esc_html_e( 'No users avaliable.', 'devdiggers-wallet-for-woocommerce' );
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
				'cb'           => '<input type="checkbox" />',
				'id'           => esc_html__( 'ID', 'devdiggers-wallet-for-woocommerce' ),
				'user'         => esc_html__( 'User', 'devdiggers-wallet-for-woocommerce' ),
				'balance'      => esc_html__( 'Balance', 'devdiggers-wallet-for-woocommerce' ),
				'transactions' => esc_html__( 'Transactions', 'devdiggers-wallet-for-woocommerce' ),
			];

			return apply_filters( 'ddwcwm_users_list_columns', $columns );
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
				'id'      => [ 'id', true ],
				'user'    => [ 'username', true ],
				'balance' => [ 'balance', true ],
			];

			return apply_filters( 'ddwcwm_users_list_sortable_columns', $sortable_columns );
		}

		/**
		 * Render the bulk edit checkbox
		 *
		 * @param array $item Item.
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="ddwcwm-id[]" value="%d" />', esc_attr( $item[ 'id' ] ) );
		}

		/**
		 * Column User Actions
		 *
		 * @param array $item Items.
		 * @return string
		 */
		public function column_user( $item ) {
			$user_id = $item['id'];
			$avatar  = get_avatar( $user_id, 32 );

			$actions = [
				'edit'        => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'user-edit.php?user_id=' . $user_id ) ), esc_html__( 'Edit', 'devdiggers-wallet-for-woocommerce' ) ),
			];

			return sprintf(
				'<div class="ddwcwm-user-column">%1$s <strong>%2$s</strong><br /><small>%3$s</small>%4$s</div>',
				$avatar,
				$item['username'],
				$item['email'],
				$this->row_actions( apply_filters( 'ddwcwm_users_list_row_actions', $actions ) )
			);
		}

		/**
		 * Column Balance
		 *
		 * @param array $item Item.
		 * @return string
		 */
		public function column_balance( $item ) {
			return wc_price( $item['balance'] );
		}

		/**
		 * Column Transactions
		 *
		 * @param array $item Item.
		 * @return string
		 */
		public function column_transactions( $item ) {
			$count = $item['transaction_count'];
			if ( $count > 0 ) {
				return sprintf(
					'<a href="%s">%d</a>',
					esc_url( admin_url( 'admin.php?page=ddwcwm-dashboard&menu=transactions&customer-id=' . $item['id'] ) ),
					$count
				);
			}
			return $count;
		}

		/**
		 * Filters function
		 *
		 * @param string $which Position of filter.
		 */
		public function extra_tablenav( $which ) {
			if ( 'top' === $which ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page/menu nav params.
				$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page/menu nav params.
				$menu = isset( $_GET['menu'] ) ? sanitize_text_field( wp_unslash( $_GET['menu'] ) ) : '';
				?>
				<div class="alignleft actions bulkactions">
					<select name="balance">
						<option value=""><?php esc_html_e( 'All Users', 'devdiggers-wallet-for-woocommerce' ); ?></option>
						<option value="yes" <?php echo esc_attr( 'yes' === $this->balance ? 'selected="selected"' : '' ); ?>><?php esc_html_e( 'Users having amount in wallet', 'devdiggers-wallet-for-woocommerce' ); ?></option>
						<option value="no" <?php echo esc_attr( 'no' === $this->balance ? 'selected="selected"' : '' ); ?>><?php esc_html_e( 'Users don\'t have amount in wallet', 'devdiggers-wallet-for-woocommerce' ); ?></option>
					</select>

					<input type="submit" value="<?php esc_attr_e( 'Filter', 'devdiggers-wallet-for-woocommerce' ); ?>" name="ddwcwm_filter_submit" class="button" />

					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page . '&menu=' . $menu ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Reset', 'devdiggers-wallet-for-woocommerce' ); ?></a>
				</div>
				<?php
			}
		}
	}
}
