<?php

namespace App\Events;

use App\Http\Controllers\Backend\TelegramService;
use Illuminate\Auth\Events\Logout;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogSuccessfulLogout
{
    protected $telegram;
    protected $request;

    public function __construct(TelegramService $telegram, Request $request)
    {
        $this->telegram = $telegram;
        $this->request = $request;
    }

    public function handle(Logout $event)
    {
        $user = $event->user;
        $loginTime = session('login_time'); // Ambil waktu login dari session
        $logoutTime = now();
        $loginDuration = $loginTime ? $loginTime->diffForHumans($logoutTime, true) : 'unknown'; // Hitung durasi login

        $message = env('APP_NAME') . "\n";
        $message .= "User: {$user->name}\n";
        $message .= "Email: {$user->email}\n";
        $message .= "Logout Time: {$logoutTime}\n";
        $message .= "Login Duration: {$loginDuration}";

        // Kirim pesan ke Telegram
        $this->telegram->sendMessage($message);

        // Log ke storage Laravel
        // Log::info($message);

        // Hapus waktu login dari session
        session()->forget('login_time');
    }
}
