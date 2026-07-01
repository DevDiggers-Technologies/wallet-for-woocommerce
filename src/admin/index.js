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
				const $maxIndexField = ddwcwm('#ddwcwm-max-index');

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

							var floatFields = [ 'ddwcwm-amount-from', 'ddwcwm-amount-to', 'ddwcwm-cashback-amount' ];

							if ( floatFields.some( className => input.classList.contains( className ) ) ) {
								if ( isNaN( inputVal ) ) {
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
	});
})(ddwcwm);