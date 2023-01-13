# Twigcs

[![Integrate](https://github.com/friendsoftwig/twigcs/workflows/Integrate/badge.svg)](https://github.com/friendsoftwig/twigcs/actions)

[![Code Coverage](https://codecov.io/github/friendsoftwig/twigcs/branch/main/graph/badge.svg)](https://codecov.io/github/friendsoftwig/twigcs)
[![Type Coverage](https://shepherd.dev/github/friendsoftwig/twigcs/coverage.svg)](https://shepherd.dev/github/friendsoftwig/twigcs)

[![Latest Stable Version](https://poser.pugx.org/friendsoftwig/twigcs/v/stable)](https://packagist.org/packages/friendsoftwig/twigcs)
[![Total Downloads](https://poser.pugx.org/friendsoftwig/twigcs/downloads)](https://packagist.org/packages/friendsoftwig/twigcs)

The missing checkstyle for twig!

Twigcs aims to be what [phpcs](https://github.com/squizlabs/PHP_CodeSniffer) is to php. It checks your codebase for
violations on coding standards.

## How to install

Run

```bash
composer require --dev friendsoftwig/twigcs
```

to install `friendsoftwig/twigcs` with [`composer`](https://getcomposer.org).

Run

```bash
phive install friendsoftwig/twigcs
```

to install `friendsoftwig/twigcs` with [`phive`](https://phar.io).

## How to run

Basically, just run:

```bash
twigcs /path/to/views
```

On Symfony projects, you can run, for instance:

```bash
twigcs /project/dir/app/Resources/views
```

You will get a summary of the violations in the console. The exit code of the command is based on the severity
of any violation found. By default, twigcs only tolerates info, this can be changed at run time:

```bash
twigcs /path/to/views --severity error   # Allows info and warnings
twigcs /path/to/views --severity warning # Allows info
twigcs /path/to/views --severity info    # Disallows info
twigcs /path/to/views --severity ignore  # Allows everything
```

With the example above, info is still displayed but not altering the exit code.

You can also exclude relative subfolders of path like this:

```bash
twigcs /path/to/views --exclude vendor
```

Tips: You can use multiple _exclude_ parameters.

## Restricting output

By default TwigCS will output all lines that have violations regardless of whether they match the severity level
specified or not. If you only want to see violations that are greater than or equal to the severity level you've specified
you can use the `--display` option. For example.

```bash
twigcs /path/to/views --severity error --display blocking
```

Would only display errors and not warnings.

Alternatively you can use `--display all` which is the default behaviour as described above.

## Continuous Integration

Twigcs can be used with your favorite CI server. The command itself will return a consistent exit code telling
the CI job if it failed or succeeded. You can also have a nice xml report (checkstyle format):

```bash
twigcs /path/to/views --reporter checkstyle > /path/to/report.xml
```

## Reporters

Twigcs currently supports to following reporters:

```bash
twigcs /path/to/views --reporter console
twigcs /path/to/views --reporter checkstyle
twigcs /path/to/views --reporter junit
twigcs /path/to/views --reporter emacs
twigcs /path/to/views --reporter json
twigcs /path/to/views --reporter csv
twigcs /path/to/views --reporter githubAction
twigcs /path/to/views --reporter gitlab
```

## Using older twig versions

By default twigcs is using Twig 3. This means that features like `filter` tags or filtered loops using `if` are not supported
anymore. You can use an older twig version using the `twig-version` option:

```bash
twigcs /path/to/views --twig-version 2
```

## Custom coding standard

At the moment the only available standard is the [official one from twig](https://twig.symfony.com/doc/3.x/coding_standards.html).

You can create a class implementing `RulesetInterface` and supply it as a `--ruleset` option to the CLI script:

```bash
twigcs /path/to/views --ruleset \MyApp\TwigCsRuleset
```

_Note:_ `twigcs` needs to be used via composer and the ruleset class must be reachable via composer's autoloader for this feature to work.
Also note that depending on your shell, you might need to escape backslashes in the fully qualified class name:

```bash
twigcs /path/to/views --ruleset \\MyApp\\TwigCsRuleset
```

For more complex needs, have a look at the [custom ruleset documentation](doc/ruleset.md).

## File-based configuration

Using configuration, you can easily store per-project settings:

```php
// ~/.twig_cs.dist.php
<?php

declare(strict_types=1);

use FriendsOfTwig\Twigcs;

return Twigcs\Config\Config::create()
    ->setName('my-config')
    ->setSeverity('warning')
    ->setReporter('json')
    ->setRuleSet(Twigcs\Ruleset\Official::class)
    ->setSpecificRuleSets([ // Every file matching the pattern will use a different ruleset.
        '*/template.html.twig' => Acme\Project\CustomRuleset::class,
    ])
;
```

This configuration will be applied if you call twigcs from the `~/` directory. If you run twigcs from outside this directory,
you must use the `--config` option:

```
cd ~/dirA
twigcs --config ~/dirB/.twig_cs.dist.php # Will lint templates in ~/dirA with the config of ~/dirB
```

By default, the files

- `.twig_cs.php`
- `.twig_cs`
- `.twig_cs.dist.php`
- `.twig_cs.dist`

are looked up in your current working directory (CWD).

You can also provide finders inside config files, they will completely replace the path in the CLI:

```php
// ~/.twig_cs.dist.php
<?php

declare(strict_types=1);

use FriendsOfTwig\Twigcs;

$finderA = Twigcs\Finder\TemplateFinder::create()->in(__DIR__.'/dirA');
$finderB = Twigcs\Finder\TemplateFinder::create()->in(__DIR__.'/dirB');

return Twigcs\Config\Config::create()
    // ...
    ->addFinder($finderA)
    ->addFinder($finderB)
    ->setName('my-config')
;
```

In this case, calling `twigcs` from the `~/` directory of the config will run the linter on the directories pointed by the finders.
If you explicitly supply a path to the CLI, it will be added to the list of linted directories:

```
twigcs ~/dirC # This will lint ~/dirA, ~/dirB and ~/dirC using the configuration file of the current directory.
```

## Template resolution

Using file based configuration, you can provide a way for twigcs to resolve template. This enables better unused variable/macro detection. Here's the
simplest example when you have only one directory of templates.

```php
<?php

declare(strict_types=1);

use FriendsOfTwig\Twigcs;

return Twigcs\Config\Config::create()
    // ...
    ->setTemplateResolver(new Twigcs\TemplateResolver\FileResolver(__DIR__))
    ->setRuleSet(FriendsOfTwig\Twigcs\Ruleset\Official::class)
;
```

Here is a more complex example that uses a chain resolver and a namespaced resolver to handle vendor templates:

```php
<?php

declare(strict_types=1);

use FriendsOfTwig\Twigcs;

return Twigcs\Config\Config::create()
    ->setFinder($finder)
    ->setTemplateResolver(new Twigcs\TemplateResolver\ChainResolver([
        new Twigcs\TemplateResolver\FileResolver(__DIR__ . '/templates'),
        new Twigcs\TemplateResolver\NamespacedResolver([
            'acme' =>  new Twigcs\TemplateResolver\FileResolver(__DIR__ . '/vendor/Acme/AcmeLib/templates')
        ]),
    ]))
;
```

This handles twig namespaces of the form `@acme/<templatepath>`.

## Upgrading

If you're upgrading from 3.x to 4.x or later, please read the [upgrade guide](doc/upgrade.md).

## Community

Join us on [Symfony Devs](https://symfony.com/slack) via the **twigcs** channel.

## Changelog

Please have a look at [`CHANGELOG.md`](CHANGELOG.md).

## Contributing

The `main` branch is the development branch. If you find any bug or false positive during style checking, please
open an issue or submit a pull request.

When creating or changing a class, don't forget to add you as an `@author` at the top of the file.

Please have a look at [`CONTRIBUTING.md`](.github/CONTRIBUTING.md).
