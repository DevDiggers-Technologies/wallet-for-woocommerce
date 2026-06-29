<?php
/**
 * Front ajax functions class
 *
 * @package DevDiggers Wallet for WooCommerce
 * @version 1.0.0
 */

namespace DDWCWalletManagement\Includes\Front;

use DDWCWalletManagement\Helper\Transactions\DDWCWM_Transactions_Helper;
use DDWCWalletManagement\Helper\Users\DDWCWM_Users_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCWM_Front_Ajax_Functions' ) ) {
	/**
	 * Front Ajax fuctions class
	 */
	class DDWCWM_Front_Ajax_Functions {


		/**
		 * Send money to user (peer-to-peer wallet transfer).
		 *
		 * @return void
		 */
		public function ddwcwm_send_money_to_user() {
			if ( check_ajax_referer( 'ddwcwm-nonce', 'ddwcwm_nonce', false ) && is_user_logged_in() ) {
				$email  = ! empty( $_POST['ddwcwm_email'] ) && is_email( wp_unslash( $_POST['ddwcwm_email'] ) ) ? sanitize_email( wp_unslash( $_POST['ddwcwm_email'] ) ) : '';
				$amount = ! empty( $_POST['ddwcwm_amount'] ) ? floatval( wp_unslash( $_POST['ddwcwm_amount'] ) ) : '';
				$note   = ! empty( $_POST['ddwcwm_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ddwcwm_note'] ) ) : '';

				$amount = apply_filters( 'ddwcwm_modify_amount_to_base_currency', $amount );

				if ( empty( $email ) || empty( $amount ) ) {
					$response = [
						'success' => false,
						'message' => esc_html__( 'Email or amount fields are either empty or invalid.', 'devdiggers-wallet-for-woocommerce' ),
					];
				} else {
					$error = 0;

					if ( ! email_exists( $email ) ) {
						$response = [
							'success' => false,
							'message' => esc_html__( 'Email you entered does not exist.', 'devdiggers-wallet-for-woocommerce' ),
						];
						$error = 1;
					}

					$user = wp_get_current_user();

					if ( $user->user_email === $email ) {
						$response = [
							'success' => false,
							'message' => esc_html__( 'Cannot transfer money to yourself.', 'devdiggers-wallet-for-woocommerce' ),
						];
						$error = 1;
					}

					global $ddwcwm_wallet;

					$user_helper                 = new DDWCWM_Users_Helper();
					$current_user_wallet_balance = $user_helper->ddwcwm_get_user_wallet_balance();

					if ( empty( $error ) && $amount > $current_user_wallet_balance ) {
						$response = [
							'success' => false,
							'message' => esc_html__( 'You do not have enough wallet balance to send.', 'devdiggers-wallet-for-woocommerce' ),
						];
						$error = 1;
					}

					if ( empty( $error ) ) {
						$current_user_wallet_balance -= $amount;

						$user_id = get_current_user_id();

						$user_helper->ddwcwm_set_user_wallet_balance( $current_user_wallet_balance, $user_id );

						do_action( 'ddwcwm_after_send_money_from_wallet', $user_id, $amount );

						$receiver_id = get_user_by( 'email', $email )->ID;

						$receiver_user_wallet_balance = $user_helper->ddwcwm_get_user_wallet_balance( $receiver_id );

						$receiver_user_wallet_balance += $amount;

						$user_helper->ddwcwm_set_user_wallet_balance( $receiver_user_wallet_balance, $receiver_id );

						$data = [
							'type'      => 'transfer',
							'amount'    => $amount,
							'sender_id' => $user_id,
							'user_id'   => $receiver_id,
							'note'      => $note,
							'date'      => current_time( 'Y-m-d H:i:s' ),
							'reference' => 'wallet_transfer',
						];

						$transaction_helper = new DDWCWM_Transactions_Helper();

						$transaction_helper->ddwcwm_save_transaction( $data );

						$response = [
							'success' => true,
							/* translators: %s: transferred wallet amount. */
							'message' => sprintf( esc_html__( 'Wallet amount %s transferred successfully.', 'devdiggers-wallet-for-woocommerce' ), wp_strip_all_tags( wc_price( apply_filters( 'ddwcwm_modify_amount_to_multi_currency', $amount ) ) ) ),
						];
					}
				}
			} else {
				$response = [
					'success' => false,
					'message' => esc_html__( 'Security check failed!', 'devdiggers-wallet-for-woocommerce' ),
				];
			}

			wp_send_json( $response );
			die;
		}

		/**
		 * Get transaction table rows function
		 *
		 * @return void
		 */
		public function ddwcwm_get_transaction_rows() {
			$response = [];
			if ( check_ajax_referer( 'ddwcwm-nonce', 'nonce', false ) ) {
				if ( ! empty( $_POST['current_page'] ) ) {
					$current_page = sanitize_text_field( wp_unslash( $_POST['current_page'] ) );
					$per_page     = 10;
					$offset       = 1 == $current_page ? 0 : ( $current_page - 1 ) * $per_page;
					$html         = '';

					$user_helper  = new DDWCWM_Users_Helper();
					$transactions = $user_helper->ddwcwm_get_user_transactions( $per_page, $offset );
					
					ob_start();
					$this->ddwcwm_render_transaction_table_rows( $transactions );
					$html = ob_get_clean();

					$response = [
						'success' => true,
						'message' => esc_html__( 'Table rows fetched!', 'devdiggers-wallet-for-woocommerce' ),
						'html'    => $html,
					];
				} else {
					$response = [
						'success' => false,
						'message' => esc_html__( 'Arguments are missing.', 'devdiggers-wallet-for-woocommerce' ),
					];
				}
			} else {
				$response = [
					'success' => false,
					'message' => esc_html__( 'Security check failed!', 'devdiggers-wallet-for-woocommerce' ),
				];
			}
			wp_send_json( $response );
			die;
		}

		/**
		 * Render transaction table rows
		 *
		 * @param array $transactions
		 * @return void
		 */
		public function ddwcwm_render_transaction_table_rows( $transactions ) {
			$user_id = get_current_user_id();

			if ( ! empty( $transactions ) ) {
				foreach ( $transactions as $key => $transaction ) {
					$order_id              = $transaction[ 'order_id' ];
					$order                 = wc_get_order( $order_id );
					$order_currency        = $order ? $order->get_currency() : '';
					$order_currency        = apply_filters( 'ddwcwm_modify_transaction_currency', $order_currency, $order_id, $transaction );
					$transaction_type      = $transaction[ 'type' ];
					$transaction_reference = $transaction[ 'reference' ];

					$transaction[ 'amount' ] = apply_filters( 'ddwcwm_modify_amount_to_multi_currency', $transaction[ 'amount' ] );

					ob_start();
					if ( $transaction_type == 'credit' ) {
						?>
						<span class="ddwcwm-credit">+<?php echo wp_kses_post( wc_price( $transaction[ 'amount' ], [ 'currency' => $order_currency ] ) ) ?></span>
						<?php
					} else if ( $transaction_type == 'transfer' ) {
						$sender_id = $transaction[ 'sender_id' ];
						if ( $user_id == $sender_id ) {
							?>
							<span class="ddwcwm-debit">-<?php echo wp_kses_post( wc_price( $transaction[ 'amount' ], [ 'currency' => $order_currency ] ) ) ?></span>
							<?php
						} else {
							?>
							<span class="ddwcwm-credit">+<?php echo wp_kses_post( wc_price( $transaction[ 'amount' ], [ 'currency' => $order_currency ] ) ) ?></span>
							<?php
						}
						?>
						<?php
					} else if ( $transaction_type == 'debit' ) {
						?>
						<span class="ddwcwm-debit">-<?php echo wp_kses_post( wc_price( $transaction[ 'amount' ], [ 'currency' => $order_currency ] ) ) ?></span>
						<?php
					}
					$amount = ob_get_contents();
					ob_end_clean();

					?>
					<tr>
						<td>
							<div class="ddwcwm-transaction-id-column">
								<div class="ddwcwm-transaction-id">#<?php echo esc_html( $transaction[ 'id' ] ); ?></div>
								<div class="ddwcwm-transaction-date"><?php echo esc_html( date_i18n( 'F j, Y H:i:s', strtotime( $transaction[ 'date' ] ) ) ); ?></div>
							</div>
						</td>
						<td>
							<div class="ddwcwm-transaction-context-column">
								<div class="ddwcwm-type-tier">
									<mark class="ddwcwm-status"><?php echo esc_html( DDWCWM_Transactions_Helper::ddwcwm_get_transactions_translation( $transaction_type ) ); ?></mark>
								</div>
								<div class="ddwcwm-reference-tier">
									<span class="ddwcwm-label-mini"><?php esc_html_e( 'Ref:', 'devdiggers-wallet-for-woocommerce' ); ?></span> <em><?php echo esc_html( DDWCWM_Transactions_Helper::ddwcwm_get_transactions_translation( $transaction_reference ) ); ?></em>
								</div>
								<?php if ( ! empty( $transaction['sender_id'] ) || ! empty( $order_id ) ) : ?>
									<div class="ddwcwm-order-tier">
										<span class="ddwcwm-label-mini"><?php esc_html_e( 'Link:', 'devdiggers-wallet-for-woocommerce' ); ?></span> <?php 
											if ( ! empty( $transaction['sender_id'] ) ) {
												$sender_id = $transaction['sender_id'];
												$sender = get_user_by( 'ID', $sender_id );

												if ( $user_id == $sender_id ) {
													$recipient_id = $transaction['user_id'];
													$recipient    = get_userdata( $recipient_id );
													if ( $recipient ) {
														/* translators: %s: recipient email and user ID. */
														echo sprintf( esc_html__( 'Receiver: %s', 'devdiggers-wallet-for-woocommerce' ), esc_html( $recipient->user_email . ' (#' . $recipient_id . ')' ) );
													} else {
														/* translators: %d: recipient user ID. */
														echo sprintf( esc_html__( 'Receiver: #%d', 'devdiggers-wallet-for-woocommerce' ), absint( $recipient_id ) );
													}
												} else {
													if ( $sender ) {
														/* translators: %s: sender email and user ID. */
														echo sprintf( esc_html__( 'Sender: %s', 'devdiggers-wallet-for-woocommerce' ), esc_html( $sender->user_email . ' (#' . $sender_id . ')' ) );
													} else {
														/* translators: %d: sender user ID. */
														echo sprintf( esc_html__( 'Sender: #%d', 'devdiggers-wallet-for-woocommerce' ), absint( $sender_id ) );
													}
												}
											} elseif ( ! empty( $order_id ) && $order ) {
												/* translators: %s: order link HTML. */
												echo wp_kses_post( sprintf( esc_html__( 'Order: %s', 'devdiggers-wallet-for-woocommerce' ), '<a href="' . esc_url( $order->get_view_order_url() ) . '">#' . esc_html( $order_id ) . '</a>' ) );
											}
										?>
									</div>
								<?php endif; ?>
							</div>
						</td>
						<td><div class="ddwcwm-amount-column"><?php echo wp_kses_post( $amount ); ?></div></td>
						<td><div class="ddwcwm-note-column"><em><?php echo esc_html( $transaction[ 'note' ] ? stripslashes( $transaction[ 'note' ] ) : esc_html__( 'N/A', 'devdiggers-wallet-for-woocommerce' ) ); ?></em></div></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr>
					<td colspan="4" class="ddwcwm-empty-table-row"><center><?php esc_html_e( 'No transactions made yet.', 'devdiggers-wallet-for-woocommerce' ); ?></center></td>
				</tr>
				<?php
			}
		}
	}
}
