<?php declare(strict_types = 1);

namespace Utilitte\Intl;

final class StaticNumberFormatterFactory implements NumberFormatterFactory
{

	public function __construct(
		private readonly string $locale = 'en-US',
	)
	{
	}

	public function createCurrency(?string $locale = null): CurrencyNumberFormatter
	{
		return new CurrencyNumberFormatter($locale ?? $this->locale);
	}

	public function createDecimal(?string $locale = null): DecimalNumberFormatter
	{
		return new DecimalNumberFormatter($locale ?? $this->locale);
	}

	public function createPattern(string $pattern, ?string $locale = null): PatternNumberFormatter
	{
		return new PatternNumberFormatter($locale ?? $this->locale, $pattern);
	}

	public function createPercent(?string $locale = null): PercentNumberFormatter
	{
		return new PercentNumberFormatter($locale ?? $this->locale);
	}

	public function createShort(?string $locale = null): ShortNumberFormatter
	{
		return new ShortNumberFormatter($locale ?? $this->locale);
	}

	public function createByte(?string $locale = null): ByteNumberFormatter
	{
		return new ByteNumberFormatter($locale ?? $this->locale);
	}

}
