<?php

namespace Allocine\TwigLinter\Test;

use Allocine\TwigLinter\Lexer;
use Allocine\TwigLinter\Ruleset\Official;
use Allocine\TwigLinter\Validator\Validator;

/**
 * Twigcs' main functional tests
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getData
     */
    public function testExpressions($expression, $expectedViolation)
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Array());
        $twig->setLexer(new Lexer($twig));

        $validator = new Validator();

        $violations = $validator->validate(new Official(), $twig->tokenize(new \Twig_Source($expression, 'src', 'src.html.twig')));

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

            // Parenthesis spacing is not appliable to control structures.
            ['{% if (1 + 2) == 3 %}', null],
            ['{% for i in (some_array) %}', null],
            ['{% if (foo and (not bar or baz)) %}', null],
            ['{% for i in  (some_array) %}', 'More than 1 space(s) found after "in".'],
            ['{% if  (1 + 2) == 3 %}', 'More than 1 space(s) found before "(".'],

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
            ["{{ [1,\n2] }}", null],
            ['{{ { hash: "," } }}', null],
            ['{{ [","] }}', null],
            ['{{ [func(1, 2)] }}', null],
            ["{{ [1\n, 2] }}", 'There should be no new line before ",".'],
            ['{{ [1, 2,3] }}', 'There should be 1 space(s) after ",".'],
            ['{{ [1, 2 , 3] }}', 'There should be no space before ",".'],
            ['{{ [1, 2,     3] }}', 'More than 1 space(s) found after ",".'],
            ['{{ sliced_array[0:4] }}', null],
            ['{{ sliced_array[:4] }}', null],
            ['{{ sliced_array[0:] }}', null],
            ['{{ sliced_array[: 4] }}', 'There should be no space after ":".'],
            ['{{ sliced_array[0: 4] }}', 'There should be no space after ":".'],
            ['{{ sliced_array[0 :4] }}', 'There should be no space before ":".'],
            ['{{ sliced_array[0:4 ] }}', 'There should be no space before "]".'],
            ['{{ sliced_array[ 0:4] }}', 'There should be no space after "[".'],

            // Put one (and only one) space before and after the following operators: comparison operators (==, !=, <, >, >=, <=), math operators (+, -, /, *, %, //, **), logic operators (not, and, or), ~, is, in, and the ternary operator (?:).
            ['{{ 1 + 2 }}', null],
            ['{{ 1+ 2 }}', 'There should be 1 space(s) before "+".'],
            ['{{ 1 +2 }}', 'There should be 1 space(s) after "+".'],
            ['{{ 1- 2 }}', 'There should be 1 space(s) before "-".'],
            ['{{ 1 -2 }}', 'There should be 1 space(s) after "-".'],
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
            ['{{ test ? { foo: bar } : 1 }}', null],
            ['{{ test ? 1 }}', null],
            ['{{ {foo: test ? path({bar: baz}) : null} }}', null],
            ['{{ [test ? path({bar: baz}) : null] }}', null],
            ['{{ { prop1: foo ? "bar", prop2: true } }}', null],
            ['{% foo == -1 %}', null],
            ['{{ -1 }}', null],
            ['{{ -10 }}', null],
            ['{{ (-10) }}', null],

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

            // Complex encountered cases
            ['{% set baz = foo is defined ? object.property : default %}{{ baz }}', null],

            // Spaces
            ["{{ foo }}    \n", "A line should not end with blank space(s)."],
            ["{{ foo }}\t\n", "A line should not end with blank space(s)."],
            ["{{ foo }}\r\n\r\n", null],

            // @TODO: Not in spec : one space separated arguments
            // @TODO: Indent your code inside tags (use the same indentation as the one used for the target language of the rendered template):
        ];
    }
}
