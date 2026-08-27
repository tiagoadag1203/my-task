<?php

namespace App\Http\Requests;

use App\Enums\SprintStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSprintStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::in([
                    SprintStatus::Active->value,
                    SprintStatus::Finished->value,
                ]),
            ],
        ];
    }
}
