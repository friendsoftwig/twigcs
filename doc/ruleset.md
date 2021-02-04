# Custom rulesets

Twigcs ships with a ruleset based on the [official coding standard as proposed by Twig](http://twig.sensiolabs.org/doc/coding_standards.html).

You can however customize or even replace this ruleset.

## Using a custom ruleset

```bash
twigcs /path/to/views --ruleset \MyApp\TwigCsRuleset
```
*Note:* `twigcs` needs to be used via composer and the ruleset class must be reachable via composer's autoloader for this feature to work.
Also note that depending on your shell, you might need to escape backslashes in the fully qualified class name:

```bash
twigcs /path/to/views --ruleset \\MyApp\\TwigCsRuleset
```

## Creating a custom ruleset

You must create a class implementing `FriendsOfTwig\Twigcs\Ruleset\RulesetInterface` : 

```php
use FriendsOfTwig\Twigcs\Ruleset\RulesetInterface;

class MyRuleset implements RulesetInterface
{
    private $twigMajorVersion;

    public function __construct(int $twigMajorVersion)
    {
        // Use this to customize your rules based on the major twig version
        $this->twigMajorVersion = $twigMajorVersion;
    }

    public function getRules()
    {
        $configurator = new RulesetConfigurator();
        $configurator->setTwigMajorVersion($this->twigMajorVersion);
        $builder = new RulesetBuilder($configurator);

        return [
            new Rule\LowerCaseVariable(Violation::SEVERITY_ERROR),
            new Rule\RegEngineRule(Violation::SEVERITY_ERROR, $builder->build()),
            new Rule\ForbiddenFunctions(Violation::SEVERITY_ERROR, ['dump']),
            // ...
        ];
    }
}
```

The `getRules` method returns an array of rules that need to be enforced. They usually needs a `$severity` argument
that gives the priority of the violations that it will generate.

## Existing rules

There are 5 built-ins rules at the moment:

- `FriendsOfTwig\Twigcs\Rule\LowerCaseVariable`: Ensures that every declared variable name is lower case (using `_` as a separator).
- `FriendsOfTwig\Twigcs\Rule\ForbiddenFunctions`: Ensures that the given functions are not used in any twig template.
- `FriendsOfTwig\Twigcs\Rule\TrailingSpace`: Ensures that there are no space at the end of a line.
- `FriendsOfTwig\Twigcs\Rule\UnusedMacro`: Ensures that an imported macro is called inside the current or any child scope.
- `FriendsOfTwig\Twigcs\Rule\UnusedVariable`: Ensures that a declared variable is used in the current of any child scope.
- `FriendsOfTwig\Twigcs\Rule\RegEngineRule`: This rule is actually a complete engine that checks low level coding style (like spacing). See [the RegEngine documentation](regengine.md)

## Creating your own rules

You can create **any rule you want**. Simply create a class implementing `FriendsOfTwig\Twigcs\Rule\RuleInterface`.
You can also extends `FriendsOfTwig\Twigcs\Rule\AbstractRule` that provides common methods.

Every rule receives a [token stream from twig](https://twig.symfony.com/doc/2.x/internals.html#the-lexer) and must return an array of `FriendsOfTwig\Twigcs\Validator\Violation`.

Here's as an example the LowerCaseVariable rule implementation :

```php
use FriendsOfTwig\Twigcs\Lexer;
use FriendsOfTwig\Twigcs\Token;

class LowerCaseVariable extends AbstractRule implements RuleInterface
{
    public function check(\Twig\TokenStream $tokens)
    {
        $violations = [];

        while (!$tokens->isEOF()) {
            $token = $tokens->getCurrent();

            // If the token is a name token and any uppercase letter is detected
            if (\Twig\Token::NAME_TYPE === $token->getType() && preg_match('/[A-Z]/', $token->getValue())) {

                // Then we look 2 tokens before to see if with find a "set" keyword, indicating it is a variable declaration
                if (Token::WHITESPACE_TYPE === $tokens->look(Lexer::PREVIOUS_TOKEN)->getType() && 'set' === $tokens->look(-2)->getValue()) {

                    // If it is the case, a violation is created. The line and column can be obtained from the token.
                    $violations[] = $this->createViolation($tokens->getSourceContext()->getPath(), $token->getLine(), $token->columnno, sprintf('The "%s" variable should be in lower case (use _ as a separator).', $token->getValue()));
                }
            }

            $tokens->next();
        }

        return $violations;
    }
}
```
