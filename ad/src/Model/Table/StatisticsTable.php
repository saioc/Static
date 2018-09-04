<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use GeoIp2\Database\Reader;

class StatisticsTable extends Table
{
    public function initialize(array $config)
    {
        $this->belongsTo('Users');
        $this->belongsTo('Links');
        $this->addBehavior('Timestamp');
    }

    public function get_country($ip)
    {
        $reader = new Reader(CONFIG.'binary/geoip/GeoLite2-Country.mmdb');

        try {
            $record = $reader->country($ip);
            $countryCode = (trim($record->country->isoCode)) ? $record->country->isoCode : 'Others';
        } catch (\Exception $ex) {
            $countryCode = 'Others';
        }
        return $countryCode;
    }
}
