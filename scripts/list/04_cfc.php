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
        if (!isset($pool[$line[2]])) {
            $pool[$line[2]] = [];
        }
        $pool[$line[2]][] = $fname . $line[0];
    }
}

$fh = fopen($basePath . '/raw/cfcmweb.cy.gov.tw/list.csv', 'r');
$header = fgetcsv($fh, 2048);
$oFh = [];
while ($line = fgetcsv($fh, 2048)) {
    if (isset($pool[$line[9]])) {
        foreach ($pool[$line[9]] as $key) {
            if (!isset($oFh[$key])) {
                $oFh[$key] = fopen($basePath . '/raw/cfcmweb.cy.gov.tw/' . $key . '.csv', 'w');
                fputcsv($oFh[$key], $header);
            }
            fputcsv($oFh[$key], $line);
        }
    }
}
