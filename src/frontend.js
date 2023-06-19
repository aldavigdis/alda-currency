// Import the currency converter React component
import CurrencyConverter from '../build/currency-converter.js';

// Find matching WordPress blocks
const containers = document.querySelectorAll(
	'.alda-currency-frontend-block-container'
);

// Assign a root and create a CurrencyConverter component in every DOM element
// matching the query selector.
containers.forEach(function (container) {
	let currency_converter_root = ReactDOM.createRoot(container);
  currency_converter_root.render(React.createElement(CurrencyConverter, null));
});
