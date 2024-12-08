<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'email' => 'required|email',
        ]);

        // Store the subscription in DB
        $subscription = Subscription::firstOrCreate(
            ['url' => $request->url, 'email' => $request->email],
            ['last_known_price' => null, 'actual_price' => null]
        );

        return response()->json([
            'message' => 'Subscription successfully created',
            'data' => $subscription,
        ]);
    }
}
