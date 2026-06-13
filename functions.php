<?php
/**
 * Plugin Name: Wallet Management for WooCommerce
 * Description: Allows customers to use virtual money on the store to top up their wallet, pay for orders, earn cart cashback and track every transaction.
 * Plugin URI: https://devdiggers.com/product/woocommerce-wallet-management/
 * Author: DevDiggers
 * Author URI: https://devdiggers.com/
 * Version: 1.0.0
 * Text Domain: wallet-management-for-woocommerce
 * Domain Path: /i18n
 * WC requires at least: 9.0.0
 * WC tested up to: 10.8.1
 * WP requires at least: 6.2.0
 * WP tested up to: 7.0
 * DevDiggersPrefix: ddwcwm
 * Requires Plugins: woocommerce
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Wallet Management for WooCommerce
 */

// ddwcwm: DevDiggers Wallet Management for WooCommerce.
use DDWCWalletManagement\Includes;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Free_Init' ) ) {
	/**
	 * Free Init class
	 */
	final class DDWCWM_Free_Init {
		/**
		 * Instance variable
		 *
		 * @var DDWCWM_Free_Init|null
		 */
		private static $_instance = null;

		/**
		 * Class constructor
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'ddwcwm_init' ] );
			add_action( 'woocommerce_blocks_loaded', [ $this, 'ddwcwm_blocks_loaded' ] );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'ddwcwm_plugin_settings_link' ] );
			add_filter( 'plugin_row_meta', [ $this, 'ddwcwm_plugin_row_meta' ], 10, 2 );
		}

		/**
		 * Create a plugin instance.
		 *
		 * @return static
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new self();

				/**
				 * Action hook fired when the main plugin instance is loaded.
				 *
				 * @since 1.0.0
				 */
				do_action( 'ddwcwm_loaded' );
			}

			return self::$_instance;
		}

		/**
		 * Init function
		 *
		 * @return void
		 */
		public function ddwcwm_init() {
			// WordPress.org loads plugin translations automatically.

			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', function () {
					?>
					<div class="error">
						<p>
							<?php
							/* translators: %1$s for a opening tag and %2$s for a closing tag */
							echo sprintf( esc_html__( 'Wallet Management for WooCommerce is activated but not effective. It requires %1$sWooCommerce Plugin%2$s in order to use its functionalities.', 'wallet-management-for-woocommerce' ), '<a href="' . esc_url( '//wordpress.org/plugins/woocommerce/' ) . '" target="_blank">', '</a>' );
							?>
						</p>
					</div>
					<?php
				} );
			} else {
				require_once DDWCWM_PLUGIN_FILE . 'autoload/autoload.php';
				new Includes\DDWCWM_File_Handler();
				new Includes\Admin\DDWCWM_Setup_Wizard();

				// Initialize review notice if framework is available.
				if ( class_exists( '\DevDiggers\Framework\Includes\DDFW_Review_Notice' ) ) {
					new \DevDiggers\Framework\Includes\DDFW_Review_Notice( [
						'plugin_name'   => esc_html__( 'Wallet Management for WooCommerce', 'wallet-management-for-woocommerce' ),
						'plugin_prefix' => 'ddwcwm',
						'review_url'    => 'https://wordpress.org/support/plugin/wallet-management-for-woocommerce/reviews/#new-post',
					] );
				}
			}
		}

		/**
		 * Blocks loaded
		 *
		 * @return void
		 */
		public function ddwcwm_blocks_loaded() {
			if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry' ) ) {
				require_once DDWCWM_PLUGIN_FILE . 'autoload/autoload.php';
				add_action(
					'woocommerce_blocks_payment_method_type_registration',
					function ( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
						$payment_method_registry->register( new \DDWCWalletManagement\Includes\Blocks\DDWCWM_Wallet_Gateway_Blocks_Support() );
					}
				);
			}
		}

		/**
		 * Plugin settings link
		 *
		 * @param array $links Links Array.
		 * @return array $links
		 */
		public function ddwcwm_plugin_settings_link( $links ) {
			ob_start();
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcwm-dashboard' ) ); ?>"><?php esc_html_e( 'Dashboard', 'wallet-management-for-woocommerce' ); ?></a>
			|
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcwm-dashboard&menu=configuration' ) ); ?>"><?php esc_html_e( 'Configuration', 'wallet-management-for-woocommerce' ); ?></a>
			|
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ddwcwm-dashboard&setup-wizard=true' ) ); ?>"><?php esc_html_e( 'Setup Wizard', 'wallet-management-for-woocommerce' ); ?></a>
			|
			<a href="//devdiggers.com/product/woocommerce-wallet-management/" style="color: #0256ff; font-weight: bold;" target="_blank"><?php esc_html_e( 'Upgrade to Pro', 'wallet-management-for-woocommerce' ); ?></a>
			<?php
			$new_links = ob_get_clean();
			array_unshift( $links, $new_links );
			return $links;
		}

		/**
		 * Plugin Doc link
		 *
		 * @param array  $links Links.
		 * @param string $file File name.
		 * @return array $links
		 */
		public function ddwcwm_plugin_row_meta( $links, $file ) {
			if ( plugin_basename( __FILE__ ) === $file ) {
				$row_meta = [
					'support'       => '<a href="//devdiggers.com/contact/" aria-label="' . esc_attr__( 'Support', 'wallet-management-for-woocommerce' ) . '">' . esc_html__( 'Support', 'wallet-management-for-woocommerce' ) . '</a>',
					'documentation' => '<a href="//devdiggers.com/woocommerce-wallet-management/" aria-label="' . esc_attr__( 'Documentation', 'wallet-management-for-woocommerce' ) . '">' . esc_html__( 'Documentation', 'wallet-management-for-woocommerce' ) . '</a>',
					'review'        => '<a href="//wordpress.org/support/plugin/wallet-management-for-woocommerce/reviews/#new-post" target="_blank" title="' . esc_attr__( 'Review', 'wallet-management-for-woocommerce' ) . '" aria-label="' . esc_attr__( 'Review', 'wallet-management-for-woocommerce' ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 32" height="10"><path d="M16 26.534L6.111 32 8 20.422l-8-8.2 11.056-1.688L16 0l4.944 10.534L32 12.223l-8 8.2L25.889 32zm40 0L46.111 32 48 20.422l-8-8.2 11.056-1.688L56 0l4.944 10.534L72 12.223l-8 8.2L65.889 32zm40 0L86.111 32 88 20.422l-8-8.2 11.056-1.688L96 0l4.944 10.534L112 12.223l-8 8.2L105.889 32zm40 0L126.111 32 128 20.422l-8-8.2 11.056-1.688L136 0l4.944 10.534L152 12.223l-8 8.2L145.889 32zm40 0L166.111 32 168 20.422l-8-8.2 11.056-1.688L176 0l4.944 10.534L192 12.223l-8 8.2L185.889 32z" fill="#F5A623" fill-rule="evenodd"/></svg></a>',
				];
				$links = array_merge( $links, $row_meta );
			}

			return $links;
		}
	}
}

// Load Free version and the DevDiggers Framework if the Pro plugin is not active.
add_action( 'plugins_loaded', function() {
	if ( ! class_exists( 'DDWCWM_Init' ) ) {
		defined( 'DDWCWM_PLUGIN_FILE' ) || define( 'DDWCWM_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );
		defined( 'DDWCWM_PLUGIN_URL' ) || define( 'DDWCWM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

		// Load Free version.
		DDWCWM_Free_Init::get_instance();

		// Load DevDiggers Framework if not loaded already.
		if ( ! defined( 'DDFW_LOADED' ) && file_exists( DDWCWM_PLUGIN_FILE . 'devdiggers-framework/init.php' ) ) {
			$should_load = true;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Page is read-only admin routing input.
			if ( ! empty( $_GET['page'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Page is read-only admin routing input.
				$current_page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
				$prefix       = explode( '-', $current_page )[0];

				if ( 0 === strpos( $prefix, 'ddwc' ) || 0 === strpos( $prefix, 'ddwp' ) ) {
					$pro_class  = strtoupper( $prefix ) . '_Init';
					$free_class = strtoupper( $prefix ) . '_Free_Init';

					if ( class_exists( $free_class ) && ! class_exists( $pro_class ) && 'ddwcwm' !== $prefix ) {
						$should_load = false;
					}
				}
			}

			if ( $should_load ) {
				require DDWCWM_PLUGIN_FILE . 'devdiggers-framework/init.php';
			}
		}
	}
}, 10 );

// For HPOS Compatibility.
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
} );

require_once plugin_dir_path( __FILE__ ) . 'includes/install.php';
register_activation_hook( __FILE__, [ 'DDWCWM_Install', 'ddwcwm_create_schema' ] );
register_deactivation_hook( __FILE__, [ 'DDWCWM_Install', 'ddwcwm_on_plugin_deactivation' ] );
