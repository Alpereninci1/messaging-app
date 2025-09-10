<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Message;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = [
            ['to' => '+905551111111', 'content' => 'Hoş geldiniz! Insider projesi mesajı.'],
            ['to' => '+905552222222', 'content' => 'Laravel Queue sistemi test mesajı.'],
            ['to' => '+905553333333', 'content' => 'Bu mesaj, Insider API için gönderim denemesidir.'],
            ['to' => '+905554444444', 'content' => 'Merhaba! Kuyruk sistemi doğru çalışıyor mu?'],
            ['to' => '+905555555555', 'content' => 'Son test mesajı, hazırız!'],
            ['to' => '+905556666666', 'content' => 'Redis cache sistemi test mesajı.'],
            ['to' => '+905557777777', 'content' => 'Swagger API dokümantasyonu test mesajı.'],
            ['to' => '+905558888888', 'content' => 'Docker containerization test mesajı.'],
            ['to' => '+905559999999', 'content' => 'Repository Pattern implementasyonu test mesajı.'],
            ['to' => '+905550000000', 'content' => 'Service Layer architecture test mesajı.'],
            ['to' => '+905551111110', 'content' => 'Laravel 10.x framework test mesajı.'],
            ['to' => '+905552222221', 'content' => 'RESTful API standartları test mesajı.'],
            ['to' => '+905553333332', 'content' => 'Unit ve Integration testler mesajı.'],
            ['to' => '+905554444443', 'content' => 'Database migration test mesajı.'],
            ['to' => '+905555555554', 'content' => 'Webhook.site entegrasyonu test mesajı.'],
            ['to' => '+905556666665', 'content' => '202 Response Code handling test mesajı.'],
            ['to' => '+905557777776', 'content' => 'Message Response Object test mesajı.'],
            ['to' => '+905558888887', 'content' => 'CORS middleware test mesajı.'],
            ['to' => '+905559999998', 'content' => 'Predis Redis client test mesajı.'],
            ['to' => '+905550000009', 'content' => 'Production-ready deployment test mesajı.'],
            ['to' => '+905551111100', 'content' => 'Performance optimization test mesajı.'],
            ['to' => '+905552222201', 'content' => 'Clean Code principles test mesajı.'],
            ['to' => '+905553333302', 'content' => 'OOP yaklaşımı test mesajı.'],
            ['to' => '+905554444403', 'content' => 'Design patterns test mesajı.'],
            ['to' => '+905555555504', 'content' => 'Laravel best practices test mesajı.'],
        ];

        foreach ($messages as $msg) {
            Message::create($msg);
        }
    }
}
