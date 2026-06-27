<?php
/**
 * Wallet Management Setup Wizard Integration
 */

namespace DDWCWalletManagement\Includes\Admin;

defined( 'ABSPATH' ) || exit();

use DevDiggers\Framework\Includes\DDFW_Form_Field;
use DevDiggers\Framework\Includes\DDFW_Setup_Wizard;

/**
 * DDWCWM_Setup_Wizard class
 */
class DDWCWM_Setup_Wizard {

    /**
     * Constructor
     */
    public function __construct() {
        $slug = 'devdiggers-wallet-for-woocommerce';
        if ( ! get_option( 'ddfw_setup_wizard_completed_' . $slug ) ) {
            // Check if plugin is already configured by checking for existing options.
            if ( get_option( '_ddwcwm_enabled' ) ) {
                update_option( 'ddfw_setup_wizard_completed_' . $slug, true );
            }
        }
        new DDFW_Setup_Wizard( $this->get_wizard_config() );
    }

    /**
     * Get the wizard configuration
     *
     * @return array
     */
    public function get_wizard_config() {
        return [
            'plugin_slug'    => 'devdiggers-wallet-for-woocommerce',
            'plugin_file'    => 'devdiggers-wallet-for-woocommerce/functions.php',
            'dashboard_page' => 'ddwcwm-dashboard',
            'redirect_url'   => admin_url( 'admin.php?page=ddwcwm-dashboard' ),
            'logo'           => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="16" cy="16" r="15" fill="var(--ddfw-tab-background-color)"/>
                <path d="M8 13C8 11.3431 9.34315 10 11 10H21C22.6569 10 24 11.3431 24 13V19C24 20.6569 22.6569 22 21 22H11C9.34315 22 8 20.6569 8 19V13Z" fill="var(--ddfw-primary-color)"/>
                <path d="M20 16C20 16.5523 19.5523 17 19 17H13C12.4477 17 12 16.5523 12 16C12 15.4477 12.4477 15 13 15H19C19.5523 15 20 15.4477 20 16Z" fill="white"/>
                <circle cx="21" cy="16" r="2" fill="white" fill-opacity="0.2"/>
            </svg>',
            'steps'        => [
                'welcome'   => [
                    'label'         => esc_html__( 'Welcome', 'devdiggers-wallet-for-woocommerce' ),
                    'view_callback' => [ $this, 'welcome_view' ],
                ],
                'general'   => [
                    'label'         => esc_html__( 'General Settings', 'devdiggers-wallet-for-woocommerce' ),
                    'title'         => esc_html__( 'Core Wallet Configuration', 'devdiggers-wallet-for-woocommerce' ),
                    'description'   => esc_html__( 'Set up the basic foundation of your customer wallet system.', 'devdiggers-wallet-for-woocommerce' ),
                    'view_callback' => [ $this, 'general_settings_view' ],
                    'save_callback' => [ $this, 'save_fields' ],
                ],
                'cashback'   => [
                    'label'         => esc_html__( 'Cashback & Rewards', 'devdiggers-wallet-for-woocommerce' ),
                    'title'         => esc_html__( 'Customer Loyalty Incentives', 'devdiggers-wallet-for-woocommerce' ),
                    'description'   => esc_html__( 'Configure automated rewards to encourage repeat purchases.', 'devdiggers-wallet-for-woocommerce' ),
                    'view_callback' => [ $this, 'cashback_settings_view' ],
                    'save_callback' => [ $this, 'save_fields' ],
                ],
                'ready'     => [
                    'label'             => esc_html__( 'Ready!', 'devdiggers-wallet-for-woocommerce' ),
                    'ready_title'       => esc_html__( 'Your Wallet system is ready!', 'devdiggers-wallet-for-woocommerce' ),
                    'ready_description' => esc_html__( 'You have successfully configured the core wallet features. You can now manage transactions and adjust advanced settings from the dashboard.', 'devdiggers-wallet-for-woocommerce' ),
                ],
            ],
        ];
    }

    /**
     * Welcome view
     * 
     * @return void
     */
    public function welcome_view() {
        ?>
        <div class="ddfw-setup-wizard-ready ddfw-setup-wizard-onboarding">
            <div class="ddfw-success-icon-wrap">
                <svg width="100" height="100" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="15" fill="var(--ddfw-tab-background-color)"/>
                    <path d="M8 13C8 11.3431 9.34315 10 11 10H21C22.6569 10 24 11.3431 24 13V19C24 20.6569 22.6569 22 21 22H11C9.34315 22 8 20.6569 8 19V13Z" fill="var(--ddfw-primary-color)"/>
                    <path d="M20 16C20 16.5523 19.5523 17 19 17H13C12.4477 17 12 16.5523 12 16C12 15.4477 12.4477 15 13 15H19C19.5523 15 20 15.4477 20 16Z" fill="white"/>
                    <circle cx="21" cy="16" r="2" fill="white" fill-opacity="0.2"/>
                </svg>
            </div>
            <h2 class="ddfw-setup-wizard-ready-title"><?php esc_html_e( 'Welcome to DevDiggers Wallet for WooCommerce!', 'devdiggers-wallet-for-woocommerce' ); ?></h2>
            <p class="ddfw-setup-wizard-ready-desc">
                <?php esc_html_e( 'Thank you for choosing our Wallet Management solution. This quick setup wizard will help you configure the core features of your new customer wallet system.', 'devdiggers-wallet-for-woocommerce' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * General settings view
     * 
     * @return void
     */
    public function general_settings_view() {
        ?>
        <div class="ddfw-fields-section">
            <table class="form-table">
                <tbody>
                    <?php
                    $fields = [
                        [
                            'type'           => 'checkbox',
                            'label'          => esc_html__( 'Enable/Disable', 'devdiggers-wallet-for-woocommerce' ),
                            'checkbox_label' => esc_html__( 'Enable DevDiggers Wallet for WooCommerce', 'devdiggers-wallet-for-woocommerce' ),
                            'description'    => esc_html__( 'Activate or deactivate the entire wallet system and its features for all customers.', 'devdiggers-wallet-for-woocommerce' ),
                            'id'             => 'ddwcwm-enable',
                            'name'           => '_ddwcwm_enabled',
                            'value'          => get_option( '_ddwcwm_enabled', 'yes' ),
                        ],
                        [
                            'type'              => 'number',
                            'label'             => esc_html__( 'Registration Credit', 'devdiggers-wallet-for-woocommerce' ),
                            'description'       => esc_html__( 'The amount credited to a customer\'s wallet upon successful account registration. Leave blank to disable registration rewards.', 'devdiggers-wallet-for-woocommerce' ),
                            'id'                => 'ddwcwm-registration-credit',
                            'name'              => '_ddwcwm_registration_credit',
                            'value'             => get_option( '_ddwcwm_registration_credit', 0 ),
                            'custom_attributes' => [ 'step' => '0.01' ],
                            'after_field_text'  => '<strong>' . get_woocommerce_currency_symbol() . '</strong>',
                        ],
                        [
                            'type'        => 'select',
                            'label'       => esc_html__( 'Wallet Topup Order Status', 'devdiggers-wallet-for-woocommerce' ),
                            'description' => esc_html__( 'Choose the order status that triggers the balance update for top-up purchases. \'Completed\' is recommended for security.', 'devdiggers-wallet-for-woocommerce' ),
                            'options'     => [
                                'default'   => esc_html__( 'Default', 'devdiggers-wallet-for-woocommerce' ),
                                'completed' => esc_html__( 'Completed', 'devdiggers-wallet-for-woocommerce' ),
                            ],
                            'id'          => 'ddwcwm-topup-order-status',
                            'name'        => '_ddwcwm_topup_order_status',
                            'value'       => get_option( '_ddwcwm_topup_order_status', 'completed' ),
                        ],
                    ];

                    foreach ( $fields as $field ) {
                        DDFW_Form_Field::display_form_field( $field );
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Cashback settings view
     * 
     * @return void
     */
    public function cashback_settings_view() {
        ?>
        <div class="ddfw-fields-section">
            <table class="form-table">
                <tbody>
                    <?php
                    $fields = [
                        [
                            'type'           => 'checkbox',
                            'label'          => esc_html__( 'Exclude Sale Products', 'devdiggers-wallet-for-woocommerce' ),
                            'checkbox_label' => esc_html__( 'Exclude products on sale from cashback', 'devdiggers-wallet-for-woocommerce' ),
                            'description'    => esc_html__( 'When enabled, products already on sale will not contribute to the cashback calculation.', 'devdiggers-wallet-for-woocommerce' ),
                            'id'             => 'ddwcwm-cashback-exclude-sale-products',
                            'name'           => '_ddwcwm_cashback_exclude_sale_products',
                            'value'          => get_option( '_ddwcwm_cashback_exclude_sale_products', 'no' ),
                        ],
                    ];

                    foreach ( $fields as $field ) {
                        DDFW_Form_Field::display_form_field( $field );
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Generic save helper
     * 
     * @param array $form_data Form data array.
     * @return bool True if save successful, false otherwise.
     */
    public function save_fields( $form_data ) {
        foreach ( $form_data as $field ) {
            if ( strpos( $field['name'], '_ddwcwm_' ) === 0 ) {
                update_option( $field['name'], sanitize_text_field( $field['value'] ) );
            }
        }

        return true;
    }
}
