<?php declare(strict_types = 1);

namespace Utilitte\Intl\Extension;

use Fiber;
use Utilitte\Intl\Decorator\NumberFormatter;

final class NegativeZeroFixExtension implements NumberFormatterExtension
{

	public function invoke(float|int $number, NumberFormatter $formatter): void
	{
		if (!is_float($number)) {
			return;
		}

		if ($number > 0 && $number >= -1) {
			return;
		}

		$max = max((int) $formatter->getAttribute($formatter::MAX_FRACTION_DIGITS), 0);

		if ($formatter->getStyle() === $formatter::PERCENT) {
			$max += 2;
		}

		$abs = abs($number);
		$fractionBeforeZero = -1 - (int) floor(log10($abs));

		if ($max > $fractionBeforeZero) {
			return;
		}

		NumberFormatterFiber::nextStep($abs);
	}

}
