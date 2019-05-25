<?php

namespace Allocine\Twigcs\Experimental;

class DefaultRuleset
{
    const OP_VARS = [
        ' ' => ' *',
        '$' => '.+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
    ];

    const BLOCK_VARS = [
        ' ' => ' +',
        '_' => ' *',
        '$' => '.+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
    ];

    const LIST_VARS = [
        ' ' => ' *',
        '_' => ' *',
        '$' => '.+?',
        '%' => '.+?',
        '@' => '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*',
    ];

    public static function get()
    {
        $expr = [];

        $binaryOpHandler = Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'There should be exactly one space between binary operator and its operand.');

        $expr[] = [self::BLOCK_VARS, '{% endfor %}', Handler::create()->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{% for @ in $ %}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{% for @ in $ if $ %}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::BLOCK_VARS, '{% set @ = $ %}', Handler::create()->delegate('$', 'expr')->enforceSize(' ', 1, 'More than one space used')];
        $expr[] = [self::OP_VARS, '@ \( \)', Handler::create()->enforceSize(' ', 0, 'No space should be used inside function call with no argument.')];
        $expr[] = [self::OP_VARS, '@ \( $ \)', Handler::create()->delegate('$', 'list')->enforceSize(' ', 0, 'No space should be used')];
        $expr[] = [self::OP_VARS, '\[ \]', Handler::create()->enforceSize(' ', 0, 'No space should be used for empty arrays.')];
        $expr[] = [self::OP_VARS, '\[ $ \]', Handler::create()->delegate('$', 'list')->enforceSize(' ', 0, 'No space should be used')];
        $expr[] = [self::OP_VARS, '\{ \}', Handler::create()->enforceSize(' ', 0, 'No space should be used for empty hashes.')];
        $expr[] = [self::OP_VARS, '\{ $ \}', Handler::create()->delegate('$', 'hash')->enforceSize(' ', 1, 'One space should be used')];
        $expr[] = [self::OP_VARS, '$ \.\. $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ \?\? $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ \*\* $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ % $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ // $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ / $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ \* $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ ~ $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ - $', $binaryOpHandler];
        $expr[] = [self::OP_VARS, '$ \+ $', $binaryOpHandler];

        $list = [];
        $list[] = [self::LIST_VARS, ' ', Handler::create()->enforceSize(' ', 0, 'Empty list should have no whitespace')];
        $list[] = [self::LIST_VARS, '$_, %', Handler::create()->delegate('$', 'expr')->delegate('%', 'list')->enforceSize('_', 0, 'Empty list should have no whitespace')->enforceSize(' ', 1, 'Requires a space for the following list value.')];
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
