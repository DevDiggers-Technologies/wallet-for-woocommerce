<?php
/**
 * Wallet Transactions Shortcode
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

use DDWCWalletManagement\Helper\Users\DDWCWM_Users_Helper;
use DDWCWalletManagement\Helper\Transactions\DDWCWM_Transactions_Helper;
use DDWCWalletManagement\Includes\Front\DDWCWM_Front_Ajax_Functions;

defined( 'ABSPATH' ) || exit();

global $ddwcwm_wallet;

$ddwcwm_user_id = get_current_user_id();

$per_page = 10;

$ddwcwm_current_page = ! empty( get_query_var( $ddwcwm_wallet[ 'my_account_endpoint' ] ) ) && is_numeric( get_query_var( $ddwcwm_wallet[ 'my_account_endpoint' ] ) ) ? get_query_var( $ddwcwm_wallet[ 'my_account_endpoint' ] ) : 1;

$ddwcwm_offset = $ddwcwm_current_page == 1 ? 0 : ( $ddwcwm_current_page - 1 ) * $per_page;

$ddwcwm_user_helper = new DDWCWM_Users_Helper();

$ddwcwm_total_transactions_count = $ddwcwm_user_helper->ddwcwm_get_user_total_transactions_count();
$ddwcwm_transactions             = $ddwcwm_user_helper->ddwcwm_get_user_transactions( $per_page, $ddwcwm_offset );

?>
<div class="ddwcwm-table-container" data-section="transactions">
	<div class="ddwcwm-table-loader-overlay">
		<div class="ddwcwm-table-loader-spinner"></div>
	</div>
	<div class="ddwcwm-table-wrapper">
		<table class="my_account_orders shop_table_responsive ddwcwm-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID & Date', 'devdiggers-wallet-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Transaction Context', 'devdiggers-wallet-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'devdiggers-wallet-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Note', 'devdiggers-wallet-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody id="ddwcwm-transactions-table-body">
				<?php
					$ddwcwm_front_ajax = new DDWCWM_Front_Ajax_Functions();
					$ddwcwm_front_ajax->ddwcwm_render_transaction_table_rows( $ddwcwm_transactions );
				?>
			</tbody>
		</table>
	</div>
	<?php
	if ( $per_page < $ddwcwm_total_transactions_count ) {
		?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination ddwcwm-pagination">
			<input type="hidden" class="ddwcwm-current-page" value="<?php echo esc_attr( $ddwcwm_current_page ); ?>" />
			<input type="hidden" class="ddwcwm-total-count" value="<?php echo esc_attr( $ddwcwm_total_transactions_count ); ?>" />
			<button class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button ddwcwm-pagination-button" data-table="transactions" data-perform="previous" <?php echo esc_attr( 1 === (int)$ddwcwm_current_page ? 'disabled' : '' ); ?>><?php esc_html_e( 'Previous', 'devdiggers-wallet-for-woocommerce' ); ?></button>
			<button class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button ddwcwm-pagination-button" data-table="transactions" data-perform="next"><?php esc_html_e( 'Next', 'devdiggers-wallet-for-woocommerce' ); ?></button>
		</div>
		<?php
	}
	?>
</div>
