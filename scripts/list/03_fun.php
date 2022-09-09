<?php
$basePath = dirname(dirname(__DIR__));

$fh = fopen($basePath . '/reports/links.csv', 'r');
$pool = [];
fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    if (!isset($pool[$line[0]])) {
        $pool[$line[0]] = 0;
    }
    ++$pool[$line[0]];
}
$money = [
    '直轄市長' => 150,
    '直轄市議員' => 20,
    '縣市長' => 20,
    '縣市議員' => 12,
    '鄉鎮市長' => 12,
    '鄉鎮市民代表' => 12,
    '直轄市山地原住民區長' => 12,
    '直轄市山地原住民區民代表' => 5,
    '村里長' => 5,
];

$total = 0;
foreach($pool AS $k => $v) {
    $total += $v * $money[$k];
}
arsort($pool);
print_r($pool);