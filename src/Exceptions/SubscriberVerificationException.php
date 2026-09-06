<?php

namespace Mydnic\Kanpen\Exceptions;

use Exception;
use Illuminate\Http\Response;

class SubscriberVerificationException extends Exception
{
    public function report(): bool
    {
        return true;
    }

    public function render(): Response
    {
        return new Response('', 404);
    }
}
