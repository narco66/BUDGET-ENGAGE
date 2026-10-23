<?php

namespace App\Http\Requests\Rbm;

use Illuminate\Validation\Rule;

class UpdateChaineResultatRequest extends RbmRootReferentialRequest
{
    public function rules(): array
    {
        $chaine = $this->route('chaine_resultat');

        return array_merge($this->baseRules(), [
            'cadre_logique_id' => ['nullable', 'integer', 'exists:cadres_logiques,id'],
            'niveau' => ['required', 'string', 'max:50'],
            'element_type' => ['nullable', 'string', 'max:255'],
            'element_id' => ['nullable', 'integer'],
            'indicateur_id' => ['nullable', 'integer', 'exists:indicateurs,id'],
            'resultat_attendu' => ['nullable', 'string'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('chaines_resultats', 'code')
                    ->where(fn ($q) => $q->where('cadre_logique_id', $this->input('cadre_logique_id')))
                    ->ignore($chaine),
            ],
        ]);
    }
}
