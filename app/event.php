<?php
// 事件定义文件
return [
    'bind' => [
    ],

    'listen' => [
        'AppInit' => [],
        'HttpRun' => [],
        'RouteLoaded' => ['\app\plugin\listener\InitHookListener'],
        'HttpEnd' => [],
        'LogWrite' => [],
    ],

    'subscribe' => [
    ],
];
