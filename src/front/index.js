"use strict";

import './front.less';

/**
 * Free front-end behaviour.
 *
 * Only the cart/topup and transaction features ship in Free. All Pro operations
 * (partial payment, send/request/withdraw/refer money, OTP and money-request
 * management) live in the Pro plugin and are intentionally absent here.
 */
document.addEventListener( 'DOMContentLoaded', () => {

	// Transaction Table AJAX Pagination
	if ( document.querySelectorAll( '.ddwcwm-pagination-button' ) ) {
		document.querySelectorAll( '.ddwcwm-pagination-button' ).forEach( element => {
			element.addEventListener( 'click', e => {
				const targetElement = e.target.closest( '.ddwcwm-pagination-button' );
				const perform = targetElement.dataset.perform;
				const tableSection = targetElement.closest( '.ddwcwm-table-container' );

				let currentPage = parseInt( tableSection.querySelector( '.ddwcwm-current-page' ).value );
				const totalCount = parseInt( tableSection.querySelector( '.ddwcwm-total-count' ).value );
				const perPage = 10;

				tableSection.querySelector( '.ddwcwm-table-loader-overlay' ).style.display = 'flex';

				if ( 'next' === perform ) {
					currentPage++;
				} else if ( 'previous' === perform ) {
					currentPage--;
				}

				const formData = new FormData();
				formData.append( 'action', 'ddwcwm_get_transaction_rows' );
				formData.append( 'nonce', ddwcwmFrontObj.ajaxNonce );
				formData.append( 'current_page', currentPage );

				fetch( ddwcwmFrontObj.ajaxUrl, {
					method: 'post',
					body  : formData,
				} )
				.then( response => response.ok ? response.json() : false ).then( response => {
					if ( response.success ) {
						tableSection.querySelector( 'table tbody' ).innerHTML = response.html;
						tableSection.querySelector( '.ddwcwm-current-page' ).value = currentPage;

						if ( Math.ceil( totalCount / perPage ) > currentPage ) {
							tableSection.querySelector( '.ddwcwm-pagination-button[data-perform="next"]' ).removeAttribute( 'disabled' );
						} else {
							tableSection.querySelector( '.ddwcwm-pagination-button[data-perform="next"]' ).setAttribute( 'disabled', 'disabled' );
						}

						if ( currentPage > 1 ) {
							tableSection.querySelector( '.ddwcwm-pagination-button[data-perform="previous"]' ).removeAttribute( 'disabled' );
						} else {
							tableSection.querySelector( '.ddwcwm-pagination-button[data-perform="previous"]' ).setAttribute( 'disabled', 'disabled' );
						}
					} else {
						if ( response.message ) {
							alert( response.message );
						}
					}

					tableSection.querySelector( '.ddwcwm-table-loader-overlay' ).style.display = 'none';
				} )
				.catch( error => {
					tableSection.querySelector( '.ddwcwm-table-loader-overlay' ).style.display = 'none';
					console.error( 'Error:', error );
				} );
			} );
		} );
	}

	// Topup Presets
	if ( document.querySelectorAll( '.ddwcwm-topup-preset-item' ) ) {
		document.querySelectorAll( '.ddwcwm-topup-preset-item' ).forEach( element => {
			element.addEventListener( 'click', e => {
				const amount = e.target.closest( '.ddwcwm-topup-preset-item' ).dataset.amount;
				const topupInput = document.querySelector( '#ddwcwm-wallet-topup-amount' );
				if ( topupInput ) {
					topupInput.value = amount;
				}
			} );
		} );
	}

	// Wallet operations: open the operation popup (Send Money in Free).
	if ( document.querySelectorAll( '.ddwcwm-wallet-operations a' ) ) {
		document.querySelectorAll( '.ddwcwm-wallet-operations a' ).forEach( element => {
			element.addEventListener( 'click', e => {
				e.preventDefault();
				const operation = e.target.closest( 'a' ).getAttribute( 'data-operation' );
				if ( operation == 'send' ) {
					e.target.closest( '.ddwcwm-wallet-operations-wrapper' ).querySelector( '#ddwcwm-wallet-send-money-popup' ).classList.add( 'ddwcwm-popup-open' );
				}
			} );
		} );
	}

	const ddwcwmClosePopup = element => {
		const popupElement = element.closest( '.ddwcwm-wallet-operation-popup' );
		popupElement.classList.remove( 'ddwcwm-popup-open' );

		popupElement.querySelectorAll( 'form' ).forEach( form => {
			form.reset();
		} );

		popupElement.querySelectorAll( '.ddwcwm-message' ).forEach( messageElement => {
			messageElement.innerHTML = '';
			messageElement.classList.remove(
				'ddwcwm-notice',
				'ddwcwm-notice-success',
				'ddwcwm-notice-error'
			);
		} );
	};

	if ( document.querySelectorAll( '.ddwcwm-close-popup' ) ) {
		document.querySelectorAll( '.ddwcwm-close-popup' ).forEach( element => {
			element.addEventListener( 'click', e => {
				ddwcwmClosePopup( e.target );
			} );
		} );
	}

	if ( document.querySelectorAll( '.ddwcwm-wallet-operation-popup' ) ) {
		document.querySelectorAll( '.ddwcwm-wallet-operation-popup' ).forEach( element => {
			element.addEventListener( 'click', e => {
				if ( e.target.classList.contains( 'ddwcwm-wallet-operation-popup' ) ) {
					ddwcwmClosePopup( e.target );
				}
			} );
		} );
	}

	if ( document.querySelector( '#ddwcwm-send-money-form' ) ) {
		document.querySelector( '#ddwcwm-send-money-form' ).addEventListener( 'submit', e => {
			e.preventDefault();

			const SubmitButton = document.querySelector( '#ddwcwm-send-money-submit' );
			SubmitButton.setAttribute( 'disabled', 'disabled' );

			const formData = new FormData( e.target );
			formData.append( 'action', 'ddwcwm_send_money_to_user' );
			formData.append( 'ddwcwm_nonce', ddwcwmFrontObj.ajaxNonce );

			fetch( ddwcwmFrontObj.ajaxUrl, {
				method: 'post',
				body: formData
			} )
			.then( response => response.ok ? response.json() : false ).then( response => {
				SubmitButton.removeAttribute( 'disabled' );

				const messageElement = e.target.querySelector( '.ddwcwm-message' );
				const icon = response.success ? ddwcwmFrontObj.i18n.successIcon : ddwcwmFrontObj.i18n.errorIcon;
				messageElement.innerHTML = `<div class="ddwcwm-notice-icon">${icon}</div><div>${response.message}</div>`;

				if ( response.success ) {
					messageElement.classList.add( 'ddwcwm-notice', 'ddwcwm-notice-success' );
					messageElement.classList.remove( 'ddwcwm-notice-error' );
					e.target.reset();
				} else {
					messageElement.classList.add( 'ddwcwm-notice', 'ddwcwm-notice-error' );
					messageElement.classList.remove( 'ddwcwm-notice-success' );
				}
			} );
		} );
	}

} );
