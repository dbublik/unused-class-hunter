# Unused class hunter

Detects unused classes in your PHP codebase.

[![PHP Version Requirement](https://img.shields.io/packagist/dependency-v/dbublik/unused-class-hunter/php)](https://packagist.org/packages/dbublik/unused-class-hunter)
[![License](https://poser.pugx.org/dbublik/unused-class-hunter/license)](https://choosealicense.com/licenses/mit/)
[![Tests](https://github.com/dbublik/unused-class-hunter/actions/workflows/tests.yaml/badge.svg)](https://github.com/dbublik/unused-class-hunter/actions/workflows/tests.yaml)
[![Lint](https://github.com/dbublik/unused-class-hunter/actions/workflows/lint.yaml/badge.svg)](https://github.com/dbublik/unused-class-hunter/actions/workflows/lint.yaml)
[![Code coverage](https://coveralls.io/repos/github/dbublik/unused-class-hunter/badge.svg)](https://coveralls.io/github/dbublik/unused-class-hunter)
[![Mutation score](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fdbublik%2Funused-class-hunter%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/dbublik/unused-class-hunter/main)

## Installation

```bash
composer require --dev dbublik/unused-class-hunter
```

## Usage

After installation, you can run the following command to start hunting:

```bash
./vendor/bin/unused-class-hunter hunt
```

And that’s it! The Hunter will scan your entire codebase and find all unused classes.
If you want to delete them immediately, just run the command:

```bash
./vendor/bin/unused-class-hunter hunt --delete
```

## Customization

### Config:

Most likely, after the first run, the Hunter will find classes that are actually used.
In this case you can help it by creating a configuration file `.unused-class-hunter.php` in the root of the project:

```php
<?php

declare(strict_types=1);

use DBublik\UnusedClassHunter\Config;
use Symfony\Component\Finder\Finder;

$finder = Finder::create()
    ->in(__DIR__);

return (new Config())
    ->setFinder($finder);
```

If your config file has another path, you can specify this via the "config" option:

```bash
./vendor/bin/unused-class-hunter hunt --config=example/directory/.unused-class-hunter.php
```

### Ignoring classes:

If you want to ignore certain classes, you have three options.
The first two are to specify the classes or the attributes of these classes in the config:

```php
<?php

declare(strict_types=1);

use DBublik\UnusedClassHunter\Config;

return (new Config())
    ->withIgnoredClasses(
        \ExampleNamespace\FirstExampleClass::class,
        \ExampleNamespace\SecondExampleClass::class,
    )
    ->withIgnoredAttributes(
        \ExampleNamespace\Attribute\ExampleAttribute::class,
    );
```

The third option is to use one of our "filter".
In the engine of this project we use two filters - `ClassFilter` and `AttributeFilter`.

But there are other filters - for example `ApiTagFilter` (which filters classes with the tag `@api`).
See all filters in directory `/src/Filter`.

Or you can create your own custom filter:

```php
<?php

declare(strict_types=1);

namespace ExampleNamespace\Filter;

use DBublik\UnusedClassHunter\Filter\FilterInterface;
use DBublik\UnusedClassHunter\ValueObject\ClassNode;
use DBublik\UnusedClassHunter\ValueObject\ReaderResult;

final readonly class ExampleFilter implements FilterInterface
{
    #[\Override]
    public function isIgnored(ClassNode $class, ReaderResult $reader): bool
    {
        return str_starts_with($class->getName(), 'BadName');
    }
}
```

```php
<?php

declare(strict_types=1);

use DBublik\UnusedClassHunter\Config;

return (new Config())
    ->withFilters(
        new \ExampleNamespace\Filter\ExampleFilter(),
    );
```

### Unignoring classes:

If classes are used as false positives, you can use our pre-filter or create your own.
It helps indicate to the Hunter that the classes should be marked as unused.

For more details, see directory `/src/PreFilter`.

### Sets for some libraries:

The Hunter contains several predefined sets for different libraries, which can be enabled with this config:

```php
<?php

declare(strict_types=1);

use DBublik\UnusedClassHunter\Config;

return (new Config())
    ->withSets(
        symfony: true,
        doctrine: true,
        twig: true,
        phpunit: true,
        codeception: true,
    );
```

### Custom cache directory:

By default, the Hunter stores its cache files in `sys_get_temp_dir() . '/unused-class-hunter'` (usually
`/tmp/unused-class-hunter`).
You can override this by setting the `setCacheDir` method:

```php
<?php

declare(strict_types=1);

use DBublik\UnusedClassHunter\Config;

return (new Config())
    ->setCacheDir(__DIR__ . '/var/cache');
```

### Custom bootstrap files:

If you need to initialize something in PHP runtime before the Hunter runs (like your own autoloader), you can provide
your own bootstrap files:

```php
<?php

declare(strict_types=1);

use DBublik\UnusedClassHunter\Config;

return (new Config())
    ->withBootstrapFiles(
        __DIR__ . '/example/bootstrap.php',
    );
```

## Strict mode

If you want the strictest rules, you can enable this:

```php
<?php

declare(strict_types=1);

use DBublik\UnusedClassHunter\Config;
use Symfony\Component\Finder\Finder;

return (new Config())
    ->allowStrictMode();
```

List of them:

- Classes that are only listed in phpdoc are not considered to be used;

## Output format

The Hunter supports `text` (by default), `gitlab` and `github` formats.

```bash
./vendor/bin/unused-class-hunter hunt --format=github
```

## Supported PHP versions

PHP 8.2 and later.
