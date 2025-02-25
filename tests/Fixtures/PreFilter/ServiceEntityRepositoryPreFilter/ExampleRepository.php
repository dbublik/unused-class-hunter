<?php

declare(strict_types=1);

namespace Doctrine\Bundle\DoctrineBundle\Repository {

    if (!class_exists(ServiceEntityRepository::class)) {
        abstract class ServiceEntityRepository {}
    }
}

namespace DBublik\UnusedClassHunter\Tests\Fixtures\PreFilter\ServiceEntityRepositoryPreFilter {

    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

    final class ExampleRepository extends ServiceEntityRepository {}
}
