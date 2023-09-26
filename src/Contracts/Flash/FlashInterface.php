<?php

declare(strict_types=1);

namespace Zaphyr\Session\Contracts\Flash;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface FlashInterface
{
    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $key, mixed $value = true): void;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function now(string $key, mixed $value): void;

    /**
     * @return void
     */
    public function reflash(): void;

    /**
     * @param string[] $keys
     *
     * @return void
     */
    public function keep(array $keys = []): void;

    /**
     * @return void
     */
    public function clear(): void;
}
