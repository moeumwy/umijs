<?php

namespace App\Http\Controllers\V1\Passport;


use Illuminate\Support\Facades\Http;

class CommController extends Controller
{
    private function isEmailVerify()
    {
        return response([
            'data' => (int)config('v2board.email_verify', 0) ? 1 : 0
        ]);
    }

    public function sendEmailVerify(CommSendEmailVerify $request)
    {
        $userIP = $request->ip();
        if ((int)config('v2board.recaptcha_enable', 0)) {

            $secret = config('v2board.recaptcha_key');
            $response = $request->input('recaptcha_data');

            $response = Http::post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $secret,
                'response' => $response,
                'ip' => $userIP,
            ]);

            if ($response->failed()) {
                abort(500, ('Failed to verify turnstile'));
            }

            $responseData = $response->json();

            if (!isset($responseData['success']) && $responseData['success'] === false) {
                abort(500, ('Invalid code is incorrect'));
            }
        }
        $email = $request->input('email');
        if (Cache::get(CacheKey::get('LAST_SEND_EMAIL_VERIFY_TIMESTAMP', $email))) {
            abort(500, __('Email verification code has been sent, please request again later'));
        }
        $code = rand(100000, 999999);
        $subject = config('v2board.app_name', 'V2Board') . __('Email verification code');

        SendEmailJob::dispatch([
            'email' => $email,
            'subject' => $subject,
            'template_name' => 'verify',
            'template_value' => [
                'name' => config('v2board.app_name', 'V2Board'),
                'code' => $code,
                'url' => config('v2board.app_url')
            ]
        ]);

        Cache::put(CacheKey::get('EMAIL_VERIFY_CODE', $email), $code, 300);
        Cache::put(CacheKey::get('LAST_SEND_EMAIL_VERIFY_TIMESTAMP', $email), time(), 60);
        return response([
            'data' => true
        ]);
    }

    public function pv(Request $request)
    {

    }

    private function getEmailSuffix()
    {

    }
}
