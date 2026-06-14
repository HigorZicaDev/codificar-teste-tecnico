<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'priority' => ['sometimes', Rule::enum(TicketPriority::class)],
            'status' => ['sometimes', Rule::enum(TicketStatus::class)],
            'owner_id' => ['sometimes', 'nullable', 'exists:owners,id'],
            'date_start' => ['sometimes', 'date'],
        ];
    }
}
