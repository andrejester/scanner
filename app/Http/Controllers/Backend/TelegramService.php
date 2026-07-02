<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class TelegramService extends Controller
{
    //
    protected $token;
    protected $client;
    protected $chatId;

    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
        $this->client = new Client();
    }

    public function sendMessage($message)
    {
        /*
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

        $this->client->post($url, [
            'form_params' => [
                'chat_id' => $this->chatId,
                'text' => $message
            ]
        ]);
        */
    }
}
