<?php

declare(strict_types=1);

namespace Zaphyr\Session\Flash;

use Zaphyr\Session\Contracts\Flash\FlashInterface;
use Zaphyr\Session\Contracts\SessionInterface;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Flash implements FlashInterface
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
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value = true): void
    {
        $this->session->set($key, $value);
        $this->session->add(self::NEW_KEY, $key);

        $this->removeFromOldFlashData([$key]);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function reflash(): void
    {
        $this->mergeNewFlashData($this->session->get(self::OLD_KEY, []));

        $this->session->set(self::OLD_KEY, []);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->session->removeMultiple($this->session->get(self::OLD_KEY, []));

        $this->session->set(self::OLD_KEY, $this->session->get(self::NEW_KEY, []));
        $this->session->set(self::NEW_KEY, []);
    }
}
