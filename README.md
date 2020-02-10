# Twigcs

The missing checkstyle for twig!

Twigcs aims to be what [phpcs](https://github.com/squizlabs/PHP_CodeSniffer) is to php. It checks your codebase for
violations on coding standards.

## How to install

```bash
composer global require friendsoftwig/twigcs
```

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
of any violation found. By default, twigcs only tolerates notices, this can be changed at run time:

```bash
twigcs /path/to/views --severity error # Allows notices and warnings
twigcs /path/to/views --severity notice # Disallows notices
twigcs /path/to/views --severity ignore # Allows everything
```

With the example above, notices are still displayed but not altering the exit code.

You can also exclude relative subfolders of path like this:

```bash
twigcs /path/to/views --exclude vendor
```

Tips: You can use multiple _exclude_ parameters.

## Restricting output

By default TwigCS will output all lines that have violations regardless of whether they match the severity level
specified or not. If you only want to see errors that are greater than or equal to the severity level you've specified
you can use the `--display` option. For example. 

```bash
twigcs /path/to/views --severity error --display blocking
```

Would only display errors and not warnings.

### Continuous Integration

Twigcs can be used with your favorite CI server. The command itself will return a consistent exit code telling
the CI job if it failed or succeeded. You can also have a nice xml report (checkstyle format):

```bash
twigcs /path/to/views --reporter checkstyle > /path/to/report.xml
```

### Using older twig versions

By default twigcs is using Twig 3. This means that features like `filter` tags or filtered loops using `if` are not supported
anymore. You can use an older twig version using the `twig-version` option:

```bash
twigcs /path/to/views --twig-version 2
```

#### Custom coding standard

At the moment the only available standard is the [official one from twig](http://twig.sensiolabs.org/doc/coding_standards.html).

You can create a class implementing `RulesetInterface` and supply it as a `--ruleset` option to the CLI script:

```bash
twigcs /path/to/views --ruleset \MyApp\TwigCsRuleset
```

*Note:* `twigcs` needs to be used via composer and the ruleset class must be reachable via composer's autoloader for this feature to work.
Also note that depending on your shell, you might need to escape backslashes in the fully qualified class name:

```bash
twigcs /path/to/views --ruleset \\MyApp\\TwigCsRuleset
```

For more complex needs, have a look at the [custom ruleset documentation](doc/ruleset.md).

### Upgrading

If you're upgrading from 3.x to 4.x or later, please read the [upgrade guide](doc/upgrade.md).

### Community

Join us on [Symfony Devs](https://symfony.com/slack) via the **twigcs** channel.

### Contributing

The master is the development branch, if you find any bug or false positive during style checking, please
open an issue or submit a pull request.

When creating or changing a class, don't forget to add you as an `@author` at the top of the file.
