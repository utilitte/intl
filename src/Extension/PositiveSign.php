<?php declare(strict_types = 1);

namespace Utilitte\Intl\Extension;

use NumberFormatter;

final class PositiveSign extends NumberFormatterExtension
{

	public bool $enabled = false;

	public bool $append = true;

	private string $store;

	public function before(NumberFormatter $formatter): void
	{
		if (!$this->enabled) {
			return;
		}

		$string = (string) $formatter->getTextAttribute($formatter::POSITIVE_PREFIX);

		$this->store = $string;

		if (!str_contains($string, '+')) {
			if ($this->append) {
				$string .= '+';
			} else {
				$string = '+' . $string;
			}

			$formatter->setTextAttribute($formatter::POSITIVE_PREFIX, $string);
		}
	}

	public function after(NumberFormatter $formatter): void
	{
		if (!$this->enabled) {
			return;
		}

		$formatter->setTextAttribute($formatter::POSITIVE_PREFIX, $this->store);
	}

}
