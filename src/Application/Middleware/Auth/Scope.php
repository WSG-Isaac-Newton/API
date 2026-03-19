<?php

namespace App\Application\Middleware\Auth;

enum Scope: string
{
    case BirthdayProducer = 'BirthdayProducer';
    case BirthdayConsumer = 'BirthdayConsumer';
}
