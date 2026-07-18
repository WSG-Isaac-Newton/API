<?php

declare(strict_types=1);

use App\Domain\Auth\BasicAuthRepository;
use App\Domain\Directory\EventQueueRepository;
use App\Domain\Poll\Repository\PollRepository;
use App\Infrastructure\Persistence\Auth\PdoBasicAuthRepository;
use App\Infrastructure\Persistence\Directory\PdoEventQueueRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        BasicAuthRepository::class => \DI\autowire(PdoBasicAuthRepository::class),
        EventQueueRepository::class => \DI\autowire(PdoEventQueueRepository::class),
        PollRepository::class => \DI\autowire()->constructorParameter('db', \DI\get('db.poll')),
    ]);
};
