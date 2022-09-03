<?php
$basePath = dirname(dirname(__DIR__));

$pool = [];
foreach (glob($basePath . '/raw/list/csv/*.csv') as $csvFile) {
    $p = pathinfo($csvFile);
    $parts = explode('111年', $p['filename']);
    $parts = explode('選舉候選人', $parts[1]);
    $fname = $parts[0];
    $fh = fopen($csvFile, 'r');
    while ($line = fgetcsv($fh, 2048)) {
        if ($line[0] === '選舉區') {
            continue;
        }
        foreach ($line as $k => $v) {
            $line[$k] = str_replace(["\n", "\r"], '', $v);
        }
        if (!isset($pool[$fname])) {
            $pool[$fname] = [];
        }
        if (!isset($pool[$fname][$line[0]])) {
            $pool[$fname][$line[0]] = [
                'count' => 0,
                'data' => [],
            ];
        }
        ++$pool[$fname][$line[0]]['count'];
        $pool[$fname][$line[0]]['data'][] = $line;
    }
}

$oFh = fopen($basePath . '/reports/only_candidates.csv', 'w');
fputcsv($oFh, ['選舉類型', '選區', '候選人', '推薦政黨']);
foreach ($pool as $fname => $lv1) {
    foreach ($lv1 as $area => $item) {
        if ($item['count'] === 1) {
            $candidate = $item['data'][0];
            fputcsv($oFh, [$fname, $area, $candidate[2], $candidate[3]]);
        }
    }
}
