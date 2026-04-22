<?php

declare(strict_types=1);

namespace App\Helpers;

final class Validator
{
    public static function requireString(array $input, string $field, string $label, int $max = 0): ?string
    {
        $value = trim((string) ($input[$field] ?? ''));
        if ($value === '') {
            throw new \InvalidArgumentException($label . 'は必須です。');
        }
        if ($max > 0 && mb_strlen($value) > $max) {
            throw new \InvalidArgumentException($label . 'は' . $max . '文字以内で入力してください。');
        }
        return $value;
    }

    public static function optionalString(array $input, string $field, int $max = 0): ?string
    {
        $value = trim((string) ($input[$field] ?? ''));
        if ($value === '') {
            return null;
        }
        if ($max > 0 && mb_strlen($value) > $max) {
            throw new \InvalidArgumentException($field . 'が長すぎます。');
        }
        return $value;
    }
}
