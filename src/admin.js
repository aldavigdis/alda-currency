class AldaCurrencyUI {
	static ratesTab() {
		return document.querySelector('#nav-tab-rates');
	}

	static settingsTab() {
		return document.querySelector('#nav-tab-settings');
	}

	static ratesForm() {
		return document.querySelector('#alda-currency-rates-form')
	}

	static settingsForm() {
		return document.querySelector('#alda-currency-settings-form')
	}

	static settingsTabClickEvent(event) {
		event.preventDefault();
		AldaCurrencyUI.ratesForm().classList.add('hidden');
		AldaCurrencyUI.settingsForm().classList.remove('hidden');
		AldaCurrencyUI.ratesTab().classList.remove('nav-tab-active');
		AldaCurrencyUI.settingsTab().classList.add('nav-tab-active');
	}

	static ratesTabClickEvent(event) {
		event.preventDefault();
		AldaCurrencyUI.ratesForm().classList.remove('hidden');
		AldaCurrencyUI.settingsForm().classList.add('hidden');
		AldaCurrencyUI.ratesTab().classList.add('nav-tab-active');
		AldaCurrencyUI.settingsTab().classList.remove('nav-tab-active');
	}
}

class AldaCurrencySettings {
	static form() {
		return document.querySelector('#alda-currency-settings-form');
	}

	static submitButton() {
		return document.querySelector(
			'#alda-currency-settings-form input[type=submit]'
		);
	}

	static baseCurrencySelect() {
		return document.querySelector(
			'#alda-currency-settings-form select[name=base_currency]'
		)
	}

	static submitEvent(event) {
		event.preventDefault();

		AldaCurrencySettings.saveBaseCurrency(
			AldaCurrencySettings.baseCurrencySelect().value
		);
	}

	static saveBaseCurrency( currency_code ) {
		AldaCurrencySettings.submitButton().disabled = true;

		let endpointUrl = wpApiSettings.root + 'alda-currency/v1/base-currency';
		let request = new XMLHttpRequest();

		request.open( 'POST', endpointUrl, true );
		request.setRequestHeader(
			'Content-Type', 'application/json;charset=UTF-8'
		);

		request.send(
			JSON.stringify( { 'currency_code': currency_code } )
		);

		request.onreadystatechange = function( request ) {
			if ( this.status == 200 ) {
				AldaCurrencySettings.submitButton().disabled = false;
			}
		}
	}
}

class AldaCurrencyRates {
	static form() {
		return document.querySelector(
			'#alda-currency-rates-form'
		);
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
		let currencyRates = {};
		document.querySelectorAll('input.currency-rate').forEach(function(element) {
			currencyRates[element.name] = parseFloat(element.value);
		});
		return currencyRates;
	}

	static formEnabledCurrencies() {
		let enabledCurrencies = [];
		AldaCurrencyRates.enabledCheckboxes().forEach((checkbox) => {
			if ( checkbox.checked ) {
				enabledCurrencies.push(
					checkbox.attributes['data-currency'].value
				);
			}
		});
		return enabledCurrencies;
	}

	static saveCurrencyRates() {
		AldaCurrencyRates.submitButton().disabled = true;

		let currencyRates = AldaCurrencyRates.formRates();
		let enabledCurrencies = AldaCurrencyRates.formEnabledCurrencies();
		let requestBody = {
			rates: currencyRates,
			enabledCurrencies: enabledCurrencies
		};

		let endpointUrl = wpApiSettings.root + 'alda-currency/v1/rates';
		let request = new XMLHttpRequest();

		request.open( 'POST', endpointUrl, true );
		request.setRequestHeader(
			'Content-Type', 'application/json;charset=UTF-8'
		);

		request.send(
			JSON.stringify( requestBody )
		);

		request.onreadystatechange = function( request ) {
			if ( this.status == 200 ) {
				AldaCurrencyRates.submitButton().disabled = false;
			}
		}
	}

	static submitEvent(event) {
		event.preventDefault();
		AldaCurrencyRates.saveCurrencyRates();
	}
}

window.addEventListener(
	'DOMContentLoaded',
	( event ) =>
	{
		if ( document.body.classList.contains('toplevel_page_alda-currency') ) {
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
	}
)
