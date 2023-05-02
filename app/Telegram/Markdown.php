<?php

namespace App\Telegram;

final class Markdown
{
    public static function escapeText(string $text, array $replace = []): string
    {
        $toEscape = [
            '_' => '\_',
            '*' => '\*',
            '[' => '\[',
            ']' => '\]',
            '(' => '\(',
            ')' => '\)',
            '~' => '\~',
            '`' => '\`',
            '>' => '\>',
            '#' => '\#',
            '+' => '\+',
            '-' => '\-',
            '=' => '\=',
            '|' => '\|',
            '{' => '\{',
            '}' => '\}',
            '.' => '\.',
            '!' => '\!',
        ];
        if (!$text) {
            return '';
        }

        $text = strtr($text, $toEscape);
        if ($replace) {
            $text = str_replace(array_keys($replace), array_values($replace), $text);
        }

        return $text;
    }
}
