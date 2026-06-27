<?php
/**
 * Email Notification Handler
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Email_Notification_Handler' ) ) {
    /**
     * Email Notification Handler class.
     */
    class DDWCWM_Email_Notification_Handler extends \WC_Email {
		/**
		 * Footer variable
		 *
		 * @var string
		 */
		protected $footer;

        /**
         * Constructor.
         */
        public function __construct() {
            $this->id             = 'ddwcwm_notification';
            $this->title          = esc_html__( 'Wallet Notification', 'devdiggers-wallet-for-woocommerce' );
            $this->heading        = esc_html__( 'Wallet Notification', 'devdiggers-wallet-for-woocommerce' );
            $this->subject        = '[' . get_option( 'blogname' ) . '] ' . esc_html__( 'Wallet Notification', 'devdiggers-wallet-for-woocommerce' );
            $this->description    = esc_html__( 'On using Wallet this mail is sent to user.', 'devdiggers-wallet-for-woocommerce' );
            $this->template_html  = 'emails/wallet.php';
            $this->template_plain = 'emails/plain/wallet.php';
            $this->template_base  = DDWCWM_PLUGIN_FILE . '/templates/';
            $this->footer         = esc_html__( 'Thanks for choosing Wallet.', 'devdiggers-wallet-for-woocommerce' );

			add_action( 'ddwcwm_mail_notification', [ $this, 'trigger' ] );

            // Call parent constructor
            parent::__construct();

            // Other settings
			$this->recipient = $this->get_option( 'recipient' );
			
			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}

            // Other settings
			$this->recipient = $this->get_option( 'recipient' );
			$this->subject   = $this->get_option( 'subject' );
			$this->heading   = $this->get_option( 'heading' );

			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}

            if ( ! $this->subject ) {
                $this->subject = '[' . get_option( 'blogname' ) . '] ' . esc_html__( 'Wallet Notification', 'devdiggers-wallet-for-woocommerce' );
			}

            if ( ! $this->heading ) {
                $this->heading = esc_html__( 'Wallet Notification', 'devdiggers-wallet-for-woocommerce' );
			}
        }

        /**
         * Trigger.
         *
         * @param array $data
         * @return void
         */
        public function trigger( $data ) {
			if ( empty( $data ) ) {
				return;
			} else {
                $this->display_name  = '';
                $this->email_message = isset( $data[ 'message' ] ) ? $data[ 'message' ] : '';
                if ( ! empty( $data[ 'email' ] ) ) {
                    $this->recipient = $data[ 'email' ];
                }

                if ( ! empty( $data[ 'display_name' ] ) ) {
                    $this->display_name = $data[ 'display_name' ];
                }

				if ( ! empty( $data[ 'subject' ] ) ) {
					$this->subject = $data[ 'subject' ];
				}

                // Centralized Placeholder Replacement
                $user = null;
                if ( ! empty( $data['user_id'] ) ) {
                    $user = get_userdata( $data['user_id'] );
                } elseif ( ! empty( $this->recipient ) ) {
                    $user = get_user_by( 'email', $this->recipient );
                }

                $replace = [
                    '{site_title}' => get_bloginfo( 'name' ),
                ];

                if ( $user ) {
                    $replace['{user_name}']         = $user->user_login;
                    $replace['{user_email}']        = $user->user_email;
                    $replace['{user_display_name}'] = $user->display_name;
                    if ( empty( $this->display_name ) ) {
                        $this->display_name = $user->display_name;
                    }
                }

                if ( ! empty( $data['replace'] ) && is_array( $data['replace'] ) ) {
                    $replace = array_merge( $replace, $data['replace'] );
                }

                // Apply replacements to Heading, Subject, and Message
                if ( ! empty( $data[ 'heading' ] ) ) {
                    $this->settings['heading'] = strtr( $data[ 'heading' ], $replace );
                }

                if ( ! empty( $data[ 'subject' ] ) ) {
                    $this->settings['subject'] = html_entity_decode( wp_strip_all_tags( strtr( $data[ 'subject' ], $replace ) ) );
                }

                if ( ! empty( $this->email_message ) ) {
                    if ( is_array( $this->email_message ) ) {
                        foreach ( $this->email_message as $key => $msg ) {
                            $this->email_message[$key] = strtr( $msg, $replace );
                        }
                    } else {
                        $this->email_message = strtr( $this->email_message, $replace );
                        
                        // Convert to array of paragraphs for the template if it's a string
                        $this->email_message = array_filter( array_map( 'trim', explode( "\n", strip_tags( $this->email_message, '<a><strong><em><b><i><br>' ) ) ) );
                    }
                }
            }

            if ( $this->is_enabled() && $this->get_recipient() ) {
                $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
            }
        }

        /**
         * Get content html.
         *
         * @access public
         * @return string
         */
        public function get_content_html() {
            return wc_get_template_html(
                $this->template_html, [
                    'email_heading'      => $this->get_heading(),
                    'email_message'      => $this->email_message,
                    'additional_content' => $this->get_additional_content(),
                    'display_name'       => $this->display_name,
                    'customer_email'     => $this->get_recipient(),
                    'blogname'           => $this->get_blogname(),
                    'sent_to_admin'      => false,
                    'plain_text'         => false,
                    'email'              => $this,
                ], '', $this->template_base
            );
        }

        /**
         * Get content plain.
         *
         * @access public
         * @return string
         */
        public function get_content_plain() {
            return wc_get_template_html(
                $this->template_plain, [
                    'email_heading'      => $this->get_heading(),
                    'email_message'      => $this->email_message,
                    'additional_content' => $this->get_additional_content(),
                    'display_name'       => $this->display_name,
                    'customer_email'     => $this->get_recipient(),
                    'blogname'           => $this->get_blogname(),
                    'sent_to_admin'      => false,
                    'plain_text'         => true,
                    'email'              => $this,
                ], '', $this->template_base
            );
        }
    }
}