<?php

namespace App\Services\Internal;

use GeoIp2\Database\Reader;

class GeoIPService extends BaseService
{
    public function getRecordFromIP($ipAddress): ?\GeoIp2\Model\City
    {
        return cache()->remember('geoip|ip:' . $ipAddress, 1440, function () use ($ipAddress) {
            $GEOReader = new Reader(storage_path('files/geoip/GeoLite2-City.mmdb-data'));

            if (!in_array(config('app.env'), ['production', 'staging'])) {
                $ipAddress = '45.118.132.8';
            }

            try {
                return $GEOReader->city($ipAddress);
            } catch (\Exception $e) {
                return null;
            }
        });
    }
}
