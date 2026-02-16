<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;
use App\Models\Order;
use App\Models\Sale;

/**
 * OrderService - handles order-related business logic.
 * Replaces legacy order management functions.
 */
class OrderService
{
    public function __construct(
        private readonly PriceService $priceService,
    ) {}

    /**
     * Insert a sale record.
     * Replaces legacy insertSale() function.
     */
    public function insertSale(array $data): bool
    {
        $data['date'] = $data['date'] ?? date('Y-m-d');

        return Sale::create($data) !== null;
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(int $orderId, string $status): bool
    {
        $dateField = match ($status) {
            'completed' => 'order_completed_date',
            'cancelled' => 'order_cancelled_date',
            'delivered' => 'order_delivered_date',
            default => null,
        };

        $data = ['order_status' => $status];
        if ($dateField) {
            $data[$dateField] = now()->format('Y-m-d H:i:s');
        }

        return Order::where('order_id', $orderId)->update($data) > 0;
    }

    /**
     * Create a notification.
     * Replaces legacy notification insertion.
     */
    public function createNotification(
        int $sellerId,
        string $type,
        string $text,
        string $url = '',
    ): Notification {
        return Notification::create([
            'notification_seller_id' => $sellerId,
            'notification_type' => $type,
            'notification_text' => $text,
            'notification_url' => $url,
            'notification_date' => now()->format('Y-m-d H:i:s'),
            'notification_read' => 0,
        ]);
    }

    /**
     * Calculate processing fee for an order amount.
     */
    public function calculateProcessingFee(float $amount): float
    {
        return $this->priceService->processingFee($amount);
    }
}
