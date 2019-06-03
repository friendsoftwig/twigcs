<?php

namespace Allocine\Twigcs\RegEngine;

use Allocine\Twigcs\RegEngine\Checker\Handler;

class DefaultRuleset
{
    const OP_VARS = [
        ' ' => '\s*',
        '$' => '(?:.|\n|\r)+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
    ];

    const TAGS_VARS = [
        ' ' => '\s+',
        '_' => '\s*',
        '…' => '\s*',
        '$' => '(?:.|\n|\r)+?',
        '&' => '(?:.|\n|\r)+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
        '<' => '{%[~-]?',
        '>' => '[~-]?%}',
        '{' => '{{[~-]?',
        '}' => '[~-]?}}',
    ];

    const LIST_VARS = [
        ' ' => '\s*',
        '_' => '\s*',
        '$' => '(?:.|\n|\r)+?',
        '%' => '(?:.|\n|\r)+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
    ];

    const SLICE_VARS = [
        ' ' => '\s*',
        '$' => '(?:[^\?]|\n|\r)+?', // Excludes ternary from slice detection
    ];

    public static function handle()
    {
        return Handler::create();
    }

    public static function using($vars, array $rules)
    {
        return array_map(function ($rule) use ($vars) {
            array_unshift($rule, $vars);

            return $rule;
        }, $rules);
    }

    public static function unaryOpSpace($opName)
    {
        return self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, sprintf('There should be exactly one space between the "%s" operator and its value.', $opName));
    }

    public static function binaryOpSpace($opName)
    {
        return self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, sprintf('There should be exactly one space between the "%s" operator and its values.', $opName));
    }

    public static function ternaryOpSpace()
    {
        return self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'There should be exactly one space between each part of the ternary operator.');
    }

    public static function noArgTag()
    {
        return self::handle()->enforceSize('…', 1, 'A tag statement should start with one space and end with one space.');
    }

    public static function argTag()
    {
        return self::handle()
            ->enforceSize(' ', 1, 'Tag arguments should be separated by one space.')
            ->enforceSize('…', 1, 'A tag statement should start with one space and end with one space.')
        ;
    }

    public static function slice()
    {
        return self::handle()
            ->delegate('$', 'expr')
            ->enforceSize(' ', 0, 'There should be no space inside an array slice short notation.')
        ;
    }

    public static function get()
    {
        $expr = [];

        $tags = self::using(self::TAGS_VARS, [
            ['<…use $ with $…>', self::argTag()->delegate('$', 'expr')],
            ['<…use $…>', self::argTag()->delegate('$', 'expr')],
            ['<…apply $…>', self::argTag()->delegate('$', 'expr')],
            ['<…endapply…>', self::noArgTag()],
            ['<…autoescape $…>', self::argTag()->delegate('$', 'expr')],
            ['<…endautoescape…>', self::noArgTag()],
            ['<…deprecated $…>', self::argTag()->delegate('$', 'expr')],
            ['<…do $…>', self::argTag()->delegate('$', 'expr')],
            ['<…flush…>', self::noArgTag()],
            ['<…sandbox…>', self::noArgTag()],
            ['<…endsandbox…>', self::noArgTag()],
            ['<…verbatim…>', self::noArgTag()],
            ['<…endverbatim…>', self::noArgTag()],
            ['<…with…>', self::noArgTag()],
            ['<…with only…>', self::noArgTag()],
            ['<…with $…>', self::argTag()->delegate('$', 'expr')],
            ['<…with $ only…>', self::argTag()->delegate('$', 'expr')],
            ['<…endwith…>', self::noArgTag()],
            ['<…spaceless…>', self::noArgTag()],
            ['<…endspaceless…>', self::noArgTag()],
            ['<…extends $…>', self::argTag()->delegate('$', 'expr')],
            ['<…embed $ ignore missing with & only…>', self::argTag()->delegate('$', 'expr')->delegate('&', 'hash')],
            ['<…embed $ ignore missing only…>', self::argTag()->delegate('$', 'expr')],
            ['<…embed $ ignore missing…>', self::argTag()->delegate('$', 'expr')],
            ['<…embed $ with & only…>', self::argTag()->delegate('$', 'expr')->delegate('&', 'hash')],
            ['<…embed $ with &…>', self::argTag()->delegate('$', 'expr')->delegate('&', 'hash')],
            ['<…embed $ only…>', self::argTag()->delegate('$', 'expr')],
            ['<…embed $…>', self::argTag()->delegate('$', 'expr')],
            ['<…elseif $…>', self::argTag()->delegate('$', 'expr')],
            ['<…else…>', self::noArgTag()],
            ['<…include $ ignore missing with & only…>', self::argTag()->delegate('$', 'expr')->delegate('&', 'hash')],
            ['<…include $ ignore missing only…>', self::argTag()->delegate('$', 'expr')],
            ['<…include $ ignore missing…>', self::argTag()->delegate('$', 'expr')],
            ['<…include $ with & only…>', self::argTag()->delegate('$', 'expr')->delegate('&', 'hash')],
            ['<…include $ with &…>', self::argTag()->delegate('$', 'expr')->delegate('&', 'hash')],
            ['<…include $ only…>', self::argTag()->delegate('$', 'expr')],
            ['<…include $…>', self::argTag()->delegate('$', 'expr')],
            ['<…set @…>', self::argTag()],
            ['<…endset…>', self::noArgTag()],
            ['<…macro @_@…>', self::handle()->enforceSize('…', 1, 'A tag statement should start with one space and end with one space.')->enforceSize('_', 0, 'No space between macro name and args.')],
            ['<…endmacro…>', self::noArgTag()],
            ['<…block @…>', self::argTag()],
            ['<…block @ $…>', self::argTag()->delegate('$', 'expr')],
            ['<…endblock…>', self::noArgTag()],
            ['<…filter @…>', self::argTag()],
            ['<…endfilter…>', self::noArgTag()],
            ['<…import $ as &…>', self::argTag()->delegate('$', 'expr')->delegate('&', 'list')],
            ['<…from $ import @…>', self::argTag()->delegate('$', 'expr')],
            ['<…from $ import @ as &…>', self::argTag()->delegate('$', 'expr')->delegate('&', 'list')],
            ['<…from $ import &…>', self::argTag()->delegate('$', 'expr')->delegate('&', 'list')],
            ['{…$…}', self::handle()->delegate('$', 'expr')->enforceSize('…', 1, 'A print statement should start with one space and end with one space.')],
            ['<…if $…>', self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'There should be one space between the if keyword and its condition.')],
            ['<…endif…>', self::noArgTag()],
            ['<…endfor…>', self::noArgTag()],
            ['<…for @, @ in $…>', self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'There should be one space between each for part.')],
            ['<…for @, @ in $ if $…>', self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'There should be one space between each for part.')],
            ['<…for @ in $…>', self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'There should be one space between each for part.')],
            ['<…for @ in $ if $…>', self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'There should be one space between each for part.')],
            ['<…set @ = $…>', self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'There should be one space between each part of the set.')],
            ['<…@…>', self::noArgTag()],
            ['<…@ &…>', self::argTag()->delegate('&', 'list')],
        ]);

        $ops = self::using(self::OP_VARS, [
            ['@ __PARENTHESES__', self::handle()->enforceSize(' ', 0, 'There should be no space between a function name and its opening parentheses.')],
            ['\( \)', self::handle()->enforceSize(' ', 0, 'No space should be used inside empty parentheses.')],
            ["\(\n $\n \)", self::handle()->delegate('$', 'list')], // Multiline function call
            ['\( $ \)', self::handle()->delegate('$', 'list')->enforceSize(' ', 0, 'There should be no space between parentheses and their content.')],
            ['\[ \]', self::handle()->enforceSize(' ', 0, 'No space should be used for empty arrays.')],
            ['\[ $ \]', self::handle()->delegate('$', 'list')->enforceSize(' ', 0, 'There should be no space before and after the array values.')],
            ['\{ \}', self::handle()->enforceSize(' ', 0, 'No space should be used for empty hashes.')],
            ["\{\n $ \n\}", self::handle()->delegate('$', 'hash')],
            ["\{ $ \}", self::handle()->delegate('$', 'hash')->enforceSize(' ', 0, 'There should be no space before and after the hash values.')],
            ['$ <= $', self::binaryOpSpace('<=')],
            ['$ >= $', self::binaryOpSpace('>=')],
            ['$ < $', self::binaryOpSpace('<')],
            ['$ > $', self::binaryOpSpace('>')],
            ['$ != $', self::binaryOpSpace('!=')],
            ['$ == $', self::binaryOpSpace('==')],
            ['$ __TERNARY__ $', self::ternaryOpSpace()],
            ['$ \?: $', self::ternaryOpSpace()],
            ['$ \? $', self::ternaryOpSpace()],
            ['\? $ \:', self::ternaryOpSpace()],
            ['$ \+ $', self::binaryOpSpace('+')],
            ['$ - $', self::binaryOpSpace('-')],
            ['$ ~ $', self::binaryOpSpace('~')],
            ['$ \* $', self::binaryOpSpace('*')],
            ['$ / $', self::binaryOpSpace('/')],
            ['$ // $', self::binaryOpSpace('//')],
            ['$ % $', self::binaryOpSpace('%')],
            ['$ \*\* $', self::binaryOpSpace('**')],
            ['$ is $', self::binaryOpSpace('is')],
            ['$ \?\? $', self::binaryOpSpace('??')],
            ['$ \.\. $', self::binaryOpSpace('..')],
            ['not $', self::unaryOpSpace('not')],
            ['$ \| $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'There should be no space before and after filters.')],
            ['$ \. $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'There should be no space before and after the dot when accessing a property.')],
        ]);

        $list = self::using(self::LIST_VARS, [
            [' ', Handler::create()->enforceSize(' ', 0, 'Empty list should have no whitespace')],
            ['$_, %', Handler::create()->delegate('$', 'expr')->delegate('%', 'list')->enforceSize('_', 0, 'A list value should be immediately followed by a coma.')->enforceSpaceOrLineBreak(' ', 1, 'The next value of a list should be separated by one space.')],
            ['$_, %', Handler::create()->delegate('$', 'expr')->delegate('%', 'list')->enforceSize('_', 0, 'A list value should be immediately followed by a coma.')->enforceSpaceOrLineBreak(' ', 1, 'The next value of a list should be separated by one space.')],
            [' @ ', Handler::create()->enforceSize(' ', 0, 'A single valued list should have no inner whitespace.')],
            [' $ ', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'A single valued list should have no inner whitespace.')],
        ]);

        $hash = self::using(self::LIST_VARS, [
            [' ', Handler::create()->enforceSize(' ', 0, 'Empty hash should have no whitespace')],
            ['@ :_$ ,_%', Handler::create()->delegate('$', 'expr')->delegate('%', 'hash')->enforceSize(' ', 0, 'There should be no space between the key and ":".')->enforceSize('_', 1, 'There should be one space between ":" and the value.')],
            ['"@" :_$ ,_%', Handler::create()->delegate('$', 'expr')->delegate('%', 'hash')->enforceSize(' ', 0, 'There should be no space between the key and ":".')->enforceSize('_', 1, 'There should be one space between ":" and the value.')],
            ['@ :_$', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'There should be no space between the key and ":".')->enforceSize('_', 1, 'There should be one space between ":" and the value.')],
            ['"@" :_$', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'There should be no space between the key and ":".')->enforceSize('_', 1, 'There should be one space between ":" and the value.')],
        ]);

        $slice = self::using(self::SLICE_VARS, [
            ['\[ : $ \]', self::slice()],
            ['\[ $ : \]', self::slice()],
            ['\[ $ : $ \]', self::slice()],
        ]);

        $array = self::using(self::OP_VARS, [
            ['\[ $ \]', Handler::create()->delegate('$', 'list')], // Redirects to array checking
        ]);

        return [
            'expr' => array_merge($tags, $ops),
            'list' => $list,
            'hash' => $hash,
            'arrayOrSlice' => array_merge($slice, $array),
        ];
    }
}
