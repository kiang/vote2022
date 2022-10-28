<?php
$basePath = dirname(dirname(__DIR__));

$cities = json_decode(file_get_contents($basePath . '/raw/city.topo.json'), true);
$codes = [];
foreach ($cities['objects']['city']['geometries'] as $city) {
    $codes[$city['properties']['COUNTYNAME']] = $city['properties']['COUNTYCODE'];
    $codes[$city['properties']['COUNTYNAME'] . $city['properties']['TOWNNAME']] = $city['properties']['TOWNCODE'];
}

$fh = fopen($basePath . '/reports/links.csv', 'r');
$head = fgetcsv($fh, 2048);
$pool = [];
while ($line = fgetcsv($fh, 2048)) {
    $data = array_combine($head, $line);
    switch ($data['type']) {
        case '直轄市長':
        case '縣市長':
            $code = $codes[$data['election']];
            $key = 'C1/' . $code;
            break;
        case '直轄市議員':
        case '縣市議員':
            $parts = explode('|', $data['election']);
            $code = $codes[$parts[0]];
            $key = 'T/' . $code;
            break;
        case '鄉鎮市長':
            $parts = explode('|', $data['election']);
            $code = $codes[$parts[0]];
            $key = 'D1/' . $code;
            break;
        case '鄉鎮市民代表':
            $parts = explode('|', $data['election']);
            $code = $codes[$parts[0]];
            $key = 'R/' . $code;
            break;
        case '直轄市山地原住民區長':
            $parts = explode('|', $data['election']);
            $code = $codes[$parts[0]];
            $key = 'D2/' . $code;
            break;
        case '直轄市山地原住民區民代表':
            $parts = explode('|', $data['election']);
            $code = $codes[$parts[0]];
            $key = 'R3/' . $code;
            break;
        case '村里長':
            $parts = explode('|', $data['election']);
            $code = $codes[$parts[0] . $parts[1]];
            $key = 'V1/' . $code;
            break;
    }
    if (!isset($pool[$key])) {
        $pool[$key] = [];
    }
    if (!isset($pool[$key][$data['candidate']])) {
        $pool[$key][$data['candidate']] = [];
    }
    $pool[$key][$data['candidate']][] = $data;
}

$oFh = fopen($basePath . '/reports/links_cec.jsonl', 'w');
$oFhMissing = fopen($basePath . '/reports/links_cec_missing.jsonl', 'w');
foreach ($pool as $key => $d1) {
    $cecFile = $basePath . '/raw/cec/candidates/' . $key . '.json';
    if (file_exists($cecFile)) {
        $cec = json_decode(file_get_contents($cecFile), true);
        foreach ($cec as $type => $cecData) {
            foreach ($cecData as $cecProfile) {
                if (isset($cecProfile['name'])) {
                    if (isset($d1[$cecProfile['name']])) {
                        if (count($d1[$cecProfile['name']]) === 1) {
                            $cecProfile['election_id'] = $d1[$cecProfile['name']][0]['election_id'];
                            $cecProfile['election'] = $d1[$cecProfile['name']][0]['election'];
                        } else {
                            switch ($cecProfile['name']) {
                                case '許進旺':
                                    if ($cecProfile['liCode'] === '004') {
                                        $cunli = '圳安里';
                                    } else {
                                        $cunli = '大同里';
                                    }
                                    foreach ($d1[$cecProfile['name']] as $dCand) {
                                        if (false !== strpos($dCand['area'], $cunli)) {
                                            $cecProfile['election_id'] = $dCand['election_id'];
                                            $cecProfile['election'] = $dCand['election'];
                                        }
                                    }
                                    break;
                            }
                        }
                        fputs($oFh, json_encode($cecProfile, JSON_UNESCAPED_UNICODE) . "\n");
                    } else {
                        fputs($oFhMissing, json_encode($cecProfile, JSON_UNESCAPED_UNICODE) . "\n");
                    }
                } elseif (isset($cecProfile['cands'])) {
                    foreach ($cecProfile['cands'] as $cand) {
                        if (isset($d1[$cand['name']])) {
                            if (count($d1[$cand['name']]) === 1) {
                                $cand['election_id'] = $d1[$cand['name']][0]['election_id'];
                                $cand['election'] = $d1[$cand['name']][0]['election'];
                                fputs($oFh, json_encode($cand, JSON_UNESCAPED_UNICODE) . "\n");
                            }
                        } else {
                            fputs($oFhMissing, json_encode($cand, JSON_UNESCAPED_UNICODE) . "\n");
                        }
                    }
                } elseif (isset($cecProfile['areas'])) {
                    foreach ($cecProfile['areas'] as $area) {
                        foreach ($area['cands'] as $cand) {
                            if (isset($d1[$cand['name']])) {
                                if (count($d1[$cand['name']]) === 1) {
                                    $cand['election_id'] = $d1[$cand['name']][0]['election_id'];
                                    $cand['election'] = $d1[$cand['name']][0]['election'];
                                    fputs($oFh, json_encode($cand, JSON_UNESCAPED_UNICODE) . "\n");
                                } else {
                                    foreach ($d1[$cand['name']] as $dCand) {
                                        if (false !== strpos($dCand['area'], $cand['area'])) {
                                            $cand['election_id'] = $dCand['election_id'];
                                            $cand['election'] = $dCand['election'];
                                            fputs($oFh, json_encode($cand, JSON_UNESCAPED_UNICODE) . "\n");
                                        }
                                    }
                                }
                            } else {
                                fputs($oFhMissing, json_encode($cand, JSON_UNESCAPED_UNICODE) . "\n");
                            }
                        }
                    }
                }
            }
        }
    }
}
