<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ $category->title }} - {{ __('general.price_list') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; direction: rtl; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { font-size: 10px; color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: right; }
        th { background: #f3f4f6; font-weight: bold; }
        .ltr { direction: ltr; text-align: left; }
    </style>
</head>
<body>
    <h1>{{ __('general.price_list') }} — {{ $category->title }}</h1>
    <p class="meta">
        {{ $exportedAt }}
        @if ($brandTitle)
            | {{ __('general.brand') }}: {{ $brandTitle }}
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>{{ __('general.title') }}</th>
                <th>{{ __('general.brand') }}</th>
                <th>{{ __('general.color') }}</th>
                <th>{{ __('general.price') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->brand?->title ?? '—' }}</td>
                    <td>{{ $item->color_name ?: '—' }}</td>
                    <td class="ltr">
                        @if ($item->is_contact_price)
                            {{ __('general.contact_price') }}
                        @elseif ($item->activeCashPrice)
                            {{ number_format((float) $item->activeCashPrice->sale_price) }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center">{{ __('app.no_products') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
