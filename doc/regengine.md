# RegEngine

The RegEngine is an engine dedicated to fine grained syntax checking. It is mainly used to analyze spacing at the moment. It relies on
3 steps :

- Sanitization: it transforms every string in a chain of `A` of the same length. `{% set foo = '(bar)' %}` become `{% set foo = 'AAAAA' %}`.
- Extraction: it creates a hierarchy of expressions by isolating parentheses, hashes and ternaries content.
- Checking: it parses using regular expressions the hierarchy created during the extraction and flags any violation encountered.

## Custom rules using the configurator

The easiest way to customize the Regengine inside your custom ruleset is to use the configurator :

```php
use FriendsOfTwig\Twigcs\RegEngine\RulesetBuilder;
use FriendsOfTwig\Twigcs\RegEngine\RulesetConfigurator;
use FriendsOfTwig\Twigcs\Rule;
use FriendsOfTwig\Twigcs\Validator\Violation;

class MyRuleset implements RulesetInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        $configurator = new RulesetConfigurator();

        // from now on, every empty hash must have 2 spaces inside.
        $configurator->setEmptyHashSpacingPattern('{  }');

        // from now on, there will be no space after the coma in a for loop.
        $configurator->setForSpacingPattern('for <key,>item in expr< if expr>');

        $builder = new RulesetBuilder($configurator);

        return [
            new Rule\RegEngineRule(Violation::SEVERITY_ERROR, $builder->build()),
            // ...
        ];
    }
}
```

For each spacing pattern you must use a particular expression, but you can insert any space between the keyword and they will get
enforced. Here's the list of expressions and their defaults:

```php
$configurator->setArraySpacingPattern('[expr]');
$configurator->setBinaryOpSpacingPattern('expr op expr');
$configurator->setRangeOpSpacingPattern('expr..expr');
$configurator->setElseifSpacingPattern('elseif expr');
$configurator->setEmbedSpacingPattern('embed expr< ignore missing>< with list>< only>');
$configurator->setEmptyArraySpacingPattern('[]');
$configurator->setEmptyHashSpacingPattern('{}');
$configurator->setEmptyListWhitespaces(0);
$configurator->setEmptyParenthesesSpacingPattern('()');
$configurator->setForSpacingPattern('for <key, >item in expr< if expr>');
$configurator->setFromSpacingPattern('from expr import expr< as list>');
$configurator->setFuncSpacingPattern('func(expr)');
$configurator->setHashSpacingPattern('{key: expr, key: expr}');
$configurator->setIfSpacingPattern('if expr');
$configurator->setImportSpacingPattern('import expr as list, expr as list');
$configurator->setIncludeSpacingPattern('include expr< ignore missing>< with list>< only>');
$configurator->setListSpacingPattern('expr, expr');
$configurator->setMacroSpacingPattern('macro name(expr)');
$configurator->setParenthesesSpacingPattern('(expr)');
$configurator->setPrintStatementSpacingPattern('{{ expr }}');
$configurator->setPropertySpacingPattern('expr.expr|filter');
$configurator->setSetSpacingPattern('set expr = expr');
$configurator->setSliceSpacingPattern('[expr:expr]');
$configurator->setTagDefaultArgSpacing(1);
$configurator->setTagSpacingPattern('{% expr %}');
$configurator->setTernarySpacingPattern('expr ? expr : expr||expr ?: expr');
$configurator->setUnaryOpSpacingPattern('op expr');
$configurator->setArrowFunctionSpacingPattern('args => expr');
$configurator->setNamedArgsSpacingPattern('name=value, expr');
```

## Fully custom rules : beware of the dragons !

If you are not satisfied with the configurator, you can still fully create your own rules by
extending the `FriendsOfTwig\Twigcs\RegEngine\RulesetBuilder` class. Here's an example of a minimalistic checker that can
only check variable declaration and additions :

```php
namespace FriendsOfTwig\Twigcs\RegEngine;

use FriendsOfTwig\Twigcs\RegEngine\Checker\Handler;

class RulesetBuilder
{
    const PATTERNS = [
        ' ' => '\s*',
        '$' => '(?:.|\n|\r)+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
        '<' => '{%[~-]?',
        '>' => '[~-]?%}',
    ];

    public function build(): array
    {
        $expr = [];

        $tags = self::using(self::PATTERNS, [
            ['< set @ = $ >',
                // Produces a regexp by replacing each chars by its associated string in RulesetBuilder::PATTERN.
                // If a char is not found, it stays unchanged.
                //
                // The regexp for this pattern is: {%[~-]?(\s*)set(\s*)([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\s*)=(\s*)(?:.|\n|\r)+?(\s*)[~-]?%}
                Handler::create()
                // The expression captured by the $ is looped inside the rule checker
                ->delegate('$', 'expr')
                // The expressions captured as whitespace as checked to be exactly of length 1.
                ->enforceSize(' ', 1, 'There should be one space here.'),
                ->attach(function (RuleChecker $ruleChecker, Report $report, array $captures) {
                    foreach ($captures['@'] as $capture) {
                        if ($capture->getText() != 'foo') {
                            $report->addError(new RuleError('Your variable can only be named "foo".', $capture->getOffset(), $capture->getSource()));
                        }
                    }
                })
            ],
            ['$ + $', Handler::create()
                ->delegate('$', 'expr')
                ->enforceSize(' ', 1, 'There should be one space here.'),
            ],
        ]);

        return ['expr' => $tags];
    }
}
```
