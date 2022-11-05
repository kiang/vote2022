<?php

function get_zodiac_sign($month, $day)
{
    // 檢查引數有效性
    if ($month < 1 || $month > 12 || $day < 1 || $day > 31)
        return (false);
    // 星座名稱以及開始日期
    $signs = array(
        array("20" => "寶瓶座"),
        array("19" => "雙魚座"),
        array("21" => "白羊座"),
        array("20" => "金牛座"),
        array("21" => "雙子座"),
        array("22" => "巨蟹座"),
        array("23" => "獅子座"),
        array("23" => "處女座"),
        array("23" => "天秤座"),
        array("24" => "天蠍座"),
        array("22" => "射手座"),
        array("22" => "摩羯座")
    );
    list($sign_start, $sign_name) = each($signs[(int)$month - 1]);
    if ($day < $sign_start)
        list($sign_start, $sign_name) = each($signs[($month - 2 < 0) ? $month = 11 : $month -= 2]);
    return $sign_name;
}


$basePath = dirname(dirname(__DIR__));

$age = $gender = $month = $signs = $home = [];
foreach (glob($basePath . '/reports/links_cec*') as $jsonFile) {
    $fh = fopen($jsonFile, 'r');
    while ($line = fgets($fh)) {
        $json = json_decode($line, true);
        $y = 111 - intval(substr($json['birth'], 0, 3));
        $m = substr($json['birth'], 3, 2);
        $d = substr($json['birth'], 5, 2);
        $sign = get_zodiac_sign($m, $d);
        if (!isset($age[$y])) {
            $age[$y] = 0;
        }
        ++$age[$y];
        if (!isset($gender[$json['gender']])) {
            $gender[$json['gender']] = 0;
        }
        ++$gender[$json['gender']];
        if (!isset($month[$m])) {
            $month[$m] = 0;
        }
        ++$month[$m];
        if(!isset($signs[$sign])) {
            $signs[$sign] = 0;
        }
        ++$signs[$sign];
        if(!isset($home[$json['home']])) {
            $home[$json['home']] = 0;
        }
        ++$home[$json['home']];
    }
}
ksort($age);
ksort($month);
print_r($age);
print_r($gender);
print_r($month);
print_r($signs);
arsort($home);
print_r($home);