import CurrencyConverter from '../build/currency-converter.js';
const containers = document.querySelectorAll('.alda-currency-frontend-block-container');

containers.forEach(function (container) {
	let currency_converter_root = ReactDOM.createRoot(container);
  currency_converter_root.render(React.createElement(CurrencyConverter, null));
});
