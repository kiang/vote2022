<?php
$basePath = dirname(dirname(__DIR__));

$fh = fopen(dirname($basePath) . '/db.cec.gov.tw/data/2022/村里長.csv', 'r');
$head = fgetcsv($fh, 2048);
$pool = [];
while ($line = fgetcsv($fh, 2048)) {
    $data = array_combine($head, $line);
    $code = $data['prv_code'] . $data['city_code'] . $data['dept_code'] . substr($data['li_code'], 1);
    if (!isset($pool[$code])) {
        $pool[$code] = [
            'major' => [],
            'candidates' => [],
        ];
    }
    array_pop($data);
    array_pop($data);
    array_pop($data);
    array_pop($data);
    array_pop($data);
    if ($data['is_victor'] === 'Y') {
        $pool[$code]['major'] = $data;
    } else {
        $pool[$code]['candidates'][] = $data;
    }
}

file_put_contents($basePath . '/docs/cunli/candidates.json', json_encode($pool, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
