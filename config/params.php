<?php

if (isset($_SERVER['HTTP_HOST'])) {
    $http = $_SERVER['REQUEST_SCHEME'];
    $host = $_SERVER['HTTP_HOST'];
    $url = $http . '://' . $host;
} else {
    $url = 'https://api.mineralogit.uz';
}

$http = $_SERVER['REQUEST_SCHEME'];
$host = $_SERVER['HTTP_HOST'];
$url = $http . '://' . $host;

return [
    'adminEmail' => 'contact@mineralogit.uz',
    'senderEmail' => 'contact@mineralogit.uz',
    'senderName' => "Mineral O'git",
    'url' => $url,
    'limits' => [
        'products' => 15
    ],
    'eskiz' => [
        'email' => 'samir_101@mail.ru',
        'password' => 'sBA3qXk46Ab79eP4A7OgN6l0U29X9s8Qg0PKslK7',
        'from' => '4546',
        'auth_token' => file_get_contents(__DIR__ . '/eskiz_auth_token.txt')
    ]
];
