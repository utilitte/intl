<?php declare(strict_types = 1);

namespace Utilitte\Intl;

abstract class NumberFormatter
{

	public const APPEND = 0;
	public const APPEND_WITH_SPACE = 1;
	public const PREPEND = 2;
	public const PREPEND_WITH_SPACE = 3;
	public const SUBSTITUTE = 4;

	private int $fraction = 0;

	private ?int $fractionStore = null;

	public function __construct(
		protected \NumberFormatter $formatter,
	)
	{
	}

	public function withMiniatureFraction(int $fraction): static
	{
		$clone = clone $this;
		$clone->fraction = $fraction;

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

	public function withPositiveSign(bool $display = true, int $type = self::PREPEND): static
	{
		return $this->withPositivePrefix($display ? '+' : '', $type);
	}

	public function format(int|float $number): string
	{
		$this->fractionStore = null;

		$this->fraction($number);
		$number = $this->fixNegativeSign($number);

		$formatted = $this->formatter->format($number);

		// after

		if ($this->fractionStore) {
			$this->formatter->setAttribute($this->formatter::MAX_FRACTION_DIGITS, $this->fractionStore);
		}

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

	private function setAttribute(int $attribute, float|int $value): static
	{
		if ($this->formatter->getAttribute($attribute) === $value) {
			return $this;
		}

		$clone = clone $this;
		$clone->formatter->setAttribute($attribute, $value);

		return $clone;
	}

	private function setTextAttribute(int $attribute, string $value, int $type): static
	{
		$previous = (string) $this->formatter->getTextAttribute($attribute);
		if ($previous === $value) {
			return $this;
		}

		$clone = clone $this;

		$value = match ($type) {
			self::APPEND => $previous . $value,
			self::APPEND_WITH_SPACE => ($previous ? $previous . ' ' : '') . $value,
			self::PREPEND => $value . $previous,
			self::PREPEND_WITH_SPACE => $value . ($previous ? ' ' . $previous : ''),
			default => $value,
		};

		$clone->formatter->setTextAttribute($attribute, $value);

		return $clone;
	}

	public function __clone(): void
	{
		$this->formatter = clone $this->formatter;
	}

	private function fraction(float|int $number): void
	{
		if ($this->fraction <= 0) {
			return;
		}

		if (!is_float($number)) {
			return;
		}

		if ($number >= 1 || $number <= -1) {
			return;
		}

		$max = max((int) $this->formatter->getAttribute($this->formatter::MAX_FRACTION_DIGITS), 0);
		$fractionBeforeZero = -1 - (int) floor(log10(abs($number)));

		if ($max > $fractionBeforeZero) {
			return;
		}

		if ($this->fraction >= $fractionBeforeZero + 1) {
			$this->fractionStore = $max;
			$this->formatter->setAttribute($this->formatter::MAX_FRACTION_DIGITS, $fractionBeforeZero + 1);
		}
	}

	private function fixNegativeSign(float|int $number): float|int
	{
		if ($this->fractionStore !== null) {
			return $number;
		}

		if (!is_float($number)) {
			return $number;
		}

		if ($number > 0 && $number >= -1) {
			return $number;
		}

		$max = max((int) $this->formatter->getAttribute($this->formatter::MAX_FRACTION_DIGITS), 0);

		$abs = abs($number);
		$fractionBeforeZero = -1 - (int) floor(log10($abs));

		if ($max > $fractionBeforeZero) {
			return $number;
		}

		return $abs;
	}

}
