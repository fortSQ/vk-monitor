<?php

require_once __DIR__ . '/src/DB.php';

$settings = parse_ini_file('settings.ini', true);

$chartParts = $settings['ui']['chart_parts'];

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
    hr { margin: 80px 0; border: 0; border-top: 1px solid #eee; }
    .chart { max-height: 400px; }
</style>
<script src="https://code.highcharts.com/highcharts.js"></script>

<?php $n = 0 ?>
<?php foreach($printData as $rangeName => $userList): ?>
    <? if ($n): ?><hr><? endif ?>
    <?php foreach($userList as $userId => $userData): ?>
        <? $isOnlineArray = array_values(array_map(function ($item) { return $item['is_online'] && !$item['is_mobile'] ? 1 : 0; }, $userData)) ?>
        <? $isMobileArray = array_values(array_map(function ($item) { return $item['is_online'] && $item['is_mobile'] ? 1 : 0; }, $userData)) ?>
        <? $timeList = array_keys($userData) ?>
        <?php $halfBreakpointList = [] ?>
        <? for ($i = 1; $i <= $chartParts; $i++): ?>
            <? $halfBreakpointList[] = ceil(count($userData) * $i / $chartParts) ?>
        <? endfor ?>
        <?php $isShowText = true ?>
        <? for ($i = 0; $i < count($halfBreakpointList); $i++): ?>
            <?php $from = $i - 1 >= 0 ? $halfBreakpointList[$i - 1] : 0 ?>
            <?php $end = $halfBreakpointList[$i] ?>

            <?php $subTime = array_slice($timeList, $from, $end - $from) ?>
            <?php $subIsOnline = array_slice($isOnlineArray, $from, $end - $from) ?>
            <?php $subIsMobile = array_slice($isMobileArray, $from, $end - $from) ?>
            <?php if(!empty($subTime)): ?>
                <div id="chart_<?php echo $n ?>" class="chart"></div>
                <script>
                    Highcharts.chart('chart_<?php echo $n ?>', {
                        chart: { type: 'area' },
                        title: { text: <?php echo json_encode($isShowText ? $rangeName : '') ?> },
                        <?php if ($isShowText): ?>subtitle: { text: <?php echo $userId ?> },<?php endif ?>
                        navigation: { buttonOptions: { enabled: false } },
                        xAxis: { categories: <?php echo json_encode($subTime) ?> },
                        yAxis: { title: { text: <?php echo json_encode($isShowText ? 'Онлайн?' : '') ?> }, labels: { enabled: false } },
                        series: [{
                            name: 'Обычный',
                            data: <?php echo json_encode($subIsOnline) ?>
                        }, {
                            name: 'Мобильный',
                            data: <?php echo json_encode($subIsMobile) ?>
                        }]
                    });
                </script>
                <? $isShowText = false ?>
            <?php endif ?>
            <?php $n++ ?>
        <? endfor ?>
    <?php endforeach ?>
<?php endforeach ?>