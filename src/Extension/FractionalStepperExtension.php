<?php declare(strict_types = 1);

namespace Utilitte\Intl\Extension;

use Utilitte\Intl\Decorator\NumberFormatter;

final class FractionalStepperExtension implements NumberFormatterExtension
{

	/** @var array<int, int> */
	public array $steps = [];

	public static function create(): self
	{
		return new self();
	}

	public function addStep(int $decimals, int $precision = 1): self
	{
		$this->steps[$decimals] = max($precision, 1);

		return $this;
	}

	/**
	 * @return array{int, int}[]
	 */
	private function getRanges(): array
	{
		$ranges = [];
		$steps = $this->steps;

		ksort($steps, SORT_NUMERIC);

		foreach ($steps as $decimal => $max) {
			$ranges[] = [$decimal, $decimal + $max];
		}

		return $ranges;
	}

	public function invoke(int|float $number, NumberFormatter $formatter): void
	{
		if (is_int($number)) {
			return;
		}

		if ($number > 1 || $number < -1) {
			return;
		}

		$max = (int) $formatter->getAttribute($formatter::MAX_FRACTION_DIGITS);
		$min = (int) $formatter->getAttribute($formatter::MIN_FRACTION_DIGITS);

		$fractionBeforeZero = -1 - (int) floor(log10(abs($number)));
		if ($fractionBeforeZero < $max) {
			return;
		}

		$candidate = [];
		foreach ($this->getRanges() as [$rangeMin, $rangeMax]) {
			if ($fractionBeforeZero >= $rangeMin && $fractionBeforeZero < $rangeMax) {
				$candidate = [$rangeMin, $rangeMax];
			}
		}

		if ($candidate) {
			$formatter->setAttribute($formatter::MIN_FRACTION_DIGITS, $candidate[0]);
			$formatter->setAttribute($formatter::MAX_FRACTION_DIGITS, $candidate[1]);
		}

		NumberFormatterFiber::nextStep();

		$formatter->setAttribute($formatter::MAX_FRACTION_DIGITS, $max);
		$formatter->setAttribute($formatter::MIN_FRACTION_DIGITS, $min);
	}

}
