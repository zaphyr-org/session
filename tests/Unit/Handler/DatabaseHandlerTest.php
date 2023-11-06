<?php

declare(strict_types=1);

namespace Zaphyr\SessionTests\Unit\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zaphyr\Session\Handler\DatabaseHandler;

class DatabaseHandlerTest extends TestCase
{
    /**
     * @var Connection&MockObject
     */
    protected Connection&MockObject $connectionMock;

    /**
     * @var MockObject&QueryBuilder
     */
    protected QueryBuilder&MockObject $queryBuilderMock;

    /**
     * @var DatabaseHandler
     */
    protected DatabaseHandler $dbHandler;

    /**
     * @var int
     */
    protected int $minutes = 60;

    protected function setUp(): void
    {
        $this->connectionMock = $this->createMock(Connection::class);
        $this->queryBuilderMock = $this->createMock(QueryBuilder::class);

        $this->dbHandler = new DatabaseHandler($this->connectionMock, minutes: $this->minutes);
    }

    protected function tearDown(): void
    {
        unset($this->connectionMock, $this->queryBuilderMock, $this->dbHandler);
    }

    /* -------------------------------------------------
     * OPEN
     * -------------------------------------------------
     */

    public function testOpenReturnsTrue(): void
    {
        self::assertTrue($this->dbHandler->open('', ''));
    }

    /* -------------------------------------------------
     * CLOSE
     * -------------------------------------------------
     */

    public function testCloseReturnsTrue(): void
    {
        self::assertTrue($this->dbHandler->close());
    }

    /* -------------------------------------------------
     * READ
     * -------------------------------------------------
     */

    public function testRead(): void
    {
        $sessionData = 'data';

        $this->connectionMock
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('select')
            ->with('data')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('from')
            ->with('sessions')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('where')
            ->with('id = :id')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('andWhere')
            ->with('time <= :time')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::exactly(2))
            ->method('setParameter')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('fetchOne')
            ->willReturn(base64_encode($sessionData));

        self::assertEquals($sessionData, $this->dbHandler->read('id'));
    }

    public function testReadReturnsFalseWhenNoEntryFound(): void
    {
        $this->connectionMock
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('select')
            ->with('data')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('from')
            ->with('sessions')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('where')
            ->with('id = :id')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('andWhere')
            ->with('time <= :time')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::exactly(2))
            ->method('setParameter')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('fetchOne')
            ->willReturn(false);

        self::assertFalse($this->dbHandler->read('id'));
    }

    public function testReadReturnsFalseOnThrowable(): void
    {
        $this->connectionMock
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willThrowException(new Exception());

        self::assertFalse($this->dbHandler->read('id'));
    }

    /* -------------------------------------------------
     * WRITE
     * -------------------------------------------------
     */

    public function testWrite(): void
    {
        $this->connectionMock
            ->expects(self::once())
            ->method('beginTransaction');

        $this->connectionMock
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('delete')
            ->with('sessions')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('where')
            ->with('id = :id')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('insert')
            ->with('sessions')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('values')
            ->with([
                'id' => ':id',
                'data' => ':data',
                'time' => ':time',
            ])
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::exactly(4))
            ->method('setParameter')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::exactly(2))
            ->method('executeStatement');

        $this->connectionMock
            ->expects(self::once())
            ->method('commit');

        self::assertTrue($this->dbHandler->write('id', 'data'));
    }

    public function testWriteReturnsFalseOnThrowable(): void
    {
        $this->connectionMock
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willThrowException(new Exception());

        $this->connectionMock
            ->expects(self::once())
            ->method('rollBack');

        self::assertFalse($this->dbHandler->write('id', 'data'));
    }

    /* -------------------------------------------------
     * DESTROY
     * -------------------------------------------------
     */

    public function testDestroy(): void
    {
        $sessionId =  'id';

        $this->connectionMock
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('delete')
            ->with('sessions')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('where')
            ->with('id = :id')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('setParameter')
            ->with('id', $sessionId)
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('executeStatement');

        self::assertTrue($this->dbHandler->destroy($sessionId));
    }

    public function testDestroyReturnsFalseOnThrowable(): void
    {
        $this->connectionMock
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willThrowException(new Exception());

        self::assertFalse($this->dbHandler->destroy('id'));
    }

    /* -------------------------------------------------
     * GARBAGE COLLECTOR
     * -------------------------------------------------
     */

    public function testGc(): void
    {
        $affectedRows = 1;
        $maxLifetime = 0;

        $this->connectionMock
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('delete')
            ->with('sessions')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('where')
            ->with('time <= :time')
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('setParameter')
            ->with('time', time() - $maxLifetime, ParameterType::INTEGER)
            ->willReturn($this->queryBuilderMock);

        $this->queryBuilderMock
            ->expects(self::once())
            ->method('executeStatement')
            ->willReturn($affectedRows);

        self::assertEquals($affectedRows, $this->dbHandler->gc($maxLifetime));
    }

    public function testGcReturnsFalseOnThrowable(): void
    {
        $this->connectionMock
            ->expects(self::once())
            ->method('createQueryBuilder')
            ->willThrowException(new Exception());

        self::assertFalse($this->dbHandler->gc(0));
    }
}
