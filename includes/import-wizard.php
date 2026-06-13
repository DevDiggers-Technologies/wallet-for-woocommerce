<?php
/**
 * Import Users with Wallet Balance
 *
 * @package Wallet Management for WooCommerce
 */

namespace DDWCWalletManagement\Includes;

use DevDiggers\Framework\Includes\DDFW_SVG;
use DDWCWalletManagement\Helper\Users\DDWCWM_Users_Helper;

defined( 'ABSPATH' ) || exit();

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only admin page/menu nav params for Done/Cancel links, no state change.
if ( ! class_exists( 'DDWCWM_Import_Wizard' ) ) {
	/**
	 * Import class
	 */
	class DDWCWM_Import_Wizard {
		/**
		 * Constructor
		 */
		public function __construct() {
			$this->enqueue_scripts();
			add_action( 'wp_ajax_ddwcwm_batch_import_wallets', [ $this, 'ajax_batch_import_wallets' ] );
		}

		/**
		 * AJAX handler for batch import wallet balances
		 */
		public static function ajax_batch_import_wallets() {
			check_ajax_referer( 'ddwcwm-nonce', 'nonce' );

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_send_json_error( [ 'error' => esc_html__( 'You do not have permission to import wallet balances.', 'wallet-management-for-woocommerce' ) ] );
			}

			// JSON payloads. The raw string is decoded first (text-field sanitizing the
			// JSON itself would corrupt multi-line CSV cells), then every decoded scalar
			// is run through sanitize_text_field via map_deep.
			$rows               = isset( $_POST['rows'] ) ? json_decode( wp_unslash( $_POST['rows'] ), true ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized post-decode via map_deep() below.
			$mapping            = isset( $_POST['mapping'] ) ? json_decode( wp_unslash( $_POST['mapping'] ), true ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized post-decode via map_deep() below.
			$rows               = is_array( $rows ) ? map_deep( $rows, 'sanitize_text_field' ) : [];
			$mapping            = is_array( $mapping ) ? map_deep( $mapping, 'sanitize_text_field' ) : [];
			$action             = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : 'add';
			$manual_description = isset( $_POST['manual_description'] ) ? sanitize_text_field( wp_unslash( $_POST['manual_description'] ) ) : '';
			$start_index        = isset( $_POST['start_index'] ) ? intval( wp_unslash( $_POST['start_index'] ) ) : 0;

			if ( empty( $rows ) || ! is_array( $rows ) ) {
				wp_send_json_error( [ 'error' => esc_html__( 'No data.', 'wallet-management-for-woocommerce' ) ] );
			}

			global $wpdb, $ddwcwm_wallet;
			$user_helper = new DDWCWM_Users_Helper();
			$results     = [];

			foreach ( $rows as $i => $row ) {
				$absolute_row_index = $start_index + $i;

				// Map fields
				$id             = isset( $mapping['id'] ) && $mapping['id'] ? ( $row[ $mapping['id'] ] ?? '' ) : '';
				$username       = isset( $mapping['username'] ) && $mapping['username'] ? ( $row[ $mapping['username'] ] ?? '' ) : '';
				$email          = isset( $mapping['email'] ) && $mapping['email'] ? ( $row[ $mapping['email'] ] ?? '' ) : '';
				$wallet_balance = isset( $mapping['wallet_balance'] ) && $mapping['wallet_balance'] ? ( $row[ $mapping['wallet_balance'] ] ?? '' ) : '';

				// Get description
				$desc = isset( $mapping['description'] ) && $mapping['description'] ? ( $row[ $mapping['description'] ] ?? '' ) : ( $manual_description ?: '' );

				// User lookup: id > username > email
				$user_id = 0;
				if ( $id && is_numeric( $id ) ) {
					$user_id = intval( $id );
				} elseif ( $username ) {
					$user_obj = get_user_by( 'login', $username );
					$user_id  = $user_obj ? $user_obj->ID : 0;
				} elseif ( $email && is_email( $email ) ) {
					$user_obj = get_user_by( 'email', $email );
					$user_id  = $user_obj ? $user_obj->ID : 0;
				}

				if ( ! $user_id ) {
					$results[] = [
						'row'         => $i,
						'absoluteRow' => $absolute_row_index,
						'status'      => 'error',
						'message'     => esc_html__( 'User not found', 'wallet-management-for-woocommerce' ),
					];
					continue;
				}

				// Validate balance
				$wallet_balance = floatval( $wallet_balance );
				if ( ! is_numeric( $wallet_balance ) ) {
					$results[] = [
						'row'         => $i,
						'absoluteRow' => $absolute_row_index,
						'status'      => 'error',
						'message'     => esc_html__( 'Invalid balance value', 'wallet-management-for-woocommerce' ),
					];
					continue;
				}

				// Process wallet action
				$current_balance = $user_helper->ddwcwm_get_user_wallet_balance( $user_id );

				// Build description if empty
				if ( empty( $desc ) ) {
					$desc = esc_html__( 'Wallet balance imported via CSV', 'wallet-management-for-woocommerce' );
				}

				$transaction_helper = new \DDWCWalletManagement\Helper\Transactions\DDWCWM_Transactions_Helper();

				switch ( $action ) {
					case 'add':
						$amount = abs( $wallet_balance );
						$user_helper->ddwcwm_set_user_wallet_balance( $current_balance + $amount, $user_id );
						
						$transaction_helper->ddwcwm_save_transaction( [
							'user_id'   => $user_id,
							'type'      => 'credit',
							'amount'    => $amount,
							'note'      => $desc,
							'reference' => 'manual_adjustment',
						] );

						$message = esc_html__( 'Balance added', 'wallet-management-for-woocommerce' );
						break;
					case 'subtract':
						$amount      = abs( $wallet_balance );
						$new_balance = max( 0, $current_balance - $amount );
						$user_helper->ddwcwm_set_user_wallet_balance( $new_balance, $user_id );

						$transaction_helper->ddwcwm_save_transaction( [
							'user_id'   => $user_id,
							'type'      => 'debit',
							'amount'    => $amount,
							'note'      => $desc,
							'reference' => 'manual_adjustment',
						] );

						$message = esc_html__( 'Balance subtracted', 'wallet-management-for-woocommerce' );
						break;
					case 'override':
						$diff = $wallet_balance - $current_balance;
						$user_helper->ddwcwm_set_user_wallet_balance( $wallet_balance, $user_id );

						$transaction_helper->ddwcwm_save_transaction( [
							'user_id'   => $user_id,
							'type'      => $diff >= 0 ? 'credit' : 'debit',
							'amount'    => abs( $diff ),
							'note'      => $desc,
							'reference' => 'manual_adjustment',
						] );
						if ( $diff > 0 ) {
							$message = esc_html__( 'Balance overridden (added)', 'wallet-management-for-woocommerce' );
						} elseif ( $diff < 0 ) {
							$message = esc_html__( 'Balance overridden (subtracted)', 'wallet-management-for-woocommerce' );
						} else {
							$message = esc_html__( 'Balance already matches', 'wallet-management-for-woocommerce' );
						}
						break;
					default:
						$results[] = [
							'row'         => $i,
							'absoluteRow' => $absolute_row_index,
							'status'      => 'error',
							'message'     => esc_html__( 'Unknown action', 'wallet-management-for-woocommerce' ),
						];
						continue 2;
				}

				$results[] = [
					'row'         => $i,
					'absoluteRow' => $absolute_row_index,
					'status'      => 'success',
					'message'     => $message,
				];
			}
			wp_send_json_success( [ 'results' => $results ] );
		}

		/**
		 * Enqueue scripts and styles
		 */
		public function enqueue_scripts() {
			wp_enqueue_style( 'ddwcwm-import-style' );
			wp_enqueue_script( 'ddwcwm-import-script' );
		}

		/**
		 * Render import page
		 */
		public function render() {
			?>
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
						<h3><?php esc_html_e( 'Import Error', 'wallet-management-for-woocommerce' ); ?></h3>
						<p>{{ data.message }}</p>
						<button class="button button-secondary ddwcwm-wizard-retry"><?php esc_html_e( 'Try Again', 'wallet-management-for-woocommerce' ); ?></button>
					</div>
				</script>

				<!-- File Info Template -->
				<script type="text/html" id="tmpl-ddwcwm-file-info">
					<span class="ddwcwm-file-icon">📄</span>
					<span class="ddwcwm-file-name">{{ data.fileName }}</span>
					<span class="ddwcwm-file-size">({{ data.fileSize }})</span>
				</script>

				<!-- Imported/Not Imported Dropdown Template -->
				<script type="text/html" id="tmpl-ddwcwm-import-summary-list">
					<# if ( data.items && data.items.length ) { #>
						<ul>
						<# _.each( data.items, function( item ) { #>
							<li>
								<span class="ddwcwm-row-number"><?php esc_html_e( 'Row', 'wallet-management-for-woocommerce' ); ?> {{ item.displayRow || ( item.row + 1 ) }}:</span>
								<span class="ddwcwm-row-message">{{ item.message }}</span>
								<# if ( item.user_id ) { #>
									<span class="ddwcwm-row-user-id">(<?php esc_html_e( 'User ID', 'wallet-management-for-woocommerce' ); ?>: {{ item.user_id }})</span>
								<# } #>
							</li>
						<# }); #>
						</ul>
					<# } else { #>
						<em>{{ data.emptyText }}</em>
					<# } #>
				</script>

				<!-- Import Summary Card Template -->
				<script type="text/html" id="tmpl-ddwcwm-wizard-summary-card">
					<div class="ddwcwm-wizard-summary-card">
						<div class="ddwcwm-wizard-summary-header">
							<span class="ddwcwm-wizard-summary-success">
								<svg width="24" height="24" fill="none"><circle cx="12" cy="12" r="12" fill="#22C55E"/><path d="M7 13l3 3 7-7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span class="ddwcwm-wizard-summary-title"><?php esc_html_e( 'Import CSV Summary', 'wallet-management-for-woocommerce' ); ?></span>
						</div>
						<div class="ddwcwm-wizard-summary-details">
							<div class="ddwcwm-wizard-summary-rows">
								{{ data.totalRows }} <?php esc_html_e( 'rows', 'wallet-management-for-woocommerce' ); ?> • {{ data.totalCols }} <?php esc_html_e( 'columns', 'wallet-management-for-woocommerce' ); ?>
							</div>
							<div class="ddwcwm-wizard-summary-table">
								<div class="ddwcwm-wizard-summary-row">
									<span class="ddwcwm-wizard-summary-label ddwcwm-success">✔ <?php esc_html_e( 'Total rows in file', 'wallet-management-for-woocommerce' ); ?></span>
									<span class="ddwcwm-wizard-summary-value ddwcwm-wizard-summary-total-rows">{{ data.totalRows }}</span>
								</div>
								<div class="ddwcwm-wizard-summary-row">
									<span class="ddwcwm-wizard-summary-label ddwcwm-warning">⚠ <?php esc_html_e( 'Duplicated rows in file', 'wallet-management-for-woocommerce' ); ?></span>
									<span class="ddwcwm-wizard-summary-value ddwcwm-wizard-summary-duplicate-rows">{{ data.duplicateRows }}</span>
								</div>
								<div class="ddwcwm-wizard-summary-row ddwcwm-import-summary-row-indent ddwcwm-import-summary-toggle" data-toggle="imported">
									<span class="ddwcwm-wizard-summary-label">↳ <?php esc_html_e( 'Imported to the system', 'wallet-management-for-woocommerce' ); ?> <span class="ddwcwm-import-summary-caret">▼</span></span>
									<span class="ddwcwm-wizard-summary-value ddwcwm-wizard-summary-imported">{{ data.imported }}</span>
								</div>
								<div class="ddwcwm-import-summary-dropdown ddwcwm-imported-list ddfw-hide">{{{ data.importedListHTML }}}</div>
								<div class="ddwcwm-wizard-summary-row ddwcwm-import-summary-row-indent ddwcwm-import-summary-toggle" data-toggle="notimported">
									<span class="ddwcwm-wizard-summary-label">↳ <?php esc_html_e( 'Not Imported', 'wallet-management-for-woocommerce' ); ?> <span class="ddwcwm-import-summary-caret">▼</span></span>
									<span class="ddwcwm-wizard-summary-value ddwcwm-wizard-summary-notimported">{{ data.notImported }}</span>
								</div>
								<div class="ddwcwm-import-summary-dropdown ddwcwm-notimported-list ddfw-hide">{{{ data.notImportedListHTML }}}</div>
							</div>
							<div class="ddwcwm-wizard-summary-total">
								<span><?php esc_html_e( 'Total rows imported', 'wallet-management-for-woocommerce' ); ?></span>
								<span class="ddwcwm-wizard-summary-total-value ddwcwm-wizard-summary-imported">{{ data.imported }}</span>
							</div>
							<div class="ddwcwm-step-actions-final ddwcwm-step-actions">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . sanitize_title( wp_unslash( $_GET['page'] ?? '' ) ) . '&menu=' . sanitize_title( wp_unslash( $_GET['menu'] ?? '' ) ) ) ); ?>" class="button button-primary"><?php esc_html_e( 'Done', 'wallet-management-for-woocommerce' ); ?></a>
							</div>
						</div>
					</div>
				</script>

				<div class="ddwcwm-steps">
					<div class="ddwcwm-step active" data-step="1">
						<span class="ddwcwm-step-index">1</span>
						<span class="ddwcwm-step-label"><?php esc_html_e( 'Select File', 'wallet-management-for-woocommerce' ); ?></span>
					</div>
					<div class="ddwcwm-step" data-step="2">
						<span class="ddwcwm-step-index">2</span>
						<span class="ddwcwm-step-label"><?php esc_html_e( 'Mapping', 'wallet-management-for-woocommerce' ); ?></span>
					</div>
					<div class="ddwcwm-step" data-step="3">
						<span class="ddwcwm-step-index">3</span>
						<span class="ddwcwm-step-label"><?php esc_html_e( 'Data Import', 'wallet-management-for-woocommerce' ); ?></span>
					</div>
				</div>

				<form id="ddwcwm-import-form" method="post" enctype="multipart/form-data">
					<div class="ddwcwm-step-content" data-step="1">
						<h2><?php esc_html_e( 'Upload a CSV file', 'wallet-management-for-woocommerce' ); ?></h2>
						<p class="ddwcwm-step-description"><?php esc_html_e( 'Make sure file includes user ID, email, and wallet balance data', 'wallet-management-for-woocommerce' ); ?></p>
						<div class="ddwcwm-dropzone" id="ddwcwm-dropzone">
							<input type="file" name="import_file" id="ddwcwm-import-file" accept=".csv" required hidden />
							<div class="ddwcwm-dropzone-inner">
								<div class="ddwcwm-dropzone-icon">
									<?php
									DDFW_SVG::get_svg_icon(
										'cloud-upload',
										false,
										[
											'size'         => '60',
											'fill'         => '#9ca3af',
											'stroke_color' => '#9ca3af',
											'stroke_width' => '0',
										]
									);
									?>
								</div>
								<p><?php esc_html_e( 'Drop a file or click to browse', 'wallet-management-for-woocommerce' ); ?></p>
								<small><?php esc_html_e( 'CSV files of any size are supported with batch processing', 'wallet-management-for-woocommerce' ); ?></small>
							</div>
						</div>
						<div class="ddwcwm-import-file-info ddfw-hide"></div>
						<div class="ddwcwm-sample-file-link">
							<a href="<?php echo esc_url( DDWCWM_PLUGIN_URL . 'assets/sample/user-wallets.csv' ); ?>" class="ddwcwm-sample-link">
								<?php esc_html_e( 'Download a sample CSV file', 'wallet-management-for-woocommerce' ); ?>
							</a>
						</div>
						<div class="ddwcwm-step-actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . sanitize_title( wp_unslash( $_GET['page'] ?? '' ) ) . '&menu=' . sanitize_title( wp_unslash( $_GET['menu'] ?? '' ) ) ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Cancel', 'wallet-management-for-woocommerce' ); ?></a>
							<button type="button" class="button button-primary ddwcwm-next-step" data-next="2" disabled><?php esc_html_e( 'Continue', 'wallet-management-for-woocommerce' ); ?></button>
						</div>
					</div>

					<div class="ddwcwm-step-content ddfw-hide" data-step="2">
						<h2><?php esc_html_e( 'CSV Fields Mapping', 'wallet-management-for-woocommerce' ); ?></h2>
						<p class="ddwcwm-step-description"><?php esc_html_e( 'Map columns from imported CSV to the default fields required for wallet balances. Not required columns could be skipped.', 'wallet-management-for-woocommerce' ); ?></p>

						<div class="ddwcwm-mapping-notice">
							<div class="ddwcwm-notice-content">
								<strong><?php esc_html_e( 'User Identification:', 'wallet-management-for-woocommerce' ); ?></strong>
								<?php esc_html_e( 'You need to map at least one of ID, Username, or Email to identify users. Wallet Balance field is required.', 'wallet-management-for-woocommerce' ); ?>
							</div>
						</div>
						<div class="ddfw-table-wrapper">
							<table class="ddwcwm-mapping ddfw-table" id="ddwcwm-mapping-table">
							<thead>
								<tr>
										<th><?php esc_html_e( 'Fields', 'wallet-management-for-woocommerce' ); ?></th>
										<th><?php esc_html_e( 'CSV Column', 'wallet-management-for-woocommerce' ); ?></th>
										<th><?php esc_html_e( 'CSV Example Data', 'wallet-management-for-woocommerce' ); ?></th>
								</tr>
							</thead>
							<tbody>
									<?php
									$mapping_fields = [
										[
											'key'      => 'id',
											'label'    => esc_html__( 'ID', 'wallet-management-for-woocommerce' ),
											'required' => false,
											'group'    => 'user_identifier',
										],
										[
											'key'      => 'username',
											'label'    => esc_html__( 'Username', 'wallet-management-for-woocommerce' ),
											'required' => false,
											'group'    => 'user_identifier',
										],
										[
											'key'      => 'email',
											'label'    => esc_html__( 'Email', 'wallet-management-for-woocommerce' ),
											'required' => false,
											'group'    => 'user_identifier',
										],
										[
											'key'      => 'wallet_balance',
											'label'    => esc_html__( 'Wallet Balance', 'wallet-management-for-woocommerce' ),
											'required' => true,
										],
										[
											'key'      => 'description',
											'label'    => esc_html__( 'Description', 'wallet-management-for-woocommerce' ),
											'required' => false,
										],
									];
									foreach ( $mapping_fields as $field ) {
										?>
										<tr>
											<td>
												<?php echo esc_html( $field['label'] ); ?>
												<?php
												if ( $field['required'] ) {
													?>
													&nbsp;<abbr class="required" title="<?php esc_attr_e( 'required', 'wallet-management-for-woocommerce' ) ?>">*</abbr>
													<?php
												} elseif ( isset( $field['group'] ) && 'user_identifier' === $field['group'] ) {
													?>
													&nbsp;<abbr class="required" title="<?php esc_attr_e( 'at least one required', 'wallet-management-for-woocommerce' ) ?>">*</abbr>
													<?php
												}
												?>
									</td>
									<td>
												<select name="map[<?php echo esc_attr( $field['key'] ); ?>]" class="ddwcwm-map-select" data-field="<?php echo esc_attr( $field['key'] ); ?>" data-default="<?php echo esc_attr( $field['key'] ); ?>">
													<option value=""><?php esc_html_e( 'Select column', 'wallet-management-for-woocommerce' ); ?></option>
										</select>
									</td>
									<td class="ddwcwm-example-data"></td>
								</tr>
										<?php
									}
									?>
							</tbody>
						</table>
						</div>

						<div class="ddwcwm-import-action-row">
							<label><?php esc_html_e( 'Wallet Action', 'wallet-management-for-woocommerce' ); ?>:</label>
							<div class="ddwcwm-action-card-group">
								<?php
								$action_cards = [
									[
										'value' => 'add',
										'icon'  => '+',
										'title' => esc_html__( 'Add to existing balance', 'wallet-management-for-woocommerce' ),
									],
									[
										'value' => 'subtract',
										'icon'  => '−',
										'title' => esc_html__( 'Subtract from existing balance', 'wallet-management-for-woocommerce' ),
									],
									[
										'value' => 'override',
										'icon'  => '=',
										'title' => esc_html__( 'Override balance (set to value)', 'wallet-management-for-woocommerce' ),
									],
								];

								foreach ( $action_cards as $card ) {
									$checked = isset( $card['value'] ) && $card['value'] ? ' checked' : '';
									?>
									<label class="ddwcwm-action-card">
										<input type="radio" name="ddwcwm_import_action" value="<?php echo esc_attr( $card['value'] ); ?>"<?php echo esc_attr( $checked ); ?> />
										<div class="ddwcwm-action-card-content">
											<div class="ddwcwm-action-card-icon"><?php echo esc_html( $card['icon'] ); ?></div>
											<div class="ddwcwm-action-card-title"><?php echo esc_html( $card['title'] ); ?></div>
										</div>
									</label>
									<?php
								}
								?>
							</div>
						</div>

						<div class="ddwcwm-import-action-row">
							<label for="ddwcwm-manual-description"><?php esc_html_e( 'Manual Description (if not in CSV):', 'wallet-management-for-woocommerce' ); ?></label>
							<textarea id="ddwcwm-manual-description" name="ddwcwm_manual_description" class="ddwcwm-description-textarea regular-text ddfw-full-width" rows="3" placeholder="<?php esc_attr_e( 'Enter description for all rows if not mapped', 'wallet-management-for-woocommerce' ); ?>"></textarea>
						</div>

						<div class="ddwcwm-step-actions">
							<button type="button" class="button button-secondary ddwcwm-prev-step" data-prev="1"><?php esc_html_e( 'Back', 'wallet-management-for-woocommerce' ); ?></button>
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Import Wallet Balances', 'wallet-management-for-woocommerce' ); ?></button>
						</div>
					</div>

					<div class="ddwcwm-step-content ddwcwm-step-content-final ddfw-hide" data-step="3">
						<h2><?php esc_html_e( 'Import Wallet Balances', 'wallet-management-for-woocommerce' ); ?></h2>
						<p class="ddwcwm-step-description"><?php esc_html_e( 'Processing your import...', 'wallet-management-for-woocommerce' ); ?></p>
						<div class="ddwcwm-wizard-summary-wrap">
							<div class="ddwcwm-wizard-summary">
								<!-- Progress bar and results will be rendered using templates -->
							</div>
						</div>
					</div>
				</form>
			</div>
			<?php
		}
	}
}
