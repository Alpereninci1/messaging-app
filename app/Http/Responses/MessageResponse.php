<?php

namespace App\Http\Responses;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MessageResponse
{
    /**
     * Başarılı mesaj listesi response'u
     */
    public static function sentMessages(array $data, array $meta): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data' => $data,
            'meta' => $meta,
            'timestamp' => Carbon::now()->toDateTimeString()
        ], Response::HTTP_OK);
    }

    /**
     * Hata response'u
     */
    public static function error(string $message, int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'meta' => null,
            'timestamp' => Carbon::now()->toDateTimeString()
        ], $statusCode);
    }


    /**
     * Boş veri response'u
     */
    public static function noData(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'No messages found',
            'data' => [],
            'meta' => [
                'total' => 0,
                'from_database' => 0,
                'from_cache' => 0
            ],
            'timestamp' => Carbon::now()->toDateTimeString()
        ], Response::HTTP_OK);
    }

    public static function sendResponse($status,$body): array
    {
        return [
            'status' => $status,
            'body' => $body,

        ];
    }
}
