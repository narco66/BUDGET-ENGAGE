<?php

namespace App\Http\Requests\Rbm;

use Illuminate\Foundation\Http\FormRequest;

class StoreRbmImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (
            $user->can('rbm.import')
            || $user->can('rbm.admin')
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'fichier' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ];
    }
}
