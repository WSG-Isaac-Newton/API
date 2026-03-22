<?php

namespace App\Infrastructure\Persistence\Auth;

use App\Domain\Auth\BasicAuthRepository;
use App\Domain\Auth\Scope;

final class PdoBasicAuthRepository implements BasicAuthRepository
{
    private readonly string $tableName;

    public function __construct(
        private \PDO $db,
    ) {
        $this->tableName = 'congressus_webhooks_auth';
    }

    public function getPasswordHash(Scope $scope): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT password_hash FROM $this->tableName 
            WHERE scope = :scope AND active = 1"
        );
        $stmt->execute([':scope' => $scope->value]);

        return $stmt->fetch(\PDO::FETCH_COLUMN);
    }
}
