<?php declare(strict_types = 1);

namespace Utilitte\Intl;

class ByteNumberFormatter extends NumberFormatter
{

	private const END = 'PB';
	private const UNITS = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];

	private string $prefix = '';

	public function __construct(string $locale)
	{
		parent::__construct(new \NumberFormatter($locale, \NumberFormatter::DECIMAL));

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
		$unit = 'B';
		foreach (self::UNITS as $unit) {
			if (abs($number) < 1024 || $unit === self::END) {
				break;
			}

			$number /= 1024;
		}

		$this->setTextAttribute($this->formatter::POSITIVE_SUFFIX, $this->prefix . $unit, self::PREPEND, false);
		$this->setTextAttribute($this->formatter::NEGATIVE_SUFFIX, $this->prefix . $unit, self::PREPEND, false);

		return parent::format($number);
	}

}
