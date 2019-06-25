# Doctrine DataLocking Bundle #

[![Latest Stable Version](https://poser.pugx.org/webonaute/doctrine-datalocking-bundle/v/stable.svg)](https://packagist.org/packages/webonaute/doctrine-datalocking-bundle) [![Total Downloads](https://poser.pugx.org/webonaute/doctrine-datalocking-bundle/downloads.svg)](https://packagist.org/packages/webonaute/doctrine-datalocking-bundle) [![Latest Unstable Version](https://poser.pugx.org/webonaute/doctrine-datalocking-bundle/v/unstable.svg)](https://packagist.org/packages/webonaute/doctrine-datalocking-bundle) [![License](https://poser.pugx.org/webonaute/doctrine-datalocking-bundle/license.svg)](https://packagist.org/packages/webonaute/doctrine-datalocking-bundle)

<!--ts-->
   * [Doctrine DataLocking Bundle](#doctrine-datalocking-bundle)
      * [About](#about)
      * [Branches](#branches)
      * [Installation](#installation)
      * [Documentation](#documentation)
         * [Configure Entity](#configure-entity)
         * [Lock data who are due.](#lock-data-who-are-due)
         * [Get objects related to one lock ID.](#get-objects-related-to-one-lock-id)
         * [Unlock object after usage.](#unlock-object-after-usage)
         * [Consume object after usage with deleteLocked](#consume-object-after-usage-with-deletelocked)
      * [License](#license)

<!-- Added by: mdelisle, at: Tue 25 Jun 2019 14:12:44 EDT -->

<!--te-->

## About ##

Lock a list of object of the same entity to be executed by a single processor. When the lock is aquire, the lock ID generated can be use by a processor to execute action on that locked list.

## Branches ##

* Use version `1.0-dev` for Symfony 4.0+. [![build status](https://travis-ci.org/webonaute/DoctrineDataLockingBundle.svg?branch=master)](https://travis-ci.org/webonaute/DoctrineDataLockingBundle)

## Installation ##

This bundle is available via [composer](https://github.com/composer/composer), find it on [packagist](https://packagist.org/packages/webonaute/doctrine-datalocking-bundle).

Run : 
```composer require webonaute/doctrine-datalocking-bundle 1.0-dev```

## Documentation ##

### Configure Entity
Add this snipped code to your entity.

``` 
    use Webonaute\DoctrineDataLockingBundle\Entity\ProcessLock;
    
    ... 
    
    /**
     * @var ProcessLock
     *
     * @ORM\Embedded(class=ProcessLock::class)
     */
    private $processLock;
     
    ...
     
    public function __construct()
    {
        ...
        $this->processLock = new ProcessLock();
    }

    /**
     * @return ProcessLock
     */
    public function getProcessLock(): ProcessLock
    {
        return $this->processLock;
    }     
```

### Lock data who are due.
```
while (null !== $lockId = $dataLockerService->lock(Entity::class, 500, $extraWhere, $lockAt)) {
    $this->queue->push($lockId);
}
```

### Get objects related to one lock ID.
```
$lockedEntities = $dataLockerService->findLocked(Entity::class, $lockId);
```

### Unlock object after usage.
This will simply unlock the object. To consume the entity object, use deleteLocked method. 
```
$lockedEntities = $dataLockerService->unlock(Entity::class, $lockId);
```

### Consume object after usage with deleteLocked
This method will consume the entity object by deleting it from the database.
```
$lockedEntities = $dataLockerService->deleteLocked(Entity::class, $lockId);
```

## License ##

See [LICENSE](LICENSE).
