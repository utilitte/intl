<?php declare(strict_types = 1);

namespace Utilitte\Intl\Extension;

use NumberFormatter;

abstract class NumberFormatterExtension
{

	public function before(NumberFormatter $formatter): void
	{

	}

	public function after(NumberFormatter $formatter): void
	{

	}

}
