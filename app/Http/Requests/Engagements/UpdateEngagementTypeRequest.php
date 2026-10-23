<?php

namespace App\Http\Requests\Engagements;

use Illuminate\Validation\Rule;

class UpdateEngagementTypeRequest extends StoreEngagementTypeRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('engagement_type')) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['code'] = ['required', 'string', 'max:50', Rule::unique('engagement_types', 'code')->ignore($this->route('engagement_type')?->id)];

        return $rules;
    }
}
