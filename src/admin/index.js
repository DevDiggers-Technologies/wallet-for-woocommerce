"use strict";

import './admin.less';

var ddwcwm = jQuery.noConflict();
(() => {
document.addEventListener('DOMContentLoaded', () => {
	const ddwcwmRemoveElements = elms => {
		[...elms].forEach( el => {
			el.remove();
		});
	};

	if (document.querySelectorAll('.ddwcwm-rule-wrap').length) {
		document.addEventListener('click', (e) => {
			if (e.target.matches('.ddwcwm-add-row') || e.target.closest('.ddwcwm-add-row')) {
				e.preventDefault();
				const button = e.target.closest('.ddwcwm-add-row');
				const templateId = button.getAttribute('data-template');
				const $buttonRow = ddwcwm(button).closest('tr');
				let $maxIndexField;

				if (templateId === 'ddwcwm-product-row') {
					$maxIndexField = ddwcwm('#ddwcwm-product-max-index');
				} else if (templateId === 'ddwcwm-category-row') {
					$maxIndexField = ddwcwm('#ddwcwm-category-max-index');
				} else if (templateId === 'ddwcwm-user-role-row') {
					$maxIndexField = ddwcwm('#ddwcwm-user-role-max-index');
				} else if (templateId === 'ddwcwm-payment-method-row') {
					$maxIndexField = ddwcwm('#ddwcwm-payment-method-max-index');
				} else {
					$maxIndexField = ddwcwm('#ddwcwm-max-index');
				}

				var currentIndex = parseInt($maxIndexField.val(), 10) || 0;
				currentIndex++;
				$maxIndexField.val(currentIndex);

				var template = wp.template(templateId),
					rowHtml = template({ key: currentIndex });

				$buttonRow.before(rowHtml);
				
				// Get the newly added row as a DOM element
				const newRow = $buttonRow[0].previousElementSibling;
				if (newRow) {
					initializeSelect2InRow(newRow);
				}
			}
			// Remove Row functionality
			else if (e.target.matches('.ddwcwm-remove-row') || e.target.closest('.ddwcwm-remove-row')) {
				e.preventDefault();
				if (confirm("Are you sure you want to remove this row?")) {
					ddwcwm(e.target).closest('tr').remove();
				}
			}
		});

		const ruleForm = document.getElementById('ddwcwm-rule-form');
		if (ruleForm) {
			ruleForm.addEventListener('submit', e => {
				ddwcwmRemoveElements(document.querySelectorAll(".notice-error"));
				ddwcwmFormTraverse(ruleForm, e);
			});
		}

			function initializeSelect2InRow(row) {
				// Use the global framework functions
				if (typeof window.ddfwInitializeProductsSelect2 === 'function') {
					window.ddfwInitializeProductsSelect2(row);
				}

				if (typeof window.ddfwInitializeCategoriesSelect2 === 'function') {
					window.ddfwInitializeCategoriesSelect2(row);
				}

				if (typeof window.ddfwInitializeSelect2 === 'function') {
					window.ddfwInitializeSelect2(row);
				}
			}

			// Initialize Select2 for all existing rows within the rule wrap
			document.querySelectorAll('.ddwcwm-rule-wrap').forEach(wrap => {
				initializeSelect2InRow(wrap);
			});

			var ddwcwmFormTraverse = (form, e) => {
				var invalidFormFields = wp.template( 'ddwcwm_form_data_error' );
				let formContainsError = false;
				[...form.elements].forEach( input => {
					if( input.classList.contains( 'ddwcwm-product-select' ) ) {
						const select2ContainerElement = input.nextSibling;
						select2ContainerElement.querySelector( '.select2-selection' ).style.borderColor = '';
					}
					input.style.borderColor = '';
					let invalidData = false;
					var inputVal = input.value.trim();
					if( input.closest( '.ddwcwm-rule-wrap' ) && 'hidden' !== input.type ) {
						if ( -1 == inputVal || ! inputVal ) {
							e.preventDefault();
							if( input.classList.contains( 'ddwcwm-product-select' ) ) {
								const select2ContainerElement = input.nextSibling;
								select2ContainerElement.querySelector( '.select2-selection' ).style.borderColor = 'red';
							}
							input.style.borderColor = 'red';

							formContainsError = true;
						} else {

							var numFieldsClassLists = [ 'ddwcwm-product-quantity' ];

							var floatFields = [ 'ddwcwm-amount-from', 'ddwcwm-amount-to', 'ddwcwm-cashback-amount', 'ddwcwm-product-quantity', 'ddwcwm-product-cashback', 'ddwcwm-category-cashback', 'ddwcwm-user-role-cashback', 'ddwcwm-payment-method-cashback' ];

							if ( floatFields.some( className => input.classList.contains( className ) ) ) {
								if ( isNaN( inputVal ) ) {
									e.preventDefault();
									invalidData = true;
								}
							}

							if ( numFieldsClassLists.some( className => input.classList.contains( className ) ) ) {
								if (! /^\d+$/.test(inputVal)) {
									e.preventDefault();
									invalidData = true;
								}
							}

							if (/<\s?[^\>]*\/?\s?>/i.test(inputVal)) {
								e.preventDefault();
								invalidData = true;
							}

							if( invalidData ) {
								formContainsError = true;
								input.style.borderColor = 'red';
							}
						}
					}
				});
				if ( formContainsError ) {
					document.querySelector( '.ddwcwm-rule-wrap' ).insertAdjacentHTML('beforeBegin', invalidFormFields());
				}
			}

		}

		if ( document.querySelectorAll( '.ddwcwm-view-withdraw-requests' ) ) {
			document.querySelectorAll( '.ddwcwm-view-withdraw-requests' ).forEach( element => {
				element.addEventListener( 'click', e => {
					e.preventDefault();
					const userId = e.target.getAttribute( 'data-user-id' );

					if ( document.querySelector( `#ddwcwm-wallet-withdraw-requests-${userId}` ) ) {
						document.querySelector( `#ddwcwm-wallet-withdraw-requests-${userId}` ).classList.add( 'ddwcwm-popup-open' );
					}
				} );
			} );
		}

		const ddwcwmClosePopup = element => {
			const popupElement = element.closest( '.ddwcwm-wallet-withdraw-requests-popup' );
			popupElement.classList.remove( 'ddwcwm-popup-open' );
			const messageElement = popupElement.querySelector( '.ddwcwm-message' );
			const messageParagraphElement = messageElement.querySelector( '.ddwcwm-message p' )
			messageParagraphElement.innerHTML = '';
			messageElement.classList.remove( 'ddwcwm-message-error' );
			messageElement.classList.remove( 'ddwcwm-message-success' );
			messageElement.classList.add( 'ddwcwm-hide' );
		}

		if ( document.querySelectorAll( '.ddwcwm-close-popup' ) ) {
			document.querySelectorAll( '.ddwcwm-close-popup' ).forEach( element => {
				element.addEventListener( 'click', e => {
					ddwcwmClosePopup( e.target );
				} );
			} );
		}

		if ( document.querySelectorAll( '.ddwcwm-wallet-withdraw-requests-popup' ) ) {
			document.querySelectorAll( '.ddwcwm-wallet-withdraw-requests-popup' ).forEach( element => {
				element.addEventListener( 'click', e => {
					if ( e.target.classList.contains( 'ddwcwm-wallet-withdraw-requests-popup' ) ) {
						ddwcwmClosePopup( e.target );
					}
				} );
			} );
		}

		if ( document.querySelectorAll( '.ddwcwm-withdraw-request-action' ) ) {
			document.querySelectorAll( '.ddwcwm-withdraw-request-action' ).forEach( element => {
				element.addEventListener( 'click', e => {
					e.preventDefault();
					const actionButton = e.target.closest( '.ddwcwm-withdraw-request-action' );
					actionButton.setAttribute( 'disabled', 'disabled' );

					const userId = actionButton.getAttribute( 'data-user-id' );
					const withdrawRequestId = actionButton.getAttribute( 'data-withdraw-request-id' );
					const withdrawRequestPerform = actionButton.getAttribute( 'data-withdraw-request-action' );
					const formData = new FormData();

					formData.append( 'action', 'ddwcwm_withdraw_request_action' );
					formData.append( 'ddwcwm_nonce', ddwcwmAdminObj.ajax.ajaxNonce );
					formData.append( 'user_id', userId );
					formData.append( 'withdraw_request_id', withdrawRequestId );
					formData.append( 'perform', withdrawRequestPerform );

					fetch( ddwcwmAdminObj.ajax.ajaxUrl, {
						method: "post",
						body: formData
					} )
					.then( response => response.ok ? response.json() : false ).then( response => {

						actionButton.removeAttribute( 'disabled' );

						const messageElement = actionButton.closest( '.ddwcwm-wallet-withdraw-requests-popup-content' ).querySelector( '.ddwcwm-message' );
						const messageParagraphElement = messageElement.querySelector( 'p' )
						messageParagraphElement.innerHTML = response.message;

						messageElement.classList.remove( 'ddwcwm-hide' );
						if ( response.success ) {
							messageElement.classList.add( 'ddwcwm-message-success' );
							messageElement.classList.remove( 'ddwcwm-message-error' );

							actionButton.closest( 'tr' ).remove();
						} else {
							messageElement.classList.add( 'ddwcwm-message-error' );
							messageElement.classList.remove( 'ddwcwm-message-success' );
						}

					} );
				} );
			} );
		}

	});
})(ddwcwm);