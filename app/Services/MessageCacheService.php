<?php

namespace App\Services;

use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class MessageCacheService
{
    private const CACHE_PREFIX = 'message:';
    private const CACHE_TTL = 86400; // 24 saat

    public function cacheMessageData(Message $message, string $externalMessageId): void
    {
        $cacheKey = self::CACHE_PREFIX . $message->id;

        $cacheData = [
            'message_id' => $externalMessageId,
            'sent_at' => Carbon::now()->toDateTimeString(),
        ];

        Log::info('Redis cache keys: ' . json_encode($cacheData));

        Cache::store('redis')->put($cacheKey, $cacheData, self::CACHE_TTL);
    }


    public function getCachedMessageIds(): array
    {
        $cachedIds = [];

        try {
            $sentMessages = Message::where('status', 'sent')
                ->whereNotNull('external_message_id')
                ->get();

            foreach ($sentMessages as $message) {
                $cacheKey = self::CACHE_PREFIX . $message->id;
                $data = Cache::store('redis')->get($cacheKey);

                if ($data && isset($data['external_message_id'])) {
                    $cachedIds[] = $data['external_message_id'];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Redis cache keys error: ' . $e->getMessage());
        }

        return $cachedIds;
    }
}
