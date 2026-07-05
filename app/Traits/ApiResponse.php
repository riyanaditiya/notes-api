<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = [], string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'status'  => 'Success',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function error(string $message = 'Error', int $code = 400, $data = null)
    {
        return response()->json([
            'status'  => 'Error',
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
}