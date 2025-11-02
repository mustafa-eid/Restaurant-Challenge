<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class UserRepository
 *
 * Manages data access logic related to users.
 * Extends the base repository to include user-specific operations.
 *
 * @package App\Repositories
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     *
     * @param User $model Injected user model instance.
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Create a new user with a hashed password.
     *
     * @param array $data User data for creation.
     * @return User
     */
    public function create(array $data): User
    {
        try {
            $data['password'] = Hash::make($data['password']);
            return parent::create($data);
        } catch (Throwable $e) {
            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Find a user by their email address.
     *
     * @param string $email The email to search for.
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }
}
