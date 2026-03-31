<?php

namespace Mydnic\Kanpen\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriberRequest extends FormRequest
{
    protected $errorBag = 'subscribers';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'email', 'unique:'.config('kanpen.tables.subscribers').',email'],
        ];
    }
}
