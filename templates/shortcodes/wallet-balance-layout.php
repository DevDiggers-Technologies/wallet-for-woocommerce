<?php
/**
 * Wallet Balance Layout Shortcode
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

use DDWCWalletManagement\Helper\Users\DDWCWM_Users_Helper;
use DevDiggers\Framework\Includes\DDFW_SVG;

defined( 'ABSPATH' ) || exit();

global $ddwcwm_wallet;

$ddwcwm_user_helper       = new DDWCWM_Users_Helper();
$ddwcwm_wallet_balance    = $ddwcwm_user_helper->ddwcwm_get_user_wallet_balance();
$ddwcwm_today_transaction = $ddwcwm_user_helper->ddwcwm_get_user_today_transaction_amount();
$ddwcwm_today_transaction = [
	'credit' => isset( $ddwcwm_today_transaction['credit'] ) ? $ddwcwm_today_transaction['credit'] : 0,
	'debit'  => isset( $ddwcwm_today_transaction['debit'] ) ? $ddwcwm_today_transaction['debit'] : 0,
];

$ddwcwm_details_icons_enabled         = ! empty( $ddwcwm_wallet['details_icons_enabled'] ) ? $ddwcwm_wallet['details_icons_enabled'] : 'yes';
$ddwcwm_details_icons_wrapper_enabled = ! empty( $ddwcwm_wallet['details_icons_wrapper_enabled'] ) ? $ddwcwm_wallet['details_icons_wrapper_enabled'] : 'yes';

$ddwcwm_available_balance_icon = DDFW_SVG::get_svg_icon(
	'wallet', true,
	[
		'size'         => ! empty( $ddwcwm_wallet['details_icon_size'] ) ? absint( $ddwcwm_wallet['details_icon_size'] ) : 40,
		'stroke_color' => $ddwcwm_wallet['details_icon_color'],
		'stroke_width' => 1.5
	]
);

?>
<div class="ddwcwm-wallet-balance-wrapper <?php echo esc_attr( 'yes' === $ddwcwm_details_icons_wrapper_enabled ? 'ddwcwm-has-icon-wrappers' : '' ); ?>">
    <div class="ddwcwm-top-container ddwcwm-card">
        <!-- Left Side: Balance & Stats -->
        <div class="ddwcwm-balance-stats-section">
            <div class="ddwcwm-main-balance">
                <div class="ddwcwm-balance-info">
                    <p class="ddwcwm-card-title"><?php esc_html_e( 'Available Balance', 'devdiggers-wallet-for-woocommerce' ); ?></p>
                    <p class="ddwcwm-card-value">
                        <?php echo wp_kses_post( wc_price( apply_filters( 'ddwcwm_modify_amount_to_multi_currency', $ddwcwm_wallet_balance ) ) ); ?>
                    </p>
                </div>
                <?php if ( 'yes' === $ddwcwm_details_icons_enabled ) : ?>
                    <div class="ddwcwm-card-icon">
                        <?php echo wp_kses( $ddwcwm_available_balance_icon, array_merge( wp_kses_allowed_html( 'post' ), function_exists( 'ddfw_kses_allowed_svg_tags' ) ? ddfw_kses_allowed_svg_tags() : [] ) ); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="ddwcwm-secondary-stats">
                <div class="ddwcwm-stat-item">
                    <p class="ddwcwm-card-title"><?php esc_html_e( 'Today\'s Credit', 'devdiggers-wallet-for-woocommerce' ); ?></p>
                    <p class="ddwcwm-card-value ddwcwm-credit-text">
                        +<?php echo wp_kses_post( wc_price( apply_filters( 'ddwcwm_modify_amount_to_multi_currency', $ddwcwm_today_transaction[ 'credit' ] ) ) ); ?>
                    </p>
                </div>
                <div class="ddwcwm-stat-item">
                    <p class="ddwcwm-card-title"><?php esc_html_e( 'Today\'s Debit', 'devdiggers-wallet-for-woocommerce' ); ?></p>
                    <p class="ddwcwm-card-value ddwcwm-debit-text">
                        -<?php echo wp_kses_post( wc_price( apply_filters( 'ddwcwm_modify_amount_to_multi_currency', $ddwcwm_today_transaction[ 'debit' ] ) ) ); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Side: Add Money -->
        <div class="ddwcwm-wallet-topup-section">
            <div class="ddwcwm-topup-header">
                <div class="ddwcwm-topup-header-icon">
                    <?php DDFW_SVG::get_svg_icon( 'topup', false, [ 'size' => 20 ] ); ?>
                </div>
                <h4><?php esc_html_e( 'Add Money', 'devdiggers-wallet-for-woocommerce' ); ?></h4>
            </div>
            <form method="post" class="ddwcwm-topup-form">
                <?php
                wp_nonce_field( 'ddwcwm_wallet_topup_nonce_action', 'ddwcwm_wallet_topup_nonce' );
                ?>
                <div class="ddwcwm-wallet-topup-input-container">
                    <input type="number" class="form-control" id="ddwcwm-wallet-topup-amount" name="ddwcwm_wallet_topup_amount" min="0" step="0.01" placeholder="<?php esc_attr_e( 'Enter amount here', 'devdiggers-wallet-for-woocommerce' ); ?>" />
                    <button type="submit" class="button" name="ddwcwm_wallet_topup"><?php esc_html_e( 'Topup', 'devdiggers-wallet-for-woocommerce' ); ?></button>
                </div>
            </form>
        </div>

    </div>

    <?php
    // Incoming money-request management (pay / decline) is part of the Pro
    // Send/Request Money feature and is not available in Free.
    ?>
</div>
