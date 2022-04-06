<?php declare(strict_types = 1);

namespace Utilitte\Intl\Stepper;

use NumberFormatter;

final class FractionalStepper
{

	/** @var array<int, int> */
	public array $steps = [];

	/** @var  */
	private array $ranges;

	private ?int $max = null;

	private ?int $min = null;

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
	 * @return array{float, float, int, int}[]
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

	public function apply(NumberFormatter $formatter, int|float $number): void
	{
		$this->max = null;
		$this->min = null;

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
			$this->max = $max;
			$this->min = $min;

			$formatter->setAttribute($formatter::MIN_FRACTION_DIGITS, $candidate[0]);
			$formatter->setAttribute($formatter::MAX_FRACTION_DIGITS, $candidate[1]);
		}
	}

	public function rollback(NumberFormatter $formatter): void
	{
		if ($this->max !== null && $this->min !== null) {
			$formatter->setAttribute($formatter::MIN_FRACTION_DIGITS, $this->min);
			$formatter->setAttribute($formatter::MAX_FRACTION_DIGITS, $this->max);
		}
	}

	private function decimalToNumber(int $decimals): float
	{
		return 1 / pow(10, $decimals);
	}

}
