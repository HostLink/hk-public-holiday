<?php

namespace HostLink\Calendar;

use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Holiday
{

    protected $language;
    protected $cache;
    public function __construct(string $language = "en", CacheItemInterface $cache = null)
    {
        //language only support "en","tc" and "sc"
        if (!in_array($language, ["en", "tc", "sc"])) {
            throw new \Exception("Language not supported");
        }

        $this->language = $language;

        $this->cache = $cache;
        if (!$cache) {
            $this->cache = new FilesystemAdapter();
        }
    }

    public function clearCache()
    {
        $this->cache->delete("hk-holidays-" . $this->language);
    }

    public function getData(): array
    {
        $language = $this->language;

        $item = $this->cache->getItem("hk-holidays-$language");

        if (!$item->isHit()) {

            //cache for 1 month
            $item->expiresAfter(3600 * 24 * 30);

            //download from the internet
            $data = file_get_contents("https://raw.githubusercontent.com/mathsgod/holiday-data/refs/heads/main/data/$language.json");
            $data = json_decode($data, true);
            $item->set($data);

            $this->cache->save($item);
        }

        return $item->get();
    }

    public function isHoliday(string $date): bool
    {
        $data = $this->getData();
        foreach ($data as  $holiday) {
            if ($holiday["date"] == $date) {
                return true;
            }
        }
        return false;
    }


    public function getRange(string $from, string $to)
    {

        $data = $this->getData();
        $holidays = [];
        foreach ($data as $holiday) {
            $s_time = strtotime($holiday["date"]);

            if ($s_time >= strtotime($from) && $s_time <= strtotime($to)) {
                $holidays[] = $holiday;
            }
        }
        return $holidays;
    }
}
