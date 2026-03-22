<?php

namespace App\Domain\Auth;

interface BasicAuthRepository
{
    public function getPasswordHash(Scope $scope): ?string;
}
