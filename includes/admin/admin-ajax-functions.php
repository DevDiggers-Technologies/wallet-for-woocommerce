<?php
/**
 * Admin ajax functions class
 *
 * @package Wallet Management for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes\Admin;

use DDWCWalletManagement\Helper\Transactions\DDWCWM_Transactions_Helper;
use DDWCWalletManagement\Helper\Users\DDWCWM_Users_Helper;
use DDWCWalletManagement\Includes\Admin\Importer\DDWCWM_Users_Importer_Controller;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Admin_Ajax_Functions' ) ) {
	/**
	 * Admin Ajax fuctions class
	 */
	class DDWCWM_Admin_Ajax_Functions {

		/**
         * Ajax callback for importing one batch of products from a CSV.
         */
        public function ddwcwm_do_ajax_users_import() {
            check_ajax_referer( 'ddwcwm-users-import', 'security' );

            if ( ! current_user_can( 'manage_woocommerce' ) ) {
                wp_send_json_error( [ 'message' => esc_html__( 'Insufficient permissions.', 'wallet-management-for-woocommerce' ) ] );
            }

            if ( ! isset( $_POST[ 'file' ] ) ) { // PHPCS: input var ok.
                wp_send_json_error( [ 'message' => esc_html__( 'Insufficient privileges to import products.', 'wallet-management-for-woocommerce' ) ] );
            }

            $file   = sanitize_text_field( wp_unslash( $_POST[ 'file' ] ) );
            $params = [
                'delimiter'       => ! empty( $_POST[ 'delimiter' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'delimiter' ] ) ) : ',',
                'start_pos'       => isset( $_POST[ 'position' ] ) ? absint( $_POST[ 'position' ] ) : 0,
                'mapping'         => isset( $_POST[ 'mapping' ] ) ? map_deep( wp_unslash( $_POST[ 'mapping' ] ), 'sanitize_text_field' ) : [],
                'update_existing' => ! empty( $_POST[ 'update_existing' ] ),
                'lines'           => apply_filters( 'ddwcwm_users_import_batch_size', 30 ),
                'parse'           => true,
			];

            // Log failures.
            if ( 0 !== $params[ 'start_pos' ] ) {
                $error_log = array_filter( (array) get_user_option( 'ddwcwm_users_import_error_log' ) );
            } else {
                $error_log = [];
            }

            $importer         = DDWCWM_Users_Importer_Controller::get_importer( $file, $params );
            $results          = $importer->import();
            $percent_complete = $importer->get_percent_complete();
            $error_log        = array_merge( $error_log, $results[ 'failed' ], $results[ 'skipped' ] );

            update_user_option( get_current_user_id(), 'ddwcwm_users_import_error_log', $error_log );

            if ( 100 === $percent_complete ) {
                // Send success.
                wp_send_json_success(
                    [
                        'position'   => 'done',
                        'percentage' => 100,
                        'url'        => add_query_arg( [ '_wpnonce' => wp_create_nonce( 'ddwcwm-csv-importer' ) ], admin_url( 'admin.php?page=wallet-management-for-woocommerce&action=ddwcwm-users-import&step=done' ) ),
                        'imported'   => count( $results[ 'imported' ] ),
                        'failed'     => count( $results[ 'failed' ] ),
                        'skipped'    => count( $results[ 'skipped' ] ),
					]
                );
            } else {
                wp_send_json_success(
                    [
                        'position'   => $importer->get_file_position(),
                        'percentage' => $percent_complete,
                        'imported'   => count( $results[ 'imported' ] ),
                        'failed'     => count( $results[ 'failed' ] ),
                        'skipped'    => count( $results[ 'skipped' ] ),
					]
                );
            }
        }

		/**
		 * AJAX handler for batch manual transaction (Credit/Debit)
		 *
		 * @return void
		 */
		public function ddwcwm_batch_manual_transaction() {
			try {
				check_ajax_referer( 'ddwcwm-nonce', 'ddwcwm_nonce' );

				if ( ! current_user_can( 'manage_woocommerce' ) ) {
					wp_send_json_error( [ 'error' => esc_html__( 'Insufficient permissions.', 'wallet-management-for-woocommerce' ) ] );
				}

				// Handle both array and JSON string formats. IDs are integers, so a
				// text-field sanitize keeps the JSON intact while stripping anything unsafe.
				$raw_user_ids = isset( $_POST['user_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['user_ids'] ) ) : '';
				$user_ids     = '' !== $raw_user_ids ? json_decode( $raw_user_ids, true ) : [];

				if ( ! is_array( $user_ids ) ) {
					wp_send_json_error( [ 'error' => esc_html__( 'Invalid user_ids format.', 'wallet-management-for-woocommerce' ) ] );
				}

				$user_ids    = array_map( 'intval', $user_ids );
				$amount      = floatval( $_POST['amount'] ?? 0 );
				$action_type = sanitize_text_field( wp_unslash( $_POST['action_type'] ?? '' ) );
				$reason      = sanitize_text_field( wp_unslash( $_POST['reason'] ?? '' ) );

				if ( empty( $user_ids ) || empty( $action_type ) || $amount <= 0 ) {
					wp_send_json_error( [ 'error' => esc_html__( 'Invalid parameters.', 'wallet-management-for-woocommerce' ) ] );
				}

				$user_helper        = new DDWCWM_Users_Helper();
				$transaction_helper = new DDWCWM_Transactions_Helper();
				$results            = [];

				// Process each user in the batch
				foreach ( $user_ids as $user_id ) {
					$user_data       = get_userdata( $user_id );
					$old_wallet_balance = $user_helper->ddwcwm_get_user_wallet_balance( $user_id );

					$result = [
						'user_id'          => $user_id,
						'action_type'      => $action_type,
						'amount_requested' => wc_price( $amount ),
						'wallet_balance'   => wc_price( $old_wallet_balance ),
						'user_login'       => $user_data ? $user_data->user_login : 'Unknown',
						'display_name'     => $user_data ? $user_data->display_name : 'Unknown',
					];

					try {
						if ( 'credit' === $action_type ) {
							$new_wallet_amount = $old_wallet_balance + $amount;
							$user_helper->ddwcwm_set_user_wallet_balance( $new_wallet_amount, $user_id );

							$reference = 'manual_adjustment';

							$result['status']  = 'success';
							$result['wallet_balance'] = wc_price( $new_wallet_amount );

						} else if ( 'debit' === $action_type ) {
							if ( $old_wallet_balance >= $amount ) {
								$new_wallet_amount = $old_wallet_balance - $amount;
								$user_helper->ddwcwm_set_user_wallet_balance( $new_wallet_amount, $user_id );

								$reference = 'manual_adjustment';

								$result['status'] = 'success';
								$result['wallet_balance'] = wc_price( $new_wallet_amount );
							} else {
								$result['status'] = 'error';
								$result['message'] = esc_html__( 'Insufficient amount to debit.', 'wallet-management-for-woocommerce' );
								$results[] = $result;
								continue;
							}
						} else {
							$result['status'] = 'error';
							$result['message'] = esc_html__( 'Invalid action type', 'wallet-management-for-woocommerce' );
							$results[] = $result;
							continue;
						}

						// Save transaction and send email on success
						$data = [
							'type'      => $action_type,
							'amount'    => $amount,
							'user_id'   => $user_id,
							'note'      => $reason,
							'date'      => current_time( 'Y-m-d H:i:s' ),
							'reference' => $reference,
						];

						$transaction_helper->ddwcwm_save_transaction( $data );



					} catch ( \Exception $e ) {
						$result['status'] = 'error';
						$result['message'] = esc_html__( 'Error processing user: ', 'wallet-management-for-woocommerce' ) . $e->getMessage();
					}

					$results[] = $result;
				}

				wp_send_json_success( [ 'results' => $results ] );
			} catch ( \Exception $e ) {
				wp_send_json_error( [ 'error' => esc_html__( 'Internal error: ', 'wallet-management-for-woocommerce' ) . $e->getMessage() ] );
			}
		}

		/**
		 * AJAX handler to get all users for select all functionality
		 *
		 * @return void
		 */
		public function ddwcwm_get_all_users() {
			check_ajax_referer( 'ddwcwm-nonce', 'ddwcwm_nonce' );

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_send_json_error( [ 'error' => esc_html__( 'Insufficient permissions.', 'wallet-management-for-woocommerce' ) ] );
			}

			global $wpdb;
			$users = $wpdb->get_results(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				"SELECT ID, user_login, user_email, display_name FROM {$wpdb->users} ORDER BY display_name ASC",
				ARRAY_A
			);

			wp_send_json_success( [ 'users' => $users ] );
		}
	}
}
