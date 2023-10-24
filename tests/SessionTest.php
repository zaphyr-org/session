<?php

declare(strict_types=1);

namespace Zaphyr\SessionTests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SessionHandlerInterface;
use Zaphyr\Session\Session;

class SessionTest extends TestCase
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
     * @var Session
     */
    protected Session $session;

    protected function setUp(): void
    {
        $this->sessionHandlerMock = $this->createMock(SessionHandlerInterface::class);
        $this->session = new Session($this->sessionName, $this->sessionHandlerMock, $this->sessionId);
    }

    protected function tearDown(): void
    {
        unset($this->sessionHandlerMock, $this->session);
    }

    /* ------------------------------------------
     * CONSTRUCTOR
     * ------------------------------------------
     */

    public function testConstructorAndGetterMethods(): void
    {
        $session = new Session($this->sessionName, $this->sessionHandlerMock, $this->sessionId);

        self::assertEquals($this->sessionName, $session->getName());
        self::assertInstanceOf(SessionHandlerInterface::class, $session->getHandler());
        self::assertEquals($this->sessionId, $session->getId());
    }

    /* ------------------------------------------
     * START
     * ------------------------------------------
     */

    public function testStartSession(): void
    {
        $this->session->start();

        self::assertTrue($this->session->isStarted());
        self::assertSame(40, strlen($this->session->getToken()));
        self::assertEquals($this->sessionId, $this->session->getId());
        self::assertEquals($this->sessionName, $this->session->getName());
    }

    /* ------------------------------------------
     * TOKEN
     * ------------------------------------------
     */

    public function testTokenIsNullWhenSessionHasNotStarted(): void
    {
        self::assertNull($this->session->getToken());
    }

    public function testSetAndGetToken(): void
    {
        $this->session->setToken($token = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb');

        self::assertEquals($token, $this->session->getToken());
    }

    public function testSetTokenGeneratesTokenWhenGivenTokenIsInvalid(): void
    {
        $this->session->setToken($token = 'foo');

        self::assertNotSame($token, $this->session->getToken());
        self::assertSame(40, strlen($this->session->getToken()));
    }

    /* ------------------------------------------
     * ID
     * ------------------------------------------
     */

    public function testSetAndGetId(): void
    {
        $this->session->setId($id = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb');

        self::assertEquals($id, $this->session->getId());
    }

    public function testSetIdGeneratesIdWhenGivenIdIsInvalid(): void
    {
        $this->session->setId($id = 'foo');

        self::assertNotSame($id, $this->session->getId());
        self::assertSame(40, strlen($this->session->getId()));
    }

    /* ------------------------------------------
     * NAME
     * ------------------------------------------
     */

    public function testSetAndGetName(): void
    {
        $this->session->setName($name = 'foo');

        self::assertEquals($name, $this->session->getName());
    }

    /* ------------------------------------------
     * ALL
     * ------------------------------------------
     */

    public function testAll(): void
    {
        $this->sessionHandlerMock->expects(self::once())
            ->method('read')
            ->with($this->sessionId)
            ->willReturn(serialize($result = ['foo' => 'bar', '_token' => 'baz']));

        $session = new Session($this->sessionName, $this->sessionHandlerMock, $this->sessionId);
        $session->start();

        self::assertEquals($result, $session->all());
    }

    public function testAllReturnsEmptyArrayWhenSessionHasNotStarted(): void
    {
        self::assertEmpty($this->session->all());
    }

    /* ------------------------------------------
     * GET
     * ------------------------------------------
     */

    public function testGetCanReadFromSessionHandler(): void
    {
        $this->sessionHandlerMock->expects(self::once())
            ->method('read')
            ->with($this->sessionId)
            ->willReturn(serialize(['foo' => 'bar']));

        $session = new Session($this->sessionName, $this->sessionHandlerMock, $this->sessionId);
        $session->start();

        self::assertEquals('bar', $session->get('foo'));
    }

    public function testGetReturnsDefaultValueWhenSessionKeyDoesNotExists(): void
    {
        self::assertNull($this->session->get('nope'));
        self::assertEquals('bar', $this->session->get('foo', 'bar'));
    }

    /* ------------------------------------------
     * SET
     * ------------------------------------------
     */

    public function testSet(): void
    {
        self::assertNull($this->session->get('foo'));

        $this->session->set('foo', 'bar');

        self::assertEquals('bar', $this->session->get('foo'));
    }

    /* ------------------------------------------
     * ADD
     * ------------------------------------------
     */

    public function testAdd(): void
    {
        self::assertNull($this->session->get('foo'));

        $this->session->add('foo', 'bar');

        self::assertEquals(['bar'], $this->session->get('foo'));
    }

    public function testAddConvertsStringValueToArrayIfItemAlreadyExists(): void
    {
        $this->session->set('foo', 'bar');

        self::assertEquals('bar', $this->session->get('foo'));

        $this->session->add('foo', 'baz');

        self::assertEquals(['bar', 'baz'], $this->session->get('foo'));
    }

    /* ------------------------------------------
     * HAS
     * ------------------------------------------
     */

    public function testHas(): void
    {
        self::assertFalse($this->session->has('foo'));

        $this->session->set('foo', 'bar');

        self::assertTrue($this->session->has('foo'));
    }

    /* ------------------------------------------
    * REMOVE
    * ------------------------------------------
    */

    public function testRemove(): void
    {
        $this->session->set('foo', 'bar');

        self::assertTrue($this->session->has('foo'));

        $this->session->remove('foo');

        self::assertFalse($this->session->has('foo'));
    }

    public function testRemoveMultiple(): void
    {
        $this->session->set('foo', 'bar');
        $this->session->set('bar', 'baz');

        self::assertTrue($this->session->has('foo'));
        self::assertTrue($this->session->has('bar'));

        $this->session->removeMultiple(['foo', 'bar']);

        self::assertFalse($this->session->has('foo'));
        self::assertFalse($this->session->has('bar'));
    }

    /* ------------------------------------------
     * FLUSH
     * ------------------------------------------
     */

    public function testFlush(): void
    {
        $this->session->set('foo', 'bar');

        self::assertTrue($this->session->has('foo'));
        self::assertEquals('bar', $this->session->get('foo'));
        self::assertGreaterThan(0, $this->session->all());

        $this->session->flush();

        self::assertFalse($this->session->has('foo'));
        self::assertNull($this->session->get('foo'));
        self::assertCount(0, $this->session->all());
    }

    /* ------------------------------------------
     * MIGRATE
     * ------------------------------------------
     */

    public function testMigrate(): void
    {
        $oldId = $this->session->getId();
        $this->sessionHandlerMock->expects(self::never())
            ->method('destroy');

        self::assertTrue($this->session->migrate());
        self::assertNotSame($oldId, $this->session->getId());
    }

    public function testMigrateCanDestroySession(): void
    {
        $oldId = $this->session->getId();
        $this->sessionHandlerMock->expects(self::once())
            ->method('destroy')
            ->with($oldId);

        self::assertTrue($this->session->migrate(true));
        self::assertNotSame($oldId, $this->session->getId());
    }

    /* ------------------------------------------
     * REGENERATE
     * ------------------------------------------
     */

    public function testRegenerate(): void
    {
        $oldId = $this->session->getId();
        $this->sessionHandlerMock->expects(self::never())
            ->method('destroy');

        self::assertTrue($this->session->regenerate());
        self::assertNotSame($oldId, $this->session->getId());
    }

    public function testRegenerateCanDestroySession(): void
    {
        $oldId = $this->session->getId();
        $this->sessionHandlerMock->expects(self::once())
            ->method('destroy')
            ->with($oldId);

        self::assertTrue($this->session->regenerate(true));
        self::assertNotSame($oldId, $this->session->getId());
    }

    /* ------------------------------------------
     * INVALIDATE
     * ------------------------------------------
     */

    public function testSessionInvalidate(): void
    {
        $oldId = $this->session->getId();
        $this->session->set('foo', 'bar');

        self::assertTrue($this->session->has('foo'));
        self::assertGreaterThan(0, $this->session->all());

        $this->sessionHandlerMock->expects(self::once())
            ->method('destroy')
            ->with($oldId);

        self::assertTrue($this->session->invalidate());
        self::assertFalse($this->session->has('foo'));
        self::assertNotEquals($oldId, $this->session->getId());
        self::assertCount(0, $this->session->all());
    }

    /* ------------------------------------------
     * FLASH
     * ------------------------------------------
     */

    public function testFlash(): void
    {
        $this->session->flash('foo', 'bar');
        $this->session->flash('baz', 0);
        $this->session->flash('qux');

        self::assertTrue($this->session->has('foo'));
        self::assertEquals('bar', $this->session->get('foo'));
        self::assertEquals(0, $this->session->get('baz'));
        self::assertTrue($this->session->get('qux'));
        self::assertEquals(['new' => ['foo', 'baz', 'qux'], 'old' => []], $this->session->get('_flash'));

        $this->session->clearFlashData();

        self::assertTrue($this->session->has('foo'));
        self::assertEquals('bar', $this->session->get('foo'));
        self::assertEquals(0, $this->session->get('baz'));
        self::assertTrue($this->session->get('qux'));
        self::assertEquals(['new' => [], 'old' => ['foo', 'baz', 'qux']], $this->session->get('_flash'));

        $this->session->clearFlashData();

        self::assertFalse($this->session->has('foo'));
        self::assertNull($this->session->get('foo'));
        self::assertNull($this->session->get('baz'));
        self::assertNull($this->session->get('qux'));
        self::assertEquals(['new' => [], 'old' => []], $this->session->get('_flash'));
    }

    /* ------------------------------------------
     * NOW
     * ------------------------------------------
     */

    public function testNow(): void
    {
        $this->session->now('foo', 'bar');

        self::assertTrue($this->session->has('foo'));
        self::assertEquals('bar', $this->session->get('foo'));
        self::assertEquals(['old' => ['foo']], $this->session->get('_flash'));

        $this->session->clearFlashData();

        self::assertFalse($this->session->has('foo'));
        self::assertNull($this->session->get('foo'));
        self::assertEquals(['new' => [], 'old' => []], $this->session->get('_flash'));
    }

    /* ------------------------------------------
     * REFLASH
     * ------------------------------------------
     */

    public function testReflash(): void
    {
        $this->session->flash('foo', 'bar');
        $this->session->set('_flash.old', ['foo']);
        $this->session->reflash();

        self::assertEquals(['foo'], $this->session->get('_flash.new'));
        self::assertEquals([], $this->session->get('_flash.old'));
    }

    public function testReflashWithNow(): void
    {
        $this->session->now('foo', 'bar');
        $this->session->reflash();

        self::assertEquals(['foo'], $this->session->get('_flash.new'));
        self::assertEquals([], $this->session->get('_flash.old'));
    }

    /* ------------------------------------------
     * KEEP
     * ------------------------------------------
     */

    public function testKeep(): void
    {
        $this->session->flash('foo', 'bar');
        $this->session->set('baz', 'qux');
        $this->session->set('_flash.old', ['quu']);

        self::assertEquals(['foo'], $this->session->get('_flash.new'));

        $this->session->keep(['baz', 'quu']);

        self::assertEquals(['foo', 'baz', 'quu'], $this->session->get('_flash.new'));
        self::assertEquals([], $this->session->get('_flash.old'));
    }

    /* ------------------------------------------
     * INPUT
     * ------------------------------------------
     */

    public function testFlashInput(): void
    {
        $this->session->set('qux', 'quu');
        $this->session->flashInput(['foo' => 'bar', 'baz' => 0]);

        self::assertTrue($this->session->hasOldInput('foo'));
        self::assertEquals('bar', $this->session->getOldInput('foo'));
        self::assertEquals(0, $this->session->getOldInput('baz'));
        self::assertFalse($this->session->hasOldInput('qux'));

        $this->session->clearFlashData();

        self::assertTrue($this->session->hasOldInput('foo'));
        self::assertEquals('bar', $this->session->getOldInput('foo'));
        self::assertEquals(0, $this->session->getOldInput('baz'));
        self::assertFalse($this->session->hasOldInput('qux'));
    }

    public function testHasOldInputWithoutKey(): void
    {
        $this->session->flash('foo', 'bar');

        self::assertFalse($this->session->hasOldInput());

        $this->session->flashInput(['baz' => 'qux']);

        self::assertTrue($this->session->hasOldInput());
    }

    /* ------------------------------------------
     * SAVE
     * ------------------------------------------
     */

    public function testSave(): void
    {
        $this->sessionHandlerMock->expects(self::once())
            ->method('read')
            ->willReturn(serialize([]));

        $this->session->start();
        $this->session->set('foo', 'bar');
        $this->session->flash('baz', 'qux');

        $this->sessionHandlerMock->expects(self::once())
            ->method('write')
            ->with(
                $this->sessionId,
                serialize(
                    [
                        '_token' => $this->session->getToken(),
                        'foo' => 'bar',
                        'baz' => 'qux',
                        '_flash' => [
                            'new' => [],
                            'old' => ['baz'],
                        ],
                    ]
                )
            );

        $this->session->save();

        self::assertFalse($this->session->isStarted());
    }
}
