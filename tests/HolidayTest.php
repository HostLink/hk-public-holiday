<?php

namespace HostLink\Calendar\Tests;

use HostLink\Calendar\Holiday;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class HolidayTest extends TestCase
{
    private static array $sampleData = [
        ["date" => "2024-01-01", "name" => "The first day of January"],
        ["date" => "2024-04-04", "name" => "Ching Ming Festival"],
        ["date" => "2024-12-25", "name" => "Christmas Day"],
    ];

    private function makeHoliday(string $lang, array $data): Holiday
    {
        $cache = new ArrayAdapter();
        $item = $cache->getItem("hk-holidays-$lang");
        $item->set($data);
        $cache->save($item);
        return new Holiday($lang, $cache);
    }

    // --- Constructor ---

    public function testInvalidLanguageThrows(): void
    {
        $this->expectException(\Exception::class);
        new Holiday("fr");
    }

    public function testAllSupportedLanguagesAreAccepted(): void
    {
        $cache = new ArrayAdapter();
        foreach (["en", "tc", "sc"] as $lang) {
            $this->assertInstanceOf(Holiday::class, new Holiday($lang, $cache));
        }
    }

    // --- isHoliday ---

    public function testIsHolidayReturnsTrueForKnownDate(): void
    {
        $holiday = $this->makeHoliday("en", self::$sampleData);
        $this->assertTrue($holiday->isHoliday("2024-12-25"));
    }

    public function testIsHolidayReturnsFalseForNonHolidayDate(): void
    {
        $holiday = $this->makeHoliday("en", self::$sampleData);
        $this->assertFalse($holiday->isHoliday("2024-12-01"));
    }

    // --- getRange ---

    public function testGetRangeReturnsHolidaysWithinBounds(): void
    {
        $holiday = $this->makeHoliday("en", self::$sampleData);
        $result = $holiday->getRange("2024-01-01", "2024-06-30");
        $this->assertCount(2, $result);
        $this->assertEquals("2024-01-01", $result[0]["date"]);
        $this->assertEquals("2024-04-04", $result[1]["date"]);
    }

    public function testGetRangeIncludesBoundaryDates(): void
    {
        $holiday = $this->makeHoliday("en", self::$sampleData);
        $result = $holiday->getRange("2024-12-25", "2024-12-25");
        $this->assertCount(1, $result);
        $this->assertEquals("2024-12-25", $result[0]["date"]);
    }

    public function testGetRangeReturnsEmptyWhenNoHolidaysMatch(): void
    {
        $holiday = $this->makeHoliday("en", self::$sampleData);
        $result = $holiday->getRange("2024-07-01", "2024-11-30");
        $this->assertEmpty($result);
    }

    // --- clearCache ---

    public function testClearCacheInvalidatesStoredData(): void
    {
        $cache = new ArrayAdapter();
        $item = $cache->getItem("hk-holidays-en");
        $item->set(self::$sampleData);
        $cache->save($item);

        $holiday = new Holiday("en", $cache);
        $holiday->clearCache();

        $this->assertFalse($cache->getItem("hk-holidays-en")->isHit());
    }

    public function testClearCacheOnlyAffectsCurrentLanguage(): void
    {
        $cache = new ArrayAdapter();
        foreach (["en", "tc"] as $lang) {
            $item = $cache->getItem("hk-holidays-$lang");
            $item->set(self::$sampleData);
            $cache->save($item);
        }

        (new Holiday("en", $cache))->clearCache();

        $this->assertFalse($cache->getItem("hk-holidays-en")->isHit());
        $this->assertTrue($cache->getItem("hk-holidays-tc")->isHit());
    }
}
