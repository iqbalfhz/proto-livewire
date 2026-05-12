<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Laravel\WorkOS\Http\Requests\AuthKitAuthenticationRequest;
use Laravel\WorkOS\Http\Requests\AuthKitLoginRequest;
use Laravel\WorkOS\Http\Requests\AuthKitLogoutRequest;

Route::middleware(['guest'])->group(function () {
    Route::get('login', fn (AuthKitLoginRequest $request) => $request->redirect())->name('login');

    Route::get('authenticate', function (AuthKitAuthenticationRequest $request) {
        $request->authenticate();

        $user = auth()->user();

        // Reject any email not in the ALLOWED_EMAILS whitelist.
        // If the user was just created by WorkOS, delete their record to keep the DB clean.
        $allowedEmails = array_filter(array_map('trim', explode(',', config('auth.allowed_emails', ''))));

        if (! empty($allowedEmails) && ! in_array($user->email, $allowedEmails)) {
            $wasJustCreated = $user->wasRecentlyCreated;

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($wasJustCreated) {
                $user->delete();
            }

            abort(403, 'Access denied. Your account is not authorized to access this application.');
        }

        $currentTeam = $user->currentTeam ?? $user->personalTeam();

        if ($currentTeam && ! $user->current_team_id) {
            $user->switchTeam($currentTeam);
        }

        if ($currentTeam) {
            URL::defaults(['current_team' => $currentTeam->slug]);
        }

        return redirect()->intended(route('admin.dashboard'));
    });
});

Route::post('logout', fn (AuthKitLogoutRequest $request) => $request->logout())
    ->middleware(['auth'])->name('logout');
