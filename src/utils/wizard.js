"use strict";

// Utility functions
export const ddwcwm_utils = {
	// Show/hide elements
	showElement: ( element ) => element?.classList.remove( 'ddfw-hide' ),
	hideElement: ( element ) => element?.classList.add( 'ddfw-hide' ),

	// Update progress bar
	updateProgress: ( progressWrap, processed, total ) => {
		if ( ! progressWrap ) return;

		const percent = Math.min( ( processed / total ) * 100, 100 ).toFixed( 1 );
		const bar = progressWrap.querySelector( '.ddwcwm-progress-bar' );
		const label = progressWrap.querySelector( '.ddwcwm-progress-label' );

		if ( bar ) {
			bar.style.width = percent + '%';
			bar.setAttribute( 'data-percent', percent );
		}
		if ( label ) {
			const progressText = label.querySelector( '.ddwcwm-progress-text' );
			if ( progressText ) {
				progressText.textContent = `${ processed }/${ total }`;
			} else {
				label.innerHTML = `<span id="ddwcwm-import-progress">${ processed }</span>/${ total }`;
			}
		}
	},

	// Create template
	renderTemplate: ( templateId, data ) => {
		return ( typeof wp !== 'undefined' && wp.template ) ? wp.template( templateId )( data ) : '';
	},

	// Format file size
	formatFileSize: ( bytes ) => {
		if ( bytes === 0 ) return '0 Bytes';
		const k = 1024;
		const sizes = [ 'Bytes', 'KB', 'MB', 'GB' ];
		const i = Math.floor( Math.log( bytes ) / Math.log( k ) );
		return parseFloat( ( bytes / Math.pow( k, i ) ).toFixed( 2 ) ) + ' ' + sizes[ i ];
	}
};

// Step navigation utility
export const navigateToStep = ( stepNumber ) => {
	// Hide all step content
	document.querySelectorAll( '.ddwcwm-step-content' ).forEach( el => {
		el.classList.remove( 'active' );
		ddwcwm_utils.hideElement( el );
	} );

	// Reset step navigation
	document.querySelectorAll( '.ddwcwm-step' ).forEach( el => {
		el.classList.remove( 'active', 'completed' );
	} );

	// Mark previous steps as completed
	for ( let i = 1; i < stepNumber; i++ ) {
		const prevStep = document.querySelector( `.ddwcwm-step[data-step="${ i }"]` );
		if ( prevStep ) prevStep.classList.add( 'completed' );
	}

	// Activate current step
	const step = document.querySelector( `.ddwcwm-step[data-step="${ stepNumber }"]` );
	const content = document.querySelector( `.ddwcwm-step-content[data-step="${ stepNumber }"]` );
	if ( step ) step.classList.add( 'active' );
	if ( content ) {
		content.classList.add( 'active' );
		ddwcwm_utils.showElement( content );
	}
};

// Generic error handler with custom selector
export const showCustomError = ( message, selector, templateId ) => {
	const element = document.querySelector( selector );
	if ( element ) {
		const errorData = { message: message };
		const templateHTML = ddwcwm_utils.renderTemplate( templateId, errorData );
		if ( templateHTML ) {
			element.innerHTML = templateHTML;
		}
	}
};
