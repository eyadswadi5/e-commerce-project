<?php

namespace App\Http\Controllers;

use App\Http\Controllers\api\BaseController;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerifyEmailController extends BaseController
{
    public function verifyEmail(String $token) {
        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $user = User::findOrFail($payload->get("sub"));

            if($user->email === $payload->get("email")) {
                if ($user->hasVerifiedEmail())
                    return $this->rst(false, 400, "Your email is already verified");

                $user->email_verified_at = now();
                $user->save();
                // return $this->rst(true, 200, "email verified successfully, try to login.");
                return redirect()->away(env("WEBSITE_URL")."/login");
            } else
                return $this->rst(false, 422, "invalid verification link or email missmatch");

        } catch (TokenExpiredException $e) {
            return $this->rst(false, 400, "verification link expired");
        } catch (TokenInvalidException $e) {
            return $this->rst(false, 400, "invalid verification link");
        } catch (Exception $e) {
            return $this->rst(false, 500, "something went wrong");
        }
    }
}
