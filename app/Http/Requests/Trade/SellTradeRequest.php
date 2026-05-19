<?php

namespace App\Http\Requests\Trade;

use Illuminate\Foundation\Http\FormRequest;

class SellTradeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $amount = $this->input('amount_btc', $this->input('btc_amount', $this->input('amount')));

        if (is_string($amount)) {
            $amount = str_replace(',', '.', $amount);
        }

        $this->merge([
            'amount_btc' => $amount,
        ]);
    }

    public function rules(): array
    {
        return [
            'amount_btc' => [
                'required',
                'numeric',
                'gt:0',
                'max:1000',
                'regex:/^\d+(\.\d{1,8})?$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'amount_btc.regex' => 'O valor em BTC deve ter no máximo 8 casas decimais.',
        ];
    }
}
