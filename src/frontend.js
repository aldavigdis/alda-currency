// Import the currency converter React component
import CurrencyConverter from '../build/currency-converter.js';

// Find matching WordPress blocks
const containers = document.querySelectorAll(
	'.alda-currency-frontend-block-container'
);

// Assign a root and create a CurrencyConverter component in every DOM element
// matching the query selector.
containers.forEach( function ( container ) {
	const currencyConverterRoot = ReactDOM.createRoot( container );
	currencyConverterRoot.render(
		React.createElement( CurrencyConverter, null )
	);
} );
