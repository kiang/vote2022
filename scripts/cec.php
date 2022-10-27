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

        $file = $cecCandidatePath . '/' . $city['properties']['COUNTYCODE'] . '.json';
        $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/C1/' . $city['properties']['COUNTYCODE'] . '.json');
        if (!empty($c)) {
            file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $file = $cecCandidatePath . '/T' . $city['properties']['COUNTYCODE'] . '.json';
        $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/T/' . $city['properties']['COUNTYCODE'] . '.json');
        if (!empty($c)) {
            file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
    if (substr($city['properties']['COUNTYCODE'], 0, 1) !== '6') {
        $file = $cecCandidatePath . '/D1_' . $city['properties']['COUNTYCODE'] . '.json';
        $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/D1/' . $city['properties']['COUNTYCODE'] . '.json');
        if (!empty($c)) {
            file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $file = $cecCandidatePath . '/R_' . $city['properties']['COUNTYCODE'] . '.json';
        $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/R/' . $city['properties']['COUNTYCODE'] . '.json');
        if (!empty($c)) {
            file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    } else {
        $file = $cecCandidatePath . '/D2_' . $city['properties']['COUNTYCODE'] . '.json';
        $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/D2/' . $city['properties']['COUNTYCODE'] . '.json');
        if (!empty($c)) {
            file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $file = $cecCandidatePath . '/R3_' . $city['properties']['COUNTYCODE'] . '.json';
        $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/R3/' . $city['properties']['COUNTYCODE'] . '.json');
        if (!empty($c)) {
            file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    $file = $cecCandidatePath . '/' . $city['properties']['TOWNCODE'] . '.json';
    $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/V1/' . $city['properties']['COUNTYCODE'] . '/' . $city['properties']['TOWNCODE'] . '.json');
    if (!empty($c)) {
        file_put_contents($file, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
