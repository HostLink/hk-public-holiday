# Copilot Instructions

## Project

PHP Composer library (`hostlink/hk-public-holiday`) for querying Hong Kong public holidays. Requires PHP >= 8.0.

## Setup

```bash
composer install
```

No test suite or linter is currently configured.

## Architecture

The entire library is a single class: `HostLink\Calendar\Holiday` in `src/Holiday.php`.

**PSR-4 autoload:** `HostLink\Calendar\` → `src/`

**Data flow:**
1. On first call to `getData()`, the class fetches JSON from `https://raw.githubusercontent.com/mathsgod/holiday-data/refs/heads/main/data/{language}.json`
2. The response is stored in a PSR-6 cache under the key `hk-holidays-{language}`, expiring after 30 days
3. Subsequent calls return the cached data

**Cache injection:** The constructor accepts an optional `CacheItemInterface` (PSR-6 pool). If omitted, it defaults to `Symfony\Component\Cache\Adapter\FilesystemAdapter`.

## Key Conventions

- **Languages:** Only `en`, `tc` (Traditional Chinese), and `sc` (Simplified Chinese) are valid; the constructor throws on any other value.
- **Date format:** All date parameters (`isHoliday`, `getRange`) use `YYYY-MM-DD` strings; comparisons are string equality or `strtotime`.
- **Cache key pattern:** `hk-holidays-{language}` — one cache entry per language.
- **Holiday data structure:** Each entry in the JSON array has at minimum `"date"` and `"name"` keys.
