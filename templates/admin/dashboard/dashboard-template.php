<?php
/**
 * Dashboard Template
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Dashboard;

use DDWCWalletManagement\Helper\Dashboard\DDWCWM_Dashboard_Helper;

defined( 'ABSPATH' ) || exit();

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only dashboard page/menu nav params echoed into hidden filter-form fields, no state change.
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
		 * @var object
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
		 * @param array $ddwcwm_configuration
		 */
		public function __construct( $ddwcwm_configuration ) {
			$this->ddwcwm_configuration = $ddwcwm_configuration;
			$this->dashboard_helper      = new DDWCWM_Dashboard_Helper( $ddwcwm_configuration );
			$this->dashboard_data        = $this->dashboard_helper->get_dashboard_data();

			$this->render();
		}

		/**
		 * Render dashboard
		 *
		 * @return void
		 */
		protected function render() {
			// Enqueue dashboard specific scripts/styles
			wp_enqueue_script( 'ddwcwm-dashboard-script' );
			
			wp_localize_script(
				'ddwcwm-dashboard-script',
				'ddwcwmDashboardData',
				[
					'summary'             => $this->dashboard_data['summary'],
					'recent_activities'   => $this->dashboard_data['recent_activities'],
					'top_customers'       => $this->dashboard_data['top_customers'],
					'transactionsChart'   => $this->dashboard_data['charts']['transactions_chart'],
					'typeBreakdownChart'  => $this->dashboard_data['charts']['type_breakdown'],
					'dateRange'           => $this->dashboard_data['date_range'],
					'currency'            => get_woocommerce_currency_symbol(),
					'i18n'                => [
						'transactions'        => esc_html__( 'Transactions', 'wallet-management-for-woocommerce' ),
						'amount'              => esc_html__( 'Amount', 'wallet-management-for-woocommerce' ),
						'typeBreakdown'       => esc_html__( 'Transaction Types', 'wallet-management-for-woocommerce' ),
						'noData'              => esc_html__( 'No data available', 'wallet-management-for-woocommerce' ),
					]
				]
			);
			
			$current_user = wp_get_current_user();

			?>
			<div class="ddwcwm-dashboard">
				<div class="ddwcwm-dashboard-header">
					<div class="ddwcwm-header-top">
						<div class="ddwcwm-header-left">
							<div class="ddwcwm-welcome-section">
								<div class="ddwcwm-welcome-content">
									<div class="ddwcwm-admin-avatar">
										<img src="<?php echo esc_url( get_avatar_url( $current_user->ID, [ 'size' => 48 ] ) ); ?>" alt="<?php echo esc_attr( $current_user->display_name ); ?>" class="ddwcwm-avatar-image" />
									</div>
									<div class="ddwcwm-welcome-message">
										<h1>
				<?php
				/* translators: %s: current user display name. */
				printf( esc_html__( 'Welcome back, %s! 👋🏻', 'wallet-management-for-woocommerce' ), esc_html( $current_user->display_name ) );
				?>
			</h1>
										<p class="ddwcwm-welcome-subtitle"><?php esc_html_e( 'Here\'s what\'s happening with your customers\' wallets.', 'wallet-management-for-woocommerce' ); ?></p>
									</div>
								</div>
							</div>
						</div>

						<div class="ddwcwm-header-right">
							<div class="ddwcwm-dashboard-filters">
								<form method="get" class="ddwcwm-date-filter-form">
									<input type="hidden" name="page" value="<?php echo esc_attr( isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '' ); ?>" />
									<input type="hidden" name="menu" value="<?php echo esc_attr( isset( $_GET['menu'] ) ? sanitize_text_field( wp_unslash( $_GET['menu'] ) ) : '' ); ?>" />

									<div class="ddwcwm-date-range-container">
										<input type="text"
											id="ddwcwm-date-range-picker"
											class="ddwcwm-date-range-picker"
											value="<?php echo esc_attr( $this->dashboard_data['date_range']['label'] ); ?>"
											readonly />

										<div class="ddwcwm-date-range-dropdown" id="ddwcwm-date-range-dropdown">
											<div class="ddwcwm-dropdown-content">
												<div class="ddwcwm-date-presets">
													<div class="ddwcwm-presets-header">
														<h4><?php esc_html_e( 'Quick Select', 'wallet-management-for-woocommerce' ); ?></h4>
													</div>
													<button type="button" class="ddwcwm-date-preset" data-range="today"><?php esc_html_e( 'Today', 'wallet-management-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcwm-date-preset" data-range="7_days"><?php esc_html_e( 'This Week', 'wallet-management-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcwm-date-preset" data-range="last_week"><?php esc_html_e( 'Last Week', 'wallet-management-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcwm-date-preset" data-range="30_days"><?php esc_html_e( 'This Month', 'wallet-management-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcwm-date-preset" data-range="last_month"><?php esc_html_e( 'Last Month', 'wallet-management-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcwm-date-preset" data-range="90_days"><?php esc_html_e( 'Last 3 Months', 'wallet-management-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcwm-date-preset" data-range="180_days"><?php esc_html_e( 'Last 6 Months', 'wallet-management-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcwm-date-preset" data-range="year_to_date"><?php esc_html_e( 'Year to Date', 'wallet-management-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcwm-date-preset" data-range="last_year"><?php esc_html_e( 'Last Year', 'wallet-management-for-woocommerce' ); ?></button>
													<button type="button" class="ddwcwm-date-preset" data-range="all_time"><?php esc_html_e( 'All Time', 'wallet-management-for-woocommerce' ); ?></button>
												</div>

												<div class="ddwcwm-custom-date-range">
													<div class="ddwcwm-custom-header">
														<h4><?php esc_html_e( 'Custom Range', 'wallet-management-for-woocommerce' ); ?></h4>
														<p><?php esc_html_e( 'Select specific start and end dates for your analysis', 'wallet-management-for-woocommerce' ); ?></p>
													</div>
													<div class="ddwcwm-date-inputs">
														<div class="ddwcwm-date-input-group">
															<label for="ddwcwm-from-date"><?php esc_html_e( 'From Date', 'wallet-management-for-woocommerce' ); ?></label>
															<input type="date" name="from_date" id="ddwcwm-from-date" value="<?php echo esc_attr( $this->dashboard_data['date_range']['from'] ); ?>" />
														</div>
														<div class="ddwcwm-date-input-group">
															<label for="ddwcwm-to-date"><?php esc_html_e( 'To Date', 'wallet-management-for-woocommerce' ); ?></label>
															<input type="date" name="to_date" id="ddwcwm-to-date" value="<?php echo esc_attr( $this->dashboard_data['date_range']['to'] ); ?>" />
														</div>
													</div>
													<button type="button" class="ddwcwm-apply-custom-range button button-primary"><?php esc_html_e( 'Apply Custom Range', 'wallet-management-for-woocommerce' ); ?></button>
												</div>
											</div>
										</div>

										<input type="hidden" name="date_range" id="ddwcwm-selected-range" value="<?php echo esc_attr( $this->dashboard_data['date_range']['type'] ); ?>" />
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

				<div class="ddwcwm-dashboard-content">
					<div class="ddwcwm-dashboard-top-section">
						<div class="ddwcwm-summary-cards">
							<?php
							$this->render_summary_card(
								esc_html__( 'Available Balance', 'wallet-management-for-woocommerce' ),
								wc_price( $this->dashboard_data['summary']['total_balance']['value'] ),
								$this->dashboard_data['summary']['total_balance']['change'],
								$this->dashboard_data['summary']['total_balance']['is_positive'],
								\DevDiggers\Framework\Includes\DDFW_SVG::get_svg_icon( 'wallet_balance' ),
								'html'
							);
							
							$this->render_summary_card(
								esc_html__( 'Wallet Spent', 'wallet-management-for-woocommerce' ),
								wc_price( $this->dashboard_data['summary']['wallet_spent']['value'] ),
								$this->dashboard_data['summary']['wallet_spent']['change'],
								$this->dashboard_data['summary']['wallet_spent']['is_positive'],
								\DevDiggers\Framework\Includes\DDFW_SVG::get_svg_icon( 'wallet_spent' ),
								'html'
							);

							$this->render_summary_card(
								esc_html__( 'Total Transactions', 'wallet-management-for-woocommerce' ),
								$this->dashboard_data['summary']['total_transactions']['value'],
								$this->dashboard_data['summary']['total_transactions']['change'],
								$this->dashboard_data['summary']['total_transactions']['is_positive'],
								\DevDiggers\Framework\Includes\DDFW_SVG::get_svg_icon( 'transactions' )
							);

							$this->render_summary_card(
								esc_html__( 'Total Users', 'wallet-management-for-woocommerce' ),
								$this->dashboard_data['summary']['total_users'],
								0,
								true,
								\DevDiggers\Framework\Includes\DDFW_SVG::get_svg_icon( 'users' )
							);

							$this->render_summary_card(
								esc_html__( 'Total Cashback Awarded', 'wallet-management-for-woocommerce' ),
								wc_price( $this->dashboard_data['summary']['total_cashback']['value'] ),
								$this->dashboard_data['summary']['total_cashback']['change'],
								$this->dashboard_data['summary']['total_cashback']['is_positive'],
								\DevDiggers\Framework\Includes\DDFW_SVG::get_svg_icon( 'cashback' ),
								'html'
							);

							// Referral Earnings and Total Withdrawals summary cards are Pro-only.
							?>
						</div>
					</div>

					<div class="ddwcwm-dashboard-charts-section">
						<div class="ddwcwm-chart-container">
							<h3>
								<?php esc_html_e( 'Transaction Trends', 'wallet-management-for-woocommerce' ); ?>
								<span class="ddwcwm-chart-subtitle"><?php echo esc_html( $this->dashboard_data['date_range']['label'] ); ?></span>
							</h3>
							<div class="ddwcwm-chart-wrapper">
								<canvas id="ddwcwm-transactions-chart"></canvas>
							</div>
						</div>

						<div class="ddwcwm-chart-container">
							<h3>
								<?php esc_html_e( 'Transaction Types Breakdown', 'wallet-management-for-woocommerce' ); ?>
								<span class="ddwcwm-chart-subtitle"><?php echo esc_html( $this->dashboard_data['date_range']['label'] ); ?></span>
							</h3>
							<div class="ddwcwm-chart-wrapper">
								<canvas id="ddwcwm-type-breakdown-chart"></canvas>
							</div>
						</div>
					</div>

					<div class="ddwcwm-dashboard-tables-section">
						<div class="ddwcwm-dashboard-col">
							<div class="ddwcwm-table-widget">
								<h3><?php esc_html_e( 'Recent Activities', 'wallet-management-for-woocommerce' ); ?></h3>
								<div class="ddwcwm-widget-content">
									<?php $this->render_recent_activities_list(); ?>
								</div>
							</div>
						</div>
						<div class="ddwcwm-dashboard-col">
							<div class="ddwcwm-table-widget">
								<h3><?php esc_html_e( 'Top Customers', 'wallet-management-for-woocommerce' ); ?></h3>
								<div class="ddwcwm-widget-content">
									<?php $this->render_top_customers_list(); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Render summary card
		 */
		protected function render_summary_card( $title, $value, $change, $is_positive, $icon, $value_type = 'number' ) {
			?>
			<div class="ddwcwm-summary-card">
				<div class="ddwcwm-card-header">
					<div class="ddwcwm-card-icon"><?php echo wp_kses( $icon, array_merge( wp_kses_allowed_html( 'post' ), function_exists( 'ddfw_kses_allowed_svg_tags' ) ? ddfw_kses_allowed_svg_tags() : [] ) ); ?></div>
					<?php if ( 0 !== (int) $change ) : ?>
						<div class="ddwcwm-change-indicator <?php echo esc_attr( $is_positive ? 'positive' : 'negative' ); ?>">
							<?php if ( $is_positive ) : ?>
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23 6l-9.5 9.5-5-5L1 18M17 6h6v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							<?php else : ?>
								<svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23 18l-9.5-9.5-5 5L1 6M17 18h6v-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							<?php endif; ?>
							<span><?php echo esc_html( $change ); ?>%</span>
						</div>
					<?php endif; ?>
				</div>
				<div class="ddwcwm-card-content">
					<h4><?php echo esc_html( $title ); ?></h4>
					<div class="ddwcwm-card-value">
						<?php if ( 'html' === $value_type ) : ?>
							<span class="ddwcwm-value-text"><?php echo wp_kses_post( $value ); ?></span>
						<?php else : ?>
							<span class="ddwcwm-value-number"><?php echo esc_html( is_numeric( $value ) ? number_format( $value ) : $value ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php
		}

		/**
		 * Render recent activities list
		 */
		protected function render_recent_activities_list() {
			$activities = $this->dashboard_data['recent_activities'];
			if ( empty( $activities ) ) {
				echo '<div class="ddwcwm-empty-state">' . esc_html__( 'No recent activities found.', 'wallet-management-for-woocommerce' ) . '</div>';
				return;
			}
			?>
			<div class="ddwcwm-activities-list">
				<?php foreach ( $activities as $activity ) : ?>
					<div class="ddwcwm-activity-item">
						<div class="ddwcwm-activity-info">
							<div class="ddwcwm-activity-title">
								<strong><?php echo esc_html( $activity['display_name'] ); ?></strong>
							</div>
							<div class="ddwcwm-activity-action">
								<?php echo esc_html( \DDWCWalletManagement\Helper\Transactions\DDWCWM_Transactions_Helper::ddwcwm_get_transactions_translation( $activity['reference'] ) ); ?>
								<span class="ddwcwm-activity-date">
									• <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $activity['date'] ) ) ); ?>
								</span>
							</div>
						</div>
						<div class="ddwcwm-activity-amount">
							<span class="ddwcwm-amount-value <?php echo $activity['amount'] >= 0 ? 'positive' : 'negative'; ?>">
								<?php echo esc_html( $activity['amount'] > 0 ? '+' : '' ) . wp_kses_post( wc_price( $activity['amount'] ) ); ?>
							</span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}

		/**
		 * Render top customers list
		 */
		protected function render_top_customers_list() {
			$customers = $this->dashboard_data['top_customers'];
			if ( empty( $customers ) ) {
				echo '<div class="ddwcwm-empty-state">' . esc_html__( 'No customers found.', 'wallet-management-for-woocommerce' ) . '</div>';
				return;
			}
			?>
			<div class="ddwcwm-customers-list">
				<?php foreach ( $customers as $customer ) : ?>
					<div class="ddwcwm-customer-item">
						<div class="ddwcwm-customer-avatar">
							<img src="<?php echo esc_url( get_avatar_url( $customer['ID'], [ 'size' => 40 ] ) ); ?>" alt="" />
						</div>
						<div class="ddwcwm-customer-info">
							<div class="ddwcwm-customer-name"><?php echo esc_html( $customer['display_name'] ); ?></div>
							<div class="ddwcwm-customer-email"><?php echo esc_html( $customer['user_email'] ); ?></div>
						</div>
						<div class="ddwcwm-customer-balance">
							<span class="ddwcwm-balance-label"><?php esc_html_e( 'Balance', 'wallet-management-for-woocommerce' ); ?></span>
							<span class="ddwcwm-balance-value"><?php echo wp_kses_post( wc_price( $customer['balance'] ) ); ?></span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		}
	}
}
