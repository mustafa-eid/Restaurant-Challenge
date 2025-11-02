<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Repositories\Interfaces\UserRepositoryInterface;

/**
 * Class UserController
 *
 * Manages user registration, authentication (login/logout),
 * and token-based API authentication using Laravel Sanctum.
 *
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    use ApiResponseTrait;

    /**
     * The user repository instance.
     *
     * @var UserRepositoryInterface
     */
    private UserRepositoryInterface $userRepo;

    /**
     * Inject dependencies.
     *
     * @param  UserRepositoryInterface  $userRepo
     */
    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * Register a new user and generate an API token.
     *
     * @param  RegisterUserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterUserRequest $request)
    {
        $user = $this->userRepo->create($request->validated());
        $token = $user->createToken('api_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'User registered successfully', 201);
    }

    /**
     * Authenticate user and issue API token.
     *
     * @param  LoginUserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginUserRequest $request)
    {
        $user = $this->userRepo->findByEmail($request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Login successful');
    }

    /**
     * Logout the authenticated user by revoking the current token.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }
}
