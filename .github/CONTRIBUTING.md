# CONTRIBUTING

We are using [GitHub Actions](https://github.com/features/actions) as a continuous integration system.

For details, take a look at the following workflow configuration files:

- [`workflows/integrate.yaml`](workflows/integrate.yaml)

## Coding Standards

We are using [`friendsofphp/php-cs-fixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer) to enforce coding standards in PHP files.

Run

```sh
make coding-standards
```

to automatically fix coding standard violations.

## Dependency Analysis

We are using [`maglnet/composer-require-checker`](https://github.com/maglnet/ComposerRequireChecker) to prevent the use of unknown symbols in production code.

Run

```sh
make dependency-analysis
```

to run a dependency analysis.

## Phar

We are using [`humbug/box`](https://github.com/box-project/box) to compile a Phar.

Run

```sh
make phar
```

to compile a Phar.

## Static Code Analysis

We are using [`phpstan/phpstan`](https://github.com/phpstan/phpstan) to statically analyze the code.

Run

```sh
make static-code-analysis
```

to run a static code analysis.

We are also using the baseline feature of [`phpstan/phpstan`](https://phpstan.org/user-guide/baseline).

Run

```sh
make static-code-analysis-baseline
```

to regenerate the baseline in [`../phpstan-baseline.neon`](../phpstan-baseline.neon).

:exclamation: Ideally, the baseline should shrink over time.

## Tests

We are using [`phpunit/phpunit`](https://github.com/sebastianbergmann/phpunit) to drive the development.

Run

```sh
make tests
```

to run all the tests.

## Extra lazy?

Run

```sh
make
```

to enforce coding standards, run a static code analysis, and run tests!

## Help

:bulb: Run

```sh
make help
```

to display a list of available targets with corresponding descriptions.
