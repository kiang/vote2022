<?php
$basePath = dirname(__DIR__);
$cecCandidatePath = $basePath . '/raw/cec/candidates';
if (!file_exists($cecCandidatePath)) {
    mkdir($cecCandidatePath, 0777, true);
}
$cities = json_decode(file_get_contents($basePath . '/raw/city.topo.json'), true);
foreach ($cities['objects']['city']['geometries'] as $city) {
    $file1 = $cecCandidatePath . '/' . $city['properties']['COUNTYCODE'] . '.json';
    if (!file_exists($file1)) {
        $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/C1/' . $city['properties']['COUNTYCODE'] . '.json');
        if(!empty($c)) {
            file_put_contents($file1, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
    $file2 = $cecCandidatePath . '/' . $city['properties']['TOWNCODE'] . '.json';
    if (!file_exists($file2)) {
        $c = file_get_contents('https://2022.cec.gov.tw/data/json/cand/V1/' . $city['properties']['COUNTYCODE'] . '/' . $city['properties']['TOWNCODE'] . '.json');
        if(!empty($c)) {
            file_put_contents($file2, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}

$cecPoliticPath = $basePath . '/raw/cec/politics';
if (!file_exists($cecPoliticPath)) {
    mkdir($cecPoliticPath, 0777, true);
}
foreach ($cities['objects']['city']['geometries'] as $city) {
    $file1 = $cecPoliticPath . '/' . $city['properties']['COUNTYCODE'] . '.json';
    if (!file_exists($file1)) {
        $c = file_get_contents('https://2022.cec.gov.tw/data/json/politics/C1/' . $city['properties']['COUNTYCODE'] . '.json');
        if(!empty($c)) {
            file_put_contents($file1, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
    $file2 = $cecPoliticPath . '/' . $city['properties']['TOWNCODE'] . '.json';
    if (!file_exists($file2)) {
        $c = file_get_contents('https://2022.cec.gov.tw/data/json/politics/V1/' . $city['properties']['COUNTYCODE'] . '/' . $city['properties']['TOWNCODE'] . '.json');
        if(!empty($c)) {
            file_put_contents($file2, json_encode(json_decode($c), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}
