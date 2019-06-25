<?php

declare(strict_types=1);

namespace Webonaute\DoctrineDataLockingBundle\Service;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Psr\Log\LoggerInterface;
use function sprintf;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class DataLocker.
 */
class DataLocker
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(RegistryInterface $doctrine, LoggerInterface $logger)
    {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
    }

    /**
     * @param $entityClass
     * @param $sqlQuery
     * @param $lockId
     * @param array $parameters
     *
     * @return array
     */
    public function customLock($entityClass, $sqlQuery, $lockId, $parameters = []): array
    {
        $count = $this->executeLock($entityClass, $sqlQuery, $parameters);

        $this->logger
            ->notice('DataLocker: Locked entities', [
                'class' => $entityClass,
                'lockId' => $lockId,
                'count' => $count,
            ]);

        $lockedEntities = [];
        //We just do a query if the amount if positive
        if ($count) {
            $lockedEntities = $this->findLocked($entityClass, $lockId);
        }

        return $lockedEntities;
    }

    /**
     * @param string $entityClass
     * @param int $limit
     * @param null|string $extraWhere
     *
     * @return array
     */
    public function lockAndSelect(string $entityClass, int $limit = 50, ?string $extraWhere = null): array
    {
        $lockId = $this->lock($entityClass, $limit, $extraWhere);

        $lockedEntities = [];
        if (null !== $lockId) {
            $lockedEntities = $this->findLocked($entityClass, $lockId);
        }

        return $lockedEntities;
    }

    public function findLocked(string $entityClass, string $lockId)
    {
        /** @var EntityManager $manager */
        $manager = $this->doctrine->getManagerForClass($entityClass);

        return $manager->createQueryBuilder()
            ->select('e')
            ->from($entityClass, 'e')
            ->where('e.processLock.lockId = :lockId')->setParameter('lockId', $lockId)
            ->getQuery()
            ->execute();
    }

    /**
     * Locks requested number of entries and returns lock id.
     *
     * @param string $entityClass
     * @param int $limit
     * @param null|string $extraWhere
     *
     * @return null|string Returns lock id if at least one row was locked.
     *                     Returns null if nothing was locked.
     */
    public function lock(string $entityClass, int $limit = 50, ?string $extraWhere = null, DateTimeImmutable $lockAt = null): ?string
    {
        $lockId = uniqid('', true);
        $parameters = [$lockId];
        if (null !== $lockAt) {
            $parameters[] = $lockAt->setTimezone(new DateTimeZone('Etc/UTC'))->format('Y-m-d H:i:s');
        }
        $sqlQuery = $this->createLockQuery($entityClass, $limit, $extraWhere, null !== $lockAt);

        $lockedCount = $this->executeLock($entityClass, $sqlQuery, $parameters);

        $this->logger
            ->notice('DataLocker: Locked entities', [
                'class' => $entityClass,
                'lockId' => $lockId,
                'count' => $lockedCount,
            ]);

        if ($lockedCount > 0) {
            return $lockId;
        }

        return null;
    }

    public function unlock(string $entityClass, string $lockId)
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->doctrine
            ->getManagerForClass($entityClass);

        return $manager->createQueryBuilder()->update($entityClass, 'e')
            ->set('e.processLock.lockedAt', 'NULL')
            ->set('e.processLock.lockId', 'NULL')
            ->set('e.processLock.lockState', 'NULL')
            ->where('e.processLock.lockId = :lockId')->setParameter('lockId', $lockId)
            ->getQuery()
            ->execute();
    }

    public function deleteLocked(string $entityClass, string $lockId)
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->doctrine
            ->getManagerForClass($entityClass);

        return $manager->createQueryBuilder()->delete($entityClass, 'e')
            ->where('e.processLock.lockId = :lockId')->setParameter('lockId', $lockId)
            ->getQuery()
            ->execute();
    }

    public function executeLock(string $entityClass, string $sqlQuery, array $parameters): int
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->doctrine
            ->getManagerForClass($entityClass);
        $connection = $manager->getConnection();

        return $connection->executeUpdate($sqlQuery, $parameters);
    }

    protected function createLockQuery(string $entityClass, int $limit, ?string $extraWhere, bool $lockAtCondition = false): string
    {
        $manager = $this->doctrine->getManagerForClass($entityClass);
        /** @var $classMetadata ClassMetadataInfo */
        $classMetadata = $manager->getClassMetadata($entityClass);

        if (true === $lockAtCondition) {
            $extraWhere = "{$classMetadata->getColumnName('processLock.lockingAt')} <= ?"
                .($extraWhere ? " AND ({$extraWhere})" : '');
        }

        $sql = sprintf(
            'UPDATE %1$s SET %2$s = ?, %3$s = NOW() WHERE %4$s IS NULL %5$s',
            $classMetadata->getTableName(),
            $classMetadata->getColumnName('processLock.lockId'),
            $classMetadata->getColumnName('processLock.lockedAt'),
            $classMetadata->getColumnName('processLock.lockId'),
            $extraWhere ? 'AND (' . $extraWhere . ')' : '',
        );
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }

        return $sql;
    }
}
