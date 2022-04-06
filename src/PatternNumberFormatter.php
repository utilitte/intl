<?php declare(strict_types = 1);

namespace Utilitte\Intl;

final class PatternNumberFormatter extends NumberFormatter
{

	public function __construct(string $locale)
	{
		parent::__construct(new \NumberFormatter($locale, \NumberFormatter::DECIMAL));
	}

	public function withPattern(string $pattern): self
	{
		$clone = clone $this;
		$clone->formatter->setPattern($pattern);

		return $clone;
	}

}
