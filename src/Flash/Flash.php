<?php

declare(strict_types=1);

namespace Zaphyr\Session\Flash;

use Zaphyr\Session\Contracts\SessionInterface;

/**
 * @author   merloxx <merloxx@zaphyr.org>
 * @internal This class is not part of the public API of this package and may change at any time without notice
 */
class Flash
{
    /**
     * @const string
     */
    private const NEW_KEY = '_flash.new';

    /**
     * @const string
     */
    private const OLD_KEY = '_flash.old';

    /**
     * @param SessionInterface $session
     */
    public function __construct(protected SessionInterface $session)
    {
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $key, mixed $value = true): void
    {
        $this->session->set($key, $value);
        $this->session->add(self::NEW_KEY, $key);

        $this->removeFromOldFlashData([$key]);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function now(string $key, mixed $value): void
    {
        $this->session->set($key, $value);
        $this->session->add(self::OLD_KEY, $key);
    }

    /**
     * @param string[] $keys
     */
    protected function removeFromOldFlashData(array $keys): void
    {
        $this->session->set(self::OLD_KEY, array_diff($this->session->get(self::OLD_KEY, []), $keys));
    }

    /**
     * @return void
     */
    public function reflash(): void
    {
        $this->mergeNewFlashData($this->session->get(self::OLD_KEY, []));

        $this->session->set(self::OLD_KEY, []);
    }

    /**
     * @param string[] $keys
     *
     * @return void
     */
    public function keep(array $keys = []): void
    {
        $this->mergeNewFlashData($keys);
        $this->removeFromOldFlashData($keys);
    }

    /**
     * @param string[] $keys
     */
    protected function mergeNewFlashData(array $keys): void
    {
        $values = array_unique(array_merge($this->session->get(self::NEW_KEY, []), $keys));
        $this->session->set(self::NEW_KEY, $values);
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->session->removeMultiple($this->session->get(self::OLD_KEY, []));

        $this->session->set(self::OLD_KEY, $this->session->get(self::NEW_KEY, []));
        $this->session->set(self::NEW_KEY, []);
    }
}
