<?php

require_once __DIR__ . '/src/VK.php';

$settings = parse_ini_file('settings.ini');

$vk = new VK();
$vk->setClientId($settings['id']);
$href = $vk->getLinkForAccessToken($settings['scope']);

?>

<a href="<?php echo $href ?>">access_token</a>