<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google Gemini API Key
    |--------------------------------------------------------------------------
    |
    | Here you may specify your Google Gemini API Key. This will be
    | used to authenticate with the Google Gemini API. You can find your API key
    | in the Google AI Studio at https://aistudio.google.com/
    */

    'api_key' => env('GEMINI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Google Gemini Model
    |--------------------------------------------------------------------------
    |
    | Here you may specify which Google Gemini model to use for generating
    | responses. Common options include 'gemini-pro', 'gemini-1.5-pro',
    | 'gemini-1.5-flash', etc.
    */

    'model' => env('GEMINI_MODEL', 'models/gemini-pro'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 30 seconds.
    */

    'request_timeout' => env('GEMINI_REQUEST_TIMEOUT', 30),

];