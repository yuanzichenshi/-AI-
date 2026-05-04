<?php
// 跨域完整支持
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// 拦截预检OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// 接收前端JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$name = trim($data['name'] ?? '');
$score = intval($data['score'] ?? 0);

// 只校验昵称不为空，放开分数限制，方便手动同步
if (empty($name)) {
    echo json_encode(["msg" => "昵称不能为空"]);
    exit;
}

$file = 'rank.txt';
$list = [];

// 读取现有排行榜
if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (count($parts) === 2) {
            $n = trim($parts[0]);
            $s = intval($parts[1]);
            $list[$n] = $s;
        }
    }
}

// 同名只保留最高分
if (!isset($list[$name]) || $score > $list[$name]) {
    $list[$name] = $score;
}

// 按分数从高到低排序
arsort($list);

// 重新组装写入
$content = '';
foreach ($list as $n => $s) {
    $content .= "{$n}|{$s}\n";
}

// 安全锁定写入
file_put_contents($file, $content, LOCK_EX);

echo json_encode(["msg" => "提交成功"]);
?>

