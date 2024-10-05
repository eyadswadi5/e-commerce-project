<?php

namespace App\Http\Controllers;

use App\Http\Controllers\api\BaseController;
use App\Models\User;
use App\Services\PHPMailerService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ResetPasswordController extends BaseController
{
    protected $mailer;

    public function __construct(PHPMailerService $mailer)
    {
        $this->mailer = $mailer;
    }
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->only("email"), [
            "email" => "required|email|exists:users,email"
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "Failed to reset password", [["message" => "Email is invalid."]]);

        try {
            $user = User::where("email", "=", $request->email)->first();
            if (!$user->hasVerifiedEmail())
                return $this->rst(false, 400, "Failed to reset password", [["message" => "Email in not verified"]]);
            $this->mailer->sendResetPasswordEmail($user);
            return $this->rst(true, 200, "password reset link has been sent.");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to reset password", [["message" => "database error occurres", ]]);
        } catch (Exception $e) {
            return $this->rst(false, 500, "Failed to reset password", [["message" => "unknown error occures"]]);
        }
    }

    public function resetPassword(String $token, Request $request) {
        $validator = Validator::make($request->only("password"), [
            "password" => "required|string|min:6",
        ]);

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $user = User::findOrFail($payload->get("sub"));
            $checkToken = DB::table("password_reset_tokens")
            ->where("email", "=", $user->email)
            ->where("token", "=", $token)
            ->first();
            if (!$checkToken) 
                return $this->rst(false, 422, "Failed to reset password", [["message"=>"this email didn't request a reset password"]]);

            $user->password = Hash::make($request->password);
            $user->save();
            return $this->rst(true, 200, "Password updated successfully");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to reset password", [["message"=>"database error occurres"]]);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "Failed to reset password", [["message"=>"invalid link"]]);
        } catch (JWTException $e) {
            return $this->rst(false, 422, "Failed to reset password", [["message"=>"invalid link"]]);
        } catch (Exception $e) {
            return $this->rst(false, 500, "Failed to reset password", [["message"=>"unknown error occurres"]]);
        }
    }
}
