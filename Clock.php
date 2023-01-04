<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Clock;

use Psr\Clock\ClockInterface as PsrClockInterface;

/**
 * A global clock.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
final class Clock implements ClockInterface
{
    private static ClockInterface $globalClock;

    public function __construct(
        private readonly ?PsrClockInterface $clock = null,
        private ?\DateTimeZone $timezone = null,
    ) {
    }

    /**
     * Returns the current global clock.
     *
     * Note that you should prefer injecting a ClockInterface or using
     * ClockAwareTrait when possible instead of using this method.
     */
    public static function get(): ClockInterface
    {
        return self::$globalClock ??= new NativeClock();
    }

    public static function set(PsrClockInterface $clock): void
    {
        self::$globalClock = $clock instanceof ClockInterface ? $clock : new self($clock);
    }

    public function now(): \DateTimeImmutable
    {
        $now = ($this->clock ?? self::get())->now();

        return isset($this->timezone) ? $now->setTimezone($this->timezone) : $now;
    }

    public function sleep(float|int $seconds): void
    {
        $clock = $this->clock ?? self::get();

        if ($clock instanceof ClockInterface) {
            $clock->sleep($seconds);
        } else {
            (new NativeClock())->sleep($seconds);
        }
    }

    public function withTimeZone(\DateTimeZone|string $timezone): static
    {
        $clone = clone $this;
        $clone->timezone = \is_string($timezone) ? new \DateTimeZone($timezone) : $timezone;

        return $clone;
    }
}
