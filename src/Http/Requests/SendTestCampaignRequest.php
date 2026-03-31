<?php

namespace Mydnic\Kanpen\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendTestCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
