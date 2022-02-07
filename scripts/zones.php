<?php
$basePath = dirname(__DIR__);
$json = json_decode(file_get_contents($basePath . '/zones/zones.json'), true);
$base = [[
    [
        0,
        90
    ],
    [
        180,
        90
    ],
    [
        180,
        -90
    ],
    [
        0,
        -90
    ],
    [
        -180,
        -90
    ],
    [
        -180,
        0
    ],
    [
        -180,
        90
    ],
    [
        0,
        90
    ]
]];
foreach ($json['features'] as $f) {
    $newCoordinates = [];
    if($f['geometry']['type'] === 'MultiPolygon') {
        $f['geometry']['type'] = 'Polygon';
        $newCoordinates[] = $base[0];
        foreach($f['geometry']['coordinates'] AS $c) {
            if(!empty($c)) {
                $newCoordinates[] = array_shift($c);
            }
        }
    } else {
        $newCoordinates = array_merge($base, $f['geometry']['coordinates']);
    }
    $f['geometry']['coordinates'] = $newCoordinates;
    
    file_put_contents($basePath . '/zones/' . $f['properties']['id'] . '.json', json_encode($f, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
