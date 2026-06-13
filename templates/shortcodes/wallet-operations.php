<?php
/**
 * Wallet Operations Shortcode
 *
 * Free renders only the "Send Money" operation (peer-to-peer wallet transfer),
 * mirroring the Pro layout. Request Money, Withdraw and Refer a Friend remain
 * Pro features and are not rendered here.
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit();

if ( ! is_user_logged_in() ) {
	return;
}

global $ddwcwm_wallet;
?>
<div class="ddwcwm-wallet-operations-wrapper">

	<div class="ddwcwm-wallet-operations">
		<a href="#" class="ddwcwm-operation-btn" data-operation="send">
			<p><?php esc_html_e( 'Send Money', 'wallet-management-for-woocommerce' ); ?></p>
			<span class="ddwcwm-operation-icon">
				<?php DDFW_SVG::get_svg_icon( 'send', false ); ?>
			</span>
		</a>
	</div>

	<div class="ddwcwm-wallet-operation-popup" id="ddwcwm-wallet-send-money-popup">
		<div class="ddwcwm-wallet-operation-popup-content">
			<form method="post" id="ddwcwm-send-money-form">
				<h3><?php esc_html_e( 'Send Money', 'wallet-management-for-woocommerce' ); ?></h3>
				<span class="ddwcwm-close-popup">&times;</span>
				<div class="ddwcwm-message"></div>
				<div class="ddwcwm-form-group">
					<label for="ddwcwm-email"><?php esc_html_e( 'Receiver\'s Email', 'wallet-management-for-woocommerce' ); ?> <abbr class="required" title="<?php esc_attr_e( 'Required', 'wallet-management-for-woocommerce' ); ?>">*</abbr></label>
					<input type="email" class="form-control" name="ddwcwm_email" id="ddwcwm-email" placeholder="<?php esc_attr_e( 'Enter receiver email address', 'wallet-management-for-woocommerce' ); ?>" required>
				</div>
				<div class="ddwcwm-form-group">
					<label for="ddwcwm-amount"><?php esc_html_e( 'Amount', 'wallet-management-for-woocommerce' ); ?> <abbr class="required" title="<?php esc_attr_e( 'Required', 'wallet-management-for-woocommerce' ); ?>">*</abbr></label>
					<input type="number" min="0" step="0.01" class="form-control" name="ddwcwm_amount" id="ddwcwm-amount" placeholder="<?php esc_attr_e( 'Enter Amount', 'wallet-management-for-woocommerce' ); ?>" required>
				</div>
				<div class="ddwcwm-form-group">
					<label for="ddwcwm-note"><?php esc_html_e( 'Note', 'wallet-management-for-woocommerce' ); ?></label>
					<textarea class="form-control" name="ddwcwm_note" id="ddwcwm-note" placeholder="<?php esc_attr_e( 'Enter Note (optional)', 'wallet-management-for-woocommerce' ); ?>"></textarea>
				</div>
				<button type="submit" class="button alt ddwcwm-full-width-btn" id="ddwcwm-send-money-submit" name="ddwcwm_send_money_submit"><?php esc_html_e( 'Send Money', 'wallet-management-for-woocommerce' ); ?></button>
			</form>
		</div>
	</div>
</div>
