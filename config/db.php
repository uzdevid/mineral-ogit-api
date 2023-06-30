<?php
$pattern = '/mineralogit\.api/';
$addr = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['SCRIPT_FILENAME'];

if (preg_match($pattern, $addr)) {
    return [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=mineralogit_base',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
    ];
}

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=mineralogit_base',
    'username' => 'mineralogit_user',
    'password' => 'jUHcM2AsFwv3LUEh',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 60,
    'schemaCache' => 'cache',
];
