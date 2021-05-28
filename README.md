## About laravel-scout-refresh-command

Refresh all your models containing the `searchable` trait

### Installation

**1. Install with composer**

```
composer require tarre/laravel-scout-refresh
```

**2. Add class to `App\Console\Kernel.php`**

```php
protected $commands = [
    \Tarre\LaravelScoutRefresh\Console\ScoutRefreshAllCommand::class
];
```

### Usage

``
php artisan scout:refresh-all
``
