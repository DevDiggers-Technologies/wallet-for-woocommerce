<?php
/**
 * Cashback Rules template class
 *
 * Free supports cart-total cashback rules only. Product, Category, User Role and
 * Payment Method cashback rules are shown as locked Pro upgrade sections.
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Templates\Admin\Rules;

use DDWCWalletManagement\Helper\Rules\DDWCWM_Rules_Helper;
use DevDiggers\Framework\Includes\DDFW_Layout;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'DDWCWM_Cashback_Rules_Template' ) ) {
	/**
	 * Cashback Rules template class
	 */
	class DDWCWM_Cashback_Rules_Template {

		/**
		 * Rules Helper variable
		 *
		 * @var object
		 */
		protected $rules_helper;

		/**
		 * Cart Rules Data
		 *
		 * @var array
		 */
		protected $cart_rules_data;

		/**
		 * Wallet Configuration variable
		 *
		 * @var array
		 */
		protected $ddwcwm_wallet;

		/**
		 * Construct
		 *
		 * @param array $ddwcwm_wallet Configuration.
		 */
		public function __construct( $ddwcwm_wallet ) {
			$this->ddwcwm_wallet   = $ddwcwm_wallet;
			$this->rules_helper    = new DDWCWM_Rules_Helper();
			$this->cart_rules_data = $this->rules_helper->ddwcwm_get_all_cashback_rules();

			$this->ddwcwm_save_rules_data();
			$this->ddwcwm_get_manage_rules_template();
		}

		/**
		 * Build a locked Pro upgrade section HTML.
		 *
		 * @param string $description Section description.
		 * @param string $image_url   Optional upgrade screenshot URL.
		 * @return string
		 */
		protected function ddwcwm_get_locked_section_html( $description, $image_url = '' ) {
			ob_start();
			ddfw_upgrade_to_pro_section( [
				'image_url'   => $image_url,
				'heading'     => esc_html__( 'Available in Pro', 'wallet-management-for-woocommerce' ),
				'description' => $description,
				'upgrade_url' => 'https://devdiggers.com/product/woocommerce-wallet-management/',
			] );
			return ob_get_clean();
		}

		/**
		 * Get Manage Rules Template function
		 *
		 * @return void
		 */
		public function ddwcwm_get_manage_rules_template() {
			$cart_rules_data = $this->cart_rules_data;
			$ddwcwm_wallet   = $this->ddwcwm_wallet;

			// Build Cart Rules table HTML.
			ob_start();
			?>
			<table class="widefat fixed striped ddfw-table">
				<thead>
					<tr>
						<th><strong><?php echo esc_html__( 'Amount From', 'wallet-management-for-woocommerce' ) . wp_kses_post( wc_help_tip( esc_html__( 'From this amount range, respective cashback applied.', 'wallet-management-for-woocommerce' ) ) ); ?></strong></th>
						<th><strong><?php echo esc_html__( 'Amount To', 'wallet-management-for-woocommerce' ) . wp_kses_post( wc_help_tip( esc_html__( 'To this amount range, respective cashback applied.', 'wallet-management-for-woocommerce' ) ) ); ?></strong></th>
						<th><strong><?php echo esc_html__( 'Cashback Type', 'wallet-management-for-woocommerce' ) . wp_kses_post( wc_help_tip( esc_html__( 'Select cashback type.', 'wallet-management-for-woocommerce' ) ) ); ?></strong></th>
						<th><strong><?php echo esc_html__( 'Cashback Amount', 'wallet-management-for-woocommerce' ) . wp_kses_post( wc_help_tip( esc_html__( 'This cashback amount will be credited to customers between the respective amount ranges.', 'wallet-management-for-woocommerce' ) ) ); ?></strong></th>
						<th><strong><?php echo esc_html__( 'Status', 'wallet-management-for-woocommerce' ) . wp_kses_post( wc_help_tip( esc_html__( 'Select rule status.', 'wallet-management-for-woocommerce' ) ) ); ?></strong></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if ( ! empty( $cart_rules_data ) ) {
						foreach ( $cart_rules_data as $key => $cart_rule_data ) {
							?>
							<tr valign="top" class="form-row">
								<td class="forminp forminp-text">
									<div class="ddfw-table-column-flex">
										<input type="number" name="ddwcwm_amount_from[<?php echo esc_attr( $cart_rule_data[ 'id' ] ); ?>]" class="regular-text ddwcwm-width-100 ddwcwm-amount-from" placeholder="<?php esc_attr_e( 'Enter Amount From', 'wallet-management-for-woocommerce' ); ?>" min="0" step="0.01" value="<?php echo esc_attr( $cart_rule_data[ 'amount_from' ] ); ?>" autocomplete="off" />
										<strong><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></strong>
									</div>
								</td>
								<td class="forminp forminp-text">
									<div class="ddfw-table-column-flex">
										<input type="number" name="ddwcwm_amount_to[<?php echo esc_attr( $cart_rule_data[ 'id' ] ); ?>]" class="regular-text ddwcwm-width-100 ddwcwm-amount-to" placeholder="<?php esc_attr_e( 'Enter Amount to', 'wallet-management-for-woocommerce' ); ?>" min="0" step="0.01" value="<?php echo esc_attr( $cart_rule_data[ 'amount_to' ] ); ?>" autocomplete="off" />
										<strong><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></strong>
									</div>
								</td>
								<td class="forminp forminp-text">
									<select class="ddwcwm-cashback-type ddwcwm-width-100" name="ddwcwm_cashback_type[<?php echo esc_attr( $cart_rule_data[ 'id' ] ); ?>]" data-placeholder="<?php esc_attr_e( 'Cashback Type', 'wallet-management-for-woocommerce' ); ?>">
										<option value="fixed" <?php echo esc_attr( $cart_rule_data[ 'cashback_type' ] === 'fixed' ? 'selected="selected"' : '' ); ?>><?php /* translators: %s: currency symbol. */ printf( esc_html__( 'Fixed (%s)', 'wallet-management-for-woocommerce' ), esc_html( get_woocommerce_currency_symbol() ) ); ?></option>
										<option value="percentage" <?php echo esc_attr( $cart_rule_data[ 'cashback_type' ] === 'percentage' ? 'selected="selected"' : '' ); ?>><?php esc_html_e( 'Percentage (%)', 'wallet-management-for-woocommerce' ); ?></option>
									</select>
								</td>
								<td class="forminp forminp-text">
									<input type="number" name="ddwcwm_cashback_amount[<?php echo esc_attr( $cart_rule_data[ 'id' ] ); ?>]" class="regular-text ddwcwm-width-100 ddwcwm-cashback-amount" placeholder="<?php esc_attr_e( 'Enter Cashback Amount', 'wallet-management-for-woocommerce' ); ?>" min="0" step="0.01" value="<?php echo esc_attr( $cart_rule_data[ 'cashback_amount' ] ); ?>" autocomplete="off" />
								</td>
								<td class="forminp forminp-text">
									<div class="ddfw-table-column-flex">
										<select class="ddwcwm-rule-status ddwcwm-width-100" name="ddwcwm_rule_status[<?php echo esc_attr( $cart_rule_data[ 'id' ] ); ?>]" data-placeholder="<?php esc_attr_e( 'Select Status', 'wallet-management-for-woocommerce' ); ?>">
											<option value="enabled" <?php echo esc_attr( $cart_rule_data[ 'status' ] === 'enabled' ? 'selected="selected"' : '' ); ?>><?php esc_html_e( 'Enabled', 'wallet-management-for-woocommerce' ); ?></option>
											<option value="disabled" <?php echo esc_attr( $cart_rule_data[ 'status' ] === 'disabled' ? 'selected="selected"' : '' ); ?>><?php esc_html_e( 'Disabled', 'wallet-management-for-woocommerce' ); ?></option>
										</select>
										<span class="dashicons dashicons-trash ddwcwm-remove-row" title="<?php esc_attr_e( 'Remove', 'wallet-management-for-woocommerce' ); ?>"></span>
									</div>
								</td>
							</tr>
							<?php
						}
					}
					?>
					<tr>
						<td colspan="5">
							<a href="javascript:void(0);" class="ddwcwm-add-row button" data-template="ddwcwm-general-rule-row"><?php esc_html_e( 'Add Row', 'wallet-management-for-woocommerce' ); ?></a>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" id="ddwcwm-max-index" value="<?php echo esc_attr( isset( $key ) ? $key : -1 ); ?>">
			<?php
			$general_rules_html = ob_get_clean();

			$args = [
				[
					'header' => [
						'heading'     => esc_html__( 'Cart Cashback Rules', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Define cashback amount ranges for cart purchases. Rules are matched based on the order subtotal falling within the specified range.', 'wallet-management-for-woocommerce' ),
					],
					'class'             => 'ddwcwm-rule-wrap',
					'after_header_html' => $general_rules_html,
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Product Cashback Rules', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Configure per-product cashback rewards.', 'wallet-management-for-woocommerce' ),
					],
					'class'             => 'ddwcwm-rule-wrap',
					'after_header_html' => $this->ddwcwm_get_locked_section_html( esc_html__( 'Reward customers with cashback when they purchase specific products or product categories.', 'wallet-management-for-woocommerce' ) ),
				],
				[
					'header' => [
						'heading'     => esc_html__( 'User Role Cashback Rules', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Configure cashback rewards based on user roles.', 'wallet-management-for-woocommerce' ),
					],
					'class'             => 'ddwcwm-rule-wrap',
					'after_header_html' => $this->ddwcwm_get_locked_section_html( esc_html__( 'Give different cashback rates to different customer user roles.', 'wallet-management-for-woocommerce' ) ),
				],
				[
					'header' => [
						'heading'     => esc_html__( 'Payment Method Cashback Rules', 'wallet-management-for-woocommerce' ),
						'description' => esc_html__( 'Configure cashback rewards based on the payment method used.', 'wallet-management-for-woocommerce' ),
					],
					'class'             => 'ddwcwm-rule-wrap',
					'after_header_html' => $this->ddwcwm_get_locked_section_html( esc_html__( 'Reward customers with cashback for paying with a specific payment gateway.', 'wallet-management-for-woocommerce' ) ),
				],
			];

			// Cart cashback rules are the only saveable (Free) section, so render it in
			// its own form with the Save button directly below it. The remaining Pro
			// upsell sections render separately, after the Save button.
			$cart_args = array_slice( $args, 0, 1 );
			$pro_args  = array_slice( $args, 1 );

			$layout = new DDFW_Layout();
			$layout->get_form_section_layout(
				$cart_args,
				'',
				[
					'name'  => 'ddwcwm-save-rule',
					'value' => esc_html__( 'Save', 'wallet-management-for-woocommerce' ),
				],
				'ddwcwm-rule-form'
			);

			$layout->get_form_section_layout( $pro_args );

			// Cart Cashback Rule Row Template.
			?>
			<script id="tmpl-ddwcwm-general-rule-row" type="text/html">
				<tr valign="top" class="form-row">
					<td class="forminp forminp-text">
						<div class="ddfw-table-column-flex">
							<input type="number" name="ddwcwm_amount_from[{{data.key}}]" class="regular-text ddwcwm-width-100 ddwcwm-amount-from" placeholder="<?php esc_attr_e( 'Enter Amount From', 'wallet-management-for-woocommerce' ); ?>" min="0" step="0.01" autocomplete="off" />
							<strong><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></strong>
						</div>
					</td>
					<td class="forminp forminp-text">
						<div class="ddfw-table-column-flex">
							<input type="number" name="ddwcwm_amount_to[{{data.key}}]" class="regular-text ddwcwm-width-100 ddwcwm-amount-to" placeholder="<?php esc_attr_e( 'Enter Amount to', 'wallet-management-for-woocommerce' ); ?>" min="0" step="0.01" autocomplete="off" />
							<strong><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></strong>
						</div>
					</td>
					<td class="forminp forminp-text">
						<select class="ddwcwm-cashback-type ddwcwm-width-100" name="ddwcwm_cashback_type[{{data.key}}]" data-placeholder="<?php esc_attr_e( 'Cashback Type', 'wallet-management-for-woocommerce' ); ?>">
							<option value="fixed"><?php /* translators: %s: currency symbol. */ printf( esc_html__( 'Fixed (%s)', 'wallet-management-for-woocommerce' ), esc_html( get_woocommerce_currency_symbol() ) ); ?></option>
							<option value="percentage"><?php esc_html_e( 'Percentage (%)', 'wallet-management-for-woocommerce' ); ?></option>
						</select>
					</td>
					<td class="forminp forminp-text">
						<div class="ddfw-table-column-flex">
							<input type="number" name="ddwcwm_cashback_amount[{{data.key}}]" class="regular-text ddwcwm-width-100 ddwcwm-cashback-amount" placeholder="<?php esc_attr_e( 'Enter Cashback Amount', 'wallet-management-for-woocommerce' ); ?>" min="0" step="0.01" autocomplete="off" />
						</div>
					</td>
					<td class="forminp forminp-text">
						<div class="ddfw-table-column-flex">
							<select class="ddwcwm-rule-status ddwcwm-width-100" name="ddwcwm_rule_status[{{data.key}}]" data-placeholder="<?php esc_attr_e( 'Select Status', 'wallet-management-for-woocommerce' ); ?>">
								<option value="enabled"><?php esc_html_e( 'Enabled', 'wallet-management-for-woocommerce' ); ?></option>
								<option value="disabled"><?php esc_html_e( 'Disabled', 'wallet-management-for-woocommerce' ); ?></option>
							</select>
							<span class="dashicons dashicons-trash ddwcwm-remove-row" title="<?php esc_attr_e( 'Remove', 'wallet-management-for-woocommerce' ); ?>"></span>
						</div>
					</td>
				</tr>
			</script>

			<!-- Invalid form data Template -->
			<script id="tmpl-ddwcwm_form_data_error" type="text/html">
				<div class='notice notice-error is-dismissible'>
					<p><?php esc_html_e( 'Some Fields are empty or not valid.', 'wallet-management-for-woocommerce' ); ?></p>
				</div>
			</script>
			<?php
		}

		/**
		 * Save Rules function
		 *
		 * @return void
		 */
		public function ddwcwm_save_rules_data() {
			if ( ! empty( $_POST[ 'ddwcwm-save-rule' ] ) && ! empty( $_POST[ 'ddwcwm-save-rule_nonce' ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ 'ddwcwm-save-rule_nonce' ] ) ), 'ddwcwm-save-rule_nonce_action' ) ) {
				// Only cart-total rules are processed in Free; Pro rule sets are passed as empty.
				if ( $this->rules_helper->ddwcwm_prepare_cashback_rule_data_and_save( $_POST, $this->cart_rules_data ) ) {
					$this->cart_rules_data = $this->rules_helper->ddwcwm_get_all_cashback_rules();
				}
			}
		}
	}
}
