<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscribeRequest;
use App\Mail\VerifySubscriberMail;
use App\Models\Subscriber;
use App\Models\Subscription;
use App\Models\UrlPrice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function subscribe(SubscribeRequest $request)
    {
        $email = $request->input('email');
        $urls = $request->input('urls');
        $messages = [];

        $subscriber = Subscriber::firstOrCreate(['email' => $email], [
            'verification_token' => Str::random(32),
        ]);
        if ($subscriber->wasRecentlyCreated) {
            // For new subscribers send verification token to validate email
            Mail::to($subscriber->email)
                ->send(new VerifySubscriberMail($subscriber));
            $emailVerified = false;
            $messages = ['Welcome new subscriber! Please verify your email to receive price notifications.'];
        } else {
            $emailVerified = $subscriber->verified_at !== null;
            if (!$emailVerified) {
                $messages = ['Email not verified. Please verify your email to subscribe.'];
            }
        }
        $alreadySubscribedUrls = [];
        $newSubscriptions = [];

        foreach ($urls as $url) {
            $urlPrice = UrlPrice::firstOrCreate(['url' => $url]);

            // Find or create the subscription
            $subscription = Subscription::firstOrCreate([
                'url_price_id' => $urlPrice->id,
                'subscriber_id' => $subscriber->id,
            ]);
            if ($subscription->wasRecentlyCreated) {
                $newSubscriptions[] = $url; // Newly created subscription
            } else {
                $alreadySubscribedUrls[] = $url; // Subscription already exists
            }
        }
        $messages[] = empty($newSubscriptions) ? 'No new subscription(s) added' : (count($newSubscriptions) . ' subscription(s) successfully added!');

        return response()->json([
            'message' => implode("<br/>", $messages),
            'email_verified' => $emailVerified,
            'new_subscriptions' => $newSubscriptions,
            'already_subscribed' => $alreadySubscribedUrls,
        ], 200);
    }

    /**
     * @param string $token
     * @return View
     */
    public function verifyEmail(string $token): View
    {
        $subscriber = Subscriber::where('verification_token', $token)->first();

        if (!$subscriber) {
            return view('subscriber.verification', [
                'message' => 'Invalid or expired verification token.',
                'success' => false
            ]);
        }

        if ($subscriber->verified_at) {
            return view('subscriber.verification', [
                'message' => 'Your email is already verified.',
                'success' => true
            ]);
        }

        $subscriber->update([
            'verified_at' => now(),  // Carbon::now(),
            'verification_token' => null   // clear the token after verification
        ]);

        return view('subscriber.verification', [
            'message' => 'Your subscription email successfully verified!!',
            'success' => true
        ]);
    }
}
