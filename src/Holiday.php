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
            $data = file_get_contents("https://www.1823.gov.hk/common/ical/$language.json");
            //clean the file bom and utf8
            $data = preg_replace('/\x{FEFF}/u', '', $data);
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

            $item->set($data["vcalendar"][0]);

            $this->cache->save($item);
        }

        return $item->get();
    }

    public function isHoliday(string $date): bool
    {
        $data = $this->getData();
        foreach ($data["vevent"] as $holiday) {
            $s = $holiday["dtstart"][0];
            $s_date = substr($s, 0, 4) . "-" . substr($s, 4, 2) . "-" . substr($s, 6, 2);
            if ($s_date == $date) {
                return true;
            }
        }
        return false;
    }


    public function getRange(string $from, string $to)
    {

        $data = $this->getData();
        $holidays = [];
        foreach ($data["vevent"] as $holiday) {
            $s = $holiday["dtstart"][0];

            $s_date = substr($s, 0, 4) . "-" . substr($s, 4, 2) . "-" . substr($s, 6, 2);
            $s_time = strtotime($s_date);

            if ($s_time >= strtotime($from) && $s_time <= strtotime($to)) {
                $holidays[] = [
                    "id" => $holiday["uid"],
                    "date" => $s_date,
                    "name" => $holiday["summary"]
                ];
            }
        }
        return $holidays;
    }
}
