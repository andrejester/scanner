<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * Simpan atau update push subscription dari browser
     */
    public function store(Request $request)
    {
        $request->validate([
            'endpoint'    => 'required|string',
            'keys.auth'   => 'required|string',
            'keys.p256dh' => 'required|string',
        ]);

        $user = Auth::user();

        $user->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'],
            $request->keys['auth']
        );

        return response()->json(['success' => true]);
    }

    /**
     * Hapus push subscription (saat logout atau user minta berhenti)
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        Auth::user()->deletePushSubscription($request->endpoint);

        return response()->json(['success' => true]);
    }
}
