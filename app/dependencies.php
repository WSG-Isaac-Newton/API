<?php

declare(strict_types=1);

use App\Application\Middleware\Auth\BasicAuthMiddleware;
use App\Application\Middleware\Auth\Scope;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\ResponseFactory;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        \PDO::class => function () {
            $driver = $_ENV['DB_DRIVER'];
            $host   = $_ENV['DB_HOST'];
            $db     = $_ENV['DB_DATABASE'];
            $user   = $_ENV['DB_USERNAME'];
            $pass   = $_ENV['DB_PASSWORD'];

            return new \PDO("$driver:host=$host;dbname=$db", $user, $pass);
        },
        ResponseFactoryInterface::class => function (ContainerInterface $c) {
            return $c->get(ResponseFactory::class);
        },
        "BasicAuth.BirthdayProducer" => function (ContainerInterface $c) {
            return new BasicAuthMiddleware(
                $c->get(ResponseFactoryInterface::class),
                $c->get(\PDO::class),
                Scope::BirthdayProducer,
            );
        },
        "BasicAuth.BirthdayConsumer" => function (ContainerInterface $c) {
            return new BasicAuthMiddleware(
                $c->get(ResponseFactoryInterface::class),
                $c->get(\PDO::class),
                Scope::BirthdayConsumer,
            );
        },
    ]);
};
