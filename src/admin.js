class AldaCurrencyUI {
	static ratesTab() {
		return document.querySelector( '#nav-tab-rates' );
	}

	static settingsTab() {
		return document.querySelector( '#nav-tab-settings' );
	}

	static ratesForm() {
		return document.querySelector( '#alda-currency-rates-form' );
	}

	static settingsForm() {
		return document.querySelector( '#alda-currency-settings-form' );
	}

	static settingsTabClickEvent( event ) {
		event.preventDefault();
		AldaCurrencyUI.ratesForm().classList.add( 'hidden' );
		AldaCurrencyUI.settingsForm().classList.remove( 'hidden' );
		AldaCurrencyUI.ratesTab().classList.remove( 'nav-tab-active' );
		AldaCurrencyUI.settingsTab().classList.add( 'nav-tab-active' );
	}

	static ratesTabClickEvent( event ) {
		event.preventDefault();
		AldaCurrencyUI.ratesForm().classList.remove( 'hidden' );
		AldaCurrencyUI.settingsForm().classList.add( 'hidden' );
		AldaCurrencyUI.ratesTab().classList.add( 'nav-tab-active' );
		AldaCurrencyUI.settingsTab().classList.remove( 'nav-tab-active' );
	}
}

class AldaCurrencySettings {
	static form() {
		return document.querySelector( '#alda-currency-settings-form' );
	}

	static submitButton() {
		return document.querySelector(
			'#alda-currency-settings-form input[type=submit]'
		);
	}

	static baseCurrencySelect() {
		return document.querySelector(
			'#alda-currency-settings-form select[name=base_currency]'
		);
	}

	static submitEvent( event ) {
		event.preventDefault();

		AldaCurrencySettings.saveBaseCurrency(
			AldaCurrencySettings.baseCurrencySelect().value
		);
	}

	static saveBaseCurrency( currencyCode ) {
		AldaCurrencySettings.submitButton().disabled = true;

		const endpointUrl =
			wpApiSettings.root + 'alda-currency/v1/base-currency';
		const request = new XMLHttpRequest();

		request.open( 'POST', endpointUrl, true );

		request.setRequestHeader(
			'Content-Type',
			'application/json;charset=UTF-8'
		);

		request.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );

		request.send( JSON.stringify( { currencyCode } ) );

		request.onreadystatechange = function () {
			if ( this.status === 200 ) {
				AldaCurrencySettings.submitButton().disabled = false;
			}
		};
	}
}

class AldaCurrencyRates {
	static form() {
		return document.querySelector( '#alda-currency-rates-form' );
	}

	static submitButton() {
		return document.querySelector(
			'#alda-currency-rates-form input[type=submit]'
		);
	}

	static enabledCheckboxes() {
		return document.querySelectorAll(
			'#alda-currency-rates-form input[type=checkbox]'
		);
	}

	static formRates() {
		const currencyRates = {};
		document
			.querySelectorAll( 'input.currency-rate' )
			.forEach( function ( element ) {
				currencyRates[ element.name ] = parseFloat( element.value );
			} );
		return currencyRates;
	}

	static formEnabledCurrencies() {
		const enabledCurrencies = [];
		AldaCurrencyRates.enabledCheckboxes().forEach( ( checkbox ) => {
			if ( checkbox.checked ) {
				enabledCurrencies.push(
					checkbox.attributes[ 'data-currency' ].value
				);
			}
		} );
		return enabledCurrencies;
	}

	static saveCurrencyRates() {
		AldaCurrencyRates.submitButton().disabled = true;

		const currencyRates = AldaCurrencyRates.formRates();
		const enabledCurrencies = AldaCurrencyRates.formEnabledCurrencies();
		const requestBody = {
			rates: currencyRates,
			enabledCurrencies,
		};

		const endpointUrl = wpApiSettings.root + 'alda-currency/v1/rates';
		const request = new XMLHttpRequest();

		request.open( 'POST', endpointUrl, true );

		request.setRequestHeader(
			'Content-Type',
			'application/json;charset=UTF-8'
		);

		request.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );

		request.send( JSON.stringify( requestBody ) );

		request.onreadystatechange = function () {
			if ( this.status === 200 ) {
				AldaCurrencyRates.submitButton().disabled = false;
			}
		};
	}

	static submitEvent( event ) {
		event.preventDefault();
		AldaCurrencyRates.saveCurrencyRates();
	}
}

window.addEventListener( 'DOMContentLoaded', () => {
	if ( document.body.classList.contains( 'toplevel_page_alda-currency' ) ) {
		AldaCurrencySettings.form().addEventListener(
			'submit',
			AldaCurrencySettings.submitEvent
		);

		AldaCurrencyRates.form().addEventListener(
			'submit',
			AldaCurrencyRates.submitEvent
		);

		AldaCurrencyUI.settingsTab().addEventListener(
			'click',
			AldaCurrencyUI.settingsTabClickEvent
		);

		AldaCurrencyUI.ratesTab().addEventListener(
			'click',
			AldaCurrencyUI.ratesTabClickEvent
		);
	}
} );
