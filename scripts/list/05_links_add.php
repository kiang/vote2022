<?php
$basePath = dirname(dirname(__DIR__));

$fh = fopen($basePath . '/raw/election_add.csv', 'r');
$head = fgetcsv($fh, 2048);
$pool = [];
while ($line = fgetcsv($fh, 2048)) {
    $data = array_combine($head, $line);
    $key1 = $data['推薦之政黨'];
    $key2 = $data['選舉區'];
    $key3 = $data['name'];
    if (!isset($pool[$key1])) {
        $pool[$key1] = [];
    }
    if (!isset($pool[$key1][$key2])) {
        $pool[$key1][$key2] = [];
    }
    $pool[$key1][$key2][$key3] = $data;
}

$oFh1 = fopen($basePath . '/reports/candidate_number.csv', 'w');
fputcsv($oFh1, ['election_id', 'candidate', 'number']);

$oFh2 = fopen($basePath . '/reports/candidate_number_missing.csv', 'w');
fputcsv($oFh2, $head);

$fh = fopen($basePath . '/reports/links.csv', 'r');
$head = fgetcsv($fh, 2048);
while ($line = fgetcsv($fh, 2048)) {
    $data = array_combine($head, $line);
    $key1 = $data['party'];
    $key2 = $data['area'];
    $key3 = $data['candidate'];
    if (isset($pool[$key1][$key2][$key3])) {
        fputcsv($oFh1, [$data['election_id'], $key3, $pool[$key1][$key2][$key3]['號次']]);
        unset($pool[$key1][$key2][$key3]);
    }
}

foreach ($pool as $k1 => $v1) {
    foreach ($v1 as $k2 => $v2) {
        foreach ($v2 as $k3 => $v3) {
            if (!empty($v3)) {
                fputcsv($oFh2, $v3);
            }
        }
    }
}
