<?php

declare(strict_types=1);

return [
    'price_type' => [
        'cash' => 'Cash',
        'wholesale' => 'Wholesale',
        'installment' => 'Installment',
        'corporate' => 'Corporate',
        'credit' => 'Credit',
    ],

    'item_type' => [
        'product' => 'Product',
        'service' => 'Service',
        'digital' => 'Digital',
    ],

    'order_status' => [
        'pending_approval' => 'Pending approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'awaiting_down_payment' => 'Awaiting down payment',
        'installment_active' => 'Installment active',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'order_installment_status' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ],

    'organization_user_role' => [
        'approver' => 'Approver',
    ],
];
