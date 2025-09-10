<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\MessageCacheService;
use App\Http\Responses\MessageResponse;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="Insider Messaging API",
 *     version="1.0.0",
 *     description="API for automated message sending system"
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local development server (port 8000)"
 * )
 */
class MessageController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/messages/sent",
     *   summary="List sent messages (external message IDs)",
     *   description="Returns a list of external message IDs for successfully sent messages",
     *   tags={"Messages"},
     *   @OA\Response(
     *     response=200,
     *     description="Successfully retrieved sent message IDs",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="message", type="string", example="Messages retrieved successfully"),
     *       @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(
     *           type="string",
     *           example="webhook-68c080d5bff20"
     *         )
     *       ),
     *       @OA\Property(
     *         property="meta",
     *         type="object",
     *         @OA\Property(property="total", type="integer", example=5),
     *         @OA\Property(property="from_database", type="integer", example=3),
     *         @OA\Property(property="from_cache", type="integer", example=2),
     *         @OA\Property(property="cache_ratio", type="number", example=66.67)
     *       ),
     *       @OA\Property(property="timestamp", type="string", example="2025-09-09T19:32:37.786252Z")
     *     )
     *   ),
     *   @OA\Response(
     *     response=500,
     *     description="Internal server error"
     *   )
     * )
     */
    public function sent(MessageRepositoryInterface $repo, MessageCacheService $cacheService): JsonResponse
    {
        try {
            $dbIds = $repo->listSentExternalIds();

            $cachedIds = $cacheService->getCachedMessageIds();


            $allIds = collect($dbIds)->merge($cachedIds)->unique()->values();

            if ($allIds->isEmpty()) {
                return MessageResponse::noData();
            }

            $meta = [
                'total' => $allIds->count(),
                'from_database' => $dbIds->count(),
                'from_cache' => count($cachedIds),
                'cache_ratio' => $dbIds->count() > 0 ? round((count($cachedIds) / $dbIds->count()) * 100, 2) : 0
            ];


            return MessageResponse::sentMessages($allIds->toArray(), $meta);

        } catch (\Exception $e) {
            return MessageResponse::error('Failed to retrieve sent messages: ' . $e->getMessage());
        }
    }
}
