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
$targets = [
    '蔡育輝' => '',
    '張世賢' => '',
    '李宗翰' => '',
    '劉米山' => '',
    '沈家鳳' => '',
    '趙昆原' => '',
    '方一峰' => '',
    '蔡蘇秋金' => '',
    '謝財旺' => 60,
    '陳昆和' => '',
    '蔡秋蘭' => '',
    '陳秋宏' => '',
    '吳通龍' => '',
    '尤榮智' => '',
    '周奕齊' => '',
    '李文俊' => '',
    '李偉智' => '',
    '林志展' => '',
    '陳碧玉' => '',
    '林志聰' => 48,
    '郭信良' => '',
    '李中岑' => '',
    '黃麗招' => '',
    '林炳利' => 60,
    '郭清華' => '',
    '王錦德' => '',
    '林燕祝' => '',
    '林阳乙' => 61,
    '林宜瑾' => 53,
    '李鎮國' => '',
    '楊中成' => '',
    '林易瑩' => '',
    '陳秋萍' => '',
    '謝龍介' => 61,
    '沈震東' => '',
    '陳怡珍' => '',
    '邱莉莉' => '',
    '許至椿' => '',
    '洪玉鳳' => 73,
    '林美燕' => '',
    '盧崑福' => '',
    '蔡淑惠' => '',
    '李啟維' => 34,
    '周麗津' => '',
    '呂維胤' => '',
    '曾培雅' => '',
    '曾信凱' => 42,
    '王家貞' => '',
    '蔡筱薇' => '',
    '蔡旺詮' => '',
    '杜素吟' => '',
    '吳禹寰' => '',
    '鄭佳欣' => '',
    '許又仁' => '',
    '郭鴻儀' => '',
    'Ingay Tali穎艾達利' => 49,
    'Kumu Hacyo谷暮．哈就' => 44,
];
foreach (glob($basePath . '/reports/links_cec*') as $jsonFile) {
    $fh = fopen($jsonFile, 'r');
    while ($line = fgets($fh)) {
        $json = json_decode($line, true);
        if (isset($json['election']) && false === strpos($json['election'], '臺南市')) {
            continue;
        } elseif (isset($json['area']) && false === strpos($json['area'], '臺南市')) {
            continue;
        }
        if ($json['type'] !== '議員') {
            continue;
        }
        $y = 111 - intval(substr($json['birth'], 0, 3));
        $m = substr($json['birth'], 3, 2);
        $d = substr($json['birth'], 5, 2);
        $sign = get_zodiac_sign($m, $d);
        if (!isset($age[$y])) {
            $age[$y] = 0;
        }
        if(isset($targets[$json['name']])) {
            $targets[$json['name']] = $y;
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
        if (!isset($signs[$sign])) {
            $signs[$sign] = 0;
        }
        ++$signs[$sign];
        if (!isset($home[$json['home']])) {
            $home[$json['home']] = 0;
        }
        ++$home[$json['home']];
    }
}
$targetSum = 0;
foreach($targets AS $target) {
    $targetSum += $target - 4;
}
echo ($targetSum / count($targets));
ksort($age);
ksort($month);
$sum = $count = 0;
foreach ($age as $k => $v) {
    $sum += $v * $k;
    $count += $v;
}
echo ($sum / $count);
print_r($age);
$lastKey = false;
foreach ($age as $k => $v) {
    if (false !== $lastKey) {
        $age[$k] += $age[$lastKey];
    }
    $lastKey = $k;
}
print_r($age);
print_r($gender);
print_r($month);
print_r($signs);
arsort($home);
print_r($home);
