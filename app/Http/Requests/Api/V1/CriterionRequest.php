<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CriterionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        // Pastikan hanya user dengan role 'owner' yang bisa mengubah kriteria
        return $this->user() && $this->user()->role === 'owner';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        // Jika method POST (Tambah Data Baru)
        if ($this->isMethod('post')) {
            return [
                'name' => 'required|string|max:255|unique:criteria,name',
                'type' => 'required|in:cost,benefit',
                'weight_input' => 'required|integer|min:1|max:5',
            ];
        }

        // Jika method PUT/PATCH (Update Data)
        return [
            'name' => 'sometimes|required|string|max:255|unique:criteria,name,' . $this->route('criterion'),
            'type' => 'sometimes|required|in:cost,benefit',
            'weight_input' => 'sometimes|required|integer|min:1|max:5',
        ];
    }
}
