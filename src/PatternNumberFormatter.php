<?php declare(strict_types = 1);

namespace Utilitte\Intl;

class PatternNumberFormatter extends NumberFormatter
{

	public function __construct(string $locale, string $pattern)
	{
		parent::__construct(new \NumberFormatter($locale, \NumberFormatter::DECIMAL));

		$this->formatter->setPattern($pattern);
	}

	public function withPattern(string $pattern): self
	{
		$clone = clone $this;
		$clone->formatter->setPattern($pattern);

		return $clone;
	}

}
