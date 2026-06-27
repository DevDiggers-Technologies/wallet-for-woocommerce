<?php
/**
 * Wallet Notification email
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit();

echo '= ' . esc_html( $email_heading ) . " =\n\n";

if ( ! empty( $email_message ) ) {
    foreach ( $email_message as $ddwcwm_message ) {
        echo wp_kses_post( $ddwcwm_message ) . "\n\n";
    }
}

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WooCommerce email filter.