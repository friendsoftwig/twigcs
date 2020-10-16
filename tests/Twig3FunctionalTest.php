<?php

namespace FriendsOfTwig\Twigcs\Test;

use FriendsOfTwig\Twigcs\Lexer;
use FriendsOfTwig\Twigcs\Rule\RegEngineRule;
use FriendsOfTwig\Twigcs\Ruleset\Official;
use FriendsOfTwig\Twigcs\TwigPort\Source;
use FriendsOfTwig\Twigcs\Validator\Validator;
use PHPUnit\Framework\TestCase;

/**
 * Twigcs' main functional tests.
 *
 * @author Tristan Maindron <tmaindron@gmail.com>
 */
class Twig3FunctionalTest extends TestCase
{
    /**
     * @dataProvider getData
     */
    public function testExpressions($expression, $expectedViolation, array $expectedViolationPosition = null)
    {
        $lexer = new Lexer();
        $validator = new Validator();

        $violations = $validator->validate(new Official(3), $lexer->tokenize(new Source($expression, 'src', 'src.html.twig')));
        $this->assertCount(0, $validator->getCollectedData()[RegEngineRule::class]['unrecognized_expressions'] ?? []);

        if ($expectedViolation) {
            $this->assertCount(1, $violations, sprintf("There should be one violation in:\n %s", $expression));
            $this->assertSame($expectedViolation, $violations[0]->getReason());
            if ($expectedViolationPosition) {
                $this->assertSame($expectedViolationPosition[0], $violations[0]->getColumn());
                $this->assertSame($expectedViolationPosition[1], $violations[0]->getLine());
            }
        } else {
            $this->assertCount(0, $violations, sprintf("There should be no violations in:\n %s", $expression));
        }
    }

    public function getData()
    {
        return [
            // Put one (and only one) space after the start of a delimiter and before the end of a delimiter.
            ['{{ foo }}', null],
            ['{{ foo   }}', 'A print statement should end with 1 space.'],
            ['{{    foo }}', 'A print statement should start with 1 space.'],
            ['{% block foo   %}', 'A tag statement should end with 1 space.'],
            ['{%    block foo %}', 'A tag statement should start with 1 space.'],

            // Do not put any spaces after an opening parentheses and before a closing parentheses in expressions.
            // Do not put any spaces before and after the parentheses used for filter and function calls.
            ['{{ foo(1) }}', null],
            ['{{ foo(1,  2) }}', 'The next value of a list should be separated by 1 space.'],
            ['{{ foo( ) }}', 'There should be 0 space inside empty parentheses.'],
            ['{{ foo( 1) }}', 'There should be 0 space between the opening parenthese and its content.'],
            ['{{ foo(1 ) }}', 'There should be 0 space between the closing parenthese and its content.'],
            ['{{ foo (1) }}', 'There should be 0 space between a function name and its opening parentheses.'],
            ['{{ (1) }}',     null],
            ['{{ ( 1) }}',    'There should be 0 space between the opening parenthese and its content.'],
            ['{{ (1 ) }}',    'There should be 0 space between the closing parenthese and its content.'],

            // parentheses spacing is not appliable to control structures.
            ['{% if (1 + 2) == 3 %}', null],
            ['{% for i in (some_array) %}{{ i }}', null],
            ['{% if (foo and (not bar or baz)) %}', null],
            ['{% for i in  (some_array) %}{{ i }}', 'There should be 1 space after the in operator.'],
            ['{% for  i in (some_array) %}{{ i }}', 'There should be 1 space between for and the local variables.'],
            ['{% for i  in (some_array) %}{{ i }}', 'There should be 1 space after the local variable.'],
            ['{% for i in 1..10 %}{{ i }}', null],
            ['{% for i in 1.. 10 %}{{ i }}', 'There should be 0 space between the ".." operator and its right operand.'],
            ['{% if  (1 + 2) == 3 %}', 'There should be 1 space between the if keyword and its condition.'],

            // Do not put any spaces before and after the following operators: |, ., .., [].
            ['{{ foo|baz }}', null],
            ['{{ foo[0] }}', null],
            ['{{ foo[0].bar }}', null],
            ['{{ foo[0]|bar }}', null],
            ['{{ foo |baz }}', 'There should be 0 space before the "|".'],
            ['{{ foo| baz }}', 'There should be 0 space after the "|".'],
            ['{{ foo() |baz }}', 'There should be 0 space before the "|".'],
            ['{{ foo() * 2 |baz }}', 'There should be 0 space before the "|".'],
            ['{{ foo.baz }}', null],
            ['{{ foo .baz }}', 'There should be 0 space before the ".".'],
            ['{{ foo. baz }}', 'There should be 0 space after the ".".'],
            ['{{ foo() .baz }}', 'There should be 0 space before the ".".'],

            // Put one (and only one) space after the : sign in hashes and , in arrays and hashes:
            ['{{ {foo: 1} }}', null],
            ['{{ {  foo: 1} }}', 'There should be 0 space before the hash values.'],
            ['{{ {foo: 1 } }}', 'There should be 0 space after the hash values.'],
            ['{{ {foo:  1} }}', 'There should be 1 space between ":" and the value.'],
            ['{{ {foo: 1,   bar: 2} }}', 'There should be 1 space between the , and the following hash key.'],
            ['{{ [1, 2, 3] }}', null],
            ["{{ [1,\n2] }}", null],
            ['{{ {hash: ","} }}', null],
            ['{{ [","] }}', null],
            ['{{ [func(1, 2)] }}', null],
            ['{{ [ ] }}', 'There should be 0 space inside an empty array.'],
            ['{{ [ 1, 2] }}', 'There should be 0 space before the array values.'],
            ['{{ [1, 2 ] }}', 'There should be 0 space after the array values.'],
            ["{{ [1\n, 2] }}", 'There should be 0 space before the ",".'],
            ['{{ [1, 2,3] }}', 'The next value of a list should be separated by 1 space.'],
            ['{{ [1, 2 , 3] }}', 'There should be 0 space before the ",".'],
            ['{{ [1, 2,     3] }}', 'The next value of a list should be separated by 1 space.'],
            ['{{ sliced_array[0:4] }}', null],
            ['{{ sliced_array[:4] }}', null],
            ['{{ sliced_array[0:] }}', null],
            ['{{ sliced_array[: 4] }}', 'There should be 0 space after the middle ":" of a slice.'],
            ['{{ sliced_array[0: 4] }}', 'There should be 0 space after the middle ":" of a slice.'],
            ['{{ sliced_array[0 :4] }}', 'There should be 0 space before the middle ":" of a slice.'],
            ['{{ sliced_array[0:4 ] }}', 'There should be 0 space right before the closing "]" of a slice.'],
            ['{{ sliced_array[ 0:4] }}', 'There should be 0 space right after the opening "[" of a slice.'],
            ['{{ slice[true ? 0 : 1 :0] }}', 'There should be 0 space before the middle ":" of a slice.'],

            // Put one (and only one) space before and after the following operators: comparison operators (==, !=, <, >, >=, <=), math operators (+, -, /, *, %, //, **), logic operators (not, and, or), ~, is, in, and the ternary operator (?:).
            ['{{ 1 + 2 }}', null],
            ['{{ 1+ 2 }}', 'There should be 1 space between the "+" operator and its left operand.'],
            ['{{ 1 +2 }}', 'There should be 1 space between the "+" operator and its right operand.'],
            ['{{ 1- 2 }}', 'There should be 1 space between the "-" operator and its left operand.'],
            ['{{ 1 -2 }}', 'There should be 1 space between the "-" operator and its right operand.'],
            ['{{ 1  + 2 }}', 'There should be 1 space between the "+" operator and its left operand.'],
            ['{{ 1 +  2 }}', 'There should be 1 space between the "+" operator and its right operand.'],
            ['{{ 1 ? "foo" : "bar" }}', null],
            ['{{ 1 ? "foo" : "bar" ? "baz" : "foobar" }}', null],
            ['{{ 1 ? "foo" : "bar" ? "baz" :"foobar" }}', 'There should be 1 space after the ":".'],
            ['{{ 1? "foo" : "bar" }}', 'There should be 1 space before the "?".'],
            ['{{ 1 ?"foo" : "bar" }}', 'There should be 1 space after the "?".'],
            ['{{ 1 ? "foo": "bar" }}', 'There should be 1 space before the ":".'],
            ['{{ 1 ? "foo" :"bar" }}', 'There should be 1 space after the ":".'],
            ['{{ 1 ?: "foo" }}', null],
            ['{{ 1 ?:  "foo" }}', 'There should be 1 space after the "?:".'],
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
            ['{% for foo in 1..10 %}{{ foo }}{% endfor %}', null],
            ['{% for foo_bar in 1..10 %}{{ foo_bar }}{% endfor %}', null],
            ['{% for fooBar in 1..10 %}{{ fooBar }}{% endfor %}', 'The "fooBar" variable should be in lower case (use _ as a separator).'],
            ['{% for foo, bar in 1..10 %}{{ foo }}: {{ bar }}{% endfor %}', null],
            ['{% for foo_key, bar_val in 1..10 %}{{ foo_key }}: {{ bar_val }}{% endfor %}', null],
            ['{% for fooKey, bar_val in 1..10 %}{{ fooKey }}: {{ bar_val }}{% endfor %}', 'The "fooKey" variable should be in lower case (use _ as a separator).'],
            ['{% for foo_key, barVal in 1..10 %}{{ foo_key }}: {{ barVal }}{% endfor %}', 'The "barVal" variable should be in lower case (use _ as a separator).'],

            // var declaration spacing
            ['{% set  foo = 1 %}{{ foo }}', 'There should be 1 space after the "set".'],
            ['{% set foo  = 1 %}{{ foo }}', 'There should be 1 space before the "=".'],
            ['{% set foo =  1 %}{{ foo }}', 'There should be 1 space after the "=".'],

            // Unused variables
            ['{% set foo = 1 %}', 'Unused variable "foo".'],
            ['{% set foo = 1 %}{{ {foo: 1} }}', 'Unused variable "foo".'],
            ['{% set foo = 1 %}{{ foo ? foo : 0 }}', null],
            ['{% set foo = 1 %}{% macro toto() %}{{ foo }}{% endmacro %}', 'Unused variable "foo".'], // https://github.com/friendsoftwig/twigcs/issues/27
            ['{% set foo = 1 %}{% if foo %}{% endif %}', null],
            ['{% set foo = 1 %}{% if bar %}{% elseif fooBar %}{% endif %}', 'Unused variable "foo".'],
            ['{% set foo = 1 %}{% if bar %}{% elseif foo %}{% endif %}', null],
            ['{% set foo = 1 %}{% if bar %}{% elseif fooBar %}{% elseif foo %}{% endif %}', null],
            ['{% set foo = [] %}{% for bar in foo %}{{ bar }}{% endfor %}', null],
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

            // import spacing
            ['{% import  "foo.html.twig" as foo %}{{ foo() }}', 'There should be 1 space before the source.'],
            ['{% import "foo.html.twig"  as foo %}{{ foo() }}', 'There should be 1 space after the source.'],
            ['{% import "foo.html.twig" as  foo %}{{ foo() }}', 'There should be 1 space after the "as".'],
            ['{% import "foo.html.twig" as foo,  bar %}{{ foo() }}{{ bar() }}', 'The next value of a list should be separated by 1 space.'],

            // from spacing
            ['{% from  _self import foo as bar %}{{ bar() }}', 'There should be 1 space before the source.'],
            ['{% from _self  import foo as bar %}{{ bar() }}', 'There should be 1 space after the source.'],
            ['{% from _self import  foo as bar %}{{ bar() }}', 'There should be 1 space before the imported names.'],
            ['{% from _self import foo  as bar %}{{ bar() }}', 'There should be 1 space before the "as".'],
            ['{% from _self import foo as  bar %}{{ bar() }}', 'There should be 1 space after the "as".'],

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
            ['{% from _self import foo as macro %}{% macro foo() %}{% endmacro %}', 'Unused macro import "macro".'], // https://github.com/friendsoftwig/twigcs/issues/28
            ['{% import "macros.html.twig" as macros %} {{ macros.stuff() }}', null],

            // Complex include/embed spacing
            ['{% include "macros.html.twig"  ignore missing %}', 'There should be 1 space before the "ignore missing".'],
            ['{% include "macros.html.twig"  ignore missing only %}', 'There should be 1 space before the "ignore missing".'],
            ['{% include "macros.html.twig"  ignore missing with {foo: 1} only %}', 'There should be 1 space before the "ignore missing".'],
            ['{% embed "macros.html.twig"  ignore missing %}', 'There should be 1 space before the "ignore missing".'],
            ['{% embed "macros.html.twig"  ignore missing only %}', 'There should be 1 space before the "ignore missing".'],
            ['{% embed "macros.html.twig"  ignore missing with {foo: 1} only %}', 'There should be 1 space before the "ignore missing".'],

            ['{{ not  loop.first ? \',\' }}', 'There should be 1 space between the "not" operator and its value.'],
            ['{{ label  ? label ~ \':\' }}', 'There should be 1 space before the "?".'],

            ['{% use  "blocks.html" %}', 'Tag arguments should be separated by 1 space.'],
            ['{% use "blocks.html" with sidebar as base_sidebar, title as base_title  %}', 'A tag statement should end with 1 space.'],
            ['{% use  "blocks.html" with sidebar as base_sidebar, title as base_title %}', 'Tag arguments should be separated by 1 space.'],
            ['{% use "blocks.html" with sidebar as base_sidebar,  title as base_title %}', 'There should be 1 space after the previous import.'],
            ['{% use "blocks.html" with sidebar as     base_sidebar, title as base_title %}', 'There should be 1 space between the as operator and its operands.'],

            // macros
            ['{% macro toto(a, b, c) %}{% endmacro %}', null],
            ['{% macro toto   (a, b, c) %}{% endmacro %}', 'There should be 0 space between macro name and args.'],
            ['{% macro    toto(a, b, c) %}{% endmacro %}', 'There should be 1 space between macro keyword and its name.'],
            ['{% macro toto( a, b, c) %}{% endmacro %}', 'There should be 0 space between the opening parenthese and its content.'],
            ['{% macro toto(a,  b, c) %}{% endmacro %}', 'The next value of a list should be separated by 1 space.'],
            ['{% macro toto(a, b , c) %}{% endmacro %}', 'There should be 0 space before the ",".'],
            ['{% macro toto(a, b, c ) %}{% endmacro %}', 'There should be 0 space between the closing parenthese and its content.'],
            ['{% macro toto(a, b,  c) %}{% endmacro %}', 'The next value of a list should be separated by 1 space.'],

            // Complex encountered cases
            ['{% set baz = foo is defined ? object.property : default %}{{ baz }}', null],
            ['{{ a_is_b }}}', null], // Checks that it does not split on the "is" as an operator.

            // Arrow functions
            ['{% for a in b|filter(c => c > 10) %}{{ a }}', null],
            ['{% for a in b|filter(c  => c > 10) %}{{ a }}', 'There should be 1 space between the arrow and its arguments.'],
            ['{% for a in b|filter(c =>  c > 10) %}{{ a }}', 'There should be 1 space between the arrow and its body.'],
            ['{% for a in b|filter((c ) => c > 10) %}{{ a }}', 'There should be 0 space between the closing parenthese and its content.'],
            ['{% for a in b|filter(c => c >  10) %}{{ a }}', 'There should be 1 space between the ">" operator and its right operand.'],

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
            } }}', 'There should be 1 space between ":" and the value.'],
            ['{{ {
                A: "",
                B: {
                    foo: {
                        A: 1
                    },
                    bar:   baz
                },
            } }}', 'There should be 1 space between ":" and the value.'],

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
            ["{{ foo }}\r\n\r\n", null],
            ["{{ foo }}    \n", 'A line should not end with blank space(s).', [13, 1]],
            ["{{ foo }}\t\n", 'A line should not end with blank space(s).', [10, 1]],
            ["str\nstr    \nstr", 'A line should not end with blank space(s).', [7, 2]],
            ["{{ 1 }}str\nstr    \nstr", 'A line should not end with blank space(s).', [7, 2]],
            ['{{ foo }}', null],

            // Check regression of https://github.com/friendsoftwig/twigcs/issues/23
            ['{% from _self import folder_breadcrumb %}', 'Unused macro import "folder_breadcrumb".'],

            // Check regression of https://github.com/friendsoftwig/twigcs/issues/56

            // Check regression of https://github.com/friendsoftwig/twigcs/issues/60
            ['{% set foo %}1{% endset %}{% include "foo.html.twig" with {foo: foo} %}', null],
            ['{% set foo %}1{% endset %}{% include "foo.html.twig" with {foo: foo} only %}', null],
            ['{% set bar %}1{% endset %}{% include "foo.html.twig" with {bar: foo} only %}', 'Unused variable "bar".'],
            ['{% include "foo.html.twig" %}', null],

            // Check unused for embed
            ['{% set foo %}1{% endset %}{% embed "foo.html.twig" with {foo: foo} only %}{% endembed %}', null],
            ['{% set bar %}1{% endset %}{% embed "foo.html.twig" with {bar: foo} only %}{% endembed %}', 'Unused variable "bar".'],

            // Check for columns and lines numbers
            ['        {%- set vars = widget == "text" ? {"attr": {"size": 1 }} : {} -%}{{ vars }}', 'There should be 0 space after the hash values.', [61, 1]],
            ['{{ {foo: {bar: {baz: {foo: {bar: 1 }}}}} }}', 'There should be 0 space after the hash values.', [34, 1]],
            ['{{ {foo: {bar: 1}}|agg + {baz: 2 }|agg }}', 'There should be 0 space after the hash values.', [32, 1]],

            // Check regression of https://github.com/friendsoftwig/twigcs/issues/62
            ['{%~ if foo ~%}', null],
            ['{%~ if foo ~%}', null],
            ['{%~ if foo %}', null],
            ['{%- if foo -%}', null],
            ['{%- if foo %}', null],
            ['{%- if  foo ~%}', 'There should be 1 space between the if keyword and its condition.'],
            ['{{- foo ~}}', null],
            ['{{- foo -}}', null],
            ['{{- foo }}', null],
            ['{{ foo ~}}', null],
            ['{{ foo  ~}}', 'A print statement should end with 1 space.'],

            // Check regression of https://github.com/friendsoftwig/twigcs/issues/63
            ["{% block title ('page.title.' ~ type)|trans %}", null],

            // Check regression of https://github.com/friendsoftwig/twigcs/issues/64
            ['{% set sliced = foo|slice(-12, 12) %}{{ sliced }}', null],

            // Check regression of https://github.com/friendsoftwig/twigcs/issues/77
            ['{% set foo = bar ?? [] %}{{ foo }}', null],
            ['{% set foo = baz ?? (bar ?? []) %}{{ foo }}', null],

            // Check regression of https://github.com/friendsoftwig/twigcs/issues/78
            ['{% if not (foo and bar) %}', null],
            ['{% if not same as (foo and bar) %}', null],

            // Check regression of https://github.com/friendsoftwig/twigcs/issues/105
            ['{% block block_name \'some-text\' ~ (not a_function() ? \' other other-text-2\') %}', null],

            // Check regression of https://github.com/friendsoftwig/twigcs/issues/50
            ['{% set foo = false %}{% for i in 0..2 %}{{ i }}{% set foo = true %}...{% endfor %}{% if foo %}...{% endif %}', null],
            ['{% set foo = false %}{% for i in 0..2 %}{% set foo = true %}...{% endfor %}{% if foo %}...{% endif %}', 'Unused variable "i".'],
            ['{% set foo = false %}{% if foo %}...{% endif %}{% for i in 0..2 %}{{ i }}{% set foo = true %}...{% endfor %}', 'Unused variable "foo".'],

            // Regressions from the official examples
            ['{% if \'Fabien\' starts with \'F\' %}', null],
            ['{% if \'Fabien\' ends with \'F\' %}', null],
            ['{% if \'Fabien\' matches \'regex\' %}', null],

            // Regressions from the Prestashop corpus
            ['{{ \'If not,[1][2] please click here[/1]!\'|trans({\'[1]\': \' <a href="\' ~ downloadFile.url ~ \'" class="btn btn-outline-primary btn-sm">\', \'[/1]\': \'</a> \', \'[2]\': \'<i class="icon-download"></i>\'}, \'Admin.Advparameters.Notification\')|raw }}', null],
            ['{% set dimension_unit, weight_unit = \'PS_DIMENSION_UNIT\'|configuration, \'PS_WEIGHT_UNIT\'|configuration %}{{ dimension_unit }}', null],
            ['{{ collector.symfonystate in [\'eom\', \'eol\'] ? \'were\' : \'are\' }}', null],
            ['{% if limit not in limit_choices %}', null],
            ['{{ [] }}', null],
            ['{% set bar %}1{% endset %}{% include "foo.html.twig" with {foo: bar} only %}', null],
            ['{% if (quotation.data[field]|default(null) is same as(0) or quotation.data[field]|default(false)) %}', null],
            ['{{ ps.form_group_row(taxForm.rate, {}, {
                \'label\': \'Rate\'|trans({}, \'Admin.International.Feature\'),
                \'help\': rateHint,
            }) }}', null],
            ['{% set columns = [
                1,
                2,
                3,
            ] %}{{ columns }}', null],
            ['{% set foo = {a: 1 , b: 2} %}{{ foo }}', 'There should be 0 space between the value and the following ",".'],
        ];
    }
}
