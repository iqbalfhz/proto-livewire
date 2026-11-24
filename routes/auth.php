<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\WorkOS\Http\Requests\AuthKitAuthenticationRequest;
use Laravel\WorkOS\Http\Requests\AuthKitLoginRequest;
use Laravel\WorkOS\Http\Requests\AuthKitLogoutRequest;

Route::middleware(['guest'])->group(function () {
    Route::get('login', fn (AuthKitLoginRequest $request) => $request->redirect())->name('login');

    Route::get('authenticate', function (AuthKitAuthenticationRequest $request) {
        $request->authenticate();

        $user = auth()->user();

        // If a rejected user was just created by WorkOS, delete the record to keep the DB clean.
        if (! $user->isAllowedToSignIn()) {
            $wasJustCreated = $user->wasRecentlyCreated;

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($wasJustCreated) {
                $user->delete();
            }

            abort(403, 'Access denied. Your account is not authorized to access this application.');
        }

        return redirect()->intended(route('admin.dashboard'));
    });
});

Route::post('logout', function (AuthKitLogoutRequest $request) {
    return $request->logout(url('/'));
})->middleware(['auth'])->name('logout');
