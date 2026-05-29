<?php

namespace App\Http\Requests\Api;

use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseFormRequest extends FormRequest
{
    use ApiResponse;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    protected function failedValidation(Validator $validator)
    {
        // Lempar langsung dalam bentuk HttpResponseException bawaan Laravel
        throw new HttpResponseException(
            $this->errorResponse(
                'Validasi gagal.', // Message utama
                422,               // HTTP Code
                $validator->errors() // Detail error kolom
            )
        );
    }
}
