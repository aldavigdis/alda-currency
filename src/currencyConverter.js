
class CurrencyConverter extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			baseCurrency: '',
			initialBaseCurrencyAmount: 1,
			baseCurrencyAmount: (1).toFixed(2),
			currencyRates: {},
			currencyAmounts: {}
		};

		this.onUpdatePrimaryAmount = this.onUpdatePrimaryAmount.bind(this);
		this.onUpdateCurrencyAmount = this.onUpdateCurrencyAmount.bind(this);
	}

	componentDidMount() {
		this.getInfo();
	}

	async getInfo() {
		const response = await fetch( wpApiSettings.root + 'alda-currency/v1/info' );
		const result = await response.json();

		this.setState({
			baseCurrency: result.base_currency,
			currencyRates: result.rates,
			currencyAmounts: result.rates
		});

		return result;
	}

	onUpdatePrimaryAmount(event) {
		const newCurrencyAmounts = {};
		for (const key in this.state.currencyAmounts) {
			newCurrencyAmounts[key] = this.state.currencyRates[key] * event.target.value;
		}
		this.setState(
			{
				baseCurrencyAmount: parseFloat(event.target.value).toFixed(2),
				currencyAmounts: newCurrencyAmounts
			}
		);
	}

	onUpdateCurrencyAmount(event) {
		const currency = event.target.name;
		const newBaseCurrencyAmount = ( this.state.initialBaseCurrencyAmount / parseFloat(this.state.currencyRates[currency]) * parseFloat(event.target.value) ).toFixed(2);
		const newCurrencyAmounts = {};
		newCurrencyAmounts[currency] = parseFloat(event.target.value);
		for (const key in this.state.currencyAmounts) {
			if ( key != currency ) {
				newCurrencyAmounts[key] = (parseFloat(this.state.currencyRates[key]) * parseFloat(newBaseCurrencyAmount));
			}
		}
		this.setState({
			baseCurrencyAmount: newBaseCurrencyAmount,
			currencyAmounts: newCurrencyAmounts
		});
	}

	render() {
		return (
			<table>
				<tbody>
					<tr>
						<th
							scope='row'
							data-currency={ this.state.baseCurrency }
						>
							{ this.state.baseCurrency }
						</th>
						<td>{ (this.state.initialBaseCurrencyAmount).toFixed(2) }</td>
						<td>
							<input
								type='number'
								step='0.01'
								name={ this.state.baseCurrency }
								value={ this.state.baseCurrencyAmount }
								onChange={ this.onUpdatePrimaryAmount }
							/>
						</td>
					</tr>
					{Object.keys(this.state.currencyRates).filter(k => k != this.state.baseCurrency).map(key => (
						<tr key={ key }>
							<th
								scope='row'
								data-currency={ key }
							>
								{ key }
							</th>
							<td>{ this.state.currencyRates[key] }</td>
							<td>
								<input
									type='number'
									step='0.01'
									name={ key }
									value={ ( this.state.currencyAmounts[key] ).toFixed(2) }
									onChange={ this.onUpdateCurrencyAmount }
								/>
							</td>
						</tr>
					))}
				</tbody>
			</table>
		);
	}
}

export default CurrencyConverter;
