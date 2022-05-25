<?php declare(strict_types = 1);

namespace Utilitte\Intl\Extension;

use Fiber;

final class NumberFormatterFiber
{

	public static function nextStep(int|float|null $value = null): string
	{
		/** @var string $formatted */
		$formatted = Fiber::suspend($value);

		return $formatted;
	}

}
