<?php

namespace App\Http\Requests\Trade;

use Illuminate\Foundation\Http\FormRequest;

class BuyTradeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $amount = $this->input('amount_brl', $this->input('brl_amount', $this->input('amount')));

        if (is_string($amount)) {
            $amount = str_replace(',', '.', $amount);
        }

        $this->merge([
            'amount_brl' => $amount,
        ]);
    }

    public function rules(): array
    {
        return [
            'amount_brl' => [
                'required',
                'numeric',
                'gt:0',
                'max:100000000',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'amount_brl.regex' => 'O valor em BRL deve ter no máximo 2 casas decimais.',
        ];
    }
}
