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

use DBublik\UnusedClass\Config;
use Symfony\Component\Finder\Finder;

$finder = Finder::create()
    ->in(__DIR__);

return (new Config())
    ->setFinder($finder);
```

And the following command:

```bash
./vendor/bin/unused-class-hunter check --config=.unused-class-hunter.php
```

### Ignoring classes:

If you want to ignore certain classes, you have three options.
The first two are to specify the classes or the attributes of these classes in the config:

```php
<?php

declare(strict_types=1);

use DBublik\UnusedClass\Config;
use Symfony\Component\Finder\Finder;

$finder = Finder::create()
    ->in(__DIR__);

return (new Config())
    ->setFinder($finder)
    ->withIgnoredClasses([
        \ExampleNamespace\FirstExampleClass::class,
        \ExampleNamespace\SecondExampleClass::class,
    ])
    ->withIgnoredAttributes([
        \ExampleNamespace\Attribute\ExampleAttribute::class,
    ]);
```

The third option is to create your own custom filter:

```php
<?php

declare(strict_types=1);

namespace ExampleNamespace\Filter;

use DBublik\UnusedClass\Config;
use DBublik\UnusedClass\Filter\FilterInterface;
use DBublik\UnusedClass\ValueObject\FileInformation;
use DBublik\UnusedClass\ValueObject\ParseInformation;

final readonly class ExampleFilter implements FilterInterface
{
    public function isIgnored(FileInformation $class, ParseInformation $information, Config $config): bool
    {
        return str_starts_with($class->getClassName(), 'BadName');
    }
}
```

```php
<?php

declare(strict_types=1);

use DBublik\UnusedClass\Config;
use Symfony\Component\Finder\Finder;

$finder = Finder::create()
    ->in(__DIR__);

return (new Config())
    ->setFinder($finder)
    ->withFilters([
        new \ExampleNamespace\Filter\ExampleFilter(),
    ]);
```

### Custom cache directory:

```php
<?php

declare(strict_types=1);

use DBublik\UnusedClass\Config;
use Symfony\Component\Finder\Finder;

$finder = Finder::create()
    ->in(__DIR__);

return (new Config())
    ->setFinder($finder)
    ->setCacheDir(__DIR__ . '/var/cache');
```

### Sets for some libraries:

The Hunter contains several predefined sets for different libraries, which can be enabled with this config:

```php
<?php

declare(strict_types=1);

use DBublik\UnusedClass\Config;
use Symfony\Component\Finder\Finder;

$finder = Finder::create()
    ->in(__DIR__);

return (new Config())
    ->setFinder($finder)
    ->withSets(
        symfony: true,
        doctrine: true,
        twig: true,
        phpunit: true,
        codeception: true,
    );
```

## Supported PHP versions

PHP 8.3 and later.
