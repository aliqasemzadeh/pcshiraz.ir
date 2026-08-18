<?php

declare(strict_types=1);

return [
    'price_type' => [
        'cash' => 'نقدی',
        'wholesale' => 'عمده‌فروشی',
        'installment' => 'اقساطی',
        'corporate' => 'شرکتی',
        'credit' => 'اعتباری',
    ],

    'item_type' => [
        'product' => 'محصول',
        'service' => 'خدمات',
        'digital' => 'دیجیتال',
    ],

    'order_status' => [
        'pending_approval' => 'در انتظار تایید',
        'approved' => 'تایید شده',
        'rejected' => 'رد شده',
        'awaiting_down_payment' => 'در انتظار پیش‌پرداخت',
        'installment_active' => 'اقساط فعال',
        'processing' => 'در حال پردازش',
        'shipped' => 'ارسال شده',
        'completed' => 'تکمیل شده',
        'cancelled' => 'لغو شده',
    ],

    'order_installment_status' => [
        'pending' => 'در انتظار',
        'paid' => 'پرداخت شده',
        'cancelled' => 'لغو شده',
    ],

    'organization_user_role' => [
        'approver' => 'مسئول تایید',
    ],

    'price_unit' => [
        'rial' => 'ریال',
        'toman' => 'تومان',
    ],
];
