<?php
/**
 * Manual Adjustment template class
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Manual_Adjustment;

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only admin page/menu nav params for display links, no state change.
if ( ! class_exists( 'DDWCWM_Manual_Adjustment_Template' ) ) {
	/**
	 * Manual Adjustment template class
	 */
	class DDWCWM_Manual_Adjustment_Template {
		/**
		 * Construct
		 */
		public function __construct() {
            ?>
			<div id="ddwcwm-manual-transaction-wizard">
                <?php do_action( 'ddwcwm_add_after_heading_in_manual_adjustment_page' ); ?>
                <hr class="wp-header-end" />

				<div class="ddwcwm-wizard">
					<!-- Generic Progress Bar Template -->
					<script type="text/html" id="tmpl-ddwcwm-progress-bar">
						<div class="ddwcwm-progress-wrap">
							<div class="ddwcwm-progress-bar-bg">
								<div class="ddwcwm-progress-bar" style="width:{{ data.percent }}%" data-percent="{{ data.percent }}"></div>
							</div>
							<div class="ddwcwm-progress-label">
								<span>{{ data.message || 'Processing...' }}</span>
								<span class="ddwcwm-progress-text">{{ data.processed }}/{{ data.total }}</span>
							</div>
						</div>
					</script>

					<!-- Error State Template -->
					<script type="text/html" id="tmpl-ddwcwm-wizard-error">
						<div class="ddwcwm-wizard-error">
							<span class="ddwcwm-wizard-error-icon">⚠</span>
							<h3><?php esc_html_e( 'Processing Error', 'wallet-management-for-woocommerce' ); ?></h3>
							<p>{{ data.message }}</p>
							<button class="button button-secondary ddwcwm-wizard-retry"><?php esc_html_e( 'Try Again', 'wallet-management-for-woocommerce' ); ?></button>
						</div>
					</script>

					<!-- Success Summary Template -->
					<script type="text/html" id="tmpl-ddwcwm-manual-transaction-summary">
						<div class="ddwcwm-wizard-summary-card">
							<div class="ddwcwm-wizard-summary-header">
								<span class="ddwcwm-wizard-summary-success">
									<svg width="24" height="24" fill="none"><circle cx="12" cy="12" r="12" fill="#22C55E"/><path d="M7 13l3 3 7-7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
								</span>
								<span class="ddwcwm-wizard-summary-title"><?php esc_html_e( 'Processing Complete', 'wallet-management-for-woocommerce' ); ?></span>
							</div>
							<div class="ddwcwm-wizard-summary-details">
								<div class="ddwcwm-wizard-summary-stats">
									<div class="ddwcwm-wizard-summary-row">
										<span class="ddwcwm-wizard-summary-label ddwcwm-success">✔ <?php esc_html_e( 'Total users processed', 'wallet-management-for-woocommerce' ); ?></span>
										<span class="ddwcwm-wizard-summary-value">{{ data.totalUsers }}</span>
									</div>
									<div class="ddwcwm-wizard-summary-row">
										<span class="ddwcwm-wizard-summary-label ddwcwm-success">✔ <?php esc_html_e( 'Successfully processed', 'wallet-management-for-woocommerce' ); ?></span>
										<span class="ddwcwm-wizard-summary-value">{{ data.successCount }}</span>
									</div>
									<div class="ddwcwm-wizard-summary-row">
										<span class="ddwcwm-wizard-summary-label ddwcwm-warning">⚠ <?php esc_html_e( 'Failed to process', 'wallet-management-for-woocommerce' ); ?></span>
										<span class="ddwcwm-wizard-summary-value">{{ data.errorCount }}</span>
									</div>
								</div>
								<# if ( data.failedResults && data.failedResults.length > 0 ) { #>
								<div class="ddwcwm-wizard-summary-details">
									<details class="ddwcwm-details">
										<summary class="ddwcwm-details-summary">
											<span class="ddwcwm-details-icon">📋</span>
											<?php esc_html_e( 'View Failed Process Details', 'wallet-management-for-woocommerce' ); ?>
											<span class="ddwcwm-details-count">({{ data.failedResults.length }})</span>
										</summary>
										<div class="ddwcwm-details-content">
											<div class="ddwcwm-details-list">
												<# _.each( data.failedResults, function( result ) { #>
												<div class="ddwcwm-details-item">
													<div class="ddwcwm-details-item-header">
														<span class="ddwcwm-details-user">{{ result.display_name }} ({{ result.user_login }})</span>
														<span class="ddwcwm-details-status ddwcwm-error"><?php esc_html_e( 'Failed', 'wallet-management-for-woocommerce' ); ?></span>
													</div>
													<div class="ddwcwm-details-item-content">
														<p class="ddwcwm-details-message">{{ result.message }}</p>
														<div class="ddwcwm-details-info">
															<span class="ddwcwm-details-label"><?php esc_html_e( 'Action:', 'wallet-management-for-woocommerce' ); ?></span>
															<span class="ddwcwm-details-value">{{ result.action_type }}</span>
														</div>
														<div class="ddwcwm-details-info">
															<span class="ddwcwm-details-label"><?php esc_html_e( 'Amount Requested:', 'wallet-management-for-woocommerce' ); ?></span>
															<span class="ddwcwm-details-value">{{{ result.amount_requested }}}</span>
														</div>
														<div class="ddwcwm-details-info">
															<span class="ddwcwm-details-label"><?php esc_html_e( 'Current Balance:', 'wallet-management-for-woocommerce' ); ?></span>
															<span class="ddwcwm-details-value">{{{ result.wallet_balance }}}</span>
														</div>
													</div>
												</div>
												<# }); #>
											</div>
										</div>
									</details>
								</div>
								<# } #>
								<div class="ddwcwm-step-actions-final ddwcwm-step-actions">
									<button type="button" class="button button-secondary ddwcwm-start-again"><?php esc_html_e( 'Start Again', 'wallet-management-for-woocommerce' ); ?></button>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . ( isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '' ) . '&menu=' . ( isset( $_GET['menu'] ) ? sanitize_text_field( wp_unslash( $_GET['menu'] ) ) : '' ) ) ); ?>" class="button button-primary"><?php esc_html_e( 'Done', 'wallet-management-for-woocommerce' ); ?></a>
								</div>
							</div>
						</div>
					</script>

					<div class="ddwcwm-steps">
						<div class="ddwcwm-step active" data-step="1">
							<span class="ddwcwm-step-index">1</span>
							<span class="ddwcwm-step-label"><?php esc_html_e( 'Select Users & Amount', 'wallet-management-for-woocommerce' ); ?></span>
						</div>
						<div class="ddwcwm-step" data-step="2">
							<span class="ddwcwm-step-index">2</span>
							<span class="ddwcwm-step-label"><?php esc_html_e( 'Process Transaction', 'wallet-management-for-woocommerce' ); ?></span>
						</div>
						<div class="ddwcwm-step" data-step="3">
							<span class="ddwcwm-step-index">3</span>
							<span class="ddwcwm-step-label"><?php esc_html_e( 'Complete', 'wallet-management-for-woocommerce' ); ?></span>
						</div>
					</div>

					<form id="ddwcwm-manual-transaction-form" method="post">
						<?php wp_nonce_field( 'ddwcwm_manual_transaction_nonce_action', 'ddwcwm_manual_transaction_nonce' ); ?>

						<div class="ddwcwm-step-content active" data-step="1">
							<div class="ddwcwm-step-header">
								<h2><?php esc_html_e( 'Manual Adjustment', 'wallet-management-for-woocommerce' ); ?></h2>
								<p class="ddwcwm-step-description"><?php esc_html_e( 'You can manually credit or debit the wallet amount for single or multiple users from here.', 'wallet-management-for-woocommerce' ); ?></p>
							</div>

							<div class="ddwcwm-manual-adjustment-fields">
								<!-- User Selection Section -->
								<div class="ddwcwm-field-section">
									<h3><?php esc_html_e( 'User Selection', 'wallet-management-for-woocommerce' ); ?></h3>

									<div class="ddwcwm-field-option">
										<label class="ddwcwm-field-checkbox">
											<input type="checkbox" id="ddwcwm-select-all-users" name="ddwcwm_select_all_users">
											<div class="ddwcwm-card-content">
												<span class="ddwcwm-card-label"><?php esc_html_e( 'Select All Users', 'wallet-management-for-woocommerce' ); ?></span>
												<span class="ddwcwm-card-description"><?php esc_html_e( 'Apply wallet transaction to all users in the system', 'wallet-management-for-woocommerce' ); ?></span>
											</div>
											<div class="ddwcwm-card-icon">
												<?php
												// Assuming DDFW_SVG is available, otherwise use a fallback or SVG string
												if ( class_exists( 'DevDiggers\Framework\Includes\DDFW_SVG' ) ) {
													\DevDiggers\Framework\Includes\DDFW_SVG::get_svg_icon(
														'checkmark-circle',
														false,
														[
															'size'         => 30,
															'stroke_width' => 1.5,
														]
													);
												}
												?>
											</div>
										</label>
									</div>

									<div class="ddwcwm-divider">
										<span class="ddwcwm-divider-text"><?php esc_html_e( 'OR', 'wallet-management-for-woocommerce' ); ?></span>
									</div>

									<div class="ddwcwm-field-group" id="ddwcwm-individual-users-section">
										<label for="ddwcwm-users" class="ddwcwm-field-label">
											<?php esc_html_e( 'Select Users', 'wallet-management-for-woocommerce' ); ?>
											<abbr title="<?php esc_html_e( 'Required', 'wallet-management-for-woocommerce' ); ?>" class="required">*</abbr>
										</label>
										<select name="_ddwcwm_users[]" id="ddwcwm-users" class="regular-text ddfw-users" multiple data-placeholder="<?php esc_attr_e( 'Search Users...', 'wallet-management-for-woocommerce' ); ?>"></select>
										<p class="description"><?php esc_html_e( 'Select single or multiple users for the operation.', 'wallet-management-for-woocommerce' ); ?></p>
									</div>
								</div>

								<!-- Amount Configuration Section -->
								<div class="ddwcwm-field-section">
									<h3><?php esc_html_e( 'Transaction Configuration', 'wallet-management-for-woocommerce' ); ?></h3>

									<?php do_action( 'ddwcwm_add_fields_after_select_users_in_manual_credit_debit' ); ?>

									<div class="ddwcwm-points-config">
										<div class="ddwcwm-field-grid">
											<div class="ddwcwm-field-group">
												<label for="ddwcwm-wallet-amount" class="ddwcwm-field-label">
													<?php esc_html_e( 'Amount', 'wallet-management-for-woocommerce' ); ?>
													<abbr title="<?php esc_html_e( 'Required', 'wallet-management-for-woocommerce' ); ?>" class="required">*</abbr>
												</label>
												<div class="ddwcwm-input-group">
													<input type="number"
														id="ddwcwm-wallet-amount"
														name="_ddwcwm_wallet_amount"
														class="ddwcwm-wallet-amount"
														min="0.01"
														step="0.01"
														placeholder="0.00"
														required>
												</div>
												<p class="description"><?php esc_html_e( 'Enter the amount you want to credit or debit.', 'wallet-management-for-woocommerce' ); ?></p>
											</div>

											<div class="ddwcwm-field-group">
												<label for="ddwcwm-action-type" class="ddwcwm-field-label">
													<?php esc_html_e( 'Action Type', 'wallet-management-for-woocommerce' ); ?>
													<abbr title="<?php esc_html_e( 'Required', 'wallet-management-for-woocommerce' ); ?>" class="required">*</abbr>
												</label>
												<select id="ddwcwm-action-type" name="_ddwcwm_action_type" class="ddwcwm-action-type regular-text" required>
													<option value="credit" selected><?php esc_html_e( 'Credit', 'wallet-management-for-woocommerce' ); ?></option>
													<option value="debit"><?php esc_html_e( 'Debit', 'wallet-management-for-woocommerce' ); ?></option>
												</select>
												<p class="description"><?php esc_html_e( 'Choose whether to credit or debit the amount.', 'wallet-management-for-woocommerce' ); ?></p>
											</div>
										</div>
									</div>

									<div class="ddwcwm-description-config">
										<div class="ddwcwm-field-group">
											<label for="ddwcwm-reason" class="ddwcwm-field-label">
												<?php esc_html_e( 'Reason', 'wallet-management-for-woocommerce' ); ?>
												<span class="ddwcwm-optional-badge"><?php esc_html_e( 'Optional', 'wallet-management-for-woocommerce' ); ?></span>
											</label>
											<textarea id="ddwcwm-reason"
												name="_ddwcwm_reason"
												class="ddwcwm-reason regular-text ddfw-full-width"
												rows="3"
												placeholder="<?php esc_attr_e( 'e.g., Manual bonus added', 'wallet-management-for-woocommerce' ); ?>"></textarea>
											<p class="description"><?php esc_html_e( 'Add a reason that will be logged and visible to users in emails.', 'wallet-management-for-woocommerce' ); ?></p>
										</div>
									</div>

									<?php do_action( 'ddwcwm_add_fields_in_end_in_manual_credit_debit' ); ?>
								</div>
							</div>

							<div class="ddwcwm-step-actions">
								<button type="submit" class="button button-primary" id="ddwcwm-start-transaction">
									<?php esc_html_e( 'Start Process', 'wallet-management-for-woocommerce' ); ?>
								</button>
							</div>
						</div>

						<div class="ddwcwm-step-content ddfw-hide" data-step="2">
							<h2><?php esc_html_e( 'Processing Transaction', 'wallet-management-for-woocommerce' ); ?></h2>
							<p class="ddwcwm-step-description"><?php esc_html_e( 'Please wait while we process the wallet transaction...', 'wallet-management-for-woocommerce' ); ?></p>
							<div class="ddwcwm-wizard-summary-wrap">
								<div class="ddwcwm-wizard-summary">
									<!-- Progress bar and results will be rendered using templates -->
								</div>
							</div>
						</div>

						<div class="ddwcwm-step-content ddfw-hide" data-step="3">
							<h2><?php esc_html_e( 'Transaction Complete', 'wallet-management-for-woocommerce' ); ?></h2>
							<p class="ddwcwm-step-description"><?php esc_html_e( 'The wallet transactions have been processed.', 'wallet-management-for-woocommerce' ); ?></p>
							<div class="ddwcwm-wizard-summary-wrap">
								<div class="ddwcwm-wizard-summary">
									<!-- Summary will be rendered using templates -->
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
            <?php
        }
	}
}
