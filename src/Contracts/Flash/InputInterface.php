<?php

declare(strict_types=1);

namespace Zaphyr\Session\Contracts\Flash;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
interface InputInterface
{
    /**
     * @param array<string, mixed> $values
     *
     * @return void
     */
    public function set(array $values): void;

    /**
     * @param string|null $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function get(string|null $key = null, mixed $default = null): mixed;

    /**
     * @param string|null $key
     *
     * @return bool
     */
    public function has(string|null $key = null): bool;
}
