<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BaseController extends Controller
{
    // response template
    public function rst($success = false, $statusCode =  500,$message = null, $errors = null, $data = null) {
        $response = [
            "success" => $success,
            "message" => $message,
            "errors" => $errors,
        ];

        if ($data != null) $response += $data;

        return response()->json($response, $statusCode);
    }
}
