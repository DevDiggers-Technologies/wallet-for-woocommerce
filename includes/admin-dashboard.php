<?php
/**
 * This file handles all admin dashboard functionalities.
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes;

use DevDiggers\Framework\Includes\DDFW_Plugin_Dashboard;
use DevDiggers\Framework\Includes\DDFW_Assets;
use DevDiggers\Framework\Includes\DDFW_SVG;
use DDWCWalletManagement\Templates\Admin;

defined( 'ABSPATH' ) || exit();

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only admin dashboard routing: page/menu/tab/paged params drive which screen renders, no state change.
if ( ! class_exists( 'DDWCWM_Admin_Dashboard' ) ) {
	/**
	 * Admin Dashboard Class
	 */
	class DDWCWM_Admin_Dashboard {
		/**
		 * Dashboard Variable
		 *
		 * @var DDFW_Plugin_Dashboard
		 */
		protected $dashboard;

		/**
		 * Configuration Variable
		 *
		 * @var array
		 */
		protected $ddwcwm_configuration;

		/**
		 * Construct
		 */
		public function __construct() {
			global $ddwcwm_wallet;
			$this->ddwcwm_configuration = $ddwcwm_wallet;
			$this->ddwcwm_add_dashboard_menu();
			add_action( 'admin_enqueue_scripts', [ $this, 'ddwcwm_enqueue_admin_scripts' ] );
			add_filter( 'admin_footer_text', [ $this, 'ddwcwm_set_admin_footer_text' ], 99 );
		}

		/**
		 * Add Admin menu function
		 *
		 * @return void
		 */
		public function ddwcwm_add_dashboard_menu() {
			ob_start();
			?>
			<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
				<circle cx="16" cy="16" r="15" fill="var(--ddfw-tab-background-color)"/>
				<path d="M8 13C8 11.3431 9.34315 10 11 10H21C22.6569 10 24 11.3431 24 13V19C24 20.6569 22.6569 22 21 22H11C9.34315 22 8 20.6569 8 19V13Z" fill="var(--ddfw-primary-color)"/>
				<path d="M20 16C20 16.5523 19.5523 17 19 17H13C12.4477 17 12 16.5523 12 16C12 15.4477 12.4477 15 13 15H19C19.5523 15 20 15.4477 20 16Z" fill="white"/>
				<circle cx="21" cy="16" r="2" fill="white" fill-opacity="0.2"/>
			</svg>
			<?php esc_html_e( 'Wallet', 'devdiggers-wallet-for-woocommerce' ); ?>
			<?php
			$plugin_name = ob_get_clean();

			$args = [
				'page_title'              => esc_html__( 'DevDiggers Wallet for WooCommerce', 'devdiggers-wallet-for-woocommerce' ),
				'menu_title'              => esc_html__( 'Wallet', 'devdiggers-wallet-for-woocommerce' ),
				'slug'                    => 'ddwcwm-dashboard',
				'plugin_name'             => $plugin_name,
				'upgrade_url'             => 'https://devdiggers.com/product/woocommerce-wallet-management/',
				'screen_options_callback' => [ $this, 'ddwcwm_add_screen_options' ],
				'menus'                   => [
					'dashboard'         => [
						'label'    => esc_html__( 'Dashboard', 'devdiggers-wallet-for-woocommerce' ),
						'callback' => [ $this, 'ddwcwm_get_dashboard_template' ],
						'layout'   => 'full-width',
					],
					'users'         => [
						'label'    => esc_html__( 'Users', 'devdiggers-wallet-for-woocommerce' ),
						'callback' => [ $this, 'ddwcwm_get_users_template' ],
						'layout'   => 'full-width',
					],
					'manual-adjustment' => [
						'label'    => esc_html__( 'Manual Adjustment', 'devdiggers-wallet-for-woocommerce' ),
						'callback' => [ $this, 'ddwcwm_get_manual_adjustment_template' ],
						'layout'   => 'full-width',
					],
					'withdraw-requests' => [
						'label'    => esc_html__( 'Withdrawal Requests', 'devdiggers-wallet-for-woocommerce' ),
						'callback' => [ $this, 'ddwcwm_get_withdraw_requests_template' ],
						'layout'   => 'full-width',
					],
					'transactions'      => [
						'label'    => esc_html__( 'Transactions', 'devdiggers-wallet-for-woocommerce' ),
						'callback' => [ $this, 'ddwcwm_get_transactions_template' ],
						'layout'   => 'full-width',
					],
					'cashback-rules'   => [
						'label'    => esc_html__( 'Cashback Rules', 'devdiggers-wallet-for-woocommerce' ),
						'callback' => [ $this, 'ddwcwm_get_cashback_rules_template' ],
						'layout'   => 'full-width',
					],
					'configuration'     => [
						'label'    => esc_html__( 'Configuration', 'devdiggers-wallet-for-woocommerce' ),
						'layout'   => 'sidebar',
						'tabs'     => [
							'general'  => [
								'label'    => esc_html__( 'General', 'devdiggers-wallet-for-woocommerce' ),
								'icon'     => DDFW_SVG::get_svg_icon( 'general', true, [ 'size' => 18 ] ),
								'callback' => [ $this, 'ddwcwm_get_general_configuration_template' ],
							],
							'withdrawals' => [
								'label'    => esc_html__( 'Withdrawals', 'devdiggers-wallet-for-woocommerce' ),
								'icon'     => DDFW_SVG::get_svg_icon( 'withdrawals', true, [ 'size' => 18 ] ),
								'callback' => [ $this, 'ddwcwm_get_withdrawals_configuration_template' ],
							],
							'endpoints' => [
								'label'    => esc_html__( 'Endpoints', 'devdiggers-wallet-for-woocommerce' ),
								'icon'     => DDFW_SVG::get_svg_icon( 'endpoints', true, [ 'size' => 18 ] ),
								'callback' => [ $this, 'ddwcwm_get_endpoints_configuration_template' ],
							],
							'otp' => [
								'label'    => esc_html__( 'OTP', 'devdiggers-wallet-for-woocommerce' ),
								'icon'     => DDFW_SVG::get_svg_icon( 'otp', true, [ 'size' => 18 ] ),
								'callback' => [ $this, 'ddwcwm_get_otp_configuration_template' ],
							],
							'referrals' => [
								'label'    => esc_html__( 'Referrals', 'devdiggers-wallet-for-woocommerce' ),
								'icon'     => DDFW_SVG::get_svg_icon( 'referrals', true, [ 'size' => 18 ] ),
								'callback' => [ $this, 'ddwcwm_get_referrals_configuration_template' ],
							],
							'cashbacks' => [
								'label'    => esc_html__( 'Cashbacks', 'devdiggers-wallet-for-woocommerce' ),
								'icon'     => DDFW_SVG::get_svg_icon( 'cashback', true, [ 'size' => 18 ] ),
								'callback' => [ $this, 'ddwcwm_get_cashback_configuration_template' ],
							],
							'emails' => [
								'label'    => esc_html__( 'Emails', 'devdiggers-wallet-for-woocommerce' ),
								'icon'     => DDFW_SVG::get_svg_icon( 'emails', true, [ 'size' => 18 ] ),
								'callback' => [ $this, 'ddwcwm_get_emails_configuration_template' ],
							],
							'shortcodes' => [
								'label'    => esc_html__( 'Shortcodes', 'devdiggers-wallet-for-woocommerce' ),
								'icon'     => DDFW_SVG::get_svg_icon( 'shortcodes', true, [ 'size' => 18 ] ),
								'callback' => [ $this, 'ddwcwm_get_shortcodes_configuration_template' ],
							],
							'layout'  => [
								'label'    => esc_html__( 'Layout', 'devdiggers-wallet-for-woocommerce' ),
								'icon'     => DDFW_SVG::get_svg_icon( 'layout', true, [ 'size' => 18 ] ),
								'callback' => [ $this, 'ddwcwm_get_layout_configuration_template' ],
							],
						],
					],
				],
			];

			$this->dashboard = new DDFW_Plugin_Dashboard( $args );
		}

		/**
		 * Add screen options for the admin dashboard
		 *
		 * @return void
		 */
		public function ddwcwm_add_screen_options() {
			global $ddwcwm_my_list_table;

			$current_menu = ! empty( $_GET['menu'] ) ? sanitize_title( wp_unslash( $_GET['menu'] ) ) : 'dashboard';

			$args = [
				'label'    => esc_html__( 'Results Per Page', 'devdiggers-wallet-for-woocommerce' ),
				'default'  => 20,
				'hidden'   => 'id',
				'sanitize' => 'intval',
			];

			switch ( $current_menu ) {
				case 'dashboard':
					$args['option'] = '';
					break;
				case 'users':
					$args['option'] = 'users_per_page';
					$ddwcwm_my_list_table    = new Admin\Users\DDWCWM_Users_List_Template();
					break;
				case 'transactions':
					$args['option'] = 'transactions_per_page';
					$ddwcwm_my_list_table    = new Admin\Transactions\DDWCWM_Transactions_List_Template();
					break;
			}

			if ( ! empty( $args['option'] ) ) {
				add_screen_option( 'per_page', $args );
			}
		}

		/**
		 * Users Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_dashboard_template() {
			new Admin\Dashboard\DDWCWM_Dashboard_Template( $this->ddwcwm_configuration );
		}

		/**
		 * Users Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_users_template() {
			if ( ! empty( $_GET[ 'action' ] ) && 'ddwcwm-users-import' === sanitize_text_field( wp_unslash( $_GET[ 'action' ] ) ) ) {
				$import_wizard = new DDWCWM_Import_Wizard();
				$import_wizard->render();
			} else {
				$obj   = new Admin\Users\DDWCWM_Users_List_Template();
				$page  = ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
				$menu  = ! empty( $_GET['menu'] ) ? sanitize_text_field( wp_unslash( $_GET['menu'] ) ) : '';
				$paged = ! empty( $_GET['paged'] ) ? sanitize_text_field( wp_unslash( $_GET['paged'] ) ) : '';
				?>
				<div class="wrap">
					<h1 class="wp-heading-inline"><?php esc_html_e( 'Users', 'devdiggers-wallet-for-woocommerce' ); ?></h1>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page . '&menu=manual-adjustment' ) ); ?>" class="page-title-action button"><?php esc_html_e( 'Manual Adjustment', 'devdiggers-wallet-for-woocommerce' ); ?></a>
					<a href="<?php echo esc_url( admin_url( "admin.php?page={$page}&menu={$menu}&action=ddwcwm-users-import" ) ); ?>" class="page-title-action button"><?php esc_html_e( 'Import', 'devdiggers-wallet-for-woocommerce' ); ?></a>
					<button class="page-title-action button ddfw-upgrade-to-pro-tag-wrapper" title="<?php esc_attr_e( 'Exporting wallet data to CSV is available in the Pro version.', 'devdiggers-wallet-for-woocommerce' ); ?>"><?php esc_html_e( 'Export', 'devdiggers-wallet-for-woocommerce' ); ?></button>
					<hr class="wp-header-end" />
					<form method="get">
						<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
						<input type="hidden" name="menu" value="<?php echo esc_attr( $menu ); ?>" />
						<input type="hidden" name="paged" value="<?php echo esc_attr( $paged ); ?>" />
						<?php
						wp_nonce_field( 'ddwcwm_users_list_nonce_action', 'ddwcwm_users_list_nonce' );
						$obj->prepare_items();
						$obj->search_box( esc_html__( 'Search', 'devdiggers-wallet-for-woocommerce' ), 'search-id' );
						$obj->display();
						?>
					</form>
				</div>
				<?php
			}
		}

		/**
		 * Manual Credit/Debit Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_manual_adjustment_template() {
			new Admin\Manual_Adjustment\DDWCWM_Manual_Adjustment_Template();
		}

		/**
		 * Withdrawal Requests Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_withdraw_requests_template() {
			ddfw_upgrade_to_pro_section( [
				'image_url'     => DDWCWM_PLUGIN_URL . 'assets/images/pro-pages/withdrawal-requests.webp',
				'heading'       => esc_html__( 'Withdrawal Requests is a Pro feature', 'devdiggers-wallet-for-woocommerce' ),
				'description'   => esc_html__( 'Let customers request to withdraw their wallet balance and review, approve or cancel those requests from here.', 'devdiggers-wallet-for-woocommerce' ),
				'list_features' => [
					esc_html__( 'Customer wallet withdrawal requests', 'devdiggers-wallet-for-woocommerce' ),
					esc_html__( 'Approve (mark as paid) or cancel requests', 'devdiggers-wallet-for-woocommerce' ),
					esc_html__( 'Fixed or percentage withdrawal charges', 'devdiggers-wallet-for-woocommerce' ),
					esc_html__( 'Minimum and maximum withdrawal limits', 'devdiggers-wallet-for-woocommerce' ),
				],
				'upgrade_url'   => 'https://devdiggers.com/product/woocommerce-wallet-management/',
			] );
		}

		/**
		 * Transactions Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_transactions_template() {
			?>
			<div class="wrap">
				<?php
				$obj = new Admin\Transactions\DDWCWM_Transactions_List_Template();
				?>
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Transactions', 'devdiggers-wallet-for-woocommerce' ); ?></h1>
				<hr class="wp-header-end" />
				<form method="get">
					<input type="hidden" name="page" value="<?php echo isset( $_REQUEST[ 'page' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST[ 'page' ] ) ) ) : ''; ?>" />
					<input type="hidden" name="menu" value="<?php echo isset( $_REQUEST[ 'menu' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST[ 'menu' ] ) ) ) : ''; ?>" />
					<input type="hidden" name="paged" value="<?php echo isset( $_REQUEST[ 'paged' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST[ 'paged' ] ) ) ) : ''; ?>" />
					<?php
					wp_nonce_field( 'ddwcwm_transactions_list_nonce_action', 'ddwcwm_transactions_list_nonce' );
					$obj->prepare_items();
					$obj->search_box( esc_html__( 'Search', 'devdiggers-wallet-for-woocommerce' ), 'search-id' );
					$obj->display();
					?>
				</form>
			</div>
			<?php
		}

		/**
		 * Cashback Rules Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_cashback_rules_template() {
			new Admin\Rules\DDWCWM_Cashback_Rules_Template( $this->ddwcwm_configuration );
		}

		/**
		 * Configuration Templates
		 */
		public function ddwcwm_get_general_configuration_template() {
			new Admin\Configuration\DDWCWM_General_Configuration_Template( $this->ddwcwm_configuration );
		}

		/**
		 * Withdrawals Configuration Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_withdrawals_configuration_template() {
			new Admin\Configuration\DDWCWM_Withdrawals_Configuration_Template( $this->ddwcwm_configuration );
		}

		/**
		 * Endpoints Configuration Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_endpoints_configuration_template() {
			new Admin\Configuration\DDWCWM_Endpoints_Configuration_Template( $this->ddwcwm_configuration );
		}

		/**
		 * OTP Configuration Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_otp_configuration_template() {
			new Admin\Configuration\DDWCWM_OTP_Configuration_Template( $this->ddwcwm_configuration );
		}

		/**
		 * Referrals Configuration Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_referrals_configuration_template() {
			new Admin\Configuration\DDWCWM_Referrals_Configuration_Template( $this->ddwcwm_configuration );
		}

		/**
		 * Cashback Configuration Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_cashback_configuration_template() {
			new Admin\Configuration\DDWCWM_Cashback_Configuration_Template( $this->ddwcwm_configuration );
		}

		/**
		 * Emails Configuration Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_emails_configuration_template() {
			new Admin\Configuration\DDWCWM_Emails_Configuration_Template( $this->ddwcwm_configuration );
		}

		/**
		 * Shortcodes Configuration Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_shortcodes_configuration_template() {
			new Admin\Configuration\DDWCWM_Shortcodes_Configuration_Template( $this->ddwcwm_configuration );
		}

		/**
		 * Layout Configuration Template
		 *
		 * @return void
		 */
		public function ddwcwm_get_layout_configuration_template() {
			new Admin\Configuration\DDWCWM_Layout_Configuration_Template( $this->ddwcwm_configuration );
		}

		/**
		 * Enqueue admin scripts function
		 *
		 * @return void
		 */
		public function ddwcwm_enqueue_admin_scripts() {
			if ( $this->dashboard->is_a_plugin_page() ) {
				wp_register_style( 'ddwcwm-import-style', DDWCWM_PLUGIN_URL . 'assets/css/import.css', [], filemtime( DDWCWM_PLUGIN_FILE . 'assets/css/import.css' ) );
				wp_register_script( 'ddwcwm-import-script', DDWCWM_PLUGIN_URL . 'assets/js/import.js', [ 'wp-util' ], filemtime( DDWCWM_PLUGIN_FILE . 'assets/js/import.js' ), true );

				wp_localize_script(
					'ddwcwm-import-script',
					'ddwcwmImportObject',
					[
						'ajax' => [
							'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
							'ajaxNonce' => wp_create_nonce( 'ddwcwm-nonce' ),
						],
						'i18n' => [
							'noRowsImported'    => esc_html__( 'No rows imported.', 'devdiggers-wallet-for-woocommerce' ),
							'allRowsImported'   => esc_html__( 'All rows imported.', 'devdiggers-wallet-for-woocommerce' ),
							'processingImport'  => esc_html__( 'Processing import...', 'devdiggers-wallet-for-woocommerce' ),
							'importError'       => esc_html__( 'Import error occurred', 'devdiggers-wallet-for-woocommerce' ),
							'mapUserIdentifier' => esc_html__( 'Please map at least one of ID, Username, or Email to identify users.', 'devdiggers-wallet-for-woocommerce' ),
							'mapBalanceField'   => esc_html__( 'Please map the Wallet Balance field.', 'devdiggers-wallet-for-woocommerce' ),
							'unknownError'      => esc_html__( 'Unknown error occurred', 'devdiggers-wallet-for-woocommerce' ),
							'ajaxError'         => esc_html__( 'AJAX error:', 'devdiggers-wallet-for-woocommerce' ),
						],
					]
				);


				// The dashboard view (charts, stat cards, date filter, styles) is provided by the
				// shared devdiggers-framework DDFW_Dashboard builder, which enqueues its own assets.

				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin routing input.
				$current_menu = ! empty( $_GET['menu'] ) ? sanitize_title( wp_unslash( $_GET['menu'] ) ) : 'dashboard';

				if ( 'manual-adjustment' === $current_menu ) {
					wp_enqueue_style( 'ddwcwm-manual-adjustment-style', DDWCWM_PLUGIN_URL . 'assets/css/manual-adjustment.css', [ DDFW_Assets::$framework_css_handle ], filemtime( DDWCWM_PLUGIN_FILE . 'assets/css/manual-adjustment.css' ) );
					wp_enqueue_script( 'ddwcwm-manual-adjustment-script', DDWCWM_PLUGIN_URL . 'assets/js/manual-adjustment.js', [ 'ddwcwm-admin-script' ], filemtime( DDWCWM_PLUGIN_FILE . 'assets/js/manual-adjustment.js' ), true );
				}

				if ( ! empty( $_GET['tab'] ) && 'layout' === sanitize_key( wp_unslash( $_GET['tab'] ) ) ) {
					wp_enqueue_media();
				}

				wp_enqueue_style( 'ddwcwm-admin-style', DDWCWM_PLUGIN_URL . 'assets/css/admin.css', [ DDFW_Assets::$framework_css_handle ], filemtime( DDWCWM_PLUGIN_FILE . 'assets/css/admin.css' ) );

				wp_enqueue_script( 'ddwcwm-admin-script', DDWCWM_PLUGIN_URL . 'assets/js/admin.js', [ DDFW_Assets::$framework_js_handle, 'wp-util' ], filemtime( DDWCWM_PLUGIN_FILE . 'assets/js/admin.js' ), true );

				wp_localize_script( 'ddwcwm-admin-script', 'ddwcwmAdminObj', [
					'ajax' => [
						'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
						'ajaxNonce' => wp_create_nonce( 'ddwcwm-nonce' ),
					],
				] );
			}
		}
		/**
		 * Change the admin footer text function.
		 *
		 * @param  string $footer_text text to be rendered in the footer.
		 * @return string
		 */
		public function ddwcwm_set_admin_footer_text( $footer_text ) {
			if ( ! current_user_can( 'manage_woocommerce' ) || ! function_exists( 'wc_get_screen_ids' ) ) {
				return $footer_text;
			}
			$current_screen = get_current_screen();
			$wc_pages       = wc_get_screen_ids();

			// Set only WC pages.
			$wc_pages = array_diff( $wc_pages, [ 'profile', 'user-edit' ] );

			/**
			 * Check to make sure we're on a plugin page.
			 * 
			 * @since 1.0.0
			 */
			if ( isset( $current_screen->base ) && 'devdiggers-plugins_page_ddwcwm-dashboard' === $current_screen->base ) {
				// Change the footer text.
				$footer_text = sprintf(
					/* translators: %s for a tag */
					esc_html__( 'If you really like our plugin, please leave us a %s rating, we\'ll really appreciate it.', 'devdiggers-wallet-for-woocommerce' ), '<a href="//devdiggers.com/product/woocommerce-wallet-management/#reviews" target="_blank" title="' . esc_attr__( 'Review', 'devdiggers-wallet-for-woocommerce' ) . '" aria-label="' . esc_attr__( 'Review', 'devdiggers-wallet-for-woocommerce' ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 32" height="10"><path d="M16 26.534L6.111 32 8 20.422l-8-8.2 11.056-1.688L16 0l4.944 10.534L32 12.223l-8 8.2L25.889 32zm40 0L46.111 32 48 20.422l-8-8.2 11.056-1.688L56 0l4.944 10.534L72 12.223l-8 8.2L65.889 32zm40 0L86.111 32 88 20.422l-8-8.2 11.056-1.688L96 0l4.944 10.534L112 12.223l-8 8.2L105.889 32zm40 0L126.111 32 128 20.422l-8-8.2 11.056-1.688L136 0l4.944 10.534L152 12.223l-8 8.2L145.889 32zm40 0L166.111 32 168 20.422l-8-8.2 11.056-1.688L176 0l4.944 10.534L192 12.223l-8 8.2L185.889 32z" fill="#F5A623" fill-rule="evenodd"/></svg></a>'
				);
			}

			return $footer_text;
		}
	}
}
