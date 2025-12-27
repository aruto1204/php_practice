<?php

declare(strict_types=1);

/**
 * Phase 3.4: フォーム処理とバリデーション - バリデーションルールとエラーメッセージ
 *
 * このファイルでは、包括的なバリデーションシステムとエラーメッセージの表示を学習します。
 *
 * 学習内容:
 * - バリデーションルールの定義
 * - カスタムバリデーションルール
 * - エラーメッセージのカスタマイズ
 * - 条件付きバリデーション
 * - ネストしたデータのバリデーション
 */

echo "=== Phase 3.4: バリデーションルールとエラーメッセージ ===\n\n";

echo "--- 1. バリデーションルールの種類 ---\n";
echo "基本的なバリデーションルール:\n";
echo "  - required: 必須フィールド\n";
echo "  - min/max: 最小・最大長\n";
echo "  - email: メールアドレス形式\n";
echo "  - url: URL形式\n";
echo "  - numeric: 数値\n";
echo "  - alpha: 英字のみ\n";
echo "  - alphanumeric: 英数字のみ\n";
echo "  - regex: 正規表現パターン\n";
echo "  - in: 許可リスト\n";
echo "  - unique: 一意性チェック\n";
echo "  - confirmed: 確認フィールドの一致\n\n";

echo "--- 2. 包括的なバリデータークラスの実装 ---\n";

/**
 * バリデータークラス
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $customMessages = [];
    private array $attributeNames = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * カスタムエラーメッセージを設定
     */
    public function setCustomMessages(array $messages): self
    {
        $this->customMessages = $messages;
        return $this;
    }

    /**
     * 属性名（フィールド名の表示名）を設定
     */
    public function setAttributeNames(array $names): self
    {
        $this->attributeNames = $names;
        return $this;
    }

    /**
     * バリデーションを実行
     */
    public function validate(): bool
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * ルールを適用
     */
    private function applyRule(string $field, mixed $value, string $rule): void
    {
        // パラメータ付きルール（例: min:3）
        if (str_contains($rule, ':')) {
            [$ruleName, $parameter] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $parameter = null;
        }

        // ルールの適用
        $passed = match ($ruleName) {
            'required' => $this->validateRequired($value),
            'email' => $this->validateEmail($value),
            'min' => $this->validateMin($value, (int)$parameter),
            'max' => $this->validateMax($value, (int)$parameter),
            'between' => $this->validateBetween($value, $parameter),
            'numeric' => $this->validateNumeric($value),
            'integer' => $this->validateInteger($value),
            'alpha' => $this->validateAlpha($value),
            'alphanumeric' => $this->validateAlphanumeric($value),
            'url' => $this->validateUrl($value),
            'regex' => $this->validateRegex($value, $parameter),
            'in' => $this->validateIn($value, $parameter),
            'confirmed' => $this->validateConfirmed($field, $value),
            'same' => $this->validateSame($field, $value, $parameter),
            'different' => $this->validateDifferent($field, $value, $parameter),
            'date' => $this->validateDate($value),
            'after' => $this->validateAfter($value, $parameter),
            'before' => $this->validateBefore($value, $parameter),
            default => true
        };

        if (!$passed) {
            $this->addError($field, $ruleName, $parameter);
        }
    }

    // --- バリデーションメソッド ---

    private function validateRequired(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        if (is_array($value) && empty($value)) {
            return false;
        }

        return true;
    }

    private function validateEmail(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true; // requiredと組み合わせて使用
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMin(mixed $value, int $min): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_numeric($value)) {
            return (float)$value >= $min;
        }

        return mb_strlen((string)$value) >= $min;
    }

    private function validateMax(mixed $value, int $max): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_numeric($value)) {
            return (float)$value <= $max;
        }

        return mb_strlen((string)$value) <= $max;
    }

    private function validateBetween(mixed $value, ?string $range): bool
    {
        if ($value === null || $value === '' || $range === null) {
            return true;
        }

        [$min, $max] = explode(',', $range);
        return $this->validateMin($value, (int)$min) && $this->validateMax($value, (int)$max);
    }

    private function validateNumeric(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_numeric($value);
    }

    private function validateInteger(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function validateAlpha(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return preg_match('/^[a-zA-Z]+$/', (string)$value) === 1;
    }

    private function validateAlphanumeric(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return preg_match('/^[a-zA-Z0-9]+$/', (string)$value) === 1;
    }

    private function validateUrl(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function validateRegex(mixed $value, ?string $pattern): bool
    {
        if ($value === null || $value === '' || $pattern === null) {
            return true;
        }

        return preg_match($pattern, (string)$value) === 1;
    }

    private function validateIn(mixed $value, ?string $list): bool
    {
        if ($value === null || $value === '' || $list === null) {
            return true;
        }

        $allowedValues = explode(',', $list);
        return in_array($value, $allowedValues, true);
    }

    private function validateConfirmed(string $field, mixed $value): bool
    {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;

        return $value === $confirmValue;
    }

    private function validateSame(string $field, mixed $value, ?string $otherField): bool
    {
        if ($otherField === null) {
            return true;
        }

        $otherValue = $this->data[$otherField] ?? null;
        return $value === $otherValue;
    }

    private function validateDifferent(string $field, mixed $value, ?string $otherField): bool
    {
        if ($otherField === null) {
            return true;
        }

        $otherValue = $this->data[$otherField] ?? null;
        return $value !== $otherValue;
    }

    private function validateDate(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return strtotime((string)$value) !== false;
    }

    private function validateAfter(mixed $value, ?string $date): bool
    {
        if ($value === null || $value === '' || $date === null) {
            return true;
        }

        $valueTimestamp = strtotime((string)$value);
        $dateTimestamp = strtotime($date);

        if ($valueTimestamp === false || $dateTimestamp === false) {
            return false;
        }

        return $valueTimestamp > $dateTimestamp;
    }

    private function validateBefore(mixed $value, ?string $date): bool
    {
        if ($value === null || $value === '' || $date === null) {
            return true;
        }

        $valueTimestamp = strtotime((string)$value);
        $dateTimestamp = strtotime($date);

        if ($valueTimestamp === false || $dateTimestamp === false) {
            return false;
        }

        return $valueTimestamp < $dateTimestamp;
    }

    // --- エラーメッセージ処理 ---

    private function addError(string $field, string $rule, ?string $parameter): void
    {
        $message = $this->getErrorMessage($field, $rule, $parameter);
        $this->errors[$field][] = $message;
    }

    private function getErrorMessage(string $field, string $rule, ?string $parameter): string
    {
        // カスタムメッセージがあれば使用
        $customKey = "$field.$rule";
        if (isset($this->customMessages[$customKey])) {
            return $this->replaceMessagePlaceholders(
                $this->customMessages[$customKey],
                $field,
                $parameter
            );
        }

        // デフォルトメッセージ
        $attribute = $this->attributeNames[$field] ?? $field;
        $defaultMessages = [
            'required' => ':attribute は必須です',
            'email' => ':attribute はメールアドレス形式で入力してください',
            'min' => ':attribute は:min文字以上にしてください',
            'max' => ':attribute は:max文字以下にしてください',
            'between' => ':attribute は:min〜:max の範囲で入力してください',
            'numeric' => ':attribute は数値で入力してください',
            'integer' => ':attribute は整数で入力してください',
            'alpha' => ':attribute は英字のみで入力してください',
            'alphanumeric' => ':attribute は英数字のみで入力してください',
            'url' => ':attribute はURL形式で入力してください',
            'regex' => ':attribute の形式が正しくありません',
            'in' => ':attribute は:values のいずれかを選択してください',
            'confirmed' => ':attribute の確認が一致しません',
            'same' => ':attribute と:other は同じ値にしてください',
            'different' => ':attribute と:other は異なる値にしてください',
            'date' => ':attribute は日付形式で入力してください',
            'after' => ':attribute は:date より後の日付にしてください',
            'before' => ':attribute は:date より前の日付にしてください',
        ];

        $message = $defaultMessages[$rule] ?? ':attribute が無効です';
        return $this->replaceMessagePlaceholders($message, $field, $parameter);
    }

    private function replaceMessagePlaceholders(string $message, string $field, ?string $parameter): string
    {
        $attribute = $this->attributeNames[$field] ?? $field;

        $replacements = [
            ':attribute' => $attribute,
            ':field' => $field,
        ];

        // ルール固有のプレースホルダー
        if ($parameter !== null) {
            if (str_contains($parameter, ',')) {
                [$min, $max] = explode(',', $parameter);
                $replacements[':min'] = $min;
                $replacements[':max'] = $max;
            } else {
                $replacements[':min'] = $parameter;
                $replacements[':max'] = $parameter;
                $replacements[':values'] = $parameter;
                $replacements[':other'] = $this->attributeNames[$parameter] ?? $parameter;
                $replacements[':date'] = $parameter;
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    // --- エラー取得 ---

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getError(string $field): ?array
    {
        return $this->errors[$field] ?? null;
    }

    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    public function getAllErrors(): array
    {
        $all = [];
        foreach ($this->errors as $fieldErrors) {
            $all = array_merge($all, $fieldErrors);
        }
        return $all;
    }

    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }
}

echo "✅ Validatorクラスを実装しました\n\n";

echo "--- 3. 基本的なバリデーションの例 ---\n";

$data = [
    'name' => 'John',
    'email' => 'john@example.com',
    'age' => '25',
    'website' => 'https://example.com',
    'gender' => 'male'
];

$rules = [
    'name' => 'required|min:2|max:50',
    'email' => 'required|email',
    'age' => 'required|numeric|between:18,100',
    'website' => 'url',
    'gender' => 'in:male,female,other'
];

$validator = new Validator($data, $rules);

if ($validator->validate()) {
    echo "✓ バリデーション成功\n";
} else {
    echo "✗ バリデーションエラー:\n";
    foreach ($validator->getErrors() as $field => $errors) {
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
    }
}
echo "\n";

echo "--- 4. カスタムメッセージとフィールド名 ---\n";

$data = [
    'username' => 'ab',
    'password' => '123',
    'password_confirmation' => '456'
];

$rules = [
    'username' => 'required|min:3|alphanumeric',
    'password' => 'required|min:8|confirmed',
];

$validator = new Validator($data, $rules);

// カスタムメッセージを設定
$validator->setCustomMessages([
    'username.required' => 'ユーザー名を入力してください',
    'username.min' => 'ユーザー名は:min文字以上で入力してください',
    'password.confirmed' => 'パスワードと確認用パスワードが一致しません',
]);

// フィールド名の日本語表示を設定
$validator->setAttributeNames([
    'username' => 'ユーザー名',
    'password' => 'パスワード',
]);

if ($validator->validate()) {
    echo "✓ バリデーション成功\n";
} else {
    echo "✗ バリデーションエラー:\n";
    foreach ($validator->getErrors() as $field => $errors) {
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
    }
}
echo "\n";

echo "--- 5. 日付のバリデーション ---\n";

$data = [
    'birth_date' => '2000-01-01',
    'start_date' => '2024-01-01',
    'end_date' => '2023-12-31',  // start_dateより前（エラー）
];

$rules = [
    'birth_date' => 'required|date|before:2024-01-01',
    'start_date' => 'required|date',
    'end_date' => 'required|date|after:' . ($data['start_date'] ?? ''),
];

$validator = new Validator($data, $rules);
$validator->setAttributeNames([
    'birth_date' => '生年月日',
    'start_date' => '開始日',
    'end_date' => '終了日',
]);

if ($validator->validate()) {
    echo "✓ バリデーション成功\n";
} else {
    echo "✗ バリデーションエラー:\n";
    foreach ($validator->getAllErrors() as $error) {
        echo "  - $error\n";
    }
}
echo "\n";

echo "--- 6. エラーメッセージの表示パターン ---\n";

$data = [
    'email' => 'invalid',
    'password' => '123',
];

$rules = [
    'email' => 'required|email',
    'password' => 'required|min:8',
];

$validator = new Validator($data, $rules);
$validator->setAttributeNames([
    'email' => 'メールアドレス',
    'password' => 'パスワード',
]);

$validator->validate();

echo "✅ エラーメッセージ表示パターン:\n\n";

echo "1. すべてのエラーを一覧表示:\n";
foreach ($validator->getAllErrors() as $error) {
    echo "  • $error\n";
}
echo "\n";

echo "2. フィールドごとにエラーを表示:\n";
foreach ($validator->getErrors() as $field => $errors) {
    echo "  [$field]:\n";
    foreach ($errors as $error) {
        echo "    - $error\n";
    }
}
echo "\n";

echo "3. 特定フィールドの最初のエラーのみ表示:\n";
echo "  メール: " . ($validator->getFirstError('email') ?? 'エラーなし') . "\n";
echo "  パスワード: " . ($validator->getFirstError('password') ?? 'エラーなし') . "\n\n";

echo "--- 7. まとめ ---\n";
echo "✅ バリデーションのベストプラクティス:\n";
echo "  1. 必要なルールを組み合わせて使用\n";
echo "  2. わかりやすいエラーメッセージを提供\n";
echo "  3. フィールド名は日本語で表示\n";
echo "  4. エラーは複数表示できるように設計\n";
echo "  5. バリデーションロジックと表示を分離\n\n";

echo "=== Phase 3.4: バリデーションルールとエラーメッセージ 完了 ===\n";
