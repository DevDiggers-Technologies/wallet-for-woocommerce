<?php
/**
 * Wallet Balance Shortcode
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

use DDWCWalletManagement\Helper\Users\DDWCWM_Users_Helper;

defined( 'ABSPATH' ) || exit();

$ddwcwm_user_helper    = new DDWCWM_Users_Helper();
$ddwcwm_wallet_balance = $ddwcwm_user_helper->ddwcwm_get_user_wallet_balance();
?>
<span class="ddwcwm-wallet-balance"><?php echo wp_kses_post( wc_price( apply_filters( 'ddwcwm_modify_amount_to_multi_currency', $ddwcwm_wallet_balance ) ) ); ?></span>
<?php
