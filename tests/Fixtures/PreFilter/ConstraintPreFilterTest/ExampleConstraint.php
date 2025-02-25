<?php

declare(strict_types=1);

namespace Symfony\Component\Validator {

    if (!class_exists(Constraint::class)) {
        abstract class Constraint {}
    }
}

namespace DBublik\UnusedClassHunter\Tests\Fixtures\PreFilter\ConstraintPreFilterTest {

    use Symfony\Component\Validator\Constraint;

    final class ExampleConstraint extends Constraint {}
}
