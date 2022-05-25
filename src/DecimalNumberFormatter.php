<?php declare(strict_types = 1);

namespace Utilitte\Intl;

class DecimalNumberFormatter extends NumberFormatter
{

	public function __construct(string $locale)
	{
		parent::__construct($locale, \NumberFormatter::DECIMAL);
	}

}
