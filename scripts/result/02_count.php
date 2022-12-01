<?php
$basePath = dirname(dirname(__DIR__));
$json = json_decode(file_get_contents($basePath . '/reports/result/10018新竹市/新竹市縣市議員_第1選舉區.json'), true);
$cunli = [];
foreach ($json['data'] as $house) {
    if (!isset($cunli[$house['area']])) {
        $cunli[$house['area']] = [];
    }
    if (!isset($cunli[$house['area']][$house['cunli']])) {
        $cunli[$house['area']][$house['cunli']] = [
            'count' => 0,
            'subtotal' => 0,
            'total' => 0,
            'rate' => 0.0,
        ];
    }
    $cunli[$house['area']][$house['cunli']]['count'] += $house['candidates'][3];
    $cunli[$house['area']][$house['cunli']]['count'] += $house['candidates'][9];
    foreach ($house['candidates'] as $c) {
        $cunli[$house['area']][$house['cunli']]['subtotal'] += $c;
    }
    $cunli[$house['area']][$house['cunli']]['total'] += $house['count_total'];
    $cunli[$house['area']][$house['cunli']]['rate'] = round($cunli[$house['area']][$house['cunli']]['count'] / $cunli[$house['area']][$house['cunli']]['subtotal'], 4) * 100 . '%';
}

$json = json_decode(file_get_contents($basePath . '/docs/tpp_zone/10018-01.json'), true);
foreach ($json['features'] as $f) {
    $parts = explode('區', $f['properties']['name']);
    $area = $parts[0] . '區';

    $cunli[$area][$parts[1]]['2020_votes'] = $f['properties']['votes'];
    $cunli[$area][$parts[1]]['2020_rate'] = $f['properties']['rate'];
    $cunli[$area][$parts[1]]['2020_total'] = $f['properties']['total'];
}

$oFh = fopen(__DIR__ . '/test.csv', 'w');
fputcsv($oFh, ['area', 'cunli', '2020 rate', '2022 rate', '2020 count', '2020 rate']);
foreach($cunli AS $area => $l1) {
    foreach($l1 AS $c => $l2) {
        fputcsv($oFh, [$area, $c, $l2['2020_rate'] . '%', $l2['rate'], $l2['2020_votes'], $l2['count']]);
    }
}