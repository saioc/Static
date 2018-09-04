<?php
$baseDir = dirname(dirname(__FILE__));
return [
    'plugins' => [
        'ADmad/HybridAuth' => $baseDir . '/vendor/admad/cakephp-hybridauth/',
        'ClassicTheme' => $baseDir . '/plugins/ClassicTheme/',
        'CloudTheme' => $baseDir . '/plugins/CloudTheme/',
        'Migrations' => $baseDir . '/vendor/cakephp/migrations/',
        'ModernTheme' => $baseDir . '/plugins/ModernTheme/'
    ]
];