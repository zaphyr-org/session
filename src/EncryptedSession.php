<?php

declare(strict_types=1);

namespace Zaphyr\Session;

use SessionHandlerInterface;
use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Encrypt\Exceptions\DecryptException;
use Zaphyr\Session\Flash\Flash;
use Zaphyr\Session\Flash\Input;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class EncryptedSession extends Session
{
    /**
     * @param string                  $name
     * @param SessionHandlerInterface $handler
     * @param EncryptInterface        $encryptor
     * @param string|null             $id
     * @param Flash|null              $flash
     * @param Input|null              $input
     */
    public function __construct(
        string $name,
        SessionHandlerInterface $handler,
        protected EncryptInterface $encryptor,
        string|null $id = null,
        Flash|null $flash = null,
        Input|null $input = null
    ) {
        parent::__construct($name, $handler, $id, $flash, $input);
    }

    /**
     * @param string $data
     *
     * @return string
     */
    protected function prepareForUnserialize(string $data): string
    {
        try {
            return $this->encryptor->decrypt($data);
        } catch (DecryptException) {
            return serialize([]);
        }
    }

    /**
     * @param string $data
     *
     * @return string
     */
    protected function prepareForStorage(string $data): string
    {
        return $this->encryptor->encrypt($data);
    }
}
