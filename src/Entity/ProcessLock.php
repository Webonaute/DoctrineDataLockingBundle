<?php

declare(strict_types=1);

namespace Webonaute\DoctrineDataLockingBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * !!! You should put a index on at least the lockId and lockState.
 *
 * @ORM\Embeddable
 */
#[ORM\Embeddable]
class ProcessLock
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $lockId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $lockState;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lockedAt;

    /**
     * Prevent locking before this date.
     *
     * @var DateTime
     *
     * @ORM\Column(name="lockingAt", type="datetime", nullable=true)
     */
    private $lockingAt;

    /**
     * @return null|string
     */
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
     * @return null|DateTime
     */
    public function getLockedAt(): ?DateTime
    {
        return $this->lockedAt;
    }

    /**
     * @param DateTime $lockedAt
     */
    public function setLockedAt(?DateTime $lockedAt): void
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
     * @return null|DateTime
     */
    public function getLockingAt(): ?DateTime
    {
        return $this->lockingAt;
    }

    /**
     * @param null|DateTime $lockingAt
     */
    public function setLockingAt(?DateTime $lockingAt): void
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
