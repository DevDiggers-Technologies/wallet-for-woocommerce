<?php

/**
 * Error Handing class
 *
 * @package DevDiggers Wallet for WooCommerce
 */

namespace DDWCWalletManagement\Helper\Error;

defined( 'ABSPATH' ) || exit();

if ( ! trait_exists( 'DDWCWM_Error_Helper' ) ) {

    /**
     *Error Handing class
     */
    trait DDWCWM_Error_Helper {

        /**
         * Print Notification function
         *
         * @param string $message
         * @return void
         */
        public function ddwcwm_print_notification( $message, $error_type = 'success' ) {

            if ( is_admin() ) {

                ?>
                <div class='notice notice-<?php echo esc_attr( $error_type ); ?> is-dismissible'>
                    <p><?php echo wp_kses_post( $message ); ?></p>
                </div>
                <?php

            } else {

                wc_print_notice( $message, $error_type );

            }

        }

    }

}