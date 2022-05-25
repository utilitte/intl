<?php declare(strict_types = 1);

namespace Utilitte\Intl\Decorator;

use Fiber;
use Utilitte\Intl\Exception\NumberFormatterException;
use Utilitte\Intl\Extension\NumberFormatterExtension;

final class NumberFormatter extends \NumberFormatter
{

	private int $style;

	public function __construct(string $locale, int $style, ?string $pattern = null)
	{
		parent::__construct($locale, $style, $pattern);

		$this->style = $style;
	}

	public function getStyle(): int
	{
		return $this->style;
	}

	/**
	 * @param NumberFormatterExtension[] $extensions
	 */
	public function formatCurrency(float $amount, string $currency, array $extensions = []): string
	{
		return $this->decorate($extensions, $amount, function (int|float $num) use ($currency): string {
			return $this->checkFalse(parent::formatCurrency($num, $currency));
		});
	}

	/**
	 * @param NumberFormatterExtension[] $extensions
	 */
	public function format(float|int $num, int $type = null, array $extensions = []): string
	{
		return $this->decorate($extensions, $num, function (int|float $num) use ($type): string {
			if ($type !== null) {
				return $this->checkFalse(parent::format($num, $type));
			}

			return $this->checkFalse(parent::format($num));
		});
	}


	/**
	 * @param NumberFormatterExtension[] $extensions
	 * @param callable(int|float): string $callback
	 * @return string
	 */
	private function decorate(array $extensions, int|float $num, callable $callback): string
	{
		$fibers = array_map(fn (NumberFormatterExtension $extension) => new Fiber($extension->invoke(...)), $extensions);
		foreach ($fibers as $fiber) {
			$suspended = $fiber->start($num, $this);

			if (is_float($suspended) || is_int($suspended)) {
				$num = $suspended;
			}
		}

		$string = $callback($num);

		foreach ($fibers as $fiber) {
			if ($fiber->isSuspended()) {
				$fiber->resume($string);
			}
		}

		return $string;
	}

	/**
	 * @phpstan-param false|string $formatted
	 */
	private function checkFalse(bool|string $formatted): string
	{
		if ($formatted === false) {
			throw new NumberFormatterException(
				sprintf('%s, code is %d.', $this->getErrorMessage(), $this->getErrorCode())
			);
		}

		return $formatted;
	}

}
