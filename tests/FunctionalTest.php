<?php

namespace Allocine\Twigcs\Test;

use Allocine\Twigcs\Lexer;
use Allocine\Twigcs\Ruleset\Official;
use Allocine\Twigcs\Validator\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Twigcs' main functional tests.
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class FunctionalTest extends TestCase
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
            $this->assertCount(1, $violations, sprintf("There should be exactly one violation in:\n %s", $expression));
            $this->assertSame($expectedViolation, $violations[0]->getReason());
        } else {
            $this->assertCount(0, $violations, sprintf("There should be no violations in:\n %s", $expression));
        }
    }

    public function getData()
    {
        return [
            // Put one (and only one) space after the start of a delimiter and before the end of a delimiter.
            ['{{ foo }}', null],
            ['{{ foo   }}', 'A print statement should start with one space and end with one space.'],
            ['{{    foo }}', 'A print statement should start with one space and end with one space.'],
            ['{% block foo   %}', 'A tag statement should start with one space and end with one space.'],
            ['{%    block foo %}', 'A tag statement should start with one space and end with one space.'],

            // Do not put any spaces after an opening parenthesis and before a closing parenthesis in expressions.
            // Do not put any spaces before and after the parenthesis used for filter and function calls.
            ['{{ foo(1) }}', null],
            ['{{ foo( 1) }}', 'There should be no space between parentheses and their content.'],
            ['{{ foo(1 ) }}', 'There should be no space between parentheses and their content.'],
            ['{{ foo (1) }}', 'There should be no space between a function name and its opening parentheses.'],
            ['{{ (1) }}',     null],
            ['{{ ( 1) }}',    'There should be no space between parentheses and their content.'],
            ['{{ (1 ) }}',    'There should be no space between parentheses and their content.'],

            // Parenthesis spacing is not appliable to control structures.
            ['{% if (1 + 2) == 3 %}', null],
            ['{% for i in (some_array) %}', null],
            ['{% if (foo and (not bar or baz)) %}', null],
            ['{% for i in  (some_array) %}', 'There should be one space between each for part.'],
            ['{% if  (1 + 2) == 3 %}', 'There should be one space between the if keyword and its condition.'],

            // Do not put any spaces before and after the following operators: |, ., .., [].
            ['{{ foo|baz }}', null],
            ['{{ foo[0] }}', null],
            ['{{ foo[0].bar }}', null],
            ['{{ foo[0]|bar }}', null],
            ['{{ foo |baz }}', 'There should be no space before and after filters.'],
            ['{{ foo| baz }}', 'There should be no space before and after filters.'],
            ['{{ foo() |baz }}', 'There should be no space before and after filters.'],
            ['{{ foo() * 2 |baz }}', 'There should be no space before and after filters.'],
            ['{{ foo.baz }}', null],
            ['{{ foo .baz }}', 'There should be no space before and after the dot when accessing a property.'],
            ['{{ foo. baz }}', 'There should be no space before and after the dot when accessing a property.'],
            ['{{ foo() .baz }}', 'There should be no space before and after the dot when accessing a property.'],

            // Put one (and only one) space after the : sign in hashes and , in arrays and hashes:
            ['{{ {foo: 1} }}', null],
            ['{{ {foo:  1} }}', 'There should be one space between ":" and the value.'],
            ['{{ [1, 2, 3] }}', null],
            ["{{ [1,\n2] }}", null],
            ['{{ {hash: ","} }}', null],
            ['{{ [","] }}', null],
            ['{{ [func(1, 2)] }}', null],
            ["{{ [1\n, 2] }}", 'A list value should be immediately followed by a coma.'],
            ['{{ [1, 2,3] }}', 'The next value of a list should be separated by one space.'],
            ['{{ [1, 2 , 3] }}', 'A list value should be immediately followed by a coma.'],
            ['{{ [1, 2,     3] }}', 'The next value of a list should be separated by one space.'],
            ['{{ sliced_array[0:4] }}', null],
            ['{{ sliced_array[:4] }}', null],
            ['{{ sliced_array[0:] }}', null],
            ['{{ sliced_array[: 4] }}', 'There should be no space inside an array slice short notation.'],
            ['{{ sliced_array[0: 4] }}', 'There should be no space inside an array slice short notation.'],
            ['{{ sliced_array[0 :4] }}', 'There should be no space inside an array slice short notation.'],
            ['{{ sliced_array[0:4 ] }}', 'There should be no space inside an array slice short notation.'],
            ['{{ sliced_array[ 0:4] }}', 'There should be no space inside an array slice short notation.'],
            ['{{ slice[true ? 0 : 1 :0] }}', 'There should be no space inside an array slice short notation.'],

            // Put one (and only one) space before and after the following operators: comparison operators (==, !=, <, >, >=, <=), math operators (+, -, /, *, %, //, **), logic operators (not, and, or), ~, is, in, and the ternary operator (?:).
            ['{{ 1 + 2 }}', null],
            ['{{ 1+ 2 }}', 'There should be exactly one space between the "+" operator and its values.'],
            ['{{ 1 +2 }}', 'There should be exactly one space between the "+" operator and its values.'],
            ['{{ 1- 2 }}', 'There should be exactly one space between the "-" operator and its values.'],
            ['{{ 1 -2 }}', 'There should be exactly one space between the "-" operator and its values.'],
            ['{{ 1  + 2 }}', 'There should be exactly one space between the "+" operator and its values.'],
            ['{{ 1 +  2 }}', 'There should be exactly one space between the "+" operator and its values.'],
            ['{{ 1 ? "foo" : "bar" }}', null],
            ['{{ 1 ? "foo" : "bar" ? "baz" : "foobar" }}', null],
            ['{{ 1 ? "foo" : "bar" ? "baz" :"foobar" }}', 'There should be exactly one space between each part of the ternary operator.'],
            ['{{ 1? "foo" : "bar" }}', 'There should be exactly one space between each part of the ternary operator.'],
            ['{{ 1 ?"foo" : "bar" }}', 'There should be exactly one space between each part of the ternary operator.'],
            ['{{ 1 ? "foo": "bar" }}', 'There should be exactly one space between each part of the ternary operator.'],
            ['{{ 1 ? "foo" :"bar" }}', 'There should be exactly one space between each part of the ternary operator.'],
            ['{{ 1 ?: "foo" }}', null],
            ['{{ test ? {foo: bar} : 1 }}', null],
            ['{{ test ? 1 }}', null],
            ['{{ {foo: test ? path({bar: baz}) : null} }}', null],
            ['{{ [test ? path({bar: baz}) : null] }}', null],
            ['{{ {prop1: foo ? "bar", prop2: true} }}', null],
            ['{{ foo == -1 }}', null],
            ['{{ -1 }}', null],
            ['{{ -10 }}', null],
            ['{{ (-10) }}', null],
            ['{% include "file" with {foo: bar ? baz} %}', null],

            // Use lower cased and underscored variable names.
            ['{% set foo = 1 %}{{ foo }}', null],
            ['{% set foo_bar = 1 %}{{ foo_bar }}', null],
            ['{% set fooBar = 1 %}{{ fooBar }}', 'The "fooBar" variable should be in lower case (use _ as a separator).'],

            // Unused variables
            ['{% set foo = 1 %}', 'Unused variable "foo".'],
            ['{% set foo = 1 %}{{ {foo: 1} }}', 'Unused variable "foo".'],
            ['{% set foo = 1 %}{{ foo ? foo : 0 }}', null],
            ['{% set foo = 1 %}{% macro toto() %}{{ foo }}{% endmacro %}', 'Unused variable "foo".'], // https://github.com/allocine/twigcs/issues/27
            ['{% set foo = 1 %}{% if foo %}{% endif %}', null],
            ['{% set foo = [] %}{% for bar in foo %}{% endfor %}', null],
            ['{% set is = 1 %}{% if 1 is 1 %}{% endif %}', 'Unused variable "is".'],
            ['{% set uppercase = 1 %}{% filter uppercase %}{% endfilter %}', 'Unused variable "uppercase".'],
            ['{% set uppercase = 1 %}{% if "a"|uppercase %}{% endif %}', 'Unused variable "uppercase".'],
            ['{% set uppercase = 1 %}{% if "a"|uppercase(uppercase) %}{% endif %}', null],
            ['{% set bar = 1 %}{% set foo = bar %}{{ foo }}', null],
            ['{% set bar = 1 %}{# twigcs use-var bar #}', null],
            ['{% set bar = 1 %}{% set foo = 1 %}{# twigcs use-var foo, bar #}', null],
            ['{% set foo = 1 %}{# twigcs use-var bar #}', 'Unused variable "foo".'],
            ['{% set foo = 1 %}{{ foo }}', null],
            ['{% set foo %}1{% endset %}{{ foo }}', null],
            ['{% set foo %}1{% endset %}{{ include("foo.html.twig", {foo: foo}) }}', null],

            // Unused macros import
            ['{% import "foo.html.twig" as foo %}{{ foo() }}', null],
            ['{% import "foo.html.twig" as foo %}', 'Unused macro import "foo".'],
            ['{% import "foo.html.twig" as foo, bar %}{{ foo() ~ bar() }}', null],
            ['{% import "foo.html.twig" as foo, bar %}{{ foo() }}', 'Unused macro import "bar".'],
            ['{% import "foo.html.twig" as import %}', 'Unused macro import "import".'],
            ['{% import "foo.html.twig" as if %}{% if (true) %}{% endif %}', 'Unused macro import "if".'],
            ['{% import "foo.html.twig" as uppercase %}{% filter uppercase %}{% endfilter %}', 'Unused macro import "uppercase".'],
            ['{% import "foo.html.twig" as uppercase %}{% if "a"|uppercase %}{% endif %}', 'Unused macro import "uppercase".'],
            ['{% from _self import foo as bar %}', 'Unused macro import "bar".'],
            ['{% from _self import foo as request %}{{ app.request.uri }}', 'Unused macro import "request".'],
            ['{% from _self import foo as macro %}{% macro foo() %}{% endmacro %}', 'Unused macro import "macro".'], // https://github.com/allocine/twigcs/issues/28
            ['{% import "macros.html.twig" as macros %} {{ macros.stuff() }}', null],

            // Complex include/embed spacing
            ['{% include "macros.html.twig"  ignore missing %}', 'Tag arguments should be separated by one space.'],
            ['{% include "macros.html.twig"  ignore missing only %}', 'Tag arguments should be separated by one space.'],
            ['{% include "macros.html.twig"  ignore missing with {foo: 1} only %}', 'Tag arguments should be separated by one space.'],
            ['{% embed "macros.html.twig"  ignore missing %}', 'Tag arguments should be separated by one space.'],
            ['{% embed "macros.html.twig"  ignore missing only %}', 'Tag arguments should be separated by one space.'],
            ['{% embed "macros.html.twig"  ignore missing with {foo: 1} only %}', 'Tag arguments should be separated by one space.'],

            ['{{ not  loop.first ? \',\' }}', 'There should be exactly one space between the "not" operator and its value.'],
            ['{{ label  ? label ~ \':\' }}', 'There should be exactly one space between each part of the ternary operator.'],

            ['{% use  "blocks.html" %}', 'Tag arguments should be separated by one space.'],
            ['{% use "blocks.html" with sidebar as base_sidebar, title as base_title  %}', 'A tag statement should start with one space and end with one space.'],
            ['{% use  "blocks.html" with sidebar as base_sidebar, title as base_title %}', 'Tag arguments should be separated by one space.'],
            ['{% use "blocks.html" with sidebar as base_sidebar,  title as base_title %}', 'There should be one space after the previous import.'],
            ['{% use "blocks.html" with sidebar as     base_sidebar, title as base_title %}', 'There should be one space between the as operator and its operands.'],

            // Complex encountered cases
            ['{% set baz = foo is defined ? object.property : default %}{{ baz }}', null],
            ['{{ a_is_b }}}', null], // Checks that it does not split on the "is" as an operator.

            // Nested hashes
            ['{{ {
                A: "",
                B: {
                    foo: {
                        A: 1
                    },
                    bar: baz
                },
            } }}', null],
            ['{{ {
                A: "",
                B:  {
                    foo: {
                        A: 1
                    },
                    bar: baz
                },
            } }}', 'There should be one space between ":" and the value.'],
            ['{{ {
                A: "",
                B: {
                    foo: {
                        A: 1
                    },
                    bar:   baz
                },
            } }}', 'There should be one space between ":" and the value.'],

            // Recursive macro
            ['{% macro recursion(item) %}
                {% from _self import recursion %}

                {% if item.parent %}
                    {% set a = recursion(item.parent) %}
                    {{ a }}
                {% endif %}
            {% endmacro %}
            ', null],

            ['{% set a = 1 %} {% set b = a %} {{ b }}', null],

            // Wrapped call for better readability
            ["\t<meta property=\"og:url\" content=\"{{ url(\n\t\tapp.request.attributes.get('_route'),\n\t\tapp.request.attributes.get('_route_params')\n\t) }}\">", null],

            // Spaces
            ["{{ foo }}    \n", 'A line should not end with blank space(s).'],
            ["{{ foo }}\t\n", 'A line should not end with blank space(s).'],
            ["{{ foo }}\r\n\r\n", null],

            // Check regression of https://github.com/allocine/twigcs/issues/23
            ['{% from _self import folder_breadcrumb %}', 'Unused macro import "folder_breadcrumb".'],

            // Check regression of https://github.com/allocine/twigcs/issues/56
            ["{% for item in ['one', 'two'] if attribute(_context, item) is not empty %}\n{% endfor %}", null],

            // Check regression of https://github.com/allocine/twigcs/issues/60
            ['{% set foo %}1{% endset %}{% include "foo.html.twig" with {foo: foo} %}', null],
            ['{% set foo %}1{% endset %}{% include "foo.html.twig" with {foo: foo} only %}', null],
            ['{% set bar %}1{% endset %}{% include "foo.html.twig" with {bar: foo} only %}', 'Unused variable "bar".'],
            ['{% include "foo.html.twig" %}', null],

            // Check regression of https://github.com/allocine/twigcs/issues/62
            ['{%~ if foo ~%}', null],
            ['{%~ if foo ~%}', null],
            ['{%~ if foo %}', null],
            ['{%- if foo -%}', null],
            ['{%- if foo %}', null],
            ['{%- if  foo ~%}', 'There should be one space between the if keyword and its condition.'],
            ['{{- foo ~}}', null],
            ['{{- foo -}}', null],
            ['{{- foo }}', null],
            ['{{ foo ~}}', null],
            ['{{ foo  ~}}', 'A print statement should start with one space and end with one space.'],

            // Check regression of https://github.com/allocine/twigcs/issues/63
            ["{% block title ('page.title.' ~ type)|trans %}", null],

            // Check regression of https://github.com/allocine/twigcs/issues/64
            ['{% set sliced = and foo|slice(-12, 12) %}{{ sliced }}', null],

            // Regressions from the Prestashop corpus
            ['{{ \'If not,[1][2] please click here[/1]!\'|trans({\'[1]\': \' <a href="\' ~ downloadFile.url ~ \'" class="btn btn-outline-primary btn-sm">\', \'[/1]\': \'</a> \', \'[2]\': \'<i class="icon-download"></i>\'}, \'Admin.Advparameters.Notification\')|raw }}', null],
        ];
    }
}
