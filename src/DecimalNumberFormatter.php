<?php declare(strict_types = 1);

namespace Utilitte\Intl;

final class DecimalNumberFormatter extends NumberFormatter
{

	public function __construct(string $locale)
	{
		parent::__construct(new \NumberFormatter($locale, \NumberFormatter::DECIMAL));
	}

}
