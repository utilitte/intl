<?php declare(strict_types = 1);

namespace Utilitte\Intl;

use Utilitte\Intl\Stepper\FractionalStepper;

abstract class NumberFormatter
{

	public const APPEND = 0;
	public const APPEND_WITH_SPACE = 1;
	public const PREPEND = 2;
	public const PREPEND_WITH_SPACE = 3;
	public const SUBSTITUTE = 4;

	protected bool $percentFormatter = false;

	private ?FractionalStepper $fractionStepper = null;

	public function __construct(
		protected \NumberFormatter $formatter,
	)
	{
	}

	public function withFractionalStepper(FractionalStepper $fractionalStepper): static
	{
		$clone = clone $this;
		$clone->fractionStepper = $fractionalStepper;

		return $clone;
	}

	public function withMaxDigits(int $digits): static
	{
		return $this->setAttribute($this->formatter::MAX_INTEGER_DIGITS, $digits);
	}

	public function withMinDigits(int $digits): static
	{
		return $this->setAttribute($this->formatter::MIN_INTEGER_DIGITS, $digits);
	}

	public function withMaxFraction(int $fraction): static
	{
		return $this->setAttribute($this->formatter::MAX_FRACTION_DIGITS, $fraction);
	}

	public function withMinFraction(int $fraction): static
	{
		return $this->setAttribute($this->formatter::MIN_FRACTION_DIGITS, $fraction);
	}

	public function withExactFraction(int $fraction): static
	{
		return $this->withMinFraction($fraction)->withMaxFraction($fraction);
	}

	public function withExactDigits(int $digits): static
	{
		return $this->withMaxDigits($digits)->withMaxDigits($digits);
	}

	public function withPositivePrefix(string $prefix, int $type = self::SUBSTITUTE): static
	{
		return $this->setTextAttribute($this->formatter::POSITIVE_PREFIX, $prefix, $type);
	}

	public function withPositiveSuffix(string $suffix, int $type = self::SUBSTITUTE): static
	{
		return $this->setTextAttribute($this->formatter::POSITIVE_SUFFIX, $suffix, $type);
	}

	public function withNegativePrefix(string $prefix, int $type = self::SUBSTITUTE): static
	{
		return $this->setTextAttribute($this->formatter::NEGATIVE_PREFIX, $prefix, $type);
	}

	public function withNegativeSuffix(string $suffix, int $type = self::SUBSTITUTE): static
	{
		return $this->setTextAttribute($this->formatter::NEGATIVE_PREFIX, $suffix, $type);
	}

	public function withSuffix(string $suffix, int $type = self::SUBSTITUTE): static
	{
		return $this->withNegativeSuffix($suffix, $type)->withPositiveSuffix($suffix, $type);
	}

	public function withPrefix(string $prefix, int $type = self::SUBSTITUTE): static
	{
		return $this->withNegativePrefix($prefix, $type)->withPositivePrefix($prefix, $type);
	}

	public function withPositiveSign(bool $display = true, int $type = self::PREPEND): static
	{
		return $this->withPositivePrefix($display ? '+' : '', $type);
	}

	public function format(int|float $number): string
	{
		// before
		$this->fractionStepper?->apply($this->formatter, $number);

		// format
		$formatted = $this->formatter->format($this->fixNegativeSign($number));

		// after
		$this->fractionStepper?->rollback($this->formatter);

		return $formatted;
	}

	public function formatExtended(int|float|string $number): string
	{
		if (is_string($number)) {
			if (!is_numeric($number)) {
				return $number;
			}

			if (str_contains($number, '.')) {
				return $this->format((float) $number);
			}

			return $this->format((int) $number);
		}

		return $this->format($number);
	}

	protected function setAttribute(int $attribute, float|int $value, bool $clone = true): static
	{
		if ($this->formatter->getAttribute($attribute) === $value) {
			return $this;
		}

		$cloned = $clone ? clone $this : $this;
		$cloned->formatter->setAttribute($attribute, $value);

		return $cloned;
	}

	protected function setTextAttribute(int $attribute, string $value, int $type, bool $clone = true): static
	{
		$previous = (string) $this->formatter->getTextAttribute($attribute);
		if ($previous === $value) {
			return $this;
		}

		$cloned = $clone ? clone $this : $this;

		$value = match ($type) {
			self::APPEND => $previous . $value,
			self::APPEND_WITH_SPACE => ($previous ? $previous . ' ' : '') . $value,
			self::PREPEND => $value . $previous,
			self::PREPEND_WITH_SPACE => $value . ($previous ? ' ' . $previous : ''),
			default => $value,
		};

		$cloned->formatter->setTextAttribute($attribute, $value);

		return $cloned;
	}

	public function __clone(): void
	{
		$this->formatter = clone $this->formatter;
	}

	private function fixNegativeSign(float|int $number): float|int
	{
		if (!is_float($number)) {
			return $number;
		}

		if ($number > 0 && $number >= -1) {
			return $number;
		}

		$max = max((int) $this->formatter->getAttribute($this->formatter::MAX_FRACTION_DIGITS), 0);

		if ($this->percentFormatter) {
			$max += 2;
		}

		$abs = abs($number);
		$fractionBeforeZero = -1 - (int) floor(log10($abs));

		if ($max > $fractionBeforeZero) {
			return $number;
		}

		return $abs;
	}

}
