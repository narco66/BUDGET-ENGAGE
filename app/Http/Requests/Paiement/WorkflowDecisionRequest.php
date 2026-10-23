<?php

namespace App\Http\Requests\Paiement;

use App\Enums\DecisionWorkflow;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class WorkflowDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('paiement.validate') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in([
                DecisionWorkflow::Valide->value,
                DecisionWorkflow::Rejete->value,
                DecisionWorkflow::Retourne->value,
            ])],
            'commentaire' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $decision = $this->input('decision');
            if (in_array($decision, [DecisionWorkflow::Rejete->value, DecisionWorkflow::Retourne->value], true)
                && trim((string) $this->input('commentaire')) === '') {
                $v->errors()->add('commentaire', __('Un motif est obligatoire pour un rejet ou un retour.'));
            }
        });
    }
}
