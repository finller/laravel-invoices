@php
    $displayTaxColumn = !$invoice->totalTaxAmount()->isZero();
    $colspan = $displayTaxColumn ? '3' : '2';
@endphp

<div>
    <div class="h-3 w-full" style="background-color: {{ $invoice->color }}"></div>
    <div class="m-12">
        <table class="mb-5 w-full">
            <tbody>
                <tr>
                    <td class="p-0 align-top">
                        <h1 class="mb-1 text-3xl">
                            <strong>{{ $invoice->name }}</strong>
                        </h1>
                        @if ($invoice->state)
                            <p class="mb-5">
                                {{ $invoice->state }}
                            </p>
                        @endif

                        <table class="w-full">
                            <tbody>
                                <tr class="">
                                    <td class="whitespace-nowrap pb-1 pr-2">
                                        <strong>{{ __('invoices::invoice.serial_number') }} </strong>
                                    </td>
                                    <td class="pb-1" width="100%">
                                        <strong>{{ $invoice->serial_number }}</strong>
                                    </td>
                                </tr>
                                <tr class="text-sm">
                                    <td class="whitespace-nowrap pb-1 pr-2">
                                        {{ __('invoices::invoice.created_at') }}
                                    </td>
                                    <td class="pb-1" width="100%">
                                        {{ $invoice->created_at?->format(config('invoices.date_format')) }}
                                    </td>
                                </tr>
                                @if ($invoice->due_at)
                                    <tr class="text-sm">
                                        <td class="whitespace-nowrap pb-1 pr-2">
                                            {{ __('invoices::invoice.due_at') }}
                                        </td>
                                        <td class="pb-1" width="100%">
                                            {{ $invoice->due_at->format(config('invoices.date_format')) }}
                                        </td>
                                    </tr>
                                @endif
                                @if ($invoice->paid_at)
                                    <tr class="text-sm">
                                        <td class="whitespace-nowrap pb-1 pr-2">
                                            {{ __('invoices::invoice.paid_at') }}
                                        </td>
                                        <td class="pb-1" width="100%">
                                            {{ $invoice->paid_at->format(config('invoices.date_format')) }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </td>
                    @if ($invoice->logo)
                        <td class="p-0 align-top" width="30%">
                            <img src="{{ $invoice->getLogo() }}" alt="logo" height="100">
                        </td>
                    @endif
                </tr>

            </tbody>
        </table>

        <table class="mb-6 w-full">
            <tbody>
                <tr>
                    <td class="p-0 align-top" width="50%">
                        @php
                            $name = data_get($invoice->seller, 'name');
                            $street = data_get($invoice->seller, 'address.street');
                            $postal_code = data_get($invoice->seller, 'address.postal_code');
                            $city = data_get($invoice->seller, 'address.city');
                            $state = data_get($invoice->seller, 'address.state');
                            $country = data_get($invoice->seller, 'address.country');
                            $email = data_get($invoice->seller, 'email');
                            $phone_number = data_get($invoice->seller, 'phone_number');
                            $tax_number = data_get($invoice->seller, 'tax_number');
                            $company_number = data_get($invoice->seller, 'company_number');
                        @endphp
                        <p class="pb-1"><strong>{{ __('invoices::invoice.from') }}</strong></p>
                        @if ($name)
                            <p class="pb-1 text-sm">{{ $name }}</p>
                        @endif
                        @if ($street)
                            <p class="pb-1 text-sm">{{ $street }}</p>
                        @endif
                        @if ($postal_code || $city)
                            <p class="pb-1 text-sm">
                                {{ $postal_code }}
                                {{ $city }}
                            </p>
                        @endif
                        @if ($state)
                            <p class="pb-1 text-sm">{{ $state }}</p>
                        @endif
                        @if ($country)
                            <p class="pb-1 text-sm">{{ $country }}</p>
                        @endif
                        @if ($email)
                            <p class="pb-1 text-sm">{{ $email }}</p>
                        @endif
                        @if ($phone_number)
                            <p class="pb-1 text-sm">{{ $phone_number }}</p>
                        @endif
                        @if ($tax_number)
                            <p class="pb-1 text-sm">{{ $tax_number }}</p>
                        @endif
                        @if ($company_number)
                            <p class="pb-1 text-sm">{{ $company_number }}</p>
                        @endif
                        @foreach (data_get($invoice->seller, 'data') ?? [] as $key => $item)
                            @if (is_string($key))
                                <p class="pb-1 text-sm">{{ $key }}: {{ $item }}</p>
                            @else
                                <p class="pb-1 text-sm">{{ $item }}</p>
                            @endif
                        @endforeach
                    </td>
                    <td class="p-0 align-top" width="50%">
                        @php
                            $name = data_get($invoice->buyer, 'name');
                            $street = data_get($invoice->buyer, 'address.street');
                            $postal_code = data_get($invoice->buyer, 'address.postal_code');
                            $city = data_get($invoice->buyer, 'address.city');
                            $state = data_get($invoice->buyer, 'address.state');
                            $country = data_get($invoice->buyer, 'address.country');
                            $email = data_get($invoice->buyer, 'email');
                            $phone_number = data_get($invoice->buyer, 'phone_number');
                            $tax_number = data_get($invoice->buyer, 'tax_number');
                            $company_number = data_get($invoice->buyer, 'company_number');
                        @endphp
                        <p class="pb-1"><strong>{{ __('invoices::invoice.to') }}</strong></p>
                        @if ($name)
                            <p class="pb-1 text-sm">{{ $name }}</p>
                        @endif
                        @if ($street)
                            <p class="pb-1 text-sm">{{ $street }}</p>
                        @endif
                        @if ($postal_code || $city)
                            <p class="pb-1 text-sm">
                                {{ $postal_code }}
                                {{ $city }}
                            </p>
                        @endif
                        @if ($state)
                            <p class="pb-1 text-sm">{{ $state }}</p>
                        @endif
                        @if ($country)
                            <p class="pb-1 text-sm">{{ $country }}</p>
                        @endif
                        @if ($email)
                            <p class="pb-1 text-sm">{{ $email }}</p>
                        @endif
                        @if ($phone_number)
                            <p class="pb-1 text-sm">{{ $phone_number }}</p>
                        @endif
                        @if ($tax_number)
                            <p class="pb-1 text-sm">{{ $tax_number }}</p>
                        @endif
                        @if ($company_number)
                            <p class="pb-1 text-sm">{{ $company_number }}</p>
                        @endif
                        @foreach (data_get($invoice->seller, 'data') ?? [] as $key => $item)
                            @if (is_string($key))
                                <p class="pb-1 text-sm">{{ $key }}: {{ $item }}</p>
                            @else
                                <p class="pb-1 text-sm">{{ $item }}</p>
                            @endif
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="mb-5 w-full">
            <thead>
                <tr>
                    <th class="whitespace-nowrap border-b py-2 pr-2 text-left">
                        {{ __('invoices::invoice.description') }}</th>
                    <th class="whitespace-nowrap border-b p-2 text-left">{{ __('invoices::invoice.quantity') }}</th>
                    <th class="whitespace-nowrap border-b p-2 text-left">{{ __('invoices::invoice.unit_price') }}</th>
                    @if ($displayTaxColumn)
                        <th class="whitespace-nowrap border-b p-2 text-left">{{ __('invoices::invoice.tax') }}</th>
                    @endif
                    <th class="whitespace-nowrap border-b py-2 pl-2 text-right">{{ __('invoices::invoice.amount') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td @class(['align-top py-2 pr-2', 'border-b' => !$loop->last])>
                            <p class="text-sm"><strong>{{ $item->label }}</strong></p>
                            @if ($item->description)
                                <p class="pt-1 text-sm">{{ $item->description }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap border-b p-2 align-top text-sm">
                            <p>{{ $item->quantity }}</p>
                        </td>
                        <td class="whitespace-nowrap border-b p-2 align-top text-sm">
                            <p>{{ $item->formatMoney($item->unit_price) }}</p>
                        </td>
                        @if ($displayTaxColumn)
                            <td class="whitespace-nowrap border-b p-2 align-top text-sm">
                                @if ($item->unit_tax && $item->tax_percentage)
                                    <p>{{ $item->formatMoney($item->unit_tax) }}
                                        ({{ $item->formatPercentage($item->tax_percentage) }})</p>
                                @elseif ($item->unit_tax)
                                    <p>{{ $item->formatMoney($item->unit_tax) }}</p>
                                @else
                                    <p>{{ $item->formatPercentage($item->tax_percentage) }}</p>
                                @endif
                            </td>
                        @endif
                        <td class="whitespace-nowrap border-b py-2 pl-2 text-right align-top text-sm">
                            <p>{{ $item->formatMoney($item->totalAmount()) }}</p>
                        </td>
                    </tr>
                @endforeach

                <tr>
                    {{-- empty space --}}
                    <td class="py-2 pr-2"></td>
                    <td class="border-b p-2 text-sm" colspan="{{ $colspan }}">
                        {{ __('invoices::invoice.subtotal_amount') }}</td>
                    <td class="whitespace-nowrap border-b py-2 pl-2 text-right text-sm">
                        {{ $invoice->formatMoney($invoice->subTotalAmount()) }}
                    </td>
                </tr>
                @if ($invoice->discounts)
                    @foreach ($invoice->discounts as $discount)
                        <tr>
                            {{-- empty space --}}
                            <td class="py-2 pr-2"></td>
                            <td class="border-b p-2 text-sm" colspan="{{ $colspan }}">
                                {{ __($discount->name) ?? __('invoices::invoice.discount_name') }}
                                @if ($discount->percent_off)
                                    ({{ $discount->formatPercentage($discount->percent_off) }})
                                @endif
                            </td>
                            <td class="whitespace-nowrap border-b py-2 pl-2 text-right text-sm">
                                {{ $invoice->formatMoney($discount->computeDiscountAmountOn($invoice->subTotalAmount())?->multipliedBy(-1)) }}
                            </td>
                        </tr>
                    @endforeach
                @endif
                @if ($invoice->tax_label || $displayTaxColumn)
                    <tr>
                        {{-- empty space --}}
                        <td class="py-2 pr-2"></td>
                        <td class="border-b p-2 text-sm" colspan="{{ $colspan }}">
                            {{ $invoice->tax_label ?? __('invoices::invoice.tax_label') }}
                        </td>
                        <td class="whitespace-nowrap border-b py-2 pl-2 text-right text-sm">
                            {{ $invoice->formatMoney($invoice->totalTaxAmount()) }}
                        </td>
                    </tr>
                @endif
                <tr>
                    {{-- empty space --}}
                    <td class="py-2 pr-2"></td>
                    <td class="border-b p-2" colspan="{{ $colspan }}">
                        <strong>{{ __('invoices::invoice.total_amount') }}</strong>
                    </td>
                    <td class="whitespace-nowrap border-b py-2 pl-2 text-right">
                        <strong>
                            {{ $invoice->formatMoney($invoice->totalAmount()) }}
                        </strong>
                    </td>
                </tr>
            </tbody>
        </table>

        @if ($invoice->description)
            <p class="mb-1"><strong>{{ __('invoices::invoice.description') }}</strong></p>
            <p class="whitespace-pre-line">{!! $invoice->description !!}</p>
        @endif

    </div>
</div>
