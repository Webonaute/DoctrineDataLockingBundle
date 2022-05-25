<?php

declare(strict_types=1);

namespace Webonaute\DoctrineDataLockingBundle\Entity;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * !!! You should put a index on at least the lockId and lockState.
 */
#[ORM\Embeddable]
class ProcessLock
{
    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    private ?string $lockId = null;

    #[ORM\Column(type: 'string', length: 40, nullable: true)]
    private ?string $lockState = null;

    /**
     * @var \DateTime|DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTimeInterface $lockedAt = null;

    /**
     * Prevent locking before this date.
     *
     * @var \DateTime|DateTimeImmutable|null
     */
    #[ORM\Column(name: 'lockingAt', type: 'datetime', nullable: true)]
    private ?DateTimeInterface $lockingAt = null;

    public function getLockId(): ?string
    {
        return $this->lockId;
    }

    /**
     * @param string $lockId
     */
    public function setLockId(?string $lockId): void
    {
        $this->lockId = $lockId;
    }

    /**
     * @return \DateTime|DateTimeImmutable|null
     */
    public function getLockedAt(): ?DateTimeInterface
    {
        return $this->lockedAt;
    }

    /**
     * @param DateTime $lockedAt
     */
    public function setLockedAt(?DateTimeInterface $lockedAt): void
    {
        $this->lockedAt = $lockedAt;
    }

    /**
     * @return string
     */
    public function getLockState(): ?string
    {
        return $this->lockState;
    }

    /**
     * @param string $lockState
     */
    public function setLockState($lockState): void
    {
        $this->lockState = $lockState;
    }

    /**
     * @return \DateTime|DateTimeImmutable|null
     */
    public function getLockingAt(): ?DateTimeInterface
    {
        return $this->lockingAt;
    }

    /**
     * @param \DateTime|DateTimeImmutable|null $lockingAt
     */
    public function setLockingAt(?DateTimeInterface $lockingAt): void
    {
        $this->lockingAt = $lockingAt;
    }

    public function reset(): void
    {
        //lockingAt should not get reset.

        $this->setLockedAt(null);
        $this->setLockState(null);
        $this->setLockId(null);
    }
}
