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
    Mail::raw('This is a test email from Laravel!', function ($message) {
        $message->to('test@example.com')->subject('Test Email');
    });
    return 'Test email sent!';
});
