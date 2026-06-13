"use strict";
import './import.less';
import { ddwcwm_utils, navigateToStep, showCustomError } from '../utils/wizard.js';

// Import-specific translation strings
const ddwcwm_import_i18n = {
	noRowsImported  : ddwcwmImportObject.i18n.noRowsImported,
	allRowsImported : ddwcwmImportObject.i18n.allRowsImported,
	processingImport: ddwcwmImportObject.i18n.processingImport,
	importError     : ddwcwmImportObject.i18n.importError
};

// Import-specific error handler
const showImportError = ( message ) => {
	showCustomError( message, '.ddwcwm-wizard-summary', 'ddwcwm-wizard-error' );
};

document.addEventListener( 'DOMContentLoaded', () => {
	let csvHeader = [];
	let csvRows = [];

    // Step navigation
	document.querySelectorAll( '.ddwcwm-next-step' ).forEach( btn => {
		btn.addEventListener( 'click', function() {
			const next = this.getAttribute( 'data-next' );
			navigateToStep( next );
		} );
	} );

	document.querySelectorAll( '.ddwcwm-prev-step' ).forEach( btn => {
		btn.addEventListener( 'click', function() {
			const prev = this.getAttribute( 'data-prev' );
			navigateToStep( prev );
		} );
	} );

    // Drag & drop
	const dropzone = document.getElementById( 'ddwcwm-dropzone' );
	const importFileInput = document.getElementById( 'ddwcwm-import-file' );

	if ( dropzone && importFileInput ) {
		dropzone.addEventListener( 'click', () => importFileInput.click() );
		dropzone.addEventListener( 'dragover', e => {
            e.preventDefault();
			dropzone.classList.add( 'dragover' );
		} );
		dropzone.addEventListener( 'dragleave', e => {
            e.preventDefault();
			dropzone.classList.remove( 'dragover' );
		} );
		dropzone.addEventListener( 'drop', e => {
            e.preventDefault();
			dropzone.classList.remove( 'dragover' );
			const files = e.dataTransfer.files;
            importFileInput.files = files;
			handleFile( files[ 0 ] );
		} );
		importFileInput.addEventListener( 'change', function( e ) {
			handleFile( this.files[ 0 ] );
		} );
	}

	/**
	 * Handle file upload
	 */
	const handleFile = file => {
		if ( ! file ) {
			return;
		}

		// Use template to render file info
		const fileInfo = document.querySelector( '.ddwcwm-import-file-info' );
		if ( fileInfo ) {
			const fileData = {
				fileName: file.name,
				fileSize: ddwcwm_utils.formatFileSize( file.size )
			};

			const templateHTML = ddwcwm_utils.renderTemplate( 'ddwcwm-file-info', fileData );

			if ( templateHTML ) {
				fileInfo.innerHTML = templateHTML;
				ddwcwm_utils.showElement( fileInfo );
			} else {
				fileInfo.textContent = file.name;
				ddwcwm_utils.showElement( fileInfo );
			}
		}

		const reader = new FileReader();
		reader.onload = function( e ) {
			const lines = e.target.result.split( /\r?\n/ ).filter( Boolean );
			csvHeader = lines[ 0 ].split( ',' );
			// Store full CSV data for processing
			window.ddwcwm_full_csv_lines = lines;
			// Only use first 3 rows for preview
			csvRows = lines.slice( 1, 4 ).map( l => l.split( ',' ) );
            renderMappingTable();
			const nextBtn = document.querySelector( '.ddwcwm-next-step[data-next="2"]' );
			if ( nextBtn ) {
				nextBtn.disabled = false;
			}
		};
		reader.readAsText( file );
	}

	/**
	 * Render mapping table
	 */
    const renderMappingTable = () => {
        // Populate select options
		document.querySelectorAll( '.ddwcwm-map-select' ).forEach( sel => {
			const field = sel.getAttribute( 'data-field' );
			const defaultVal = sel.getAttribute( 'data-default' );
			
			// Clear existing options except first
			while ( sel.options.length > 1 ) sel.remove( 1 );
			
			// Add CSV header options
			csvHeader.forEach( col => {
				const opt = document.createElement( 'option' );
                opt.value = col;
                opt.textContent = col;
				sel.appendChild( opt );
			} );
			
			// Auto-select default if present
			sel.value = csvHeader.includes( defaultVal ) ? defaultVal : '';
			
			// Trigger select2 if available
			if ( typeof jQuery !== 'undefined' && jQuery( sel ).data( 'select2' ) ) {
				jQuery( sel ).trigger( 'change.select2' );
			}
		} );

        // Fill example data
		document.querySelectorAll( '#ddwcwm-mapping-table tbody tr' ).forEach( tr => {
			const field = tr.querySelector( 'select.ddwcwm-map-select' ).getAttribute( 'data-field' );
			const colIdx = csvHeader.indexOf( field );
			const td = tr.querySelector( '.ddwcwm-example-data' );
			
			if ( td && colIdx !== -1 ) {
				td.textContent = csvRows.map( r => r[ colIdx ] || '' ).join( '; ' );
			}
		} );
    };

    // Batch import logic
	const importForm = document.getElementById( 'ddwcwm-import-form' );
	if ( importForm ) {
		importForm.addEventListener( 'submit', function( e ) {
            e.preventDefault();

            // Gather mapping
			const mapping = {};
			document.querySelectorAll( '.ddwcwm-map-select' ).forEach( function( sel ) {
				const field = sel.getAttribute( 'data-field' );
				mapping[ field ] = sel.value;
			} );

            // Validate that at least one user identifier is mapped
            const userIdentifiers = [ 'id', 'username', 'email' ];
            const hasUserIdentifier = userIdentifiers.some( field => mapping[ field ] && mapping[ field ].trim() !== '' );
            
            if ( ! hasUserIdentifier ) {
                alert( ddwcwmImportObject.i18n.mapUserIdentifier );
                return;
            }

            // Validate that wallet_balance is mapped
            if ( ! mapping.wallet_balance || mapping.wallet_balance.trim() === '' ) {
                alert( ddwcwmImportObject.i18n.mapBalanceField );
                return;
            }

			// Navigate to step 3 first
			navigateToStep( 3 );

            // Gather action
			const actionRadio = document.querySelector( 'input[name="ddwcwm_import_action"]:checked' );
			const actionVal = actionRadio ? actionRadio.value : 'override';

            // Manual description
			const manualDesc = document.getElementById( 'ddwcwm-manual-description' );
			const manualDescVal = manualDesc ? manualDesc.value : '';

            // Prepare data for batch
            let header = csvHeader;
            let allRows = [];
			if ( window.ddwcwm_full_csv_lines ) {
				header = window.ddwcwm_full_csv_lines[ 0 ].split( ',' );
				for ( let i = 1; i < window.ddwcwm_full_csv_lines.length; i++ ) {
					const row = window.ddwcwm_full_csv_lines[ i ].split( ',' );
					allRows.push( row );
                }
            } else {
                allRows = csvRows;
            }

            // Convert to array of objects for mapping
			const dataRows = allRows.map( row => {
				const obj = {};
				header.forEach( ( col, idx ) => {
					obj[ col ] = row[ idx ] || '';
				} );
                return obj;
			} );

            // Batch size
			const batchSize = 50;
			const total = dataRows.length;
            let processed = 0;
            let results = [];
			const importSummary = document.querySelector( '.ddwcwm-wizard-summary' );

			// Show progress bar using wp.template
			let progressWrap = document.querySelector( '.ddwcwm-progress-wrap' );
			if ( ! progressWrap && importSummary ) {
				const progressData = { processed: 0, total: total, percent: 0, message: ddwcwm_import_i18n.processingImport };
				const templateHTML = ddwcwm_utils.renderTemplate( 'ddwcwm-progress-bar', progressData );

				if ( templateHTML ) {
					importSummary.insertAdjacentHTML( 'beforeend', templateHTML );
				}
			} else if ( progressWrap ) {
				ddwcwm_utils.showElement( progressWrap );
				ddwcwm_utils.updateProgress( progressWrap, 0, total );
			}

			/**
			 * Process batch
			 */
			const processBatch = start => {
				const batch = dataRows.slice( start, start + batchSize );
				if ( batch.length === 0 ) {
					const totalRows = dataRows.length;
					const importedArr = results.filter( r => r.status === 'success' );
					const notImportedArr = results.filter( r => r.status === 'error' );
					const imported = importedArr.length;
					const duplicateRows = results.filter( r => r.message && r.message.toLowerCase().includes( 'duplicate' ) ).length;
					const notImported = notImportedArr.length;

					const progressWrap = document.querySelector( '.ddwcwm-progress-wrap' );
					if ( progressWrap ) {
						progressWrap.classList.add( 'ddfw-hide' );
					}

					let importedListHTML = '';
					let notImportedListHTML = '';

					if ( typeof wp !== 'undefined' && wp.template ) {
						importedListHTML = wp.template( 'ddwcwm-import-summary-list' )( {
							items: importedArr.map( it => Object.assign( {}, it, { displayRow: ( it.absoluteRow || ( it.row + 1 ) ) } ) ),
							emptyText: ddwcwm_import_i18n.noRowsImported
						} );
						notImportedListHTML = wp.template( 'ddwcwm-import-summary-list' )( {
							items: notImportedArr.map( it => Object.assign( {}, it, { displayRow: ( it.absoluteRow || ( it.row + 1 ) ) } ) ),
							emptyText: ddwcwm_import_i18n.allRowsImported
						} );

						const summaryTmpl = wp.template( 'ddwcwm-wizard-summary-card' );
						const summaryHTML = summaryTmpl( {
							totalRows: totalRows,
							totalCols: header.length,
							duplicateRows: duplicateRows,
							imported: imported,
							notImported: notImported,
							importedListHTML: importedListHTML,
							notImportedListHTML: notImportedListHTML
						} );

						const importSummary = document.querySelector( '.ddwcwm-wizard-summary' );
						if ( importSummary ) {
                        	importSummary.innerHTML = summaryHTML;
							const finalStep = document.querySelector( '.ddwcwm-step-content-final' );
							if ( finalStep ) {
								document.querySelectorAll( '.ddwcwm-step-content-final>h2, .ddwcwm-step-content-final>.ddwcwm-step-description' ).forEach( el => el.classList.add( 'ddfw-hide' ) );
							}
							bindSummaryEvents();
                        }
                    }
                    return;
                }

				const formData = new FormData();
				formData.append( 'action', 'ddwcwm_batch_import_wallets' );
				formData.append( 'nonce', ddwcwmImportObject.ajax.ajaxNonce );
				formData.append( 'rows', JSON.stringify( batch ) );
				formData.append( 'mapping', JSON.stringify( mapping ) );
				formData.append( 'action_type', actionVal );
				formData.append( 'manual_description', manualDescVal );
				formData.append( 'start_index', start );

				fetch( ddwcwmImportObject.ajax.ajaxUrl, {
                    method: 'POST',
					body  : formData
				} )
                .then( r => r.json() )
                .then( res => {
                    if ( res.success && res.data && res.data.results ) {
                        const adjustedResults = res.data.results.map( ( item, idx ) => {
                            const batchRelativeIndex = typeof item.row === 'number' ? item.row : idx;
                            const absoluteRow = start + batchRelativeIndex + 1;
                            return Object.assign( {}, item, {
                                row: batchRelativeIndex,
                                absoluteRow: absoluteRow
                            } );
                        } );
                        results = results.concat( adjustedResults );
                        processed += batch.length;

                        const progressWrap = document.querySelector( '.ddwcwm-progress-wrap' );
                        if ( progressWrap ) {
                            if ( typeof wp !== 'undefined' && wp.template ) {
                                const tmpl = wp.template( 'ddwcwm-progress-bar' );
                                progressWrap.outerHTML = tmpl( {
                                    processed: processed,
                                    total: total,
                                    percent: ( ( processed / total ) * 100 ).toFixed( 1 ),
                                    message: ddwcwm_import_i18n.processingImport
                                } );
                            } else {
                                ddwcwm_utils.updateProgress( progressWrap, processed, total );
                            }
                        }
                        processBatch( start + batchSize );
                    } else {
                        showImportError( res.data && res.data.error ? res.data.error : ddwcwmImportObject.i18n.unknownError );
                    }
                } )
                .catch( err => {
                    showImportError( ddwcwmImportObject.i18n.ajaxError + ' ' + err );
                } );
			}

			processBatch( 0 );
		} );
	}

	/**
	 * Helper function to bind summary events
	 */
	const bindSummaryEvents = () => {
		const card = document.querySelector( '.ddwcwm-wizard-summary-card' );
		if ( ! card ) {
			return;
		}

		card.querySelectorAll( '.ddwcwm-import-summary-toggle' ).forEach( toggle => {
			if ( ! toggle.dataset.toggleBound ) {
				toggle.addEventListener( 'click', function() {
					const type = this.getAttribute( 'data-toggle' );
					const dropdown = card.querySelector( '.ddwcwm-' + type + '-list' );
					if ( dropdown ) {
						const isOpen = ! dropdown.classList.contains( 'ddfw-hide' );
						if ( isOpen ) {
							dropdown.classList.add( 'ddfw-hide' );
						} else {
							dropdown.classList.remove( 'ddfw-hide' );
						}
						this.classList.toggle( 'active', ! isOpen );
					}
				} );
				toggle.dataset.toggleBound = '1';
			}
		} );

		const doneBtn = card.querySelector( '.ddwcwm-import-summary-done' );
		if ( doneBtn && ! doneBtn.dataset.bound ) {
			doneBtn.addEventListener( 'click', e => {
				e.preventDefault();
				window.location.reload();
			} );
			doneBtn.dataset.bound = '1';
		}
	}
} );
