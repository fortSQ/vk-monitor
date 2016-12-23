<?php

require_once __DIR__ . '/src/VK.php';
require_once __DIR__ . '/src/DB.php';

$settings = parse_ini_file('settings.ini', true);

$vk = new VK();
$vk->setAccessToken($settings['access_token']);

$db = DB::getInstance()
    ->setParams($settings['db']['host'], $settings['db']['database'], $settings['db']['user'], $settings['db']['password'])
    ->setTable($settings['db']['table']);


foreach ($vk->methodUsersGet($settings['user_id']) as $userId => $userData) {
    $isOnline = $userData['is_online'] ? 'y' : 'n';
    $isMobile = $userData['is_mobile'] ? 'y' : 'n';
    $db->insertIntoTable($userId, $isOnline, $isMobile);
}
