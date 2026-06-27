<?php
/**
 * Dashboard Template
 *
 * Builds the dashboard configuration and renders it through the shared devdiggers-framework
 * dashboard builder ( DDFW_Dashboard ). The plugin only supplies data + config.
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Dashboard;

use DDWCWalletManagement\Helper\Dashboard\DDWCWM_Dashboard_Helper;
use DDWCWalletManagement\Helper\Transactions\DDWCWM_Transactions_Helper;
use DevDiggers\Framework\Includes\DDFW_SVG;
use DevDiggers\Framework\Includes\DDFW_Dashboard;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Dashboard_Template' ) ) {
	/**
	 * Dashboard template class
	 */
	class DDWCWM_Dashboard_Template {
		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcwm_configuration;

		/**
		 * Dashboard Helper Variable
		 *
		 * @var DDWCWM_Dashboard_Helper
		 */
		protected $dashboard_helper;

		/**
		 * Dashboard Data Variable
		 *
		 * @var array
		 */
		protected $dashboard_data;

		/**
		 * Construct
		 *
		 * @param array $ddwcwm_configuration Plugin configuration.
		 */
		public function __construct( $ddwcwm_configuration ) {
			$this->ddwcwm_configuration = $ddwcwm_configuration;

			if ( ! class_exists( '\\DevDiggers\\Framework\\Includes\\DDFW_Dashboard' ) ) {
				return;
			}

			$this->dashboard_helper = new DDWCWM_Dashboard_Helper( $ddwcwm_configuration );
			$this->dashboard_data   = $this->dashboard_helper->get_dashboard_data();

			$this->render();
		}

		/**
		 * Render dashboard via the shared framework builder.
		 *
		 * @return void
		 */
		protected function render() {
			$data       = $this->dashboard_data;
			$summary    = $data['summary'];
			$date_label = $data['date_range']['label'];

			$type_breakdown = [];
			foreach ( $data['charts']['type_breakdown'] as $row ) {
				$type_breakdown[] = [
					'label' => DDWCWM_Transactions_Helper::ddwcwm_get_transactions_translation( $row['type'] ),
					'value' => $row['count'],
				];
			}

			new DDFW_Dashboard(
				[
					'columns' => 5,
					'header'  => [
						/* translators: %s: current user display name. */
						'welcome'  => esc_html__( 'Welcome back, %s! 👋🏻', 'devdiggers-wallet-for-woocommerce' ),
						'subtitle' => esc_html__( 'Here\'s what\'s happening with your customers\' wallets.', 'devdiggers-wallet-for-woocommerce' ),
					],
					'summary_cards' => [
						[
							'title'       => esc_html__( 'Available Balance', 'devdiggers-wallet-for-woocommerce' ),
							'value'       => wc_price( $summary['total_balance']['value'] ),
							'change'      => $summary['total_balance']['change'],
							'is_positive' => $summary['total_balance']['is_positive'],
							'value_type'  => 'html',
							'icon'        => DDFW_SVG::get_svg_icon( 'wallet_balance' ),
						],
						[
							'title'       => esc_html__( 'Wallet Spent', 'devdiggers-wallet-for-woocommerce' ),
							'value'       => wc_price( $summary['wallet_spent']['value'] ),
							'change'      => $summary['wallet_spent']['change'],
							'is_positive' => $summary['wallet_spent']['is_positive'],
							'value_type'  => 'html',
							'icon'        => DDFW_SVG::get_svg_icon( 'wallet_spent' ),
						],
						[
							'title'       => esc_html__( 'Total Transactions', 'devdiggers-wallet-for-woocommerce' ),
							'value'       => $summary['total_transactions']['value'],
							'change'      => $summary['total_transactions']['change'],
							'is_positive' => $summary['total_transactions']['is_positive'],
							'icon'        => DDFW_SVG::get_svg_icon( 'transactions' ),
						],
						[
							'title' => esc_html__( 'Total Users', 'devdiggers-wallet-for-woocommerce' ),
							'value' => $summary['total_users'],
							'icon'  => DDFW_SVG::get_svg_icon( 'users' ),
						],
						[
							'title'       => esc_html__( 'Cashback Awarded', 'devdiggers-wallet-for-woocommerce' ),
							'value'       => wc_price( $summary['total_cashback']['value'] ),
							'change'      => $summary['total_cashback']['change'],
							'is_positive' => $summary['total_cashback']['is_positive'],
							'value_type'  => 'html',
							'icon'        => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M19 5L5 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="7" cy="7" r="2" stroke="currentColor" stroke-width="2"/><circle cx="17" cy="17" r="2" stroke="currentColor" stroke-width="2"/></svg>',
						],
					],
					'charts' => [
						[
							'id'         => 'transactions',
							'title'      => esc_html__( 'Transaction Trends', 'devdiggers-wallet-for-woocommerce' ),
							'date_label' => $date_label,
							'type'       => 'line',
							'data'       => $data['charts']['transactions_chart'],
							'x_key'      => 'date',
							'series'     => [
								[ 'key' => 'count', 'label' => esc_html__( 'Transactions', 'devdiggers-wallet-for-woocommerce' ), 'color' => '#0256ff' ],
							],
							'empty'      => [
								'title' => esc_html__( 'No transaction data', 'devdiggers-wallet-for-woocommerce' ),
								'desc'  => esc_html__( 'Transaction trends will appear here once wallets are used.', 'devdiggers-wallet-for-woocommerce' ),
							],
						],
						[
							'id'        => 'type-breakdown',
							'title'     => esc_html__( 'Transaction Types Breakdown', 'devdiggers-wallet-for-woocommerce' ),
							'date_label' => $date_label,
							'type'      => 'doughnut',
							'data'      => $type_breakdown,
							'label_key' => 'label',
							'value_key' => 'value',
							'empty'     => [
								'title' => esc_html__( 'No transaction data', 'devdiggers-wallet-for-woocommerce' ),
								'desc'  => esc_html__( 'The transaction type split will appear here.', 'devdiggers-wallet-for-woocommerce' ),
							],
						],
					],
					'widgets' => [
						[
							'title'  => esc_html__( 'Recent Activities', 'devdiggers-wallet-for-woocommerce' ),
							'render' => [ $this, 'render_recent_activities' ],
						],
						[
							'title'  => esc_html__( 'Top Customers', 'devdiggers-wallet-for-woocommerce' ),
							'render' => [ $this, 'render_top_customers' ],
						],
					],
				]
			);
		}

		/**
		 * Render the recent activities widget body.
		 *
		 * @return void
		 */
		public function render_recent_activities() {
			$activities = $this->dashboard_data['recent_activities'];

			if ( empty( $activities ) ) {
				?>
				<div class="ddfw-dash-no-data"><?php esc_html_e( 'No recent activities found.', 'devdiggers-wallet-for-woocommerce' ); ?></div>
				<?php
				return;
			}
			?>
			<div class="ddfw-dash-list">
				<?php foreach ( $activities as $activity ) : ?>
					<div class="ddfw-dash-list-item">
						<div class="ddfw-dash-list-info">
							<div class="ddfw-dash-list-title"><strong><?php echo esc_html( $activity['display_name'] ); ?></strong></div>
							<div class="ddfw-dash-list-meta">
								<?php
								echo esc_html( DDWCWM_Transactions_Helper::ddwcwm_get_transactions_translation( $activity['reference'] ) );
								echo ' • ';
								echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $activity['date'] ) ) );
								if ( ! empty( $activity['expiry_date'] ) ) {
									echo ' • ';
									/* translators: %s: expiry date. */
									printf( esc_html__( 'Expires: %s', 'devdiggers-wallet-for-woocommerce' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $activity['expiry_date'] ) ) ) );
								}
								?>
							</div>
						</div>
						<div class="ddfw-dash-list-value <?php echo esc_attr( $activity['amount'] >= 0 ? 'positive' : 'negative' ); ?>">
							<?php echo ( $activity['amount'] > 0 ? '+' : '' ) . wp_kses_post( wc_price( $activity['amount'] ) ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}

		/**
		 * Render the top customers widget body.
		 *
		 * @return void
		 */
		public function render_top_customers() {
			$customers = $this->dashboard_data['top_customers'];

			if ( empty( $customers ) ) {
				?>
				<div class="ddfw-dash-no-data"><?php esc_html_e( 'No customers found.', 'devdiggers-wallet-for-woocommerce' ); ?></div>
				<?php
				return;
			}
			?>
			<div class="ddfw-dash-list">
				<?php foreach ( $customers as $customer ) : ?>
					<div class="ddfw-dash-list-item">
						<div class="ddfw-dash-list-avatar"><?php echo get_avatar( $customer['ID'], 32 ); ?></div>
						<div class="ddfw-dash-list-info">
							<div class="ddfw-dash-list-title"><strong><?php echo esc_html( $customer['display_name'] ); ?></strong></div>
							<div class="ddfw-dash-list-meta"><?php echo esc_html( $customer['user_email'] ); ?></div>
						</div>
						<div class="ddfw-dash-list-value"><?php echo wp_kses_post( wc_price( $customer['balance'] ) ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}
	}
}
