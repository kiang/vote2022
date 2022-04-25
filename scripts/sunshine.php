<?php
require __DIR__ . '/vendor/autoload.php';

use Goutte\Client;

$client = new Client();

$crawler = $client->request('GET', 'https://sunshine.cy.gov.tw/PAQuery.aspx?n=21&sms=0');
$form = $crawler->selectButton('查詢')->form();

$client->submit($form, [
    'ctl00$ContentPlaceHolder_PageContent_title$hdDoQuery' => 2,
]);

$c = $client->getResponse()->getContent();
$pos = strpos($c, 'PAQuery.aspx?n=21&_Query=');
$posEnd = strpos($c, '"', $pos);

$c = file_get_contents('https://sunshine.cy.gov.tw/' . substr($c, $pos, $posEnd - $pos) . '&page=1&PageSize=1000');

$lines = explode('</tr>', $c);

array_shift($lines);
$header = ['申請名稱', '政治獻金專戶名稱', '金融機構名稱', '帳號', '金融機構地址', '許可日期及文號', '同意變更日期及文號', '廢止日期及文號', ''];
$dataPath = dirname(__DIR__) . '/sunshine.cy.gov.tw';

$pool = [];
foreach ($lines as $line) {
    $cols = explode('</td>', $line);
    if (count($cols) === 9) {
        foreach ($cols as $k => $v) {
            $cols[$k] = trim(strip_tags($v));
        }
        $key = substr($cols[1], 0, 3);
        if (!is_numeric($key)) {
            $parts = explode('擬參選人', $cols[1]);
            $key = $parts[0];
        }
        $dataFile = $dataPath . '/' . $key . '.csv';

        if (!isset($pool[$key])) {
            $pool[$key] = [];
            if (file_exists($dataFile)) {
                $fh = fopen($dataFile, 'r');
                fgetcsv($fh, 2048);
                while ($dataLine = fgetcsv($fh, 2048)) {
                    $pool[$key][$dataLine[1]] = true;
                }
                fclose($fh);
            }
        }
        if (!isset($pool[$key][$line[0]])) {
            if (file_exists($dataFile)) {
                $oFh = fopen($dataFile, 'a');
            } else {
                $oFh = fopen($dataFile, 'w');
                fputcsv($oFh, $header);
            }
            fputcsv($oFh, $cols);
            fclose($oFh);
            $pool[$key][$line[1]] = true;
        }
    }
}
