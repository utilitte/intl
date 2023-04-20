<?php declare(strict_types = 1);

namespace Utilitte\Intl;

class ShortNumberFormatter extends NumberFormatter
{

	private const UNITS = [
		'' => 1_000,
		'K' => 1_000_000,
		'M' => 1_000_000_000,
		'B' => 1_000_000_000_000,
		'T' => null,
	];

	private string $prefix = '';

	public function __construct(string $locale)
	{
		parent::__construct($locale, \NumberFormatter::DECIMAL);

		$this->formatter->setAttribute($this->formatter::MIN_FRACTION_DIGITS, 0);
		$this->formatter->setAttribute($this->formatter::MAX_FRACTION_DIGITS, 0);
	}

	public function withSpace(bool $space): static
	{
		$clone = clone $this;
		$clone->prefix = $space ? ' ' : '';

		return $clone;
	}

	public function format(float|int $number): string
	{
		$unit = '';
		$lessThanZero = $number < 0;

		$divider = 1;

		$number = abs($number);

		foreach (self::UNITS as $str => $limit) {
			if ($limit === null || $number < $limit) {
				$unit = $str;
				$number = $number / $divider;

				break;
			}

			$divider = $limit;
		}

		if ($lessThanZero) {
			$number = -$number;
		}

		return $this->setTextAttribute($this->formatter::POSITIVE_SUFFIX, $this->prefix . $unit, self::PREPEND)
			->setTextAttribute($this->formatter::NEGATIVE_SUFFIX, $this->prefix . $unit, self::PREPEND)
			->formatParent($number);
	}

	private function formatParent(float|int $number): string
	{
		return parent::format($number);
	}

}
