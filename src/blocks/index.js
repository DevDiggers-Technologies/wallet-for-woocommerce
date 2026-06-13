import { getSetting } from '@woocommerce/settings';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import './style.less';

const { decodeEntities } = window.wp.htmlEntities;
const { __ } = window.wp.i18n;
const { createElement } = window.wp.element;
const { registerPlugin } = window.wp.plugins;
const { ExperimentalOrderMeta } = window.wc.blocksCheckout;

// Consolidate gateway settings. Partial-payment is a Pro feature and is not registered here.
const walletSettings = getSetting( 'ddwcwm_wallet_data', {} );
const extensionSettings = getSetting( 'ddwcwm-wallet-extension_data', {} );
const settings = { ...walletSettings, ...extensionSettings };

/**
 * Helper to format currency
 */
const formatCurrency = ( value ) => {
	let balanceNum = parseFloat( value );
	if ( isNaN( balanceNum ) ) {
		return value;
	}
	let parts = balanceNum.toFixed( settings.decimals ).split( '.' );
	parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, settings.thousand_separator );
	let formatted = parts.join( settings.decimal_separator );
	return decodeEntities( `${ settings.currency_symbol }${ formatted }` );
};

/**
 * Cashback Message Plugin (cart-total cashback in Free)
 */
registerPlugin( 'ddwcwm-wallet-cashback-info', {
	render: () => {
		if ( settings.total_cashback <= 0 ) {
			return null;
		}

		const formattedCashback = formatCurrency( settings.total_cashback );
		const message = ( settings.cashback_message || __( 'You will get {cashback_amount} cashback on this order.', 'wallet-management-for-woocommerce' ) )
			.replace( '{cashback_amount}', formattedCashback );

		return createElement( ExperimentalOrderMeta, null,
			createElement( 'div', {
				className: 'ddwcwm-cashback-info',
				style: {
					padding: '16px',
					backgroundColor: '#f6f7f7',
					borderRadius: '5px',
					fontSize: '0.9em',
					color: '#1e1e1e'
				}
			}, decodeEntities( message ) )
		);
	},
	scope: 'woocommerce-checkout',
} );

/**
 * Wallet Payment Method Label component
 */
const WalletLabel = ( props ) => {
	const { PaymentMethodLabel } = props.components;
	return createElement( 'div', { style: { display: 'flex', alignItems: 'center', gap: '5px' } },
		createElement( PaymentMethodLabel, { text: decodeEntities( settings.title ) || __( 'Wallet', 'wallet-management-for-woocommerce' ) } ),
		createElement( 'span', { className: 'ddwcwm-wallet-balance-display' }, decodeEntities( settings.available_balance_text ) )
	);
};

/**
 * Wallet Payment Method registration (full pay-with-wallet)
 */
const WalletPaymentMethod = {
	name: 'ddwcwm_wallet',
	label: createElement( WalletLabel ),
	content: createElement( 'div', null, decodeEntities( settings.description || '' ) ),
	edit: createElement( 'div', null, decodeEntities( settings.description || '' ) ),
	canMakePayment: () => settings.canMakePayment,
	ariaLabel: decodeEntities( settings.title ) || __( 'Wallet', 'wallet-management-for-woocommerce' ),
	supports: {
		features: settings.supports,
	},
};

registerPaymentMethod( WalletPaymentMethod );
