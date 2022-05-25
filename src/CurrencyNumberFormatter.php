<?php declare(strict_types = 1);

namespace Utilitte\Intl;

class CurrencyNumberFormatter extends NumberFormatter
{

	public function __construct(string $locale)
	{
		parent::__construct($locale, \NumberFormatter::CURRENCY);
	}

	public function formatCurrency(int|float $value, string $currency): string
	{
		return parent::formatCurrency($value, $currency);
	}

}
