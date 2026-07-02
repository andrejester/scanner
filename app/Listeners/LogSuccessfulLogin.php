<?php

namespace App\Listeners;

use App\Http\Controllers\Backend\TelegramService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    protected $telegram;
    protected $request;

    public function __construct(TelegramService $telegram, Request $request)
    {
        $this->telegram = $telegram;
        $this->request = $request;
    }

    public function handle(Login $event)
    {
        $user = $event->user;
        $agent = new Agent();

        $loginTime = now();
        session(['login_time' => $loginTime]); // Simpan waktu login di session

        $ip = $this->request->ip();
        $browser = $agent->browser();
        $platform = $agent->platform();
        $userAgent = $this->request->header('User-Agent');

        $message = env('APP_NAME') . "\n";
        $message .= "User: {$user->name}\n";
        $message .= "Email: {$user->email}\n";
        $message .= "Login Time: {$loginTime}\n";
        $message .= "IP Address: {$ip}\n";
        $message .= "Browser: {$browser}\n";
        $message .= "Platform: {$platform}\n";
        $message .= "User Agent: {$userAgent}";

        // Kirim pesan ke Telegram
        $this->telegram->sendMessage($message);

        // Log ke storage Laravel
        Log::info($message);
    }
}
