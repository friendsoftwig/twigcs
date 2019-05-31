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

    const BLOCK_VARS = [
        ' ' => '\s+',
        '_' => '\s*',
        '~' => '\s*',
        '$' => '(?:.|\n|\r)+?',
        '&' => '(?:.|\n|\r)+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
    ];

    const LIST_VARS = [
        ' ' => '\s*',
        '_' => '\s*',
        '$' => '(?:.|\n|\r)+?',
        '%' => '(?:.|\n|\r)+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
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

    public static function noArgBlock()
    {
        return self::handle()->enforceSize('~', 1, 'A block statement should start with one space and end with one space.');
    }

    public static function argBlock()
    {
        return self::handle()
            ->enforceSize(' ', 1, 'Block arguments should be separated by one space.')
            ->enforceSize('~', 1, 'A block statement should start with one space and end with one space.')
        ;
    }

    public static function slice()
    {
        return self::handle()
            ->delegate('$', 'expr')
            ->enforceSize(' ', 0, 'There should be no space inside slices.')
        ;
    }

    public static function get()
    {
        $expr = [];

        $blocks = self::using(self::BLOCK_VARS, [
            ['{%~spaceless~%}', self::noArgBlock()],
            ['{%~endspaceless~%}', self::noArgBlock()],
            ['{%~extends $~%}', self::argBlock()->delegate('$', 'expr')],
            ['{%~embed $~%}', self::argBlock()->delegate('$', 'expr')],
            ['{%~endembed~%}', self::noArgBlock()],
            ['{%~elseif $~%}', self::argBlock()->delegate('$', 'expr')],
            ['{%~else~%}', self::noArgBlock()],
            ['{%~include $ with &~%}', self::argBlock()->delegate('$', 'expr')->delegate('&', 'hash')],
            ['{%~include $ with & only~%}', self::argBlock()->delegate('$', 'expr')->delegate('&', 'hash')],
            ['{%~include $~%}', self::argBlock()->delegate('$', 'expr')],
            ['{%~set @~%}', self::argBlock()],
            ['{%~endset~%}', self::noArgBlock()],
            ['{%~macro @_@~%}', self::handle()->enforceSize('~', 1, 'A block statement should start with one space and end with one space.')->enforceSize('_', 0, 'No space between macro name and args.')],
            ['{%~endmacro~%}', self::noArgBlock()],
            ['{%~block @~%}', self::argBlock()],
            ['{%~block @ $~%}', self::argBlock()->delegate('$', 'expr')],
            ['{%~endblock~%}', self::noArgBlock()],
            ['{%~filter @~%}', self::argBlock()],
            ['{%~endfilter~%}', self::noArgBlock()],
            ['{%~import $ as &~%}', self::argBlock()->delegate('$', 'expr')->delegate('&', 'list')],
            ['{%~from $ import @~%}', self::argBlock()->delegate('$', 'expr')],
            ['{%~from $ import @ as &~%}', self::argBlock()->delegate('$', 'expr')->delegate('&', 'list')],
            ['{%~from $ import &~%}', self::argBlock()->delegate('$', 'expr')->delegate('&', 'list')],
            ['{{~$~}}', self::handle()->delegate('$', 'expr')->enforceSize('~', 1, 'A print statement should start with one space and end with one space.')],
            ['{%~if $~%}', self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')],
            ['{%~endif~%}', self::noArgBlock()],
            ['{%~endfor~%}', self::noArgBlock()],
            ['{%~for @, @ in $~%}', self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')],
            ['{%~for @, @ in $ if $~%}', self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')],
            ['{%~for @ in $~%}', self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')],
            ['{%~for @ in $ if $~%}', self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')],
            ['{%~set @ = $~%}', self::handle()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')],
            ['{%~@~%}', self::handle()->enforceSize('~', 1, 'More than one space used')],
            ['{%~@ &~%}', self::handle()->delegate('&', 'list')->enforceSize('~', 1, 'More than one space used')->enforceSize(' ', 1, 'More than one space used')],
        ]);

        $ops = self::using(self::OP_VARS, [
            ['\( \)', self::handle()->enforceSize(' ', 0, 'No space should be used inside function call with no argument.')],
            ['\( $ \)', self::handle()->delegate('$', 'list')->enforceSize(' ', 0, 'There should be no space before and after the function argument list.')],
            ['@ \( \)', self::handle()->enforceSize(' ', 0, 'No space should be used inside function call with no argument.')],
            ['@ \( $ \)', self::handle()->delegate('$', 'list')->enforceSpaceOrLineBreak(' ', 0, 'There should be no space before and after the function argument list.')],
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
            ['$ \? $ \: $', self::ternaryOpSpace()],
            ['$ \?: $', self::ternaryOpSpace()],
            ['$ \? $', self::ternaryOpSpace()],
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
            ['$ \[ : $ \]', self::slice()],
            ['$ \[ $ : \]', self::slice()],
            ['$ \[ $ : $ \]', self::slice()],
            ['$ \[ $ \]', self::slice()],
            ['$ \. $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'There should be no space before and after the dot when accessing a property.')],
        ]);

        $list = self::using(self::LIST_VARS, [
            [' ', Handler::create()->enforceSize(' ', 0, 'Empty list should have no whitespace')],
            ['$_, %', Handler::create()->delegate('$', 'expr')->delegate('%', 'list')->enforceSize('_', 0, 'Empty list should have no whitespace')->enforceSpaceOrLineBreak(' ', 1, 'Requires a space for the following list value.')],
            ['$_, %', Handler::create()->delegate('$', 'expr')->delegate('%', 'list')->enforceSize('_', 0, 'Empty list should have no whitespace')->enforceSpaceOrLineBreak(' ', 1, 'Requires a space for the following list value.')],
            [' @ ', Handler::create()->enforceSize(' ', 0, 'Empty list should have no whitespace')],
            [' $ ', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'Empty list should have no whitespace')],
        ]);

        $hash = self::using(self::LIST_VARS, [
            [' ', Handler::create()->enforceSize(' ', 0, 'Empty hash should have no whitespace')],
            ['@ :_$ ,_%', Handler::create()->delegate('$', 'expr')->delegate('%', 'hash')->enforceSize(' ', 0, 'No space should be used')->enforceSize('_', 1, 'One space should be used')],
            ['"@" :_$ ,_%', Handler::create()->delegate('$', 'expr')->delegate('%', 'hash')->enforceSize(' ', 0, 'No space should be used')->enforceSize('_', 1, 'One space should be used')],
            ['@ :_$', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'No space should be used')->enforceSize('_', 1, 'One space should be used')],
            ['"@" :_$', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'No space should be used')->enforceSize('_', 1, 'One space should be used')],
        ]);

        return [
            'expr' => array_merge($blocks, $ops),
            'list' => $list,
            'hash' => $hash,
        ];
    }
}
