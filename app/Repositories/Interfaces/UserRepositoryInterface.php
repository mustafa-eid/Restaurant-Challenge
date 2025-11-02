<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Repositories\Contracts\RepositoryInterface;
use App\Models\User;

/**
 * Interface UserRepositoryInterface
 *
 * Extends the base repository interface to include
 * user-specific data access operations.
 *
 * @package App\Repositories\Interfaces
 */
interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a user record by email.
     *
     * @param string $email User email address.
     * @return User|null Returns the user model instance if found, null otherwise.
     */
    public function findByEmail(string $email): ?User;
}
