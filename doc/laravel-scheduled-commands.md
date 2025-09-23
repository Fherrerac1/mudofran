# Scheduled Artisan Commands in Laravel

This document explains how custom Artisan commands are defined and scheduled for recurring execution in a Laravel application.

## Overview

The following PHP code defines and schedules custom Artisan commands:

```php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
```

These facades allow you to:

-   Register new Artisan commands (`Artisan::command`)
-   Log events (`Log::info`)
-   Schedule recurring tasks (`Schedule::command`)

---

## 1. Manual Command: `inspire`

```php
Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
```

### Description:

This defines a custom Artisan command called `inspire`. When executed, it outputs a randomly chosen inspirational quote to the console.

### Notes:

-   The quote is retrieved from `\Illuminate\Foundation\Inspiring::quote()`.
-   The command is given a purpose: `"Display an inspiring quote"`.
-   It is scheduled to run **hourly** using Laravel's task scheduler, though the actual scheduling needs `php artisan schedule:run` to be triggered periodically (usually via cron).

---

## 2. Scheduled Command: `facturas:generar-recurrentes`

```php
Schedule::command('facturas:generar-recurrentes')
    ->everyMinute()
    ->before(function () {
        Log::info('Comando facturas:generar-recurrentes está por ejecutarse a las ' . now());
    })
    ->after(function () {
        Log::info('Comando facturas:generar-recurrentes finalizó a las ' . now());
    });
```

### Description:

This schedules the command `facturas:generar-recurrentes` to run **every minute**.

### Pre- and Post-Execution Logging:

-   **Before Execution:** Logs a message before the command starts.
-   **After Execution:** Logs a message after the command finishes.

This helps with monitoring and debugging automated tasks.

---

## Laravel Scheduler Setup

To ensure scheduled tasks actually run, make sure your system cron runs the Laravel scheduler:

```bash
* * * * * php /path/to/your/artisan schedule:run >> /dev/null 2>&1
example cd /home/elayudante/domains/mudo2.es/public_html && php artisan schedule:run >> /dev/null 2>&1

```

---

## Conclusion

This script demonstrates how to:

-   Register a simple custom Artisan command
-   Schedule recurring commands
-   Add logging before and after command execution for visibility
