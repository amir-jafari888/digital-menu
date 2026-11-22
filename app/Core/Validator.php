<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Small rule-based validator with Persian messages.
 *
 * Rules are strings: required, nullable, string, int, bool, email, phone,
 * digits, url, date, min:N, max:N, in:a,b,c
 *
 * Values are normalised (Persian/Arabic digits, whitespace, ZWNJ) before they
 * are checked, and the returned `data` array only contains known fields.
 */
final class Validator
{
    /**
     * @param array<string, mixed>              $input
     * @param array<string, string|array>       $rules  field => 'required|max:20'
     * @param array<string, string>             $labels field => Persian label
     * @return array{valid: bool, errors: array<string,string>, data: array<string,mixed>}
     */
    public static function validate(array $input, array $rules, array $labels = []): array
    {
        $errors = [];
        $data   = [];

        foreach ($rules as $field => $ruleset) {
            $list  = is_array($ruleset) ? $ruleset : explode('|', (string) $ruleset);
            $label = $labels[$field] ?? $field;
            $raw   = $input[$field] ?? null;

            if (is_string($raw)) {
                $raw = trim($raw);
            } elseif (is_array($raw)) {
                // Nested arrays are never accepted as scalar input.
                $errors[$field] = $label . ' معتبر نیست.';
                continue;
            }

            $nullable = in_array('nullable', $list, true);
            $missing  = $raw === null || $raw === '';

            if ($missing) {
                if (in_array('required', $list, true)) {
                    $errors[$field] = $label . ' را وارد کن.';
                } else {
                    $data[$field] = null;
                }
                continue;
            }

            $value = $raw;
            $fail  = null;

            foreach ($list as $rule) {
                if ($rule === '' || $rule === 'required' || $rule === 'nullable') {
                    continue;
                }

                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);

                switch ($name) {
                    case 'string':
                        if (!is_scalar($value)) {
                            $fail = $label . ' معتبر نیست.';
                        }
                        $value = (string) $value;
                        break;

                    case 'int':
                        $value = self::normaliseDigits((string) $value);
                        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                            $fail = $label . ' باید عدد صحیح باشد.';
                        } else {
                            $value = (int) $value;
                        }
                        break;

                    case 'digits':
                        $value = self::normaliseDigits((string) $value);
                        if (!preg_match('/^\d+$/', $value)) {
                            $fail = $label . ' باید فقط رقم باشد.';
                        }
                        break;

                    case 'bool':
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if ($value === null) {
                            $fail = $label . ' معتبر نیست.';
                        }
                        break;

                    case 'email':
                        $value = mb_strtolower((string) $value);
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL) || mb_strlen($value) > 190) {
                            $fail = 'ایمیل واردشده معتبر نیست.';
                        }
                        break;

                    case 'phone':
                        $value = self::normaliseDigits((string) $value);
                        $value = preg_replace('/[\s\-()]/', '', $value) ?? '';
                        if (!preg_match('/^(?:0|\+?98)?9\d{9}$/', $value)) {
                            $fail = 'شماره موبایل باید با ۰۹ شروع شود و ۱۱ رقم باشد.';
                        }
                        break;

                    case 'url':
                        $safe = Security::safeUrl((string) $value);
                        if ($safe === null) {
                            $fail = $label . ' باید یک لینک http یا https معتبر باشد.';
                        } else {
                            $value = $safe;
                        }
                        break;

                    case 'date':
                        $ts = strtotime((string) $value);
                        if ($ts === false) {
                            $fail = $label . ' تاریخ معتبری نیست.';
                        } else {
                            $value = date('Y-m-d', $ts);
                        }
                        break;

                    case 'min':
                        $min = (int) $arg;
                        if (is_numeric($value) && !is_string($value)) {
                            if ($value < $min) {
                                $fail = $label . ' نباید کمتر از ' . fa_digits((string) $min) . ' باشد.';
                            }
                        } elseif (mb_strlen((string) $value) < $min) {
                            $fail = $label . ' باید حداقل ' . fa_digits((string) $min) . ' کاراکتر باشد.';
                        }
                        break;

                    case 'max':
                        $max = (int) $arg;
                        if (is_numeric($value) && !is_string($value)) {
                            if ($value > $max) {
                                $fail = $label . ' نباید بیشتر از ' . fa_digits((string) $max) . ' باشد.';
                            }
                        } elseif (mb_strlen((string) $value) > $max) {
                            $fail = $label . ' نباید بیشتر از ' . fa_digits((string) $max) . ' کاراکتر باشد.';
                        }
                        break;

                    case 'in':
                        $allowed = array_map('trim', explode(',', (string) $arg));
                        if (!in_array((string) $value, $allowed, true)) {
                            $fail = $label . ' مقدار مجاز ندارد.';
                        }
                        break;
                }

                if ($fail !== null) {
                    break;
                }
            }

            if ($fail !== null) {
                $errors[$field] = $fail;
            } else {
                if ($nullable && $value === '') {
                    $value = null;
                }
                $data[$field] = $value;
            }
        }

        return ['valid' => $errors === [], 'errors' => $errors, 'data' => $data];
    }

    /** Convert ۰-۹ / ٠-٩ to ASCII digits and strip bidi control chars. */
    public static function normaliseDigits(string $value): string
    {
        $value = strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '٫' => '.', '٬' => ',',
        ]);

        return preg_replace('/[\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', '', $value) ?? '';
    }

    /** Strip control characters from free text, keeping newlines. */
    public static function cleanText(?string $value): string
    {
        $value = (string) $value;
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
        $value = preg_replace("/\n{4,}/", "\n\n\n", $value) ?? '';

        return trim($value);
    }
}
