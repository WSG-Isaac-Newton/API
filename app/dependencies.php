<?php

declare(strict_types=1);

use App\Application\Middleware\Auth\BasicAuthMiddleware;
use App\Application\Settings\SettingsInterface;
use App\Domain\Auth\BasicAuthRepository;
use App\Domain\Auth\Scope;
use App\Infrastructure\FileStorage\Puzzle\LocalPuzzleFilesReader;
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
        'db.poll' => function (\Psr\Container\ContainerInterface $c) {
            $config = $c->get(SettingsInterface::class)->get('db')['poll'];
            $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']};charset=utf8mb4";

            return new \PDO($dsn, $config['username'], $config['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        },
        'db.birthday' => function (\Psr\Container\ContainerInterface $c) {
            $config = $c->get(SettingsInterface::class)->get('db')['birthday'];
            $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']};charset=utf8mb4";

            return new \PDO($dsn, $config['username'], $config['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        },
        ResponseFactoryInterface::class => function (ContainerInterface $c) {
            return $c->get(ResponseFactory::class);
        },
        "BasicAuth.BirthdayProducer" => function (ContainerInterface $c) {
            return new BasicAuthMiddleware(
                $c->get(ResponseFactoryInterface::class),
                $c->get(BasicAuthRepository::class),
                Scope::BirthdayProducer,
            );
        },
        "BasicAuth.BirthdayConsumer" => function (ContainerInterface $c) {
            return new BasicAuthMiddleware(
                $c->get(ResponseFactoryInterface::class),
                $c->get(BasicAuthRepository::class),
                Scope::BirthdayConsumer,
            );
        },
        "BasicAuth.DirectoryEventProducer" => function (ContainerInterface $c) {
            return new BasicAuthMiddleware(
                $c->get(ResponseFactoryInterface::class),
                $c->get(BasicAuthRepository::class),
                Scope::DirectoryEventProducer,
            );
        },
        "BasicAuth.DirectoryEventConsumer" => function (ContainerInterface $c) {
            return new BasicAuthMiddleware(
                $c->get(ResponseFactoryInterface::class),
                $c->get(BasicAuthRepository::class),
                Scope::DirectoryEventConsumer,
            );
        },
        LocalPuzzleFilesReader::class => function (ContainerInterface $c) {
            $config = $c->get(SettingsInterface::class)->get('puzzle');
            return new LocalPuzzleFilesReader("var" . DIRECTORY_SEPARATOR . "puzzle");
        },
    ]);
};
