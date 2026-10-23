<?php

namespace App\Http\Requests\Rbm;

use App\Services\Rbm\PapImportService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePapImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        // Le mode remplacement exige la permission dediee.
        if ($this->input('mode') === 'remplacement') {
            return $user->can('pap.replace') || $user->can('rbm.admin');
        }

        return $user->can('pap.import') || $user->can('rbm.admin');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'exercice_budgetaire_id' => ['required', 'integer', 'exists:exercices_budgetaires,id'],
            'version' => ['nullable', 'string', 'max:30'],
            'mode' => ['required', Rule::in(PapImportService::MODES)],
            'source' => ['required', Rule::in(['document', 'fichier'])],
            'fichier' => ['required_if:source,fichier', 'nullable', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'confirmation' => [Rule::requiredIf($this->input('mode') === 'remplacement'), 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirmation.accepted' => __('Vous devez confirmer explicitement le remplacement des donnees PAP existantes.'),
            'confirmation.required' => __('Vous devez confirmer explicitement le remplacement des donnees PAP existantes.'),
        ];
    }
}
