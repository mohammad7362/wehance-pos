<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class LoginController extends Controller
{
    private const DEFAULT_PERMISSIONS = [
        'view dashboard',
        'view products', 'create products', 'edit products', 'delete products',
        'view categories', 'create categories', 'edit categories', 'delete categories',
        'view inventory', 'adjust inventory',
        'view sales', 'create sales', 'refund sales', 'delete sales',
        'view purchases', 'create purchases', 'edit purchases', 'receive purchases',
        'view customers', 'create customers', 'edit customers', 'delete customers',
        'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers',
        'view discounts', 'create discounts', 'edit discounts', 'delete discounts',
        'view reports',
        'view expenses', 'create expenses', 'edit expenses', 'delete expenses',
        'view users', 'create users', 'edit users', 'delete users',
        'view branches', 'manage branches', 'manage settings',
    ];

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if ($user && ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => __('Your account has been deactivated. Please contact an administrator.'),
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $this->grantFullAccessToAuthenticatedUser();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function grantFullAccessToAuthenticatedUser(): void
    {
        $user = Auth::user();

        if (! $user instanceof \App\Models\User) {
            return;
        }

        foreach (self::DEFAULT_PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::query()->get());

        $user->syncRoles(['super_admin']);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
