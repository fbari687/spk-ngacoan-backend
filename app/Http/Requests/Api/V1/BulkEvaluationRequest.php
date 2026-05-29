<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BulkEvaluationRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->role === 'owner';
    }

    public function rules()
    {
        return [
            // Memastikan data yang dikirim bernama 'evaluations' dan berupa array
            'evaluations' => 'required|array|min:1',

            // Memvalidasi setiap isi dari array tersebut
            'evaluations.*.supplier_id' => 'required|integer|exists:suppliers,id',
            'evaluations.*.criterion_id' => 'required|integer|exists:criteria,id',
            'evaluations.*.actual_value' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'evaluations.*.exists' => 'Data supplier atau kriteria tidak valid.',
            'evaluations.*.numeric' => 'Nilai aktual harus berupa angka.',
        ];
    }
}
