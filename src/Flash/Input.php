<?php

declare(strict_types=1);

namespace Zaphyr\Session\Flash;

use Zaphyr\Session\Contracts\Flash\FlashInterface;
use Zaphyr\Session\Contracts\Flash\InputInterface;
use Zaphyr\Session\Contracts\SessionInterface;
use Zaphyr\Utils\Arr;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Input implements InputInterface
{
    /**
     * @const string
     */
    private const KEY = '_input';

    /**
     * @param SessionInterface $session
     * @param FlashInterface   $flash
     */
    public function __construct(protected SessionInterface $session, protected FlashInterface $flash)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function set(array $values): void
    {
        $this->flash->set(self::KEY, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string|null $key = null, mixed $default = null): mixed
    {
        return Arr::get($this->session->get(self::KEY, []), $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string|null $key = null): bool
    {
        $input = $this->get($key);

        return $key === null ? count($input) > 0 : $input !== null;
    }
}
