# HK Public Holiday

[![Tests](https://github.com/HostLink/hk-public-holiday/actions/workflows/tests.yml/badge.svg)](https://github.com/HostLink/hk-public-holiday/actions/workflows/tests.yml)

This library provides functionality to check Hong Kong public holidays and manage holiday data caching.

## Requirements

- PHP >= 8.1
- `symfony/cache` ^6.4, ^7.0, or ^8.0

## Installation

To install the library, use Composer:

```bash
composer require hostlink/hk-public-holiday
```

## Usage

### Initialization

To initialize the `Holiday` class, you can specify the language (`en`, `tc`, `sc`) and an optional PSR-6 cache pool.

```php
use HostLink\Calendar\Holiday;

// en: English, tc: Traditional Chinese, sc: Simplified Chinese
$holiday = new Holiday("en");
```

By default, a `FilesystemAdapter` is used to cache holiday data. You can inject any PSR-6 `CacheItemPoolInterface` implementation:

```php
use HostLink\Calendar\Holiday;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

$holiday = new Holiday("en", new ArrayAdapter());
```

### Methods

#### `clearCache()`

Clears the cached holiday data for the current language.

```php
$holiday->clearCache();
```

#### `getData()`

Fetches the holiday data. If not cached, downloads it from the internet and caches it for one month.

```php
$data = $holiday->getData();
```

#### `isHoliday(string $date): bool`

Checks if a given date (in `YYYY-MM-DD` format) is a public holiday.

```php
$isHoliday = $holiday->isHoliday("2023-12-25");
```

#### `getRange(string $from, string $to): array`

Gets all public holidays within a date range (inclusive, `YYYY-MM-DD` format).

```php
$holidays = $holiday->getRange("2023-01-01", "2023-12-31");
```

### Example

```php
use HostLink\Calendar\Holiday;

$holiday = new Holiday("en");

if ($holiday->isHoliday("2023-12-25")) {
    echo "It's a holiday!";
} else {
    echo "It's not a holiday.";
}

$holidays = $holiday->getRange("2023-01-01", "2023-12-31");
foreach ($holidays as $h) {
    echo $h["date"] . ": " . $h["name"] . "\n";
}
```

## License

This project is licensed under the MIT License.
