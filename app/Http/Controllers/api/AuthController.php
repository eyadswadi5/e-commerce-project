<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissionService;
use App\Services\PHPMailerService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    protected $mailer;

    public function __construct(PHPMailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            "name" => "required|string",
            "email" => "required|email|unique:users,email",
            "phone_number" => "required|string|nullable",
            "password" => "required|string|min:6",
        ]);
        if ($validator->fails()) 
            return $this->rst(false, 422, "Failed to register user", $validator->errors());
        
        try {
            $role = Role::where("role", "=", "user")->first();
            $userData = $request->only("name", "email", "phone_number");
            $userData += ["role_id" => $role->id, "password" => Hash::make($request->password)];
            $user = User::create($userData);
            PermissionService::setUserPermissions($user);
            $this->mailer->sendVerificationEmail($user);
            return $this->rst(true, 201, "user registerd successfully.");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to register user", [["message" => "database error occurred"]]);
        } catch (Exception $e) {
            return $this->rst(false, 500, "Failed to regsiter user", [["message" => "unknown error occurred"]]);
        }
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            "email" => "required|email|exists:users,email",
            "password" => "required|string|min:6"
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "invalid credintails", $validator->errors());

        try {
            $token = JWTAuth::attempt($request->only("email", "password"));
            $user = JWTAuth::user();
        } catch (JWTException $e) {
            return $this->rst(false, 422, "invalid credintials", [["message"=>"wrong email or password"]]);
        }
        if (!$user->hasVerifiedEmail())
            return $this->rst(false, 400, "Failed to login", [["message" => "Your email must be verified"]]);

        return $this->rst(true, 200, null, null, [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'role' => $user->role()->role,
            'user' => $user
        ]);

    }

    public function refresh() {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $token = JWTAuth::refresh();
            return $this->rst(true, 200, null, null, [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'role' => $user->role()->role,
                'user' => $user
            ]);
        } catch (JWTException $e) {
            return $this->rst(false, 403, "unknown error occurres");
        }
    }
    
    public function userProfile() {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return $this->rst(true, 200, null, null, [
                "user" => $user,
            ]);
        } catch (JWTException $e) {
            return $this->rst(false, 403, "token expired");
        }
    }

}
