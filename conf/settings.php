<?php
# ini lerde kullanılan __DIR__ ifadesi için gerekli
if (!defined('__DIR__')) define('__DIR__', __DIR__);


$localSettings = [];
if (file_exists(dirname(__DIR__) . '/conf/settings-local.ini')) {
    $localSettings = parse_ini_file(dirname(__DIR__) . '/conf/settings-local.ini', true);
}

$defaultSettings = parse_ini_file("settings.ini",true);

$settingsRaw = array_merge_recursive($defaultSettings, $localSettings);

$settings = [];
foreach ($settingsRaw as $nodeName=>$node) {
    if ($nodeName == 'base') {
        $settings = $node;
    } else {
        $settings[$nodeName] = $node;
    }
}

return  [
    'settings' => $settings
];