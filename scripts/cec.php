<?php
$basePath = dirname(__DIR__);
$cecCandidatePath = $basePath . '/raw/cec/candidates';
if (!file_exists($cecCandidatePath)) {
    mkdir($cecCandidatePath, 0777, true);
}
$cities = json_decode(file_get_contents($basePath . '/raw/city.topo.json'), true);
$countyPool = [];
foreach ($cities['objects']['city']['geometries'] as $city) {
    if (!isset($countyPool[$city['properties']['COUNTYCODE']])) {
        $countyPool[$city['properties']['COUNTYCODE']] = true;

        // 直轄市市長與其他縣市首長
        $file = $cecCandidatePath . '/C1/' . $city['properties']['COUNTYCODE'] . '.json';
        $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/C1/' . $city['properties']['COUNTYCODE'] . '.json');
        if (!empty($c)) {
            file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        // 直轄市議員與其他縣市議員
        $file = $cecCandidatePath . '/T/' . $city['properties']['COUNTYCODE'] . '.json';
        $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/T/' . $city['properties']['COUNTYCODE'] . '.json');
        if (!empty($c)) {
            file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        if (substr($city['properties']['COUNTYCODE'], 0, 1) !== '6') {
            // 鄉鎮市長
            $file = $cecCandidatePath . '/D1/' . $city['properties']['COUNTYCODE'] . '.json';
            $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/D1/' . $city['properties']['COUNTYCODE'] . '.json');
            if (!empty($c)) {
                file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            // 鄉鎮市民代表
            $file = $cecCandidatePath . '/R/' . $city['properties']['COUNTYCODE'] . '.json';
            $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/R/' . $city['properties']['COUNTYCODE'] . '.json');
            if (!empty($c)) {
                file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        } else {
            // 直轄市山地原住民區長  
            $file = $cecCandidatePath . '/D2/' . $city['properties']['COUNTYCODE'] . '.json';
            $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/D2/' . $city['properties']['COUNTYCODE'] . '.json');
            if (!empty($c)) {
                file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            // 直轄市原住民區民代表
            $file = $cecCandidatePath . '/R3/' . $city['properties']['COUNTYCODE'] . '.json';
            $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/R3/' . $city['properties']['COUNTYCODE'] . '.json');
            if (!empty($c)) {
                file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }


    // 村里長
    $file = $cecCandidatePath . '/V1/' . $city['properties']['TOWNCODE'] . '.json';
    $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/V1/' . $city['properties']['COUNTYCODE'] . '/' . $city['properties']['TOWNCODE'] . '.json');
    if (!empty($c)) {
        file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
