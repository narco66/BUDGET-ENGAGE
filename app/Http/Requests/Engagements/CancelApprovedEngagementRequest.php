<?php

namespace App\Http\Requests\Engagements;

use Illuminate\Foundation\Http\FormRequest;

class CancelApprovedEngagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cancelApproved', $this->route('engagement')) ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'motif_annulation' => ['required', 'string', 'max:5000'],
        ];
    }
}
