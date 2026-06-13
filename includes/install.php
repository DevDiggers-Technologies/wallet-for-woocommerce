<?php
/**
 * Create Schema on Activation
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Install' ) ) {
	/**
	 * Activation class
	 */
	class DDWCWM_Install {
		/**
		 * Create Schema
		 */
		public static function ddwcwm_create_schema() {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			global $wpdb;

			$charset_collate = $wpdb->get_charset_collate();

			// Transactions table.
			$transactions_table = $wpdb->prefix . 'ddwcwm_transactions';
			$transactions = "CREATE TABLE $transactions_table (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`order_id` bigint(20) DEFAULT NULL,
				`reference` varchar(100) NOT NULL,
				`sender_id` bigint(20) NOT NULL,
				`user_id` bigint(20) NOT NULL,
				`amount` varchar(20) NOT NULL,
				`type` varchar(50) NOT NULL,
				`date` datetime NOT NULL,
				`expiry_date` datetime DEFAULT NULL,
				`is_expired` tinyint(1) DEFAULT 0 NOT NULL,
				`is_reminder_sent` tinyint(1) DEFAULT 0 NOT NULL,
				`note` varchar(250),
				PRIMARY KEY (id)
			) $charset_collate;";

			dbDelta( $transactions );

			$cashback_rules_table = $wpdb->prefix . 'ddwcwm_cashback_rules';
			$cashback_rules = "CREATE TABLE $cashback_rules_table (
				`id` bigint(20) NOT NULL AUTO_INCREMENT,
				`basis` varchar(20) NOT NULL,
				`basis_value` varchar(100),
				`amount_from` varchar(20),
				`amount_to` varchar(20),
				`cashback_type` varchar(20),
				`cashback_amount` varchar(20),
				`status` varchar(10) DEFAULT 'enabled' NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";

			dbDelta( $cashback_rules );

			// Trigger setup wizard for new installations.
			set_transient( 'ddfw_activation_redirect_wallet-management-for-woocommerce', true, 30 );

			DDWCWM_Install::ddwcwm_create_product();
		}

		/**
		 * Create wallet topup product function
		 *
		 * @return void
		 */
		public static function ddwcwm_create_product() {
			$product = get_page_by_path( 'ddwcwm-wallet-topup' , OBJECT, 'product' );

			if ( empty( $product->post_title ) ) {
				global $wpdb;

				$post_id = wp_insert_post( [
					'post_author' => get_current_user_ID(),
					'post_status' => 'publish',
					'post_title'  => 'Wallet Topup',
					'post_name'   => 'ddwcwm-wallet-topup',
					'post_type'   => 'product',
				] );

				wp_set_object_terms( $post_id, 'simple', 'product_type' );

				update_post_meta( $post_id, '_regular_price', '0' );
				update_post_meta( $post_id, '_visibility', 'hidden' );
				update_post_meta( $post_id, '_sku', 'ddwcwm_topup_wallet' );
				update_post_meta( $post_id, '_price', '0' );
				update_post_meta( $post_id, '_manage_stock', 'no' );
				update_post_meta( $post_id, '_stock_status', 'instock' );
				update_post_meta( $post_id, '_downloadable', 'no' );
				update_post_meta( $post_id, '_virtual', 'yes' );
				update_post_meta( $post_id, '_purchase_note', '' );
				update_post_meta( $post_id, '_featured', 'no' );
				update_post_meta( $post_id, '_sold_individually', 'yes' );
				update_post_meta( $post_id, '_backorders', 'no' );
				update_post_meta( $post_id, '_stock', '' );
				update_post_meta( $post_id, '_tax_status', 'none' );

				$wpdb->query( $wpdb->prepare( "INSERT INTO %i ( object_id, term_taxonomy_id, term_order ) VALUES ( %d, %d, %d ), ( %d, %d, %d ), ( %d, %d, %d )", $wpdb->prefix . 'term_relationships', $post_id, 6, 0, $post_id, 7, 0, $post_id, 14, 0 ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

				$url       = DDWCWM_PLUGIN_FILE . 'assets/images/wallet.png';
				$uploaddir = wp_upload_dir();
				$filename  = basename( $url );
				$filetype  = wp_check_filetype( basename( $filename ), null );

				$upload_filepath = $uploaddir[ 'path' ] . "/$filename";

				if ( ! file_exists( $upload_filepath ) ) {
					file_put_contents( $upload_filepath, file_get_contents( $url ) );
				}

				$attachment_file = [
					'guid'           => $upload_filepath,
					'post_mime_type' => $filetype[ 'type' ],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				];

				$attach_id = wp_insert_attachment( $attachment_file, $upload_filepath, $post_id );

				update_post_meta( $post_id ,'_thumbnail_id', $attach_id );

				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				$attach_data = wp_generate_attachment_metadata( $attach_id, $upload_filepath );
				wp_update_attachment_metadata( $attach_id, $attach_data );
			}
		}

		/**
		 * On plugin deactivation
		 *
		 * @return void
		 */
		public static function ddwcwm_on_plugin_deactivation() {
			wp_clear_scheduled_hook( 'ddwcwm_handle_cashback_expiry' );
		}
	}
}
