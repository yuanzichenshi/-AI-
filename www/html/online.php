<?php
session_start();
$file = 'online.txt';
// 改成30秒无操作自动离线
$timeout = 30;

if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
}
$data = json_decode(file_get_contents($file), true);
$now = time();

// 清理超时用户
foreach ($data as $sid => $lastTime) {
    if ($now - $lastTime > $timeout) {
        unset($data[$sid]);
    }
}

// 接收前端主动退出标记
$action = $_GET['act'] ?? '';
$sid = session_id();

if ($action === 'leave') {
    // 主动离开，直接删掉当前会话
    unset($data[$sid]);
} else {
    // 正常访问，刷新时间
    $data[$sid] = $now;
}

file_put_contents($file, json_encode($data));
// 输出当前在线人数
echo count($data);
?>

