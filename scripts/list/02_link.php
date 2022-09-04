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
        if (empty($line[2])) {
            $key = implode('/', $line);
            switch ($key) {
                case '澎湖縣第1選舉區/111/08/30//中國國民黨/':
                    $line[2] = '歐中慨';
                    break;
                case '嘉義縣鹿草鄉/111/09/01//民主進步黨/':
                    $line[2] = '嚴珮瑜';
                    break;
                case '屏東縣九如鄉/111/08/31//民主進步黨/':
                    $line[2] = '藍聰信';
                    break;
                case '南投縣名間鄉第4選舉區/111/09/02//無/':
                    $line[2] = '易南聰';
                    break;
                case '屏東縣屏東市第3選舉區/111/08/31//民主進步黨/':
                    $line[2] = '李秀䅿';
                    break;
                case '臺北市大同區延平里/111/09/01//無/':
                    $line[2] = '林[車孟]昌';
                    break;
                case '新北市中和區景平里/111/09/01//無/':
                    $line[2] = '張綾祐';
                    break;
                case '新北市永和區秀林里/111/08/31//中國國民黨/':
                    $line[2] = '関宏達';
                    break;
                case '新北市蘆洲區恆德里/111/09/01//無/':
                    $line[2] = '曾渝樺';
                    break;
                case '臺中市沙鹿區斗抵里/111/09/02//無/':
                    $line[2] = '何㳵杏';
                    break;
                case '臺中市后里區聯合里/111/08/31//無/':
                    $line[2] = '曾銪寪';
                    break;
                case '臺南市官田區渡拔里/111/08/31//無/':
                    $line[2] = '賴聰禮';
                    break;
                case '臺南市大內區石城里/111/08/31//無/':
                    $line[2] = '楊聰進';
                    break;
                case '臺南市佳里區下營里/111/08/29//無/':
                    $line[2] = '謝正直';
                    break;
                case '臺南市將軍區長沙里/111/08/30//無/':
                    $line[2] = '陳啓瑜';
                    break;
                case '臺南市將軍區忠嘉里/111/09/01//無/':
                    $line[2] = '黃聰惠';
                    break;
                case '臺南市新市區永就里/111/08/31//無/':
                    $line[2] = '蔡聰連';
                    break;
                case '臺南市山上區玉峯里/111/08/31//無/':
                    $line[2] = '張明聰';
                    break;
                case '高雄市鳳山區富榮里/111/08/31//無/':
                    $line[2] = '謝綉珏';
                    break;
                case '彰化縣秀水鄉埔崙村/111/09/01//無/':
                    $line[2] = '蘇世杞';
                    break;
                case '彰化縣大城鄉東城村/111/08/29//無/':
                    $line[2] = '吳佳陽';
                    break;
                case '嘉義縣竹崎鄉仁壽村/111/08/31//無/':
                    $line[2] = '葉麵';
                    break;
                case '宜蘭縣羅東鎮維揚里/111/08/31//無/':
                    $line[2] = '駱錫聰';
                    break;
                case '新竹市東區科園里/111/09/02//中國國民黨/':
                    $line[2] = '潘沿伶';
                    break;
            }
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
