<?php

declare(strict_types=1);

namespace Zaphyr\SessionTests\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\Cookie\Contracts\CookieManagerInterface;
use Zaphyr\Session\Handler\CookieHandler;

class CookieHandlerTest extends TestCase
{

    /**
     * @var CookieManagerInterface&MockObject
     */
    protected CookieManagerInterface&MockObject $cookieManagerMock;

    /**
     * @var ServerRequestInterface&MockObject
     */
    protected ServerRequestInterface&MockObject $serverRequestMock;

    protected CookieHandler $cookieHandler;

    protected int $minutes = 60;

    public function setUp(): void
    {
        $this->cookieManagerMock = $this->createMock(CookieManagerInterface::class);
        $this->serverRequestMock = $this->createMock(ServerRequestInterface::class);
        $this->cookieHandler = new CookieHandler($this->cookieManagerMock, $this->serverRequestMock, $this->minutes);
    }

    public function tearDown(): void
    {
        unset($this->cookieManagerMock, $this->serverRequestMock, $this->cookieHandler);
    }

    /* -------------------------------------------------
     * OPEN
     * -------------------------------------------------
     */

    public function testOpenReturnsTrue(): void
    {
        self::assertTrue($this->cookieHandler->open(__DIR__, 'foo'));
    }

    /* -------------------------------------------------
     * CLOSE
     * -------------------------------------------------
     */

    public function testCloseReturnsTrue(): void
    {
        self::assertTrue($this->cookieHandler->close());
    }

    /* -------------------------------------------------
     * READ
     * -------------------------------------------------
     */

    public function testRead(): void
    {
        $sessionId = 'id';
        $json = json_encode([
            'data' => 'foo',
            'expire' => time() + ($this->minutes * 60),
        ]);

        $this->serverRequestMock->expects(self::once())
            ->method('getCookieParams')
            ->willReturn([$sessionId => $json]);

        self::assertSame('foo', $this->cookieHandler->read($sessionId));
    }

    public function testReadReturnsFalseWhenExpired(): void
    {
        $sessionId = 'id';
        $json = json_encode([
            'data' => 'foo',
            'expire' => time() - ($this->minutes * 60),
        ]);

        $this->serverRequestMock->expects(self::once())
            ->method('getCookieParams')
            ->willReturn([$sessionId => $json]);

        self::assertFalse($this->cookieHandler->read($sessionId));
    }

    public function testReadReturnsFalseOnEncodingError(): void
    {
        $json = json_encode([
            'data' => 'foo',
            'expire' => time() + ($this->minutes * 60),
        ]);

        $this->serverRequestMock->expects(self::once())
            ->method('getCookieParams')
            ->willReturn([$json]);

        self::assertFalse($this->cookieHandler->read('id'));
    }

    /* -------------------------------------------------
     * WRITE
     * -------------------------------------------------
     */

    public function testWrite(): void
    {
        $sessionId = 'id';
        $data = 'foo';
        $expire = time() + ($this->minutes * 60);

        $this->cookieManagerMock->expects(self::once())
            ->method('addToQueue');

        $this->cookieManagerMock->expects(self::once())
            ->method('create')
            ->with($sessionId, json_encode(compact('data', 'expire')));

        self::assertTrue($this->cookieHandler->write($sessionId, $data));
    }

    /* -------------------------------------------------
     * DESTROY
     * -------------------------------------------------
     */

    public function testDestroy(): void
    {
        $sessionId = 'id';

        $this->cookieManagerMock->expects(self::once())
            ->method('addToQueue');

        $this->cookieManagerMock->expects(self::once())
            ->method('forget')
            ->with($sessionId);

        self::assertTrue($this->cookieHandler->destroy($sessionId));
    }

    /* -------------------------------------------------
     * GARBAGE COLLECTOR
     * -------------------------------------------------
     */
    public function testGcReturnsFalse(): void
    {
        self::assertFalse($this->cookieHandler->gc(0));
    }
}
