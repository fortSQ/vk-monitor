<?php

require_once __DIR__ . '/src/DB.php';

$settings = parse_ini_file('settings.ini', true);

$db = DB::getInstance()
    ->setParams($settings['db']['host'], $settings['db']['database'], $settings['db']['user'], $settings['db']['password'])
    ->setTable($settings['db']['table']);

$rangeNameList = ['Сегодня', 'Вчера', 'Позавчера'];

$printData = [];

foreach ($settings['user_id'] as $userId) {
    foreach ($rangeNameList as $dayCount => $rangeName) {
        $datetime = new DateTime();
        $printData[$rangeName][$userId] = $db->selectFromTable($userId, $datetime->modify("-{$dayCount} days"));
    }
}
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { overflow-y: scroll; }
    .chart { max-height: 400px; }
</style>
<script src="https://code.highcharts.com/highcharts.js"></script>

<?php $i = 0 ?>
<?php foreach($printData as $rangeName => $userList): ?>
    <?php foreach($userList as $userId => $userData): ?>
        <div id="chart_<?php echo $i ?>" class="chart"></div>
        <?php if(!empty($userData)): ?>
            <script>
                Highcharts.chart('chart_<?php echo $i ?>', {
                    chart: { type: 'area' },
                    title: { text: <?php echo json_encode($rangeName) ?> },
                    subtitle: { text: <?php echo $userId ?> },
                    navigation: { buttonOptions: { enabled: false } },
                    xAxis: { categories: <?php echo json_encode(array_keys($userData)) ?> },
                    yAxis: { title: { text: 'Онлайн?' }, labels: { enabled: false } },
                    series: [{
                        name: 'Обычный',
                        data: <?php echo json_encode(array_values(array_map(function ($item) { return $item['is_online'] && !$item['is_mobile'] ? 1 : 0; }, $userData))) ?>
                    }, {
                        name: 'Мобильный',
                        data: <?php echo json_encode(array_values(array_map(function ($item) { return $item['is_online'] && $item['is_mobile'] ? 1 : 0; }, $userData))) ?>
                    }]
                });
            </script>
        <?php endif ?>
        <?php $i++ ?>
    <?php endforeach ?>
<?php endforeach ?>