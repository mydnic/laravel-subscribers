<?php

namespace Mydnic\Kanpen\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Mydnic\Kanpen\Models\Subscriber;

class VerifySubscriberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'email' => 'required|email|exists:subscribers,email',
        ];
    }

    public function subscriber()
    {
        return Subscriber::where('email', $this->input('email'))->firstOrFail();
    }
}
