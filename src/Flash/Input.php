<?php

declare(strict_types=1);

namespace Zaphyr\Session\Flash;

use Zaphyr\Session\Contracts\SessionInterface;
use Zaphyr\Utils\Arr;

/**
 * @author merloxx <merloxx@zaphyr.org>
*  @internal This class is not part of the public API of this package and may change at any time without notice
 */
class Input
{
    /**
     * @const string
     */
    private const KEY = '_input';

    /**
     * @param SessionInterface $session
     * @param Flash            $flash
     */
    public function __construct(protected SessionInterface $session, protected Flash $flash)
    {
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return void
     */
    public function set(array $values): void
    {
        $this->flash->set(self::KEY, $values);
    }

    /**
     * @param string|null $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function get(string|null $key = null, mixed $default = null): mixed
    {
        return Arr::get($this->session->get(self::KEY, []), $key, $default);
    }

    /**
     * @param string|null $key
     *
     * @return bool
     */
    public function has(string|null $key = null): bool
    {
        $input = $this->get($key);

        return $key === null ? count($input) > 0 : $input !== null;
    }
}
