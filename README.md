# Unused class hunter

Detects unused classes in your PHP codebase.

## Installation

```bash
composer require --dev dbublik/unused-class-hunter
```

## Usage

After installation, you can run the following command to start hunting:

```bash
./vendor/bin/unused-class-hunter check
```

And that’s it! The Hunter will scan your entire codebase and find all unused classes.

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
./vendor/bin/unused-class-hunter check --config=example/directory/.unused-class-hunter.php
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
See all filters in directory `DBublik\UnusedClassHunter\Filter`.

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

## Supported PHP versions

PHP 8.3 and later.
