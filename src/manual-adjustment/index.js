"use strict";

import './manual-adjustment.less';

var ddwcwm = jQuery.noConflict();
(() => {
	// --- Manual Adjustment Wizard Logic ---
	// Utility functions
	const toggleElement = (element, show) => {
		if (element) {
			show ? element.classList.remove('ddfw-hide') : element.classList.add('ddfw-hide');
		}
	};

	const updateProgress = (progressElement, processed, total) => {
		if (progressElement) {
			const percent = Math.min((processed / total) * 100, 100).toFixed(1);
			const progressBar = progressElement.querySelector('.ddwcwm-progress-bar');
			const progressLabel = progressElement.querySelector('.ddwcwm-progress-label');
			
			if (progressBar) {
				progressBar.style.width = percent + '%';
				progressBar.setAttribute('data-percent', percent);
			}
			
			if (progressLabel) {
				const progressText = progressLabel.querySelector('.ddwcwm-progress-text');
				if (progressText) {
					progressText.textContent = `${processed}/${total}`;
				} else {
					progressLabel.innerHTML = `<span id="ddwcwm-manual-progress">${processed}</span>/${total}`;
				}
			}
		} else {
			// Try to find the progress element again
			const newProgressElement = document.querySelector('.ddwcwm-progress-wrap');
			if (newProgressElement) {
				updateProgress(newProgressElement, processed, total);
			}
		}
	};

	const renderTemplate = (templateId, data) => {
		return (typeof wp !== 'undefined' && wp.template) ? wp.template(templateId)(data) : '';
	};

	const showError = (message) => {
		const errorTemplate = renderTemplate('ddwcwm-wizard-error', { message });
		const summaryElement = document.querySelector('.ddwcwm-step-content[data-step="2"] .ddwcwm-wizard-summary');
		if (summaryElement && errorTemplate) {
			summaryElement.innerHTML = errorTemplate;
		}
	};

	// Step navigation
	const goToStep = (stepNumber) => {
		const steps = document.querySelectorAll('.ddwcwm-step');
		const stepContents = document.querySelectorAll('.ddwcwm-step-content');
		
		steps.forEach((step, index) => {
			const stepNum = index + 1;
			step.classList.toggle('active', stepNum === stepNumber);
			step.classList.toggle('completed', stepNum < stepNumber);
		});
		
		stepContents.forEach((content, index) => {
			const isActive = index + 1 === stepNumber;
			content.classList.toggle('active', isActive);
			toggleElement(content, isActive);
		});
	};

	// User selection functionality
	let selectedUsers = [];
	let allUsers = [];

	const initializeUserSelection = () => {
		const selectAllCheckbox = document.getElementById('ddwcwm-select-all-users');
		const individualUsersSection = document.getElementById('ddwcwm-individual-users-section');
		const usersSelect = document.getElementById('ddwcwm-users');

		// Handle select all checkbox
		if (selectAllCheckbox) {
			selectAllCheckbox.addEventListener('change', (e) => {
				const isChecked = e.target.checked;
				
				// Toggle individual user selection
				if (individualUsersSection) {
					individualUsersSection.style.opacity = isChecked ? '0.5' : '1';
					individualUsersSection.style.pointerEvents = isChecked ? 'none' : 'auto';
				}
				
				if (isChecked) {
					// Get all users
					fetch(ddwcwmAdminObj.ajax.ajaxUrl, {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: new URLSearchParams({
							action: 'ddwcwm_get_all_users',
							ddwcwm_nonce: ddwcwmAdminObj.ajax.ajaxNonce
						})
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							allUsers = data.data.users;
							selectedUsers = allUsers.map(user => user.ID);
						}
					})
					.catch(error => console.error('Error fetching users:', error));
				} else {
					selectedUsers = [];
					// Clear the select2 field
					if (usersSelect && typeof jQuery !== 'undefined') {
						jQuery(usersSelect).val(null).trigger('change');
					}
				}
			});
		}

		// Handle individual user selection via Select2
		if (usersSelect && typeof jQuery !== 'undefined') {
			jQuery(usersSelect).on('select2:select select2:unselect', function() {
				selectedUsers = (jQuery(this).val() || []).map(id => parseInt(id));
			});
		}
	};

	// Batch processing functionality
	const processBatchTransaction = (userIds, amount, actionType, description) => {
		const batchSize = 50;
		const total = userIds.length;
		let processed = 0;
		let results = [];
		let successCount = 0;
		let errorCount = 0;

		// Render progress bar template
		const progressTemplate = renderTemplate('ddwcwm-progress-bar', {
			percent: 0,
			processed: 0,
			total: total,
			message: ddwcwmAdminObj.processingTransaction || 'Processing transaction...'
		});
		
		const summaryElement = document.querySelector('.ddwcwm-step-content[data-step="2"] .ddwcwm-wizard-summary');
		if (summaryElement && progressTemplate) {
			summaryElement.innerHTML = progressTemplate;
		}

		const progressElement = document.querySelector('.ddwcwm-progress-wrap');

		/**
		 * Process a batch of users
		 */
		const processBatch = (start) => {
			const batch = userIds.slice(start, start + batchSize);

			if (batch.length === 0) {
				// All batches processed, show summary
				updateProgress(progressElement, total, total);
				
				// Clear step 2 content first
				const step2Element = document.querySelector('.ddwcwm-step-content[data-step="2"]');
				const step2Summary = step2Element?.querySelector('.ddwcwm-wizard-summary');
				if (step2Summary) step2Summary.innerHTML = '';

				// Go to step 3 first
				goToStep(3);

				// Then render the summary in step 3
				setTimeout(() => {
					// Separate failed results for the details dropdown
					const failedResults = results.filter(result => result.status === 'error');
					
					const summaryTemplate = renderTemplate('ddwcwm-manual-transaction-summary', {
						successCount, errorCount, totalProcessed: total, totalUsers: total,
						results, failedResults
					});
					
					// Target the summary element in step 3 specifically
					const step3Element = document.querySelector('.ddwcwm-step-content[data-step="3"]');
					const summaryElement = step3Element?.querySelector('.ddwcwm-wizard-summary');
					if (summaryElement && summaryTemplate) {
						summaryElement.innerHTML = summaryTemplate;
					}
				}, 100);
				return;
			}

			// Process current batch
			const formData = new FormData();
			formData.append('action', 'ddwcwm_batch_manual_transaction');
			formData.append('ddwcwm_nonce', ddwcwmAdminObj.ajax.ajaxNonce);
			formData.append('user_ids', JSON.stringify(batch));
			formData.append('amount', amount);
			formData.append('action_type', actionType);
			formData.append('reason', description);

			fetch(ddwcwmAdminObj.ajax.ajaxUrl, {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success && data.data?.results) {
					// Process batch results
					data.data.results.forEach(result => {
						results.push(result);
						result.status === 'success' ? successCount++ : errorCount++;
					});
					
					processed += batch.length;
					updateProgress(progressElement, processed, total);
					processBatch(start + batchSize);
				} else {
					console.error('Batch AJAX error:', data);
					showError(data.data?.error || 'Unknown error occurred');
				}
			})
			.catch(error => {
				console.error('Batch processing error:', error);
				showError('AJAX error: ' + error.message);
			});
		};

		// Start processing first batch
		processBatch(0);
	};

	// Form validation and data collection
	const validateAndCollectFormData = () => {
		const formData = {
			amount: document.getElementById('ddwcwm-wallet-amount')?.value,
			actionType: document.getElementById('ddwcwm-action-type')?.value,
			description: document.getElementById('ddwcwm-reason')?.value,
			selectAllUsers: document.getElementById('ddwcwm-select-all-users')?.checked
		};

		// Validation
		if (!formData.amount || formData.amount <= 0) {
			alert(ddwcwmAdminObj.enterValidAmount || 'Please enter a valid amount.');
			return null;
		}
		
		let selectedUsersList = [];
		if (formData.selectAllUsers) {
			if (allUsers.length === 0) {
				alert(ddwcwmAdminObj.noUsersFound || 'No users found for processing.');
				return null;
			}
			selectedUsersList = allUsers.map(user => user.ID);
		} else {
			const usersSelect = document.getElementById('ddwcwm-users');
			if (usersSelect && typeof jQuery !== 'undefined') {
				selectedUsersList = (jQuery(usersSelect).val() || []).map(id => parseInt(id));
			}
			
			if (selectedUsersList.length === 0) {
				alert(ddwcwmAdminObj.selectUsers || 'Please select at least one user.');
				return null;
			}
		}

		return { ...formData, selectedUsers: selectedUsersList };
	};

	// Form submission
	const initializeFormSubmission = () => {
		const form = document.getElementById('ddwcwm-manual-transaction-form');
		if (form) {
			form.addEventListener('submit', (e) => {
				e.preventDefault();
				const data = validateAndCollectFormData();
				if (data) {
					goToStep(2);
					processBatchTransaction(data.selectedUsers, data.amount, data.actionType, data.description);
				}
			});
		}
	};

	// Reset form to initial state
	const resetForm = () => {
		document.getElementById('ddwcwm-manual-transaction-form')?.reset();

		// Clear selected users
		selectedUsers = [];
		allUsers = [];

		// Reset UI elements
		const selectAllCheckbox = document.getElementById('ddwcwm-select-all-users');
		const usersSelect = document.getElementById('ddwcwm-users');
		const individualUsersSection = document.getElementById('ddwcwm-individual-users-section');
		const actionTypeSelect = document.getElementById('ddwcwm-action-type');

		if (selectAllCheckbox) selectAllCheckbox.checked = false;
		if (usersSelect && typeof jQuery !== 'undefined') {
			jQuery(usersSelect).val(null).trigger('change');
		}
		if (individualUsersSection) {
			individualUsersSection.style.opacity = '1';
			individualUsersSection.style.pointerEvents = 'auto';
		}
		if (actionTypeSelect) {
			jQuery(actionTypeSelect).val('credit').trigger('change');
		}

		goToStep(1);
	};

	// Initialize start again functionality
	const initializeStartAgain = () => {
		document.addEventListener('click', (e) => {
			if (e.target.classList.contains('ddwcwm-start-again')) {
				resetForm();
			}
		});
	};

	// Initialize wizard when DOM is ready
	document.addEventListener('DOMContentLoaded', () => {
		if (document.getElementById('ddwcwm-manual-transaction-wizard')) {
			initializeUserSelection();
			initializeFormSubmission();
			initializeStartAgain();
		}
	});

})(ddwcwm);
