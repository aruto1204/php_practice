<?php

declare(strict_types=1);

namespace Phase4\RestApi\Entities;

/**
 * 注文ステータスEnum
 *
 * 注文の状態を型安全に管理
 */
enum OrderStatus: string
{
    /**
     * 保留中（注文作成直後）
     */
    case PENDING = 'pending';

    /**
     * 処理中（支払い確認済み、発送準備中）
     */
    case PROCESSING = 'processing';

    /**
     * 完了（配送完了）
     */
    case COMPLETED = 'completed';

    /**
     * キャンセル済み
     */
    case CANCELLED = 'cancelled';

    /**
     * 表示名を取得
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => '保留中',
            self::PROCESSING => '処理中',
            self::COMPLETED => '完了',
            self::CANCELLED => 'キャンセル済み',
        };
    }

    /**
     * キャンセル可能かチェック
     *
     * @return bool
     */
    public function isCancellable(): bool
    {
        return match ($this) {
            self::PENDING, self::PROCESSING => true,
            self::COMPLETED, self::CANCELLED => false,
        };
    }

    /**
     * 次のステータスに遷移可能かチェック
     *
     * @param OrderStatus $nextStatus 次のステータス
     * @return bool
     */
    public function canTransitionTo(OrderStatus $nextStatus): bool
    {
        return match ($this) {
            self::PENDING => in_array($nextStatus, [self::PROCESSING, self::CANCELLED], true),
            self::PROCESSING => in_array($nextStatus, [self::COMPLETED, self::CANCELLED], true),
            self::COMPLETED, self::CANCELLED => false, // 完了/キャンセル後は変更不可
        };
    }
}
