<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Api\BaseFormRequest;

class SupplierRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        // Bisa disesuaikan jika pengelola dapur juga diizinkan menambah supplier
        return $this->user() && $this->user()->role === 'owner';
    }

    public function rules()
    {
        if ($this->isMethod('post')) {
            return [
                'name' => 'required|string|max:255',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
            ];
        }

        return [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ];
    }
}
