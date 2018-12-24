<?php

$container = $app->getContainer();

// Configure Monolog however you would like for your platform
// if REDIS_URL and/or LOG_FILE env vars are set, it logs to those locations
// otherwise log lines are sent to error_log()
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');

    // write to configured log file
    if ($log_file = getenv('LOG_FILE')) {
        error_log("Log handler set up for $log_file");
        $file_handler = new \Monolog\Handler\StreamHandler($log_file);
        $logger->pushHandler($file_handler);
    }

    // write to redis (requires you to have a credit card on file, but is free to use)
    // you get a LIST datatype in a key called "logs"
    if ($redis_url = getenv('REDIS_URL')) {
        error_log("Redis handler set up for $redis_url");
        $redis_handler = new \Monolog\Handler\RedisHandler(
            new Predis\Client($redis_url),
            "logs"
        );
        $logger->pushHandler($redis_handler);
    }

    // if you didn't configure anything and REDIS_URL isn't set, log to error log
    if (!getenv('LOG_FILE_PATH') && !getenv('REDIS_URL')) {
        error_log("No handlers set up, log to error log");
        $errorlog_handler = new \Monolog\Handler\ErrorLogHandler();
        $logger->pushHandler($errorlog_handler);
    }

    return $logger;
};

$container['view'] = function ($container) {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/templates/');
};


