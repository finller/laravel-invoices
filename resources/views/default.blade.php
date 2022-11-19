<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ $invoice->name }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <style type="text/css" media="screen">
        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        table,
        th,
        tr,
        td,
        p,
        div {
            line-height: 1.1;
        }

        html {
            font-family: sans-serif;
            line-height: 1.15;
            margin: 0;
        }

        body {
            font-family: "Helvetica", "Courier", "Segoe UI", Roboto, Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-weight: 400;
            line-height: 1.5;
            color: #050038;
            text-align: left;
            background-color: #fff;
            font-size: 14px;
            margin: 0;
        }

        h1 {
            margin: 0;
        }

        p {
            margin: 0;
        }

        strong {
            font-weight: bolder;
        }

        img {
            vertical-align: middle;
            border-style: none;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th {
            text-align: inherit;
            font-weight: normal;
            border-bottom: 1px solid #050038;
        }

        td {
            padding: 0;
        }

        .container {
            margin: 36pt;
        }

        .pr-0,
        .px-0 {
            padding-right: 0 !important;
        }

        .pl-0,
        .px-0 {
            padding-left: 0 !important;
        }

        .pb-1 {
            padding-bottom: 0.25rem !important;
        }

        .pr-1 {
            padding-right: 0.25rem !important;
        }

        .p-1 {
            padding: 0.25rem !important;
        }

        .py-1,
        .pt-1 {
            padding-top: 0.25rem !important;
        }

        .pr-2,
        .p-2 {
            padding-right: 0.5rem !important;
        }

        .pl-2,
        .p-2 {
            padding-left: 0.5rem !important;
        }

        .py-2,
        .pb-2,
        .p-2 {
            padding-bottom: 0.5rem !important;
        }

        .py-2,
        .pt-2,
        .p-2 {
            padding-top: 0.5rem !important;
        }

        .has-text-right {
            text-align: right !important;
        }

        .has-text-centered {
            text-align: center !important;
        }

        .text-uppercase {
            text-transform: uppercase !important;
        }

        .mb-5 {
            margin-bottom: 1.5rem !important;
        }

        .mt-5 {
            margin-top: 1.5rem !important;
        }

        .mb-1 {
            margin-bottom: 0.25rem !important;
        }

        .mt-1 {
            margin-top: 0.25rem !important;
        }

        .mb-6 {
            margin-bottom: 3rem !important;
        }

        .align-top {
            vertical-align: top;
        }

        .heading {
            background-color: #050038;
            height: 10px;
            width: 100%;
        }

        .nowrap {
            white-space: nowrap;
        }

        .has-border-bottom-light {
            border-bottom: 1px solid #f4f4f4;
        }
    </style>
</head>

<body>
    <div class="heading"></div>
    <div class="container">
        <table class="mb-5">
            <tbody>
                <tr>
                    <td class="align-top">
                        <h1 class="mb-1">
                            <strong>{{ $invoice->name }}</strong>
                        </h1>
                        @if ($invoice->state)
                            <p class="mb-5">
                                <strong>{{ $invoice->state }}</strong>
                            </p>
                        @endif

                        <table>
                            <tbody>
                                <tr class="">
                                    <td class="pb-1 pr-2 nowrap">
                                        <strong>{{ __('invoices::invoice.serial_number') }} </strong>
                                    </td>
                                    <td class="pb-1" width="100%">
                                        <strong>{{ $invoice->serial_number }}</strong>
                                    </td>
                                </tr>
                                <tr class="">
                                    <td class="pb-1 pr-2 nowrap">{{ __('invoices::invoice.created_at') }}</td>
                                    <td class="pb-1" width="100%">
                                        {{ $invoice->created_at->format(config('invoices.date_format')) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="pb-1 pr-2 nowrap">{{ __('invoices::invoice.due_at') }}</td>
                                    <td class="pb-1" width="100%">
                                        {{ $invoice->due_at->format(config('invoices.date_format')) }}
                                    </td>
                                </tr>
                                @if ($invoice->paid_at)
                                    <tr>
                                        <td class="pb-1 pr-2 nowrap">{{ __('invoices::invoice.paid_at') }}</td>
                                        <td class="pb-1" width="100%">
                                            {{ $invoice->paid_at->format(config('invoices.date_format')) }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </td>
                    @if ($invoice->logo)
                        <td class="align-top" width="30%">
                            <img src="{{ $invoice->getLogo() }}" alt="logo" height="100">
                        </td>
                    @endif
                </tr>

            </tbody>
        </table>

        <table class="mb-6">
            <tbody>
                <tr>
                    <td class="align-top" width="50%">
                        @if ($name = data_get($invoice->seller, 'name'))
                            <p class="pb-1"><strong>{{ $name }}</strong></p>
                        @endif
                        @if ($street = data_get($invoice->seller, 'address.street'))
                            <p class="pb-1">{{ $street }}</p>
                        @endif
                        @if ($city = data_get($invoice->seller, 'address.city'))
                            <p class="pb-1">{{ $city }}</p>
                        @endif
                        @if ($postal_code = data_get($invoice->seller, 'address.postal_code'))
                            <p class="pb-1">{{ $postal_code }}</p>
                        @endif
                        @if ($state = data_get($invoice->seller, 'address.state'))
                            <p class="pb-1">{{ $state }}</p>
                        @endif
                        @if ($country = data_get($invoice->seller, 'address.country'))
                            <p class="pb-1">{{ $country }}</p>
                        @endif
                        @if ($email = data_get($invoice->seller, 'email'))
                            <p class="pb-1">{{ $email }}</p>
                        @endif
                        @if ($phone_number = data_get($invoice->seller, 'phone_number'))
                            <p class="pb-1">{{ $phone_number }}</p>
                        @endif
                        @if ($tax_number = data_get($invoice->seller, 'tax_number'))
                            <p>{{ $tax_number }}</p>
                        @endif
                    </td>
                    <td class="align-top" width="50%">
                        @if ($name = data_get($invoice->buyer, 'name'))
                            <p class="pb-1"><strong>{{ $name }}</strong></p>
                        @endif
                        @if ($street = data_get($invoice->buyer, 'address.street'))
                            <p class="pb-1">{{ $street }}</p>
                        @endif
                        @if ($city = data_get($invoice->buyer, 'address.city'))
                            <p class="pb-1">{{ $city }}</p>
                        @endif
                        @if ($postal_code = data_get($invoice->buyer, 'address.postal_code'))
                            <p class="pb-1">{{ $postal_code }}</p>
                        @endif
                        @if ($state = data_get($invoice->buyer, 'address.state'))
                            <p class="pb-1">{{ $state }}</p>
                        @endif
                        @if ($country = data_get($invoice->buyer, 'address.country'))
                            <p class="pb-1">{{ $country }}</p>
                        @endif
                        @if ($email = data_get($invoice->buyer, 'email'))
                            <p class="pb-1">{{ $email }}</p>
                        @endif
                        @if ($phone_number = data_get($invoice->buyer, 'phone_number'))
                            <p class="pb-1">{{ $phone_number }}</p>
                        @endif
                        @if ($tax_number = data_get($invoice->buyer, 'tax_number'))
                            <p>{{ $tax_number }}</p>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="mb-5">
            <thead>
                <tr>
                    <th class="py-2 pr-2">{{ __('invoices::invoice.description') }}</th>
                    <th class="p-2">{{ __('invoices::invoice.quantity') }}</th>
                    <th class="p-2">{{ __('invoices::invoice.unit_price') }}</th>
                    <th class="p-2">{{ __('invoices::invoice.tax') }}</th>
                    <th class="has-text-right py-2 pl-2">{{ __('invoices::invoice.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td @class([
                            'align-top py-2 pr-2',
                            'has-border-bottom-light' => !$loop->last,
                        ])>
                            <p><strong>{{ $item->label }}</strong></p>
                            @if ($item->description)
                                <p class="pt-1">{{ $item->description }}</p>
                            @endif
                        </td>
                        <td @class(['nowrap align-top p-2', 'has-border-bottom-light'])>
                            <p>{{ $item->quantity }}</p>
                        </td>
                        <td @class(['nowrap align-top p-2', 'has-border-bottom-light'])>
                            <p>{{ $item->formatMoney($item->unit_price) }}</p>
                        </td>
                        <td @class(['nowrap align-top p-2', 'has-border-bottom-light'])>
                            @if ($item->unit_tax)
                                <p>{{ $item->formatMoney($item->unit_tax) }}</p>
                            @else
                                <p>{{ $item->formatPercentage($item->tax_percentage) }}</p>
                            @endif
                        </td>
                        <td @class([
                            'nowrap align-top has-text-right pl-2 py-2',
                            'has-border-bottom-light',
                        ])>
                            <p>{{ $item->formatMoney($item->unit_price) }}</p>
                        </td>
                    </tr>
                @endforeach

                <tr>
                    {{-- empty space --}}
                    <td class="py-2 pr-2"></td>
                    <td class="p-2 has-border-bottom-light" colspan="3">
                        {{ __('invoices::invoice.subtotal_amount') }}</td>
                    <td class="nowrap py-2 pl-2 has-border-bottom-light has-text-right">
                        {{ $invoice->formatMoney($invoice->subTotalAmount()) }}
                    </td>
                </tr>
                <tr>
                    {{-- empty space --}}
                    <td class="py-2 pr-2"></td>
                    <td class="p-2 has-border-bottom-light" colspan="3">
                        {{ $invoice->tax_label ?? __('invoices::invoice.tax_label') }}</td>
                    <td class="nowrap py-2 pl-2 has-border-bottom-light has-text-right">
                        {{ $invoice->formatMoney($invoice->totalTaxAmount()) }}
                    </td>
                </tr>
                <tr>
                    {{-- empty space --}}
                    <td class="py-2 pr-2"></td>
                    <td class="p-2 has-border-bottom-light" colspan="3">
                        <strong>{{ __('invoices::invoice.total_amount') }}</strong>
                    </td>
                    <td class="nowrap py-2 pl-2 has-border-bottom-light has-text-right">
                        <strong>
                            {{ $invoice->formatMoney($invoice->totalAmount()) }}
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>

        @if ($invoice->description)
            <p class="mb-1"><strong>{{ __('invoices::invoice.description') }}</strong></p>
            <p>{!! $invoice->description !!}</p>
        @endif

    </div>
    <script type="text/php">
            if (isset($pdf) && $PAGE_COUNT > 1) {
                $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
                $size = 10;
                $font = $fontMetrics->getFont("Verdana");
                $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                $x = ($pdf->get_width() - $width);
                $y = $pdf->get_height() - 35;
                $pdf->page_text($x, $y, $text, $font, $size);
            }
    </script>
</body>

</html>
