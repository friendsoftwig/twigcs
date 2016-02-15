<?php

namespace Allocine\TwigLinter\Test;

use Allocine\TwigLinter\Lexer;
use Allocine\TwigLinter\Ruleset\Official;
use Allocine\TwigLinter\Validator\Validator;

class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getData
     */
    public function testExpressions($expression, $expectedViolation)
    {
        $twig = new \Twig_Environment();
        $twig->setLexer(new Lexer($twig));

        $validator = new Validator();


        $violations = $validator->validate(new Official(), $twig->tokenize($expression));

        if ($expectedViolation) {
            $this->assertSame(1, count($violations));
            $this->assertSame($expectedViolation, $violations[0]->getReason());
        } else {
            $this->assertSame(0, count($violations));
        }
    }

    public function getData()
    {
        return [
            // Put one (and only one) space after the start of a delimiter and before the end of a delimiter.
            ['{{ foo }}', null],
            ['{{ foo   }}', 'More than 1 space(s) found before closing a variable.'],
            ['{{    foo }}', 'More than 1 space(s) found after opening a variable.'],
            ['{% foo   %}', 'More than 1 space(s) found before closing a block.'],
            ['{%    foo %}', 'More than 1 space(s) found after opening a block.'],

            // Do not put any spaces after an opening parenthesis and before a closing parenthesis in expressions.
            // Do not put any spaces before and after the parenthesis used for filter and function calls.
            ['{{ foo(1) }}', null],
            ['{{ foo( 1) }}', 'There should be no space after "(".'],
            ['{{ foo(1 ) }}', 'There should be no space before ")".'],
            ['{{ foo (1) }}', 'There should be no space before "(".'],
            ['{{ (1) }}',     null],
            ['{{ ( 1) }}',    'There should be no space after "(".'],
            ['{{ (1 ) }}',    'There should be no space before ")".'],

            // Do not put any spaces before and after the following operators: |, ., .., [].
            ['{{ foo|baz }}', null],
            ['{{ foo[0] }}', null],
            ['{{ foo[0].bar }}', null],
            ['{{ foo[0]|bar }}', null],
            ['{{ foo |baz }}', 'There should be no space before "|".'],
            ['{{ foo| baz }}', 'There should be no space after "|".'],
            ['{{ foo() |baz }}', 'There should be no space before "|".'],
            ['{{ foo() * 2 |baz }}', 'There should be no space before "|".'],
            ['{{ foo.baz }}', null],
            ['{{ foo .baz }}', 'There should be no space before ".".'],
            ['{{ foo. baz }}', 'There should be no space after ".".'],
            ['{{ foo() .baz }}', 'There should be no space before ".".'],
            ['{{ foo() * 2 .baz }}', 'There should be no space before ".".'],

            // Put one (and only one) space after the : sign in hashes and , in arrays and hashes:
            ['{{ {foo: 1} }}', null],
            ['{{ {foo:  1} }}', 'More than 1 space(s) found after ":".'],
            ['{{ [1, 2, 3] }}', null],
            ['{{ [1, 2,3] }}', 'There should be 1 space(s) after ",".'],
            ['{{ [1, 2 , 3] }}', 'There should be no space before ",".'],
            ['{{ [1, 2,     3] }}', 'More than 1 space(s) found after ",".'],


            // Put one (and only one) space before and after the following operators: comparison operators (==, !=, <, >, >=, <=), math operators (+, -, /, *, %, //, **), logic operators (not, and, or), ~, is, in, and the ternary operator (?:).
            ['{{ 1 + 2 }}', null],
            ['{{ 1+ 2 }}', 'There should be 1 space(s) before "+".'],
            ['{{ 1 +2 }}', 'There should be 1 space(s) after "+".'],
            ['{{ 1  + 2 }}', 'More than 1 space(s) found before "+".'],
            ['{{ 1 +  2 }}', 'More than 1 space(s) found after "+".'],
            ['{{ 1 ? "foo" : "bar" }}', null],
            ['{{ 1 ? "foo" : "bar" ? "baz" : "foobar" }}', null],
            ['{{ 1 ? "foo" : "bar" ? "baz" :"foobar" }}', 'There should be 1 space(s) after ":".'],
            ['{{ 1? "foo" : "bar" }}', 'There should be 1 space(s) before "?".'],
            ['{{ 1 ?"foo" : "bar" }}', 'There should be 1 space(s) after "?".'],
            ['{{ 1 ? "foo": "bar" }}', 'There should be 1 space(s) before ":".'],
            ['{{ 1 ? "foo" :"bar" }}', 'There should be 1 space(s) after ":".'],
            ['{{ 1 ?: "foo" }}', null],

            // Use lower cased and underscored variable names.
            ['{% set foo = 1 %}{{ foo }}', null],
            ['{% set foo_bar = 1 %}{{ foo_bar }}', null],
            ['{% set fooBar = 1 %}{{ fooBar }}', 'The "fooBar" variable should be in lower case (use _ as a separator).'],

            // Unused variables
            ['{% set foo = 1 %}', 'Unused variable "foo".'],

            // Unused macros
            ['{% import "foo.html.twig" as foo %}{{ foo() }}', null],
            ['{% import "foo.html.twig" as foo %}', 'Unused macro "foo".'],
            ['{% import "foo.html.twig" as foo, bar %}{{ foo() ~ bar() }}', null],
            ['{% import "foo.html.twig" as foo, bar %}{{ foo() }}', 'Unused macro "bar".'],

            // @TODO: Not in spec : one space separated arguments
            // @TODO: Indent your code inside tags (use the same indentation as the one used for the target language of the rendered template):
        ];
    }
}
