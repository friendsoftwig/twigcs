# Twigcs

The missing checkstyle for twig !

Twigcs aims to be what phpcs is to php. It checks your codebase for violations on coding standards.

Please note that the project is still in early development stage and is subject to heavy changes.

## How to install

```bash
composer global require allocine/twigcs
```

## How to run

Basically, just run :

```bash
twigcs lint /path/to/views
```

On Symfony projects, you can run, for instance :

```bash
twigcs lint /project/dir/app/Resources/views
```

You will get a summary of the violations in the console. The exit code of the command is based on the severity
of any violation found. By default, twigcs won't even tolerate a notice, this can be changed at run time :

```bash
twigcs lint /path/to/views --severity warning # Allow notices
```

With the example above, notices are still displayed but not altering the exit code.

### Continuous Integration

Twigcs can be used with your favorite CI server. The command itself will return a consistent exit code telling
the CI job if it failed or succeeded. You can also have a nice xml report (checkstyle format) :

```bash
twigcs lint /path/to/views --reporter checkstyle > /path/to/report.xml
```

### Coding standard

At the moment the only supported standard is the [official one from twig](http://twig.sensiolabs.org/doc/coding_standards.html).

### Coming features

- Indentation checking
- Configurable coding standards

### Contributing

The master is the development branch, if you find any bug or false positive during style checking, please
open an issue or submit a pull request.

When creating or changing a class, don't forget to add you as an `@author` at the top of the file.
