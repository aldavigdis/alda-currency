<?php

/**
 * The main admin view for Alda Currency
 *
 * @package alda-currency
 */

$alda_currency      = new AldaCurrency();
$base_currency      = $alda_currency->get_base_currency();
$enabled_currencies = $alda_currency->enabled_currencies();
$currency_rates     = $alda_currency->rates();
?>

<div>
	<h1 class="wp-heading-inline">
		Alda Currency
	</h1>

	<h2 class="nav-tab-wrapper">
		<a
			id="nav-tab-rates"
			class="nav-tab nav-tab-active"
			href="#"
		>
			Currency Rates
		</a>
		<a
			id="nav-tab-settings"
			class="nav-tab"
			href="#"
		>
			Settings
		</a>
	</h2>

	<form action="#" class="type-form" id="alda-currency-rates-form">
		<div class="">
			<h2>Currency Rates</h2>
			<hr class="wp-header-end" />
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th scope="row" colspan="2">
							Currency
						</th>
						<th scope="row">
							Value
						</th>
						<th>
							Display currency in block
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $alda_currency::CURRENCIES as $c ) : ?>
					<tr>
						<td>
							<img
								src="<?php echo esc_attr( $alda_currency->currency_flag( $c ) ); ?>"
								width="40"
								height="30"
							/>
						</td>
						<th
							class="title column-title has-row-actions column-primary page-titles"
							scope="row"
						>
							<span class="row-title">
								<?php echo esc_html( $c ); ?>
							</span>
						</th>
						<td>
							<input
								class="currency-rate"
								type="number"
								name="<?php echo esc_attr( $c ); ?>"
								step="0.01"
								value="<?php echo esc_attr( $currency_rates[ $c ] ); ?>"
							/>
						</td>
						<td>
							<label>
								<input
									type="checkbox"
									name="<?php echo esc_attr( $c . '_enabled' ); ?>"
									<?php if ( in_array( $c, $enabled_currencies, true ) ) : ?>
									checked
									<?php endif ?>
									data-currency="<?php echo esc_attr( $c ); ?>"
								>
							</label>
						</td>
					</tr>
					<?php endforeach ?>
				</tbody>
			</table>
			<p class="submit">
				<input
					class="button-primary"
					type="submit"
					value="Save Currency Rates"
				/>
			</p>
		</div>
	</form>

	<form action="#" id="alda-currency-settings-form" class="type-form hidden">
		<div class="">
			<h2>Settings</h2>
			<hr class="wp-header-end" />
			<table>
				<tbody>
					<tr>
						<th>Base Currency</th>
						<td>
							<select name="base_currency">
								<?php foreach ( $alda_currency::CURRENCIES as $c ) : ?>
									<option
										value="<?php echo esc_attr( $c ); ?>"
										<?php if ( $c === $base_currency ) : ?>
											selected
										<?php endif ?>
									><?php echo esc_html( $c ); ?></option>
								<?php endforeach ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<p class="submit">
			<input
				type="submit"
				class="button-primary"
				value="Save Settings"
			/>
		</p>
	</form>
</div>
