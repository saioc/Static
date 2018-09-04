<?php
require 'class.IP2Proxy.php';

$db = new \IP2Proxy\Database();
$db->open('./samples/IP2PROXY-IP-PROXYTYPE-COUNTRY-REGION-CITY-ISP.SAMPLE.BIN', \IP2Proxy\Database::FILE_IO);

$countryCode = $db->getCountryShort('1.0.241.135');
echo '<p><strong>Country Code: </strong>' . $countryCode . '</p>';

$countryName = $db->getCountryLong('1.0.241.135');
echo '<p><strong>Country: </strong>' . $countryName . '</p>';

$regionName = $db->getRegion('1.0.241.135');
echo '<p><strong>Region: </strong>' . $regionName . '</p>';

$cityName = $db->getCity('1.0.241.135');
echo '<p><strong>City: </strong>' . $cityName . '</p>';

$isp = $db->getISP('1.0.241.135');
echo '<p><strong>ISP: </strong>' . $isp . '</p>';

$proxyType = $db->getProxyType('1.0.241.135');
echo '<p><strong>Proxy Type: </strong>' . $proxyType . '</p>';

$isProxy = $db->isProxy('1.0.241.135');
echo '<p><strong>Is Proxy: </strong>' . $isProxy . '</p>';

$records = $db->getAll('1.0.241.135');

echo '<pre>';
print_r($records);
echo '</pre>';

$db->close();