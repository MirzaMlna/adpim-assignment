<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SubDivision;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $subDivision = SubDivision::firstOrCreate([
            'name' => 'Sub Bidang Dokumentasi Pimpinan',
        ]);

        $user = User::create([
            'sub_division_id' => $subDivision->id,
            'name' => $request->name,
            'email' => $request->email,
            // Password di-hash oleh mutator pada model User.
            'password' => $request->password,
            'nip' => (string) now()->format('ymdHis') . random_int(10, 99),
            'rank' => 'Staf',
            'job_title' => 'Staff',
            'role' => 'STAFF',
            'is_active' => true,
            'note' => 'Akun registrasi',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
