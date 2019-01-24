<?php

namespace Mydnic\Subscribers\Http\Requests;

use Mydnic\Subscribers\Subscriber;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email|exists:subscribers,email',
        ];
    }

    public function subscriber()
    {
        return once(function () {
            return Subscriber::where('email', $this->input('email'))->first();
        });
    }
}
