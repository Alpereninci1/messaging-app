<?php

namespace App\Services;

use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class MessageCacheService
{
    private const CACHE_PREFIX = 'message:';
    private const CACHE_TTL = 86400; // 24 hours

    public function cacheMessageData(Message $message, string $externalMessageId): void
    {
        $cacheKey = self::CACHE_PREFIX . $message->id;

        $cacheData = [
            'external_message_id' => $externalMessageId,
            'sent_at' => now()->toISOString(),
            'message_id' => $message->id,
            'to' => $message->to,
            'content' => $message->content,
        ];

        // Redis store'u direkt kullan
        Cache::store('redis')->put($cacheKey, $cacheData, self::CACHE_TTL);
    }


    public function getCachedMessageIds(): array
    {
        $cachedIds = [];

        try {
            // Laravel cache'de key'ler hash'leniyor, bu yüzden direkt key'leri arayamıyoruz
            // Bunun yerine bilinen mesaj ID'lerini kontrol edelim
            $sentMessages = \App\Models\Message::where('status', 'sent')
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
            // Hata durumunda sessizce devam et
            \Log::warning('Redis cache keys error: ' . $e->getMessage());
        }

        return $cachedIds;
    }
}
