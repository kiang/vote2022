<?php
$basePath = dirname(dirname(__DIR__));

$fh = fopen($basePath . '/raw/elections_areas.csv', 'r');
fgetcsv($fh, 2048);
$elections = [];
while ($line = fgetcsv($fh, 2048)) {
    if (false !== strpos($line[1], '補選')) {
        continue;
    }
    $pos = strpos($line[1], '[');
    if (false !== $pos) {
        $line[1] = substr($line[1], 0, $pos);
    }
    $parts = explode(' > ', $line[1]);
    array_shift($parts);
    $electionType = array_shift($parts);
    if (!isset($elections[$electionType])) {
        $elections[$electionType] = [];
    }
    $key = implode('', $parts);
    $elections[$electionType][$key] = [
        'id' => $line[0],
        'parts' => $parts,
    ];
}

$oFh = fopen($basePath . '/reports/links.csv', 'w');
fputcsv($oFh, ['type', 'area', 'candidate', 'party', 'election_id', 'election']);
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
        switch ($fname) {
            case '縣市長':
            case '直轄市長':
            case '鄉鎮市長':
            case '直轄市山地原住民區長':
                fputcsv($oFh, [$fname, $line[0], $line[2], $line[3], $elections[$fname][$line[0]]['id'], implode('|', $elections[$fname][$line[0]]['parts'])]);
                break;
            case '鄉鎮市民代表':
            case '直轄市山地原住民區民代表':
                $parts = explode('第', $line[0]);
                if (!isset($parts[1])) {
                    $key = str_replace('選舉區', '', $line[0]) . '第01選舉區';
                } else {
                    $no = substr($parts[1], 0, 1);
                    $key = $parts[0] . '第' . str_pad($no, 2, '0', STR_PAD_LEFT) . substr($parts[1], 1);
                }
                switch ($key) {
                    case '連江縣北竿鄉第02選舉區':
                        $key = '連江縣北竿鄉第01選舉區';
                        break;
                    case '連江縣莒光鄉第03選舉區':
                        $key = '連江縣莒光鄉第01選舉區';
                        break;
                    case '連江縣東引鄉第04選舉區':
                        $key = '連江縣東引鄉第01選舉區';
                        break;
                }
                fputcsv($oFh, [$fname, $line[0], $line[2], $line[3], $elections[$fname][$key]['id'], implode('|', $elections[$fname][$key]['parts'])]);
                break;
            case '縣市議員':
            case '直轄市議員':
                $parts = explode('第', $line[0]);
                if (!isset($parts[1])) {
                    $key = str_replace('選舉區', '', $line[0]) . '第01選區';
                } else {
                    $no = substr($parts[1], 0, 1);
                    $key = $parts[0] . '第' . str_pad($no, 2, '0', STR_PAD_LEFT) . '選區';
                }
                fputcsv($oFh, [$fname, $line[0], $line[2], $line[3], $elections[$fname][$key]['id'], implode('|', $elections[$fname][$key]['parts'])]);
                break;
            case '村里長':
                switch ($line[0]) {
                    case '臺中市大安區':
                        $line[0] = '臺中市大安區龜壳里';
                        break;
                    case '臺南市安南區':
                        if ($line[2] === '林同寳') {
                            $line[0] = '臺南市安南區塭南里';
                        } else {
                            $line[0] = '臺南市安南區公塭里';
                        }
                        break;
                    case '臺南市西港區':
                        $line[0] = '臺南市西港區檨林里';
                        break;
                }
                fputcsv($oFh, [$fname, $line[0], $line[2], $line[3], $elections[$fname][$line[0]]['id'], implode('|', $elections[$fname][$line[0]]['parts'])]);
                break;
        }
    }
}
