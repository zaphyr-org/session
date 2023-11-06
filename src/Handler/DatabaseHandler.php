<?php

declare(strict_types=1);

namespace Zaphyr\Session\Handler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use SessionHandlerInterface;
use Throwable;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class DatabaseHandler implements SessionHandlerInterface
{
    /**
     * @var string
     */
    protected readonly string $table;

    /**
     * @var string
     */
    protected readonly string $idColumn;

    /**
     * @var string
     */
    protected readonly string $dataColumn;

    /**
     * @var string
     */
    protected readonly string $timeColumn;

    /**
     * @param Connection            $connection
     * @param array<string, string> $dbOptions
     * @param int $minutes
     */
    public function __construct(
        protected Connection $connection,
        array $dbOptions = [],
        protected int $minutes = 60
    ) {
        $this->table = $dbOptions['table'] ?? 'sessions';
        $this->idColumn = $dbOptions['idColumn'] ?? 'id';
        $this->dataColumn = $dbOptions['dataColumn'] ?? 'data';
        $this->timeColumn = $dbOptions['timeColumn'] ?? 'time';
    }

    /**
     * {@inheritdoc}
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $id): string|false
    {
        try {
            $qb = $this->connection->createQueryBuilder()
                ->select($this->dataColumn)
                ->from($this->table)
                ->where($this->idColumn . ' = :id')
                ->andWhere($this->timeColumn . ' <= :time')
                ->setParameter('id', $id)
                ->setParameter('time', time() + ($this->minutes * 60), ParameterType::INTEGER);

            return base64_decode($qb->fetchOne()) ?: false;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $id, string $data): bool
    {
        $this->connection->beginTransaction();

        try {
            $queryBuilder = $this->connection->createQueryBuilder();

            $queryBuilder
                ->delete($this->table)
                ->where($this->idColumn . ' = :id')
                ->setParameter('id', $id)
                ->executeStatement();

            $queryBuilder
                ->insert($this->table)
                ->values([
                    $this->idColumn => ':id',
                    $this->dataColumn => ':data',
                    $this->timeColumn => ':time',
                ])
                ->setParameter('id', $id)
                ->setParameter('data', base64_encode($data))
                ->setParameter('time', time(), ParameterType::INTEGER)
                ->executeStatement();

            $this->connection->commit();

            return true;
        } catch (Throwable) {
            $this->connection->rollBack();

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(string $id): bool
    {
        try {
            $this->connection
                ->createQueryBuilder()
                ->delete($this->table)
                ->where($this->idColumn . ' = :id')
                ->setParameter('id', $id)
                ->executeStatement();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function gc(int $max_lifetime): int|false
    {
        try {
            return $this->connection
                ->createQueryBuilder()
                ->delete($this->table)
                ->where($this->timeColumn . ' <= :time')
                ->setParameter('time', time() - $max_lifetime, ParameterType::INTEGER)
                ->executeStatement();
        } catch (Throwable) {
            return false;
        }
    }
}
