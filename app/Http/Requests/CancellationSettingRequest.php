<?php

declare(strict_types = 1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancellationSettingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
             'name' => 'required|min:1|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('Pavadinimas'),
        ];
    }
}
