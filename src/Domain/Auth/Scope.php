<?php

namespace App\Domain\Auth;

enum Scope: string
{
    case BirthdayProducer = 'BirthdayProducer';
    case BirthdayConsumer = 'BirthdayConsumer';
    case DirectoryEventProducer = 'DirectoryEventProducer';
    case DirectoryEventConsumer = 'DirectoryEventConsumer';
}
