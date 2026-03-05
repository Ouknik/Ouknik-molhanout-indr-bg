# 🚀 Molhanout Real-Time System Setup Guide

## Overview

This guide covers setting up real-time features using **Laravel Broadcasting** with **Pusher** (or self-hosted **Laravel Reverb**).

---

## 📋 Quick Setup Checklist

### 1. Install Required Packages

```bash
cd backend
composer require pusher/pusher-php-server
```

### 2. Configure Environment Variables

Add to `.env`:

```dotenv
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=eu
PUSHER_SCHEME=https
```

### 3. Get Pusher Credentials

1. Go to [pusher.com](https://pusher.com) and create a free account
2. Create a new "Channels" app
3. Copy the credentials to your `.env` file
4. Enable client events if needed (for typing indicators, etc.)

---

## 🔧 Laravel Configuration

### Broadcasting Events

The following events are now available:

| Event | Triggered When | Channels |
|-------|----------------|----------|
| `OrderCreated` | Shop publishes order | `orders`, `orders.city.{city}` |
| `OfferSubmitted` | Distributor submits offer | `private-shop.{userId}`, `private-order.{orderId}` |
| `OfferAccepted` | Shop accepts offer | `orders`, `private-distributor.{id}`, `private-order.{id}` |
| `OfferPriceUpdated` | Distributor updates price | `private-shop.{userId}`, `private-order.{orderId}` |
| `DeliveryStatusUpdated` | Delivery status changes | `private-shop.{userId}`, `private-delivery.{id}` |
| `DeliveryLocationUpdated` | Driver location updates | `private-shop.{userId}`, `private-delivery.{id}` |

### Channel Authorization

Private channels require authentication via `/broadcasting/auth` endpoint (auto-configured).

---

## 📱 Flutter Integration

### 1. Add Dependencies

```yaml
# pubspec.yaml
dependencies:
  pusher_channels_flutter: ^2.2.1
  # or for web support
  web_socket_channel: ^2.4.0
```

### 2. Create WebSocket Service

```dart
// lib/core/services/websocket_service.dart
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';

class WebSocketService {
  static final WebSocketService _instance = WebSocketService._internal();
  factory WebSocketService() => _instance;
  WebSocketService._internal();

  late PusherChannelsFlutter _pusher;
  bool _initialized = false;

  Future<void> init({
    required String appKey,
    required String cluster,
    required String authEndpoint,
    required String authToken,
  }) async {
    if (_initialized) return;

    _pusher = PusherChannelsFlutter.getInstance();
    
    await _pusher.init(
      apiKey: appKey,
      cluster: cluster,
      onConnectionStateChange: (currentState, previousState) {
        print('WebSocket: $previousState -> $currentState');
      },
      onError: (message, code, error) {
        print('WebSocket Error: $message ($code)');
      },
      onAuthorizer: (channelName, socketId, options) async {
        // Call your backend to authorize private channels
        final response = await http.post(
          Uri.parse(authEndpoint),
          headers: {
            'Authorization': 'Bearer $authToken',
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: {
            'socket_id': socketId,
            'channel_name': channelName,
          },
        );
        return jsonDecode(response.body);
      },
    );

    await _pusher.connect();
    _initialized = true;
  }

  // Subscribe to public channel (e.g., orders)
  Future<void> subscribePublic(String channelName, Function(dynamic) onEvent) async {
    final channel = await _pusher.subscribe(channelName: channelName);
    channel.bind('*', onEvent);
  }

  // Subscribe to private channel (e.g., shop.123)
  Future<void> subscribePrivate(String channelName, Function(dynamic) onEvent) async {
    final channel = await _pusher.subscribe(channelName: 'private-$channelName');
    channel.bind('*', onEvent);
  }

  Future<void> unsubscribe(String channelName) async {
    await _pusher.unsubscribe(channelName: channelName);
  }

  Future<void> disconnect() async {
    await _pusher.disconnect();
    _initialized = false;
  }
}
```

### 3. Initialize in App

```dart
// In your auth provider or main.dart after login
final wsService = WebSocketService();
await wsService.init(
  appKey: 'YOUR_PUSHER_KEY',
  cluster: 'eu',
  authEndpoint: 'http://mol.o-dev.store/api/v1/broadcasting/auth',
  authToken: userToken,
);

// Shop owner: Listen for new offers
if (user.role == 'shop_owner') {
  wsService.subscribePrivate('shop.${user.id}', (event) {
    if (event['event'] == 'offer.submitted') {
      // Show notification, refresh offers list
      showSnackBar('New offer received!');
    }
  });
}

// Distributor: Listen for new orders
if (user.role == 'distributor') {
  wsService.subscribePublic('orders', (event) {
    if (event['event'] == 'order.created') {
      // Show notification, refresh available orders
      showSnackBar('New order nearby!');
    }
  });
}
```

---

## 🖥️ VPS Production Deployment

### Prerequisites

- Ubuntu 22.04+ VPS
- PHP 8.2+
- MySQL 8+
- Nginx
- Supervisor (for queues)
- SSL Certificate (Let's Encrypt)

### 1. Queue Worker Setup

Real-time events are processed via queues. Set up Supervisor:

```bash
sudo apt install supervisor
```

Create `/etc/supervisor/conf.d/molhanout-worker.conf`:

```ini
[program:molhanout-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/molhanout/backend/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/molhanout/backend/storage/logs/worker.log
stopwaitsecs=3600
```

Start worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start molhanout-worker:*
```

### 2. Storage Link for Images

```bash
cd /var/www/molhanout/backend
php artisan storage:link
```

### 3. Nginx Configuration

Add to your Nginx config for WebSocket proxying:

```nginx
location /app {
    proxy_pass https://ws-eu.pusher.com;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
}
```

### 4. Environment Variables

Update production `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mol.o-dev.store

BROADCAST_DRIVER=pusher
QUEUE_CONNECTION=database

PUSHER_APP_ID=xxx
PUSHER_APP_KEY=xxx
PUSHER_APP_SECRET=xxx
PUSHER_APP_CLUSTER=eu
```

---

## 🧪 Testing Real-Time Events

### Using Tinker

```bash
php artisan tinker

# Test OrderCreated event
$order = App\Models\Order::first();
broadcast(new App\Events\OrderCreated($order));

# Test OfferSubmitted event
$offer = App\Models\Offer::first();
broadcast(new App\Events\OfferSubmitted($offer->load(['order.shop', 'distributor'])));
```

### Using Pusher Debug Console

1. Go to your Pusher app dashboard
2. Click "Debug Console"
3. Trigger events from your app and watch them appear

---

## 🔒 Security Notes

1. **Private Channels**: Always use private channels for user-specific data
2. **Channel Authorization**: The `routes/channels.php` file controls who can access which channels
3. **Rate Limiting**: Consider rate limiting the broadcast endpoint
4. **Token Expiry**: Handle token refresh in Flutter when auth fails

---

## 📊 Monitoring

### Check Queue Status

```bash
php artisan queue:monitor database:default --max=100
```

### View Failed Jobs

```bash
php artisan queue:failed
php artisan queue:retry all
```

### Supervisor Status

```bash
sudo supervisorctl status
```

---

## 🆘 Troubleshooting

### Events Not Broadcasting

1. Check `BROADCAST_DRIVER` is set to `pusher`
2. Verify Pusher credentials are correct
3. Check queue worker is running: `sudo supervisorctl status`
4. Check Laravel logs: `tail -f storage/logs/laravel.log`

### Authentication Failing

1. Verify the auth endpoint returns proper JSON
2. Check CORS settings allow the Flutter app origin
3. Verify Sanctum token is valid

### Connection Dropping

1. Check websocket timeout settings
2. Implement reconnection logic in Flutter
3. Use heartbeat/ping mechanism

---

## 📚 References

- [Laravel Broadcasting Docs](https://laravel.com/docs/11.x/broadcasting)
- [Pusher Channels Docs](https://pusher.com/docs/channels)
- [pusher_channels_flutter Package](https://pub.dev/packages/pusher_channels_flutter)
