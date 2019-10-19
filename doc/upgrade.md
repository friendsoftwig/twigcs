# Upgrade

## From 3.x to 4.x

If you're not using a custom coding standard, upgrading does not require any change on your side. Otherwise,
the following rules are removed and replaced by a regex based engine (also see [the regengine documentation](regengine.md)).

### DelimiterSpacing

```php
new Rule\DelimiterSpacing(Violation::SEVERITY_ERROR, 1),
```

Becomes

```php
$configurator = new RulesetConfigurator();
$builder = new RulesetBuilder($configurator);

$configurator->setTagSpacingPattern('{% expr %}');
$configurator->setPrintStatementSpacingPattern('{{ expr }}');

new Rule\RegEngineRule(Violation::SEVERITY_ERROR, $builder->build()),
```

### ParenthesisSpacing

```php
new Rule\ParenthesisSpacing(Violation::SEVERITY_ERROR, 0, 1)
```

Becomes

```php
$configurator = new RulesetConfigurator();
$builder = new RulesetBuilder($configurator);

$configurator->setEmptyParenthesesSpacingPattern('()');
$configurator->setParenthesesSpacingPattern('(expr)');

new Rule\RegEngineRule(Violation::SEVERITY_ERROR, $builder->build()),
```

### ArraySeparatorSpacing

```php
new Rule\ArraySeparatorSpacing(Violation::SEVERITY_ERROR, 0, 1)
```

Becomes

```php
$configurator = new RulesetConfigurator();
$builder = new RulesetBuilder($configurator);

$configurator->setListSpacingPattern('expr, expr'); // Dictates spaces between values
$configurator->setArraySpacingPattern('[expr]'); // Dictates spaces between the [] and the inside of the array.
$configurator->setEmptyArraySpacingPattern('[]');

new Rule\RegEngineRule(Violation::SEVERITY_ERROR, $builder->build()),
```

### HashSeparatorSpacing

```php
new Rule\HashSeparatorSpacing(Violation::SEVERITY_ERROR, 0, 1)
```

Becomes

```php
$configurator = new RulesetConfigurator();
$builder = new RulesetBuilder($configurator);

$configurator->setHashSpacingPattern('{key: expr, key: expr}');
$configurator->setEmptyHashSpacingPattern('{}');

new Rule\RegEngineRule(Violation::SEVERITY_ERROR, $builder->build()),
```

### OperatorSpacing

```php
new Rule\OperatorSpacing(Violation::SEVERITY_ERROR, [
    '==', '!=', '<', '>', '>=', '<=',
    '+', '-', '/', '*', '%', '//', '**',
    'not', 'and', 'or',
    '~',
    'is', 'in'
], 1),
new Rule\PunctuationSpacing(
    Violation::SEVERITY_ERROR,
    ['|', '.', '..', '[', ']'],
    0,
    new TokenWhitelist([
        ')',
        \Twig_Token::NAME_TYPE,
        \Twig_Token::NUMBER_TYPE,
        \Twig_Token::STRING_TYPE
    ], [2])
)
```

Becomes

```php
$configurator = new RulesetConfigurator();
$builder = new RulesetBuilder($configurator);

$configurator->setUnaryOpSpacingPattern('op expr');
$configurator->setBinaryOpSpacingPattern('expr op expr');

new Rule\RegEngineRule(Violation::SEVERITY_ERROR, $builder->build()),
```

### TernarySpacing

```php
new Rule\TernarySpacing(Violation::SEVERITY_ERROR, 1)
```

Becomes

```php
$configurator = new RulesetConfigurator();
$builder = new RulesetBuilder($configurator);

$configurator->setTernarySpacingPattern('expr ? expr : expr||expr ?: expr');

new Rule\RegEngineRule(Violation::SEVERITY_ERROR, $builder->build()),
```

### SliceShorthandSpacing

```php
new Rule\SliceShorthandSpacing(Violation::SEVERITY_ERROR)
```

Becomes

```php
$configurator = new RulesetConfigurator();
$builder = new RulesetBuilder($configurator);

$configurator->setSliceSpacingPattern('[expr:expr]');

new Rule\RegEngineRule(Violation::SEVERITY_ERROR, $builder->build()),
```