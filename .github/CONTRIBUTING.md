# CONTRIBUTING

We are using [GitHub Actions](https://github.com/features/actions) as a continuous integration system.

For details, take a look at the following workflow configuration files:

- [`workflows/integrate.yaml`](workflows/integrate.yaml)

## Coding Standards

We are using [`friendsofphp/php-cs-fixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer) to enforce coding standards in PHP files.

Run

```sh
.phive/php-cs-fixer fix
```

to automatically fix coding standard violations.

## Dependency Analysis

We are using [`maglnet/composer-require-checker`](https://github.com/maglnet/ComposerRequireChecker) to prevent the use of unknown symbols in production code.

Run

```sh
.phive/composer-require-checker check --config-file=$(shell pwd)/composer-require-checker.json
```

to run a dependency analysis.

## Static Code Analysis

We are using [`vimeo/psalm`](https://github.com/vimeo/psalm) to statically analyze the code.

Run

```sh
.phive/psalm --config=psalm.xml --show-info=false --stats
```

to run a static code analysis.

We are also using the baseline feature of [`vimeo/psalm`](https://psalm.dev/docs/running_psalm/dealing_with_code_issues/#using-a-baseline-file).

Run

```sh
.phive/psalm --config=psalm.xml --set-baseline=psalm-baseline.xml
```

to regenerate the baseline in [`../psalm-baseline.xml`](../psalm-baseline.xml).

:exclamation: Ideally, the baseline should shrink over time.

## Tests

We are using [`phpunit/phpunit`](https://github.com/sebastianbergmann/phpunit) to drive the development.

Run

```sh
vendor/bin/phpunit
```

to run all the tests.
