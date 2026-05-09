<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $branch = Branch::create([
                'name' => $validated['name'] . ' Branch',
                'code' => $this->uniqueBranchCode($validated['name']),
                'email' => $validated['email'],
                'currency' => 'USD',
                'currency_symbol' => '$',
                'tax_rate' => 0,
                'receipt_footer' => 'Thank you for your business!',
                'is_active' => true,
            ]);

            $user = new User();
            $user->forceFill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'branch_id' => $branch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ])->save();

            $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
            $superAdmin->syncPermissions(Permission::query()->get());
            $user->assignRole($superAdmin);

            Auth::login($user);
            $request->session()->regenerate();
        });

        return redirect()->intended(route('dashboard'));
    }

    private function uniqueBranchCode(string $name): string
    {
        $base = strtoupper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $name) ?: 'BRN', 0, 8));
        $code = $base;
        $index = 1;

        while (Branch::query()->where('code', $code)->exists()) {
            $suffix = (string) $index;
            $code = Str::substr($base, 0, max(1, 8 - strlen($suffix))) . $suffix;
            $index++;
        }

        return $code;
    }
}
