<?php declare(strict_types = 1);

namespace Utilitte\Intl;

use Utilitte\Intl\Extension\PositiveSign;

abstract class NumberFormatter
{

	private PositiveSign $positiveSign;

	public function __construct(
		protected \NumberFormatter $formatter,
	)
	{
		$this->positiveSign = new PositiveSign();
	}

	public function withMaxDigits(int $digits): self
	{
		return $this->setAttribute($this->formatter::MAX_INTEGER_DIGITS, $digits);
	}

	public function withMinDigits(int $digits): self
	{
		return $this->setAttribute($this->formatter::MIN_INTEGER_DIGITS, $digits);
	}

	public function withMaxFraction(int $fraction): self
	{
		return $this->setAttribute($this->formatter::MAX_FRACTION_DIGITS, $fraction);
	}

	public function withMinFraction(int $fraction): self
	{
		return $this->setAttribute($this->formatter::MIN_FRACTION_DIGITS, $fraction);
	}

	public function withExactFraction(int $fraction): self
	{
		return $this->withMinFraction($fraction)->withMaxFraction($fraction);
	}

	public function withExactDigits(int $digits): self
	{
		return $this->withMaxDigits($digits)->withMaxDigits($digits);
	}

	public function withPositivePrefix(string $prefix): self
	{
		return $this->setTextAttribute($this->formatter::POSITIVE_PREFIX, $prefix);
	}

	public function withPositiveSuffix(string $suffix): self
	{
		return $this->setTextAttribute($this->formatter::POSITIVE_SUFFIX, $suffix);
	}

	public function withNegativePrefix(string $prefix): self
	{
		return $this->setTextAttribute($this->formatter::NEGATIVE_PREFIX, $prefix);
	}

	public function withNegativeSuffix(string $suffix): self
	{
		return $this->setTextAttribute($this->formatter::NEGATIVE_PREFIX, $suffix);
	}

	public function withPositiveSign(bool $alwaysSign = true, bool $append = true): self
	{
		$clone = clone $this;
		$clone->positiveSign->enabled = $alwaysSign;
		$clone->positiveSign->append = $append;

		return $clone;
	}

	public function format(int|float $number): string
	{
		return $this->return(fn () => $this->formatter->format($number));
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

	protected function return(callable $callback): string
	{
		$this->before();

		$formatted = $callback();

		$this->after();

		return $formatted;
	}

	protected function before(): void
	{
		foreach ([$this->positiveSign] as $extension) {
			$extension->before($this->formatter);
		}
	}

	protected function after(): void
	{
		foreach ([$this->positiveSign] as $extension) {
			$extension->after($this->formatter);
		}
	}

	private function setAttribute(int $attribute, float|int $value): self
	{
		if ($this->formatter->getAttribute($attribute) === $value) {
			return $this;
		}

		$clone = clone $this;
		$clone->formatter->setAttribute($attribute, $value);

		return $clone;
	}

	private function setTextAttribute(int $attribute, string $value): self
	{
		if ($this->formatter->getTextAttribute($attribute) === $value) {
			return $this;
		}

		$clone = clone $this;
		$clone->formatter->setTextAttribute($attribute, $value);

		return $clone;
	}

	public function __clone(): void
	{
		$this->formatter = clone $this->formatter;
	}

}
