<?php

namespace App\Traits;



trait ResponseTrait
{

    public function getResponse($data, $message = 'Success')
    {
        $response = [
            'message' => $message,
            'data' => $data,
        ];
        return response()->json($response, 200);
    }

    public function getErrorResponse($message, $status = 404)
    {
        return response()->json([
            'error' => [
                'message' => $message
            ]
        ], $status);

    }
}
