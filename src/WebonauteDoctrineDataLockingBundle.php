<?php

declare(strict_types=1);

namespace Webonaute\DoctrineDataLockingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Webonaute\DoctrineDataLockingBundle\DependencyInjection\DoctrineDataLockingBundleExtension;

/**
 * WebonauteDoctrineDataLockingBundle
 */
class WebonauteDoctrineDataLockingBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new DoctrineDataLockingBundleExtension();
    }
}
