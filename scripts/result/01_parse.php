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
    if (false === strpos($parts2[0], '議員')) {
        continue;
    }
    $targetPath = $basePath . '/reports/result/' . $p2['filename'];
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
                foreach ($candidateKeys as $candidateKey) {
                    $candidates[$candidateKey] = explode("\n", $line[$candidateKey]);
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
            } elseif (!empty($line[0])) {
                $currentArea = $line[0];
            }
        }
        $title = $worksheet->getTitle();
        $targetFile = $targetPath . '/' . $parts2[0] . '_' . $title . '.json';
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
