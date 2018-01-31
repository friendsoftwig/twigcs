<?php

namespace Allocine\Twigcs\Test;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Ruleset\Official;
use Allocine\Twigcs\Validator\Validator;

/**
 * Twigcs' main functional tests
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getData
     *
     * @throws \Twig_Error_Syntax
     */
    public function testExpressions($expression, $expectedViolation, $expectedCountError = 1)
    {
        if (is_null($expectedViolation)) {
            $expectedCountError = 0;
        }
        $twig = new \Twig_Environment(new \Twig_Loader_Array());
        $twig->setLexer(new Lexer($twig));

        $validator = new Validator();
        $violations = $validator->addFile($twig->tokenize(new \Twig_Source($expression, 'src', 'src.html.twig')))
            ->check()
            ->validate();

        if ($expectedViolation) {
            $this->assertCount($expectedCountError, $violations, sprintf("There should be exactly %d violation in:\n %s", $expectedCountError, $expression));
            $this->assertSame($expectedViolation, $violations[0]->getReason());
        } else {
            $this->assertCount($expectedCountError, $violations, sprintf("There should be no violations in:\n %s", $expression));
        }
    }

    /**
     * @throws \Twig_Error_Syntax
     */
    public function testMacroImport()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Array());
        $twig->setLexer(new Lexer($twig));

        $validator = new Validator();
        $violations = $validator->addFile(
                $twig->tokenize(new \Twig_Source('{% macro used(value) %}{{ value }}{% endmacro %}{% macro unused(value) %}{{ value }}{% endmacro %}', 'src', 'macro.html.twig'))
            )->addFile(
                $twig->tokenize(new \Twig_Source('{% import "macro.html.twig" as function %} {{ function.used(value) }}', 'src', 'main.html.twig'))
            )
            ->check()
            ->validate();

        $this->assertCount(1, $violations, "There should be exactly one violation in macro.html.twig");
        $this->assertSame('Unused global macro "unused".', $violations[0]->getReason(), "There should be violation on macro 'unused'");
    }

    /**
     * @throws \Twig_Error_Syntax
     */
    public function testMacroFrom()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Array());
        $twig->setLexer(new Lexer($twig));

        $validator = new Validator();
        $violations = $validator->addFile(
                $twig->tokenize(new \Twig_Source('{% macro used(value) %}{{ value }}{% endmacro %}{% macro unused(value) %}{{ value }}{% endmacro %}', 'src', 'macro.html.twig'))
            )->addFile(
                $twig->tokenize(new \Twig_Source('{% from "macro.html.twig" import used as function %} {{ function(value) }}', 'src', 'main.html.twig'))
            )
            ->check()
            ->validate();

        $this->assertCount(1, $violations, "There should be exactly one violation in macro.html.twig");
        $this->assertSame('Unused global macro "unused".', $violations[0]->getReason(), "There should be violation on macro 'unused'");
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
            ['{% import "foo.html.twig" as foo %}', 'Unused local macro "foo".'],
            ['{% import "foo.html.twig" as foo, bar %}{{ foo() ~ bar() }}', null],
            ['{% macro foo(test) %}{{ test }}{% macro bar(test) %}{{ test }}{% import _self as foobar %}{{ foobar.foo }}', 'Unused global macro "bar".'],
            ['{% from "foo.html.twig" import test as foo, bar, baz as bbb %}{{ foo() }}', 'Unused local macro "bar".', 2],

            // Complex encountered cases
            ['{% set baz = foo is defined ? object.property : default %}{{ baz }}', null],

            // Wrapped call for better readability
            ["\t<meta property=\"og:url\" content=\"{{ url(\n\t\tapp.request.attributes.get('_route'),\n\t\tapp.request.attributes.get('_route_params')\n\t) }}\">", null],

            // Spaces
            ["{{ foo }}    \n", "A line should not end with blank space(s)."],
            ["{{ foo }}\t\n", "A line should not end with blank space(s)."],
            ["{{ foo }}\r\n\r\n", null],

            // Check regression of https://github.com/allocine/twigcs/issues/23
            ['{% from _self import folder_breadcrumb %}', 'Unused local macro "folder_breadcrumb".'],

            // Check macro of https://github.com/allocine/twigcs/issues/27
            ['{% macro foo(test) %}{{ test }}{% macro bar(test) %}{{ test }}{% from _self import foo as foobar, bar %}{{ bar(test) }}', 'Unused global macro "foo".', 2],
            // @TODO: Not in spec : one space separated arguments
            // @TODO: Indent your code inside tags (use the same indentation as the one used for the target language of the rendered template):
        ];
    }
}
