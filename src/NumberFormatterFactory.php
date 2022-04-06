<?php declare(strict_types = 1);

namespace Utilitte\Intl;

interface NumberFormatterFactory
{

	public function createCurrency(?string $locale = null): CurrencyNumberFormatter;

	public function createDecimal(?string $locale = null): DecimalNumberFormatter;

	public function createPattern(string $pattern, ?string $locale = null): PatternNumberFormatter;

	public function createPercent(?string $locale = null): PercentNumberFormatter;

}
