# HK Public Holiday

This library provides functionality to check Hong Kong public holidays and manage holiday data caching.

## Installation

To install the library, use Composer:

```bash
composer require hostlink/hk-public-holiday
```

## Usage

### Initialization

To initialize the `Holiday` class, you can specify the language (`en`, `tc`, `sc`) and an optional cache instance.

```php
use HostLink\Calendar\Holiday;

//en: English, tc: Traditional Chinese, sc: Simplified Chinese
$holiday = new Holiday("en");
```

### Methods

#### `clearCache()`

Clears the cached holiday data.

```php
$holiday->clearCache();
```

#### `getData()`

Fetches the holiday data. If the data is not cached, it will download it from the internet and cache it for one month.

```php
$data = $holiday->getData();
```

#### `isHoliday(string $date): bool`

Checks if a given date is a holiday.

```php
$isHoliday = $holiday->isHoliday("2023-12-25");
```

#### `getRange(string $from, string $to)`

Gets the holidays within a specified date range.

```php
$holidays = $holiday->getRange("2023-01-01", "2023-12-31");
```

### Example

```php
use HostLink\Calendar\Holiday;

$holiday = new Holiday("en");

// Check if a specific date is a holiday
if ($holiday->isHoliday("2023-12-25")) {
    echo "It's a holiday!";
} else {
    echo "It's not a holiday.";
}

// Get holidays within a date range
$holidays = $holiday->getRange("2023-01-01", "2023-12-31");
foreach ($holidays as $holiday) {
    echo $holiday["date"] . ": " . $holiday["name"] . "\n";
}
```

## License

This project is licensed under the MIT License.
