<?php declare(strict_types = 1);

namespace Utilitte\Intl\Extension;

use Utilitte\Intl\Decorator\NumberFormatter;

interface NumberFormatterExtension
{

	/**
	 * @return string|void
	 */
	public function invoke(int|float $number, NumberFormatter $formatter);

}
