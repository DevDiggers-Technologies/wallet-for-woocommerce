<?php
/**
 * Wallet Notification email
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit();

do_action( 'woocommerce_email_header', $email_heading, $email ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WooCommerce email hook.

if ( ! empty( $email_message ) ) {
    foreach ( $email_message as $ddwcwm_message ) {
        ?>
        <p><?php echo wp_kses_post( $ddwcwm_message ); ?></p>
        <?php
    }
}

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
    ?>
    <p><?php echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) ); ?></p>
    <?php
}

do_action( 'woocommerce_email_footer', $email ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WooCommerce email hook.