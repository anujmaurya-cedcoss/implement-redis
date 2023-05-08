<?php
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Http\Response\Cookies; // for cookies
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;
use Phalcon\Cache;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Storage\SerializerFactory;

$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);

$loader->register();

$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

// defining and registering logger
$container->set(
    'logger',
    function () {
        $adapter = new Stream(APP_PATH . '/logs/events.log');
        return new Logger(
            'messages',
            [
                'main' => $adapter,
            ]
        );
    }
);

$application = new Application($container);

$container->set(
    'db',
    function () {
        return new Mysql(
            [
                'host' => 'mysql-server',
                'username' => 'root',
                'password' => 'secret',
                'dbname' => 'firstDB',
            ]
        );
    }
);

$container->set(
    'cookies',
    function () {
        $cookies = new Cookies();
        $cookies->useEncryption(false);
        return $cookies;
    }
);

$serializerFactory = new SerializerFactory();
$options = [
    "host" => "redis",
    "port" => 6379,
    "auth" => '',
    "persistent" => false,
    "defaultSerializer" => "Php"
];
$adapter = new Redis($serializerFactory, $options);
$cache = new Cache($adapter);
$container -> set('cache', $cache);

try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}