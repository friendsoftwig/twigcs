# Twigcs

The missing checkstyle for twig !

Twigcs aims to be what phpcs is to php. It checks your codebase for violations on coding standards.

Please note that the project is still in early development stage and is subject to heavy changes.

## How to install

```bash
composer global require friendsoftwig/twigcs
```

## How to run

Basically, just run :

```bash
twigcs /path/to/views
```

On Symfony projects, you can run, for instance :

```bash
twigcs /project/dir/app/Resources/views
```

You will get a summary of the violations in the console. The exit code of the command is based on the severity
of any violation found. By default, twigcs won't even tolerate a notice, this can be changed at run time :

```bash
twigcs /path/to/views --severity warning # Allow notices
```

With the example above, notices are still displayed but not altering the exit code.

You can also exclude relative subfolders of path like this :

```bash
twigcs /path/to/views --exclude vendor
```

Tips : You can use multiple _exclude_ parameters.

### Continuous Integration

Twigcs can be used with your favorite CI server. The command itself will return a consistent exit code telling
the CI job if it failed or succeeded. You can also have a nice xml report (checkstyle format) :

```bash
twigcs /path/to/views --reporter checkstyle > /path/to/report.xml
```

### Coding standard

At the moment the only available standard is the [official one from twig](http://twig.sensiolabs.org/doc/coding_standards.html). 

#### Custom coding standard

You can create a class implementing `RulesetInterface` and supply it as a `--ruleset` option to the CLI script: 

```bash
twigcs /path/to/views --ruleset \MyApp\TwigCsRuleset
```

*Note:* `twigcs` needs to be used via composer and the ruleset class must be reachable via composer's autoloader for this feature to work.
Also note that depending on your shell, you might need to escape backslashes in the fully qualified class name:

```bash
twigcs /path/to/views --ruleset \\MyApp\\TwigCsRuleset
```

### Coming features

- Indentation checking
- Forbidden functions detection (a dump() in production for instance)

### Contributing

The master is the development branch, if you find any bug or false positive during style checking, please
open an issue or submit a pull request.

When creating or changing a class, don't forget to add you as an `@author` at the top of the file.
