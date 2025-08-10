<?php

namespace App\ProjectManagement\Http\Controllers;

use App\ProjectManagement\Http\Requests\ProfileUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Get all validated data from the request
        $validatedData = $request->validated();

        // Handle the boolean values for checkboxes
        // An unchecked checkbox doesn't send a value, so we default it to false.
        $validatedData['notify_by_email'] = $request->has('notify_by_email');
        $validatedData['notify_by_telegram'] = $request->has('notify_by_telegram');

        // Fill the user model with the validated data
        $request->user()->fill($validatedData);

        // If the user changed their email, we need to reset the verification status
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
