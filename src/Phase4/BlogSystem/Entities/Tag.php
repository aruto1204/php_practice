<?php

declare(strict_types=1);

namespace App\Phase4\BlogSystem\Entities;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * タグエンティティ
 *
 * 記事のタグを表現するドメインモデル
 */
class Tag
{
    /**
     * コンストラクタ
     *
     * @param int $id タグID
     * @param string $name タグ名
     * @param string $slug URL用スラッグ
     * @param DateTimeImmutable $createdAt 作成日時
     */
    public function __construct(
        private readonly int $id,
        private string $name,
        private string $slug,
        private readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {
        $this->validateName($name);
        $this->validateSlug($slug);
    }

    /**
     * 名前のバリデーション
     */
    private function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new InvalidArgumentException('タグ名は空にできません');
        }

        if (mb_strlen($name) > 50) {
            throw new InvalidArgumentException('タグ名は50文字以下で指定してください');
        }
    }

    /**
     * スラッグのバリデーション
     */
    private function validateSlug(string $slug): void
    {
        if (empty(trim($slug))) {
            throw new InvalidArgumentException('スラッグは空にできません');
        }

        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            throw new InvalidArgumentException('スラッグは小文字の英数字とハイフンのみ使用できます');
        }

        if (strlen($slug) > 50) {
            throw new InvalidArgumentException('スラッグは50文字以下で指定してください');
        }
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    // Setters
    public function setName(string $name): void
    {
        $this->validateName($name);
        $this->name = $name;
    }

    public function setSlug(string $slug): void
    {
        $this->validateSlug($slug);
        $this->slug = $slug;
    }

    /**
     * 名前からスラッグを生成
     *
     * @param string $name タグ名
     * @return string スラッグ
     */
    public static function generateSlug(string $name): string
    {
        // 名前を小文字に変換
        $slug = strtolower($name);

        // 英数字とハイフン以外を削除
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);

        // スペースをハイフンに変換
        $slug = preg_replace('/[\s-]+/', '-', $slug);

        // 前後のハイフンを削除
        $slug = trim($slug, '-');

        // 空の場合はデフォルト値を使用
        if (empty($slug)) {
            $slug = 'tag-' . time();
        }

        return $slug;
    }
}
