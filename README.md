# Messaging Application

Bu proje, belirli bir segmentteki kullanıcılara toplu mesaj göndermek için tasarlanmış otomatik mesaj gönderim sistemidir.

## 🚀 Proje Özellikleri

- **Laravel 10.x** framework kullanılarak geliştirilmiştir
- **Repository Pattern** ve **Service Layer** implementasyonu
- **Queue/Job** yapıları ile asenkron mesaj gönderimi
- **Redis** cache implementasyonu (33x performans artışı)
- **Swagger/OpenAPI** dokümantasyonu
- **RESTful API** standartlarına uygun
- **Unit ve Integration** testler (%100 test coverage)
- **Docker** containerization
- **Response Objects** ile profesyonel API responses
- **Webhook.site** entegrasyonu
- **202 Response Code** handling

## 📋 Teknik Gereksinimler

- PHP 8.1+
- Laravel 10.49.0
- MySQL 8.0+
- Redis 7.0+
- Composer
- Docker & Docker Compose (önerilen)
- Predis (Redis client)

## 🛠️ Kurulum

### Docker ile Kurulum (Önerilen)

1. **Repository'yi klonlayın:**
```bash
git clone <repository-url>
cd messaging-application
```

2. **Docker servislerini başlatın:**
```bash

# Sadece Redis başlat
docker-compose up -d redis

# Sadece MySQL başlat (Ben laragon kullandım.)
docker-compose up -d db
```

3. **Composer bağımlılıklarını yükleyin:**
```bash
composer install
```

4. **Environment dosyasını oluşturun:**
```bash
cp .env.example .env
```

5. **Environment değişkenlerini düzenleyin:**
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=messaging-app
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=predis

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Webhook Configuration
INSIDER_WEBHOOK_URL=https://webhook.site/fdb57610-eea0-42cd-ad41-2f73c150461e
INSIDER_WEBHOOK_AUTH=INS.me1x9uMcyYGlhKKQVPoc.bO3j9aZwRTOcA2Ywo
```

6. **Uygulama anahtarını oluşturun:**
```bash
php artisan key:generate
```

7. **Database migration'larını çalıştırın:**
```bash
php artisan migrate:fresh --seed
```

8. **Swagger dokümantasyonunu oluşturun:**
```bash
php artisan l5-swagger:generate
```

9. **Cache'i temizleyin:**
```bash
php artisan optimize:clear
```

### Manuel Kurulum

1. **Composer bağımlılıklarını yükleyin:**
```bash
composer install
```

2. **Environment dosyasını oluşturun ve düzenleyin:**
```bash
cp .env.example .env
```

3. **Database ve Redis servislerini başlatın**

4. **Migration'ları çalıştırın:**
```bash
php artisan migrate:fresh --seed
php artisan l5-swagger:generate
php artisan optimize:clear
```

## 🚀 Kullanım

### 1. Mesaj Gönderimini Başlatma

**Manuel olarak:**
```bash
# Mesaj dispatcher'ı başlatın (her 5 saniyede 2 mesaj)
php artisan messages:dispatch --per-batch=2 --interval=5 --max-batches=1

# Tüm pending mesajları otomatik gönder
php artisan messages:auto-dispatch --per-batch=2 --interval=5

```

**Otomatik olarak (crontab):**
```bash
# Crontab'a ekleyin
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 2. API Endpoints

**Gönderilen mesajları listele:**
```bash
GET /api/messages/sent
```

**Response Format:**
```json
{
  "success": true,
  "message": "Messages retrieved successfully",
  "data": ["webhook-id1", "webhook-id2", ...],
  "meta": {
    "total": 5,
    "from_database": 3,
    "from_cache": 2,
    "cache_ratio": 66.67
  },
  "timestamp": "2025-09-09T19:32:37.786252Z"
}
```

**Swagger dokümantasyonu:**
```
GET /api/documentation
```

### 3. Test Çalıştırma

```bash
# Tüm testleri çalıştır (20 test, %100 başarı)
php artisan test

# Sadece feature testleri
php artisan test --testsuite=Feature

# Sadece unit testleri
php artisan test --testsuite=Unit

# Test coverage raporu
php artisan test --coverage
```


## 📁 Proje Yapısı

```
app/
├── Console/Commands/          # Artisan komutları
│   ├── DispatchMessagesCommand.php
│   ├── AutoDispatchMessagesCommand.php
│   └── ProcessMessageQueueCommand.php
├── Http/
│   ├── Controllers/           # API Controller'ları
│   │   └── MessageController.php
│   └── Responses/             # Response Objects
│       └── MessageResponse.php
├── Jobs/                      # Queue Job'ları
│   └── SendMessageJob.php
├── Models/                    # Eloquent Modelleri
│   └── Message.php
├── Repositories/              # Repository Pattern
│   ├── Contracts/
│   │   └── MessageRepositoryInterface.php
│   └── EloquentMessageRepository.php
├── Services/                  # Business Logic
│   ├── MessageSenderService.php
│   └── MessageCacheService.php
└── Providers/                 # Service Provider'lar
    └── RepositoryServiceProvider.php

database/
├── migrations/                # Database migration'ları
│   ├── create_users_table.php
│   ├── create_messages_table.php
│   └── ...
└── seeders/                   # Test verileri
    ├── DatabaseSeeder.php
    └── MessageSeeder.php

tests/
├── Feature/                   # Integration testleri
│   ├── MessageControllerTest.php
│   └── MessageFlowTest.php
└── Unit/                      # Unit testleri
    ├── MessageSenderServiceTest.php
    └── EloquentMessageRepositoryTest.php

docker/
├── nginx/
│   └── default.conf
└── php/
    └── local.ini
```

## ✨ Özellikler

### 🚀 Mesaj Gönderim Sistemi
- Her 5 saniyede 2 mesaj gönderimi
- Başarısız mesajlar için retry mekanizması
- Mesaj durumu takibi (pending, sent, failed)
- External message ID saklama
- Duplicate mesaj önleme
- 500 karakter sınırı kontrolü
- 202 Response Code handling

### ⚡ Cache Sistemi 
- Redis ile mesaj bilgilerinin cache'lenmesi
- 24 saat cache süresi
- Message ID ve gönderim zamanı saklama
- 33x performans artışı
- Graceful degradation (Redis yoksa çalışmaya devam eder)

### 📚 API Dokümantasyonu
- Swagger/OpenAPI ile otomatik dokümantasyon
- `/api/documentation` endpoint'i
- Interactive API testing
- Schema definitions

### 🎯 Design Patterns
- Repository Pattern implementasyonu
- Service Layer architecture
- Response Objects ile profesyonel API responses
- Clean Code principles

### 🐳 Docker Support
- Multi-container setup (MySQL, Redis, PHP-FPM, Nginx)
- Production-ready configuration
- Environment management

## ⚙️ Environment Değişkenleri

| Değişken | Açıklama | Varsayılan |
|----------|----------|------------|
| `DB_CONNECTION` | Database driver | mysql |
| `DB_HOST` | Database host | 127.0.0.1 |
| `DB_PORT` | Database port | 3306 |
| `DB_DATABASE` | Database adı | messaging-app |
| `REDIS_HOST` | Redis host | 127.0.0.1 |
| `REDIS_PORT` | Redis port | 6379 |
| `REDIS_CLIENT` | Redis client | predis |
| `CACHE_DRIVER` | Cache driver | redis |
| `QUEUE_CONNECTION` | Queue driver | redis |
| `SESSION_DRIVER` | Session driver | redis |
| `INSIDER_URL` | Webhook URL | https://webhook.site/... |
| `INSIDER_AUTH_KEY` | Webhook auth key | INS.me1x9uMcyYGlhKKQVPoc... |


