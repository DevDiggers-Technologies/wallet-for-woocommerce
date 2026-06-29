<?php
/**
 * Dashboard Helper
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Helper\Dashboard;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Dashboard_Helper' ) ) {
	/**
	 * Dashboard Helper Class
	 */
	class DDWCWM_Dashboard_Helper {
		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcwm_configuration;

		/**
		 * Date Range
		 *
		 * @var array
		 */
		protected $date_range;

		/**
		 * Construct
		 *
		 * @param array $ddwcwm_configuration
		 */
		public function __construct( $ddwcwm_configuration ) {
			$this->ddwcwm_configuration = $ddwcwm_configuration;
			$this->date_range           = $this->get_date_range();
		}

		/**
		 * Get date range from request
		 * 
		 * @return array
		 */
		protected function get_date_range() {
			$range = \DevDiggers\Framework\Includes\DDFW_Dashboard_Data::get_date_range();

			// Preserve the legacy 'type' alias used internally by this helper.
			$range['type'] = $range['key'];

			return $range;
		}

		/**
		 * Get dashboard data
		 *
		 * @return array
		 */
		public function get_dashboard_data() {
			return [
				'summary' => [
					'total_balance'      => $this->get_total_wallet_balance(),
					'total_transactions' => $this->get_total_transactions(),
					'total_users'        => $this->get_total_users_with_balance(),
					'wallet_spent'       => $this->get_total_wallet_spent(),
					'total_cashback'     => $this->get_total_cashback_awarded(),
				],
				'charts' => [
					'transactions_chart' => $this->get_transactions_chart_data(),
					'type_breakdown'     => $this->get_transaction_type_breakdown(),
				],
				'recent_activities' => $this->get_recent_activities( 5 ),
				'top_customers'     => $this->get_top_customers_by_balance( 5 ),
				'date_range'        => $this->date_range,
			];
		}

		/**
		 * Get total wallet balance across all users
		 *
		 * @return array
		 */
		protected function get_total_wallet_balance() {
			global $wpdb;
			$total = $wpdb->get_var( "SELECT SUM( CAST( meta_value AS DECIMAL(10,2) ) ) FROM {$wpdb->usermeta} WHERE meta_key = '_ddwcwm_wallet_balance'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			
			return [
				'value'       => (float) $total,
				'change'      => 0, // Balance is current state, no period comparison usually
				'is_positive' => true,
			];
		}

		/**
		 * Get total transactions in period
		 *
		 * @return array
		 */
		protected function get_total_transactions() {
			global $wpdb;
			$table = $wpdb->prefix . 'ddwcwm_transactions';
			
			$current_count = $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT COUNT(*) FROM %i WHERE date >= %s AND date <= %s", $table,				$this->date_range['from'] . ' 00:00:00',
				$this->date_range['to'] . ' 23:59:59'
			) );

			// Previous period
			$days_diff = ( strtotime( $this->date_range['to'] ) - strtotime( $this->date_range['from'] ) ) / ( 60 * 60 * 24 );
			$prev_from = gmdate( 'Y-m-d', strtotime( $this->date_range['from'] . ' -' . ceil( $days_diff + 1 ) . ' days' ) );
			$prev_to   = gmdate( 'Y-m-d', strtotime( $this->date_range['from'] . ' -1 day' ) );

			$prev_count = $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT COUNT(*) FROM %i WHERE date >= %s AND date <= %s", $table,				$prev_from . ' 00:00:00',
				$prev_to . ' 23:59:59'
			) );

			$change = $prev_count > 0 ? ( ( $current_count - $prev_count ) / $prev_count ) * 100 : 0;

			return [
				'value'       => (int) $current_count,
				'change'      => round( $change, 1 ),
				'is_positive' => $change >= 0,
			];
		}

		/**
		 * Get total users with any balance
		 *
		 * @return int
		 */
		protected function get_total_users_with_balance() {
			global $wpdb;
			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = '_ddwcwm_wallet_balance' AND CAST( meta_value AS DECIMAL(10,2) ) > 0" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		/**
		 * Get total cashback awarded in period
		 *
		 * @return array
		 */
		protected function get_total_cashback_awarded() {
			global $wpdb;
			$table        = $wpdb->prefix . 'ddwcwm_transactions';
			// Free awards cart-total cashback only.
			$refs         = [ 'cart_cashback' ];
			$placeholders = implode( ', ', array_fill( 0, count( $refs ), '%s' ) );

			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- $placeholders is a list of %s, $table is internal.
			$current_total = $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT SUM(amount) FROM %i WHERE type = 'credit' AND reference IN ($placeholders) AND date >= %s AND date <= %s", array_merge( [ $table ], $refs, [ $this->date_range['from'] . ' 00:00:00', $this->date_range['to'] . ' 23:59:59' ] )
			) );

			$days_diff = ( strtotime( $this->date_range['to'] ) - strtotime( $this->date_range['from'] ) ) / ( 60 * 60 * 24 );
			$prev_from = gmdate( 'Y-m-d', strtotime( $this->date_range['from'] . ' -' . ceil( $days_diff + 1 ) . ' days' ) );
			$prev_to   = gmdate( 'Y-m-d', strtotime( $this->date_range['from'] . ' -1 day' ) );

			$prev_total = $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT SUM(amount) FROM %i WHERE type = 'credit' AND reference IN ($placeholders) AND date >= %s AND date <= %s", array_merge( [ $table ], $refs, [ $prev_from . ' 00:00:00', $prev_to . ' 23:59:59' ] )
			) );
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber

			$change = $prev_total > 0 ? ( ( $current_total - $prev_total ) / $prev_total ) * 100 : 0;

			return [
				'value'       => (float) $current_total,
				'change'      => round( $change, 1 ),
				'is_positive' => $change >= 0,
			];
		}

		/**
		 * Get total wallet spent in period
		 *
		 * @return array
		 */
		protected function get_total_wallet_spent() {
			global $wpdb;
			$table = $wpdb->prefix . 'ddwcwm_transactions';
			
			$current_total = $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT SUM(amount) FROM %i WHERE type = 'debit' AND date >= %s AND date <= %s", $table,				$this->date_range['from'] . ' 00:00:00',
				$this->date_range['to'] . ' 23:59:59'
			) );

			// Previous period
			$days_diff = ( strtotime( $this->date_range['to'] ) - strtotime( $this->date_range['from'] ) ) / ( 60 * 60 * 24 );
			$prev_from = gmdate( 'Y-m-d', strtotime( $this->date_range['from'] . ' -' . ceil( $days_diff + 1 ) . ' days' ) );
			$prev_to   = gmdate( 'Y-m-d', strtotime( $this->date_range['from'] . ' -1 day' ) );

			$prev_total = $wpdb->get_var( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT SUM(amount) FROM %i WHERE type = 'debit' AND date >= %s AND date <= %s", $table,				$prev_from . ' 00:00:00',
				$prev_to . ' 23:59:59'
			) );

			$change = $prev_total > 0 ? ( ( $current_total - $prev_total ) / $prev_total ) * 100 : 0;

			return [
				'value'       => (float) $current_total,
				'change'      => round( $change, 1 ),
				'is_positive' => $change >= 0,
			];
		}

		/**
		 * Get recent activities
		 *
		 * @param int $limit
		 * @return array
		 */
		protected function get_recent_activities( $limit = 10 ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ddwcwm_transactions';
			
			$results = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT t.*, u.display_name, u.user_email
				 FROM %i t
				 LEFT JOIN %i u ON t.user_id = u.ID
				 ORDER BY t.date DESC LIMIT %d",
				$table,
				$wpdb->users,
				$limit
			) );

			$data = [];
			foreach ( $results as $row ) {
				$data[] = [
					'id'           => $row->id,
					'display_name' => $row->display_name ?: __( 'Guest', 'devdiggers-wallet-for-woocommerce' ),
					'user_email'   => $row->user_email,
					'amount'       => (float) $row->amount,
					'type'         => $row->type,
					'date'         => $row->date,
					'note'         => $row->note,
					'reference'    => $row->reference,
				];
			}

			return $data;
		}

		/**
		 * Get top customers by wallet balance
		 *
		 * @param int $limit
		 * @return array
		 */
		public function get_top_customers_by_balance( $limit = 5 ) {
			global $wpdb;

			$customers = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT 
					u.ID,
					u.display_name,
					u.user_email,
					CAST( um.meta_value AS DECIMAL(10,2) ) as balance
				FROM {$wpdb->users} u
				INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = '_ddwcwm_wallet_balance'
				WHERE CAST( um.meta_value AS DECIMAL(10,2) ) > 0
				ORDER BY balance DESC
				LIMIT %d",
				$limit
			), ARRAY_A );

			return $customers ?: [];
		}

		/**
		 * Get transactions chart data
		 *
		 * @return array
		 */
		protected function get_transactions_chart_data() {
			global $wpdb;
			$table = $wpdb->prefix . 'ddwcwm_transactions';
			
			$from_date = $this->date_range['from'];
			$to_date   = $this->date_range['to'];
			$days_diff = ( strtotime( $to_date ) - strtotime( $from_date ) ) / ( 60 * 60 * 24 );

			$results = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT DATE(date) as day, COUNT(*) as count, SUM(amount) as amount FROM %i WHERE date >= %s AND date <= %s GROUP BY day ORDER BY day ASC", $table,				$from_date . ' 00:00:00',
				$to_date . ' 23:59:59'
			) );

			$data_by_date = [];
			foreach ( $results as $row ) {
				$data_by_date[ $row->day ] = [
					'date'   => $row->day,
					'count'  => (int) $row->count,
					'amount' => (float) $row->amount,
				];
			}

			// Fill gaps
			if ( $days_diff <= 90 ) {
				$current = $from_date;
				while ( $current <= $to_date ) {
					if ( ! isset( $data_by_date[ $current ] ) ) {
						$data_by_date[ $current ] = [
							'date'   => $current,
							'count'  => 0,
							'amount' => 0,
						];
					}
					$current = gmdate( 'Y-m-d', strtotime( $current . ' +1 day' ) );
				}
			}

			ksort( $data_by_date );
			return array_values( $data_by_date );
		}

		/**
		 * Get transaction type breakdown
		 *
		 * @return array
		 */
		protected function get_transaction_type_breakdown() {
			global $wpdb;
			$table = $wpdb->prefix . 'ddwcwm_transactions';
			
			$results = $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT type, COUNT(*) as count, SUM(amount) as amount FROM %i WHERE date >= %s AND date <= %s GROUP BY type ORDER BY count DESC", $table,				$this->date_range['from'] . ' 00:00:00',
				$this->date_range['to'] . ' 23:59:59'
			) );

			$data = [];
			foreach ( $results as $row ) {
				$data[] = [
					'type'   => $row->type,
					'count'  => (int) $row->count,
					'amount' => (float) $row->amount,
				];
			}

			return $data;
		}
	}
}
