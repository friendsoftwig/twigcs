<?php

namespace Allocine\Twigcs\Experimental;

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

    public static function get()
    {
        $expr = [];

        /*
         * TODO:
         *
         * Extracted nodes can switch from expr in the rulechecker.
         * Test that tricky expressions with () {} and [] are well sorted.
         */

        $binaryOpHandler = Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'There should be exactly one space between binary operator and its operand.');

        $expr[] = [self::BLOCK_VARS, '{%~spaceless~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~endspaceless~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~extends $~%}', Handler::create()->delegate('$', 'expr')->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~embed $~%}', Handler::create()->delegate('$', 'expr')->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~endembed~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~elseif $~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~else~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~include $ with &~%}', Handler::create()->delegate('$', 'expr')->delegate('&', 'hash')->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~include $ with & only~%}', Handler::create()->delegate('$', 'expr')->delegate('&', 'hash')->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~include $~%}', Handler::create()->delegate('$', 'expr')->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~set @~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~endset~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~macro @_@~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')->enforceSize('_', 0, 'No space between macro name and args.')];
        //$expr[] = [self::BLOCK_VARS, '{%~macro @_\(_$_\)~%}', Handler::create()->delegate('$', 'list')->enforceSize('~', 1, 'More than one space used')->enforceSize('_', 0, 'No space here')];
        $expr[] = [self::BLOCK_VARS, '{%~endmacro~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~block @~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~block @ $~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~endblock~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~filter @~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~endfilter~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~import $ as &~%}', Handler::create()->delegate('$', 'expr')->delegate('&', 'list')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~from $ import @~%}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~from $ import @ as &~%}', Handler::create()->delegate('$', 'expr')->delegate('&', 'list')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~from $ import &~%}', Handler::create()->delegate('$', 'expr')->delegate('&', 'list')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{{~$~}}', Handler::create()->delegate('$', 'expr')->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~if \(_$_\)~%}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~if $~%}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~endif~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~endfor~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~for @, @ in $~%}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~for @, @ in $ if $~%}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~for @ in $~%}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~for @ in $ if $~%}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~set @ = $~%}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~@~%}', Handler::create()->enforceSize('~', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{%~@ &~%}', Handler::create()->delegate('&', 'list')->enforceSize('~', 1, 'More than one space used')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::OP_VARS, '\( \)', Handler::create()->enforceSize(' ', 0, 'No space should be used inside function call with no argument.')];
        $expr[] = [self::OP_VARS, '\( $ \)', Handler::create()->delegate('$', 'list')->enforceSize(' ', 0, 'No space should be used')];
        $expr[] = [self::OP_VARS, '@ \( \)', Handler::create()->enforceSize(' ', 0, 'No space should be used inside function call with no argument.')];
        $expr[] = [self::OP_VARS, '@ \( $ \)', Handler::create()->delegate('$', 'list')->enforceSpaceOrLineBreak(' ', 0, 'No space should be used')];
        $expr[] = [self::OP_VARS, '\[ \]', Handler::create()->enforceSize(' ', 0, 'No space should be used for empty arrays.')];
        $expr[] = [self::OP_VARS, '\[ $ \]', Handler::create()->delegate('$', 'list')->enforceSize(' ', 0, 'No space should be used')];
        $expr[] = [self::OP_VARS, '\{ \}', Handler::create()->enforceSize(' ', 0, 'No space should be used for empty hashes.')];
        $expr[] = [self::OP_VARS, "\{\n $ \n\}", Handler::create()->delegate('$', 'hash')]; // @todo
        $expr[] = [self::OP_VARS, "\{ $ \}", Handler::create()->delegate('$', 'hash')->enforceSize(' ', 0, 'No space should be used')];
        $expr[] = [self::OP_VARS, '$ <= $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ >= $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ < $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ > $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ != $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ == $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ \? $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ \?: $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ \? $ \: $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ \+ $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ - $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ ~ $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ \* $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ / $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ // $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ % $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ \*\* $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ is $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ \?\? $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ \.\. $', $binaryOpHandler];

        // Unary operators
        $expr[] = [self::OP_VARS, 'not $', $binaryOpHandler];
        // filters
        $expr[] = [self::OP_VARS, '$ \| $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'No space allowed before and after filters')];
        // slices
        $expr[] = [self::OP_VARS, '$ \[ : $ \]', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'No space allowed on slices')];
        $expr[] = [self::OP_VARS, '$ \[ $ : \]', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'No space allowed on slices')];
        $expr[] = [self::OP_VARS, '$ \[ $ : $ \]', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'No space allowed on slices')];
        // Array access
        $expr[] = [self::OP_VARS, '$ \[ $ \]', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'No space allowed on slices')];

        // property
        $expr[] = [self::OP_VARS, '$ \. $', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'No space allowed on properties')];
        // var
        $expr[] = [self::OP_VARS, '@', Handler::create()->attach(function () {
            // noop
        })];
        $expr[] = [self::OP_VARS, '"@"', Handler::create()->attach(function () {
            // noop
        })];


        $list = [];
        $list[] = [self::LIST_VARS, ' ', Handler::create()->enforceSize(' ', 0, 'Empty list should have no whitespace')];
        $list[] = [self::LIST_VARS, '$_, %', Handler::create()->delegate('$', 'expr')->delegate('%', 'list')->enforceSize('_', 0, 'Empty list should have no whitespace')->enforceSpaceOrLineBreak(' ', 1, 'Requires a space for the following list value.')];
        $list[] = [self::LIST_VARS, '$_, %', Handler::create()->delegate('$', 'expr')->delegate('%', 'list')->enforceSize('_', 0, 'Empty list should have no whitespace')->enforceSpaceOrLineBreak(' ', 1, 'Requires a space for the following list value.')];
        $list[] = [self::LIST_VARS, ' @ ', Handler::create()->enforceSize(' ', 0, 'Empty list should have no whitespace')];
        $list[] = [self::LIST_VARS, ' $ ', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'Empty list should have no whitespace')];

        $hash = [];
        $hash[] = [self::LIST_VARS, ' ', Handler::create()->enforceSize(' ', 0, 'Empty hash should have no whitespace')];
        $hash[] = [self::LIST_VARS, '@ :_$ ,_%', Handler::create()->delegate('$', 'expr')->delegate('%', 'hash')->enforceSize(' ', 0, 'No space should be used')->enforceSize('_', 1, 'One space should be used')];
        $hash[] = [self::LIST_VARS, '"@" :_$ ,_%', Handler::create()->delegate('$', 'expr')->delegate('%', 'hash')->enforceSize(' ', 0, 'No space should be used')->enforceSize('_', 1, 'One space should be used')];
        $hash[] = [self::LIST_VARS, '@ :_$', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'No space should be used')->enforceSize('_', 1, 'One space should be used')];
        $hash[] = [self::LIST_VARS, '"@" :_$', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 0, 'No space should be used')->enforceSize('_', 1, 'One space should be used')];

        return [
            'expr' => $expr,
            'list' => $list,
            'hash' => $hash,
        ];
    }
}
