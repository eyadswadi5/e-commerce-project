<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class PHPMailerService {

    protected $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
        $this->mail->SMTPAuth = true;
        $this->mail->Username = env('MAIL_USERNAME');
        $this->mail->Password = env('MAIL_PASSWORD');
        $this->mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
        $this->mail->Port = env('MAIL_PORT', 587);
        $this->mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
    }

    public function sendVerificationEmail(User $user) {

        $payload = [
            "sub" => $user->id,
            "email" => $user->email,
            "exp" => now()->addMinutes(60)->timestamp
        ];
        $token = JWTAuth::claims($payload)->fromUser($user);

        try {
            $this->mail->addAddress("$user->email", $user->name);
            $this->mail->Subject = 'Verify your email address';
            $verificationLink = url('/email/verify/' . $token);
            $this->mail->Body = "Click the following link to verify your email address: $verificationLink";
            $this->mail->send();
        } catch (Exception $e) {
            throw new Exception('Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo);
        }
    }

    public function sendResetPasswordEmail(User $user) {
        $payload = [
            "sub" => $user->id,
            "email" => $user->email,
            "exp" => now()->addMinutes(60)->timestamp
        ];
        $token = JWTAuth::claims($payload)->fromUser($user);
        $data = [
            "email" => $user->email,
            "token" => $token,
        ];
        DB::table("password_reset_tokens")->updateOrInsert(["email" => $user->email],$data);
        
        try {
            $this->mail->addAddress("$user->email", $user->name);
            $this->mail->Subject = 'Reset your password';
            $verificationLink = url('/api/password/reset/' . $token);
            $this->mail->Body = "Click the following link to reset your password: $verificationLink";
            $this->mail->send();
        } catch (Exception $e) {
            throw new Exception('Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo);
        }
    }
}
