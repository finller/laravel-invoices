<?php

namespace Finller\Invoice;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/** @phpstan-consistent-constructor */
class InvoiceDiscount implements Arrayable, JsonSerializable
{
    use FormatForPdf;

    public function __construct(
        public ?string $name = null,
        public ?string $code = null,
        public ?Money $amount_off = null,
        public ?float $percent_off = null,
    ) {
        // code...
    }

    public function computeDiscountAmountOn(Money $amout): Money
    {
        if ($this->amount_off) {
            return $this->amount_off;
        }

        if (! is_null($this->percent_off)) {
            return $amout->multipliedBy($this->percent_off / 100, RoundingMode::HALF_CEILING);
        }

        return Money::of(0, $amout->getCurrency());
    }

    public static function fromArray(?array $array)
    {
        $currency = data_get($array, 'currency', config('invoices.default_currency'));
        $amount_off = data_get($array, 'amount_off');
        $percent_off = data_get($array, 'percent_off');

        return new static(
            name: data_get($array, 'name'),
            code: data_get($array, 'code'),
            amount_off: $amount_off ? Money::ofMinor($amount_off, $currency) : null,
            percent_off: $percent_off ? (float) $percent_off : null
        );
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'amount_off' => $this->amount_off?->getMinorAmount()->toInt(),
            'currency' => $this->amount_off?->getCurrency()->getCurrencyCode(),
            'percent_off' => $this->percent_off,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toLivewire()
    {
        return $this->toArray();
    }

    public static function fromLivewire($value)
    {
        return static::fromArray($value);
    }
}
