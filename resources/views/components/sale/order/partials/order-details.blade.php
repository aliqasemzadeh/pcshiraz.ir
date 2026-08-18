@props(['order'])

@php
    use App\Enums\PriceTypeEnum;
    use Morilog\Jalali\Jalalian;
@endphp

<div class="space-y-4 text-sm">
    <div class="grid grid-cols-2 gap-3">
        <div>
            <div class="text-body">{{ __('general.order_number') }}</div>
            <div class="font-medium text-heading" dir="ltr">{{ $order->order_number }}</div>
        </div>
        <div>
            <div class="text-body">{{ __('general.status') }}</div>
            <div class="font-medium text-heading">{{ $order->status->label() }}</div>
        </div>
        <div>
            <div class="text-body">{{ __('general.organization_code') }}</div>
            <div class="font-medium text-heading" dir="ltr">{{ $order->organization?->code }}</div>
        </div>
        <div>
            <div class="text-body">{{ __('general.sale_type') }}</div>
            <div class="font-medium text-heading">{{ $order->sale_type->label() }}</div>
        </div>
        <div>
            <div class="text-body">{{ __('general.total_amount') }}</div>
            <div class="font-medium text-heading">{{ format_price((float) $order->total_amount) }}</div>
        </div>
        <div>
            <div class="text-body">{{ __('general.total_payable') }}</div>
            <div class="font-medium text-heading">{{ format_price((float) $order->total_payable) }}</div>
        </div>
        @if ($order->sale_type === PriceTypeEnum::Installment)
            <div>
                <div class="text-body">{{ __('app.installment_subtotal') }}</div>
                <div class="font-medium text-heading">{{ format_price((float) $order->installment_subtotal) }}</div>
            </div>
            <div>
                <div class="text-body">{{ __('app.cash_only_subtotal') }}</div>
                <div class="font-medium text-heading">{{ format_price((float) $order->cash_only_subtotal) }}</div>
            </div>
            <div>
                <div class="text-body">{{ __('app.plan_down_payment_amount') }}</div>
                <div class="font-medium text-heading">{{ format_price((float) $order->plan_down_payment_amount) }}</div>
            </div>
            <div>
                <div class="text-body">{{ __('general.down_payment') }}</div>
                <div class="font-medium text-heading">{{ format_price((float) $order->down_payment_amount) }}</div>
            </div>
            <div>
                <div class="text-body">{{ __('general.financed_amount') }}</div>
                <div class="font-medium text-heading">{{ format_price((float) $order->financed_amount) }}</div>
            </div>
            <div>
                <div class="text-body">{{ __('general.total_interest') }}</div>
                <div class="font-medium text-heading">{{ format_price((float) $order->total_interest) }}</div>
            </div>
        @endif
    </div>

    <div>
        <h3 class="mb-2 font-semibold text-heading">{{ __('general.order_items') }}</h3>
        <div class="overflow-x-auto rounded-lg border border-default">
            <table class="min-w-full text-sm">
                <thead class="bg-neutral-secondary-soft">
                    <tr>
                        <th class="px-3 py-2 text-start">{{ __('general.title') }}</th>
                        <th class="px-3 py-2 text-start">{{ __('app.price_type') }}</th>
                        <th class="px-3 py-2 text-start">{{ __('general.quantity') }}</th>
                        <th class="px-3 py-2 text-start">{{ __('general.unit_price') }}</th>
                        <th class="px-3 py-2 text-start">{{ __('general.line_total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-t border-default">
                            <td class="px-3 py-2">{{ $item->title }}</td>
                            <td class="px-3 py-2">{{ $item->price_type?->label() ?? '—' }}</td>
                            <td class="px-3 py-2">{{ $item->quantity }}</td>
                            <td class="px-3 py-2">{{ format_price((float) $item->unit_price) }}</td>
                            <td class="px-3 py-2">{{ format_price((float) $item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($order->installments->isNotEmpty())
        <div>
            <h3 class="mb-2 font-semibold text-heading">{{ __('general.installment_schedule') }}</h3>
            <div class="overflow-x-auto rounded-lg border border-default">
                <table class="min-w-full text-sm">
                    <thead class="bg-neutral-secondary-soft">
                        <tr>
                            <th class="px-3 py-2 text-start">#</th>
                            <th class="px-3 py-2 text-start">{{ __('general.due_date') }}</th>
                            <th class="px-3 py-2 text-start">{{ __('general.principal_amount') }}</th>
                            <th class="px-3 py-2 text-start">{{ __('general.interest_amount') }}</th>
                            <th class="px-3 py-2 text-start">{{ __('general.total_amount') }}</th>
                            <th class="px-3 py-2 text-start">{{ __('general.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->installments as $installment)
                            <tr class="border-t border-default">
                                <td class="px-3 py-2">
                                    {{ $installment->sequence === 0 ? __('general.down_payment') : $installment->sequence }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ Jalalian::fromDateTime($installment->due_date)->format('Y/m/d') }}
                                </td>
                                <td class="px-3 py-2">{{ format_price((float) $installment->principal_amount) }}</td>
                                <td class="px-3 py-2">{{ format_price((float) $installment->interest_amount) }}</td>
                                <td class="px-3 py-2">{{ format_price((float) $installment->total_amount) }}</td>
                                <td class="px-3 py-2">{{ $installment->status->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
