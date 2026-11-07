<?php
declare(strict_types=1);

return [
    'host' => getenv('BLCKOS_DB_HOST') ?: '127.0.0.1',
    'port' => getenv('BLCKOS_DB_PORT') ?: '3306',
    'name' => getenv('BLCKOS_DB_NAME') ?: '',
    'user' => getenv('BLCKOS_DB_USER') ?: '',
    'password' => getenv('BLCKOS_DB_PASSWORD') ?: '',
];
