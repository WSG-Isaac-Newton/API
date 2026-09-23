<?php

declare(strict_types=1);

use App\Domain\Auth\BasicAuthRepository;
use App\Domain\Directory\EventQueueRepository;
use App\Domain\Member\Repository\BirthdayRepository;
use App\Infrastructure\Persistence\Auth\PdoBasicAuthRepository;
use App\Infrastructure\Persistence\Directory\PdoEventQueueRepository;
use App\Infrastructure\Persistence\Poll\PollRepository;
use DI\ContainerBuilder;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        BasicAuthRepository::class => \DI\autowire(PdoBasicAuthRepository::class),
        EventQueueRepository::class => \DI\autowire(PdoEventQueueRepository::class),
        PollRepository::class => \DI\autowire()->constructorParameter('db', \DI\get('db.poll')),
        BirthdayRepository::class => \DI\autowire()->constructorParameter('db', \DI\get('db.birthday')),
    ]);
};
