<?php
$basePath = dirname(dirname(__DIR__));
require $basePath . '/scripts/vendor/autoload.php';

$fileCount = 0;
foreach (glob($basePath . '/raw/result/選舉/*/*.xls') as $xlsFile) {
    ++$fileCount;
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsFile);
    $count = $spreadsheet->getSheetCount();
    $candidateLineNo = 0;
    $p = pathinfo($xlsFile);
    $p2 = pathinfo($p['dirname']);
    $parts = explode('111年', $p['filename']);
    $parts2 = explode('選舉候選人', $parts[1]);
    if (false !== strpos($parts2[0], '村(里)長')) {
        $parts2 = explode('選舉各投開票所', $parts[1]);
        $parts2[0] = mb_substr($parts2[0], 3, null, 'utf-8');
        $targetPath = $basePath . '/reports/result/' . $p2['filename'] . '/' . $parts2[0];
        if (!file_exists($targetPath)) {
            mkdir($targetPath, 0777, true);
        }
        for ($i = 0; $i < $count; $i++) {
            $dataLines = [];
            $worksheet = $spreadsheet->getSheet($i);
            $title = $worksheet->getTitle();
            $lineNo = 0;
            $dataLineBegin = false;
            foreach ($worksheet->getRowIterator() as $row) {
                ++$lineNo;
                $cellIterator = $row->getCellIterator();
                $line = [];
                foreach ($cellIterator as $cell) {
                    $line[] = $cell->getValue();
                }
                $parts = explode("\n", $line[2]);
                if (count($parts) === 3) {
                    if (!empty($dataLines)) {
                        $targetFile = $targetPath . '/' . $title . '_' . $currentArea . '.json';
                        $data = [
                            'candidates' => $candidates,
                            'data' => array_values($dataLines),
                        ];
                        file_put_contents($targetFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        $dataLines = [];
                    }
                    $dataLineBegin = true;
                    $candidates = [];
                    $candidateKeys = [];
                    foreach ($line as $k => $candidate) {
                        $check = explode("\n", $candidate);
                        if (count($check) === 3) {
                            $candidateKeys[$check[0]] = $k;
                            $candidates[$check[0]] = [
                                'name' => $check[1],
                                'party' => $check[2],
                            ];
                        }
                    }
                }
                if ($dataLineBegin) {
                    if (!empty($line[0])) {
                        $currentArea = $line[0];
                    } elseif (!empty($line[1])) {
                        $dataLines[$lineNo] = [
                            'area' => $title,
                            'cunli' => $currentArea,
                            'house_no' => $line[1],
                            'candidates' => [],
                        ];
                        foreach ($line as $k => $v) {
                            $line[$k] = str_replace(',', '', $v);
                        }
                        foreach ($candidateKeys as $no => $candidateKey) {
                            $dataLines[$lineNo]['candidates'][$no] = intval($line[$candidateKey]);
                        }
                        $dataLines[$lineNo]['count_wrong'] = intval($line[10]);
                        $dataLines[$lineNo]['count_unused'] = intval($line[12]);
                        $dataLines[$lineNo]['count_total'] = intval($line[15]);
                    }
                }
            }
            if (!empty($dataLines)) {
                $targetFile = $targetPath . '/' . $title . '_' . $currentArea . '.json';
                $data = [
                    'candidates' => $candidates,
                    'data' => array_values($dataLines),
                ];
                file_put_contents($targetFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    } elseif (false !== strpos($parts2[0], '鄉(鎮、市)民代表')) {
        $parts2[0] = mb_substr($parts2[0], 3, null, 'utf-8');
        $targetPath = $basePath . '/reports/result/' . $p2['filename'] . '/' . $parts2[0];
        if (!file_exists($targetPath)) {
            mkdir($targetPath, 0777, true);
        }
        for ($i = 0; $i < $count; $i++) {
            $dataLines = [];
            $worksheet = $spreadsheet->getSheet($i);
            $title = $worksheet->getTitle();
            $lineNo = 0;
            $dataLineBegin = false;
            foreach ($worksheet->getRowIterator() as $row) {
                ++$lineNo;
                $cellIterator = $row->getCellIterator();
                $line = [];
                foreach ($cellIterator as $cell) {
                    $line[] = $cell->getValue();
                }
                $parts = explode("\n", $line[3]);
                if (count($parts) === 3) {
                    $dataLineBegin = true;
                    $candidates = [];
                    $candidateKeys = [];
                    $uKey = false;
                    foreach ($line as $k => $candidate) {
                        $check = explode("\n", $candidate);
                        if (count($check) === 3) {
                            $candidateKeys[$check[0]] = $k;
                            $candidates[$check[0]] = [
                                'name' => $check[1],
                                'party' => $check[2],
                            ];
                            $lastCandidateKey = $k;
                        }
                    }
                }
                if ($dataLineBegin) {
                    if (!empty($line[0])) {
                        if (false !== strpos($line[0], '選舉區')) {
                            if (!empty($dataLines)) {
                                $targetFile = $targetPath . '/' . $title . '_' . $currentArea . '.json';
                                $data = [
                                    'candidates' => $candidates,
                                    'data' => array_values($dataLines),
                                ];
                                file_put_contents($targetFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                                $uKey = false;
                                $dataLines = [];
                            }

                            $currentArea = $line[0];
                        } else {
                            $dataLines[$currentLineNo]['cunli'] .= $line[0];
                        }
                    } elseif (!empty($line[1])) {
                        $currentLineNo = $lineNo;
                        $dataLines[$currentLineNo] = [
                            'area' => $title,
                            'cunli' => $line[1],
                            'house_no' => $line[2],
                            'candidates' => [],
                        ];
                        foreach ($line as $k => $v) {
                            $line[$k] = str_replace(',', '', $v);
                        }
                        foreach ($candidateKeys as $no => $candidateKey) {
                            $dataLines[$currentLineNo]['candidates'][$no] = intval($line[$candidateKey]);
                        }

                        $uKey = $lastCandidateKey + 1;
                        while (empty($line[$uKey])) {
                            ++$uKey;
                        }
                        $uKey += 1;
                        $dataLines[$currentLineNo]['count_wrong'] = intval($line[$uKey]);
                        $uKey += 2;
                        $dataLines[$currentLineNo]['count_unused'] = intval($line[$uKey]);
                        $uKey += 3;
                        $dataLines[$currentLineNo]['count_total'] = intval($line[$uKey]);
                    }
                }
            }
            $targetFile = $targetPath . '/' . $title . '_' . $currentArea . '.json';
            $data = [
                'candidates' => $candidates,
                'data' => array_values($dataLines),
            ];
            file_put_contents($targetFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    } else {
        $parts2[0] = mb_substr($parts2[0], 3, null, 'utf-8');
        $targetPath = $basePath . '/reports/result/' . $p2['filename'] . '/' . $parts2[0];
        if (!file_exists($targetPath)) {
            mkdir($targetPath, 0777, true);
        }
        for ($i = 0; $i < $count; $i++) {
            $dataLines = [];
            $worksheet = $spreadsheet->getSheet($i);
            $lineNo = 0;
            $dataLineBegin = false;
            foreach ($worksheet->getRowIterator() as $row) {
                ++$lineNo;
                $cellIterator = $row->getCellIterator();
                $line = [];
                foreach ($cellIterator as $cell) {
                    $line[] = $cell->getValue();
                }
                if ($line[0] === '行政區別') {
                    $candidateLineNo = $lineNo + 1;
                    $countBegin = false;
                    foreach ($line as $k => $v) {
                        if (false !== strpos($v, '各候選人得票情形')) {
                            $countBegin = true;
                            $candidateKeys = [];
                            $lastCandidateKey = 0;
                            $candidates = [];
                        }
                        if (false !== strpos($v, '有效票數')) {
                            $countBegin = false;
                        }
                        if ($countBegin) {
                            $candidateKeys[] = $k;
                            $lastCandidateKey = $k;
                        }
                    }
                } elseif ($candidateLineNo === $lineNo) {
                    foreach ($candidateKeys as $k => $candidateKey) {
                        $candidates[$candidateKey] = explode("\n", $line[$candidateKey]);
                        if (count($candidates[$candidateKey]) !== 3) {
                            unset($candidates[$candidateKey]);
                            unset($candidateKeys[$k]);
                        }
                    }
                } elseif (!empty($line[2])) {
                    foreach ($line as $k => $v) {
                        $line[$k] = str_replace(',', '', $v);
                    }
                    $dataLineBegin = $lineNo;
                    $dataLines[$lineNo] = [
                        'area' => $currentArea,
                        'cunli' => $line[1],
                        'house_no' => $line[2],
                        'candidates' => [],
                    ];
                    foreach ($candidateKeys as $candidateKey) {
                        $dataLines[$lineNo]['candidates'][$candidates[$candidateKey][0]] = intval($line[$candidateKey]);
                    }
                    $uKey = $lastCandidateKey + 2;
                    $dataLines[$lineNo]['count_wrong'] = intval($line[$uKey]);
                    $uKey += 2;
                    $dataLines[$lineNo]['count_unused'] = intval($line[$uKey]);
                    $uKey += 3;
                    $dataLines[$lineNo]['count_total'] = intval($line[$uKey]);

                    $lastLineNo = $lineNo;
                } elseif (false !== $dataLineBegin && !empty($line[1])) {
                    $dataLines[$lastLineNo]['cunli'] .= $line[1];
                } elseif (false !== $dataLineBegin && !empty($line[0])) {
                    $dataLines[$lastLineNo]['cunli'] .= $line[0];
                } elseif (!empty($line[0])) {
                    $currentArea = $line[0];
                }
            }
            $title = $worksheet->getTitle();
            $targetFile = $targetPath . '/' . $title . '.json';
            $data = [
                'candidates' => [],
                'data' => array_values($dataLines),
            ];
            foreach ($candidates as $candidate) {
                $data['candidates'][$candidate[0]] = [
                    'name' => $candidate[1],
                    'party' => $candidate[2],
                ];
            }
            file_put_contents($targetFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}
