<?php

declare(strict_types=1);

use App\Application\Console\PuzzleFileStorageSynchronizer;
use App\Application\Middleware\Auth\BasicAuthMiddleware;
use App\Application\Settings\SettingsInterface;
use App\Domain\Auth\BasicAuthRepository;
use App\Domain\Auth\Scope;
use App\Domain\Puzzle\PuzzleFileStorageInterface;
use App\Infrastructure\FileStorage\Puzzle\LocalPuzzleFilesReader;
use Cycle\Annotated;
use Cycle\Annotated\Locator\TokenizerEmbeddingLocator;
use Cycle\Annotated\Locator\TokenizerEntityLocator;
use Cycle\Database\Config\DatabaseConfig;
use Cycle\Database\Config\MySQL\TcpConnectionConfig;
use Cycle\Database\Config\MySQLDriverConfig;
use Cycle\Database\DatabaseManager;
use Cycle\Database\DatabaseProviderInterface;
use Cycle\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\Schema;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Spiral\Tokenizer\ClassLocator;

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


        /* Puzzles */

        PuzzleFileStorageInterface::class => function (ContainerInterface $c) {
            return new LocalPuzzleFilesReader(
                timezone: new \DateTimeZone($c->get(SettingsInterface::class)->get('timezone')),
                rootDirectory: dirname(__DIR__) . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "puzzles"
            );
        },
        PuzzleFileStorageSynchronizer::class => function (ContainerInterface $c) {
            return new PuzzleFileStorageSynchronizer(
                storage: $c->get(PuzzleFileStorageInterface::class),
            );
        },


        /* ORM */

        DatabaseProviderInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            return new DatabaseManager(
                new DatabaseConfig([
                    'default' => 'default',
                    'databases' => [
                        'default' => ['connection' => 'default'],
                        'poll' => ['connection' => 'poll'],
                    ],
                    'connections' => [
                        'default' => new MySQLDriverConfig(
                            connection: new TcpConnectionConfig(
                                database: $settings->get('db')['default']['database'],
                                host: $settings->get('db')['default']['host'],
                                port: (int) ($_ENV['DB_PORT'] ?? 3306),
                                user: $settings->get('db')['default']['username'],
                                password: $settings->get('db')['default']['password'],
                            ),
                            queryCache: true,
                        ),
                        'poll' => new MySQLDriverConfig(
                            connection: new TcpConnectionConfig(
                                database: $settings->get('db')['poll']['database'],
                                host: $settings->get('db')['poll']['host'],
                                port: (int) ($_ENV['DB_PORT'] ?? 3306),
                                user: $settings->get('db')['poll']['username'],
                                password: $settings->get('db')['poll']['password'],
                            ),
                            queryCache: true,
                        ),
                    ]
                ])
            );
        },

        "db.poll" => function (ContainerInterface $c)
        {
            $dbal = $c->get(DatabaseProviderInterface::class);
            return $dbal->database('poll');
        },
        
        ORMInterface::class => function (ContainerInterface $c)
        {
            $dbal = $c->get(DatabaseProviderInterface::class);
            $classLocator = new ClassLocator((new \Symfony\Component\Finder\Finder())->files()->in([dirname(__DIR__) . '/src/Domain']));
            $embeddingLocator = new TokenizerEmbeddingLocator($classLocator);
            $entityLocator = new TokenizerEntityLocator($classLocator);
            
            $schema = (new Schema\Compiler())->compile(new Schema\Registry($dbal), [
                new Schema\Generator\ResetTables(),             // Reconfigure table schemas (deletes columns if necessary)
                new Annotated\Embeddings($embeddingLocator),    // Recognize embeddable entities
                new Annotated\Entities($entityLocator),         // Identify attributed entities
                new Annotated\TableInheritance(),               // Setup Single Table or Joined Table Inheritance
                new Annotated\MergeColumns(),                   // Integrate table #[Column] attributes
                new Schema\Generator\GenerateRelations(),       // Define entity relationships
                new Schema\Generator\GenerateModifiers(),       // Apply schema modifications
                new Schema\Generator\ValidateEntities(),        // Ensure entity schemas adhere to conventions
                new Schema\Generator\RenderTables(),            // Create table schemas
                new Schema\Generator\RenderRelations(),         // Establish keys and indexes for relationships
                new Schema\Generator\RenderModifiers(),         // Implement schema modifications
                new Schema\Generator\ForeignKeys(),             // Define foreign key constraints
                new Annotated\MergeIndexes(),                   // Merge table index attributes
                new Schema\Generator\GenerateTypecast(),        // Typecast non-string columns
            ]);

            return new ORM\ORM(
                new ORM\Factory($dbal),
                new ORM\Schema($schema),
            );
        },


        /* Schedulers */

        'schedulers' => function (ContainerInterface $c) {
            return [
                $c->get(PuzzleFileStorageSynchronizer::class),
            ];
        },
    ]);
};
