<?php

use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])
    ->name('subscribe');

Route::get('/subscriber/verify/{token}', [SubscriptionController::class, 'verifyEmail'])->name('subscriber.verify');

Route::get('/test-email', function () {
    $email = 'test@example.com';
    Mail::raw('This is a test email from Laravel!', function ($message) use ($email) {
        $message->to($email)->subject('Test Email');
    });
    return "Test email sent to `$email`!";
});
