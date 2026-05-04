<?php

namespace App\Http\Requests\Engagements;

use App\Enums\DecisionWorkflow;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkflowDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('decideWorkflow', $this->route('engagement')) ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'decision' => [
                'required',
                'string',
                Rule::in([
                    DecisionWorkflow::Valide->value,
                    DecisionWorkflow::Rejete->value,
                    DecisionWorkflow::Retourne->value,
                ]),
            ],
            'commentaire' => [
                'nullable',
                'string',
                'max:5000',
                Rule::requiredIf(fn () => in_array($this->input('decision'), [
                    DecisionWorkflow::Rejete->value,
                    DecisionWorkflow::Retourne->value,
                ], true)),
            ],
        ];
    }
}
