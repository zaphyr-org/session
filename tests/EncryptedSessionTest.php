<?php

declare(strict_types=1);

namespace Zaphyr\SessionTests;

use PHPUnit\Framework\MockObject\MockObject;
use SessionHandlerInterface;
use Zaphyr\Encrypt\Contracts\EncryptInterface;
use Zaphyr\Encrypt\Exceptions\DecryptException;
use Zaphyr\Session\EncryptedSession;
use PHPUnit\Framework\TestCase;

class EncryptedSessionTest extends TestCase
{
    /**
     * @var string
     */
    protected string $sessionId = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    /**
     * @var string
     */
    protected string $sessionName = 'test';

    /**
     * @var SessionHandlerInterface&MockObject
     */
    protected SessionHandlerInterface&MockObject $sessionHandlerMock;

    /**
     * @var EncryptInterface&MockObject
     */
    protected EncryptInterface&MockObject $encryptorMock;

    /**
     * @var EncryptedSession
     */
    protected EncryptedSession $session;

    protected function setUp(): void
    {
        $this->sessionHandlerMock = $this->createMock(SessionHandlerInterface::class);
        $this->encryptorMock = $this->createMock(EncryptInterface::class);

        $this->session = new EncryptedSession(
            $this->sessionName,
            $this->sessionHandlerMock,
            $this->encryptorMock,
            $this->sessionId
        );
    }

    protected function tearDown(): void
    {
        unset($this->sessionHandlerMock, $this->encryptorMock, $this->session);
    }

    /* -------------------------------------------------
     * ENCRYPT
     * -------------------------------------------------
     */

    public function testSessionIsEncrypted(): void
    {
        $this->encryptorMock->expects(self::once())
            ->method('decrypt')
            ->with(serialize([]))
            ->willReturn(serialize([]));

        $this->sessionHandlerMock->expects(self::once())
            ->method('read')
            ->willReturn(serialize([]));

        $this->session->start();
        $this->session->set('foo', 'bar');
        $this->session->flash('baz', 'qux');
        $this->session->now('quu', 'qii');

        $serialized = serialize(
            [
                '_token' => $this->session->getToken(),
                'foo' => 'bar',
                'baz' => 'qux',
                '_flash' => [
                    'new' => [],
                    'old' => ['baz'],
                ],
            ]
        );

        $this->encryptorMock->expects(self::once())
            ->method('encrypt')
            ->with($serialized)
            ->willReturn($serialized);

        $this->sessionHandlerMock->expects(self::once())
            ->method('write')
            ->with($this->sessionId, $serialized);

        $this->session->save();

        self::assertFalse($this->session->isStarted());
    }

    public function testDecryptExceptionReturnsEmptySerializedArray(): void
    {
        $this->encryptorMock->expects(self::once())
            ->method('decrypt')
            ->willThrowException(new DecryptException());

        $this->sessionHandlerMock->expects(self::once())
            ->method('read')
            ->willReturn(serialize([]));

        $this->session->start();

        self::assertSame(['_token' => $this->session->getToken()], $this->session->all());
    }
}
