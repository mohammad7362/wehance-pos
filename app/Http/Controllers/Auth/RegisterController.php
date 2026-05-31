<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
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
            $user = new User();
            $user->forceFill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'branch_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
            ])->save();

            $branch = $this->createBranchWithUniqueCode($validated['name'], $validated['email'], $user->id);

            $user->forceFill([
                'branch_id' => $branch->id,
            ])->save();

            $this->ensureRolesAndPermissions();
            $user->syncRoles(['super_admin']);

            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            Auth::login($user);
            $request->session()->regenerate();
        });

        return redirect()->intended(route('dashboard'));
    }

    private function createBranchWithUniqueCode(string $name, string $email, int $userId): Branch
    {
        $base = strtoupper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $name) ?: 'BRN', 0, 8));

        for ($index = 0; $index < 500; $index++) {
            $suffix = $index === 0 ? '' : (string) $index;
            $code = Str::substr($base, 0, max(1, 8 - strlen($suffix))) . $suffix;

            try {
                return Branch::create([
                    'name' => $name . ' Branch',
                    'code' => $code,
                    'email' => $email,
                    'created_by' => $userId,
                    'currency' => 'USD',
                    'currency_symbol' => '$',
                    'tax_rate' => 0,
                    'receipt_footer' => 'Thank you for your business!',
                    'is_active' => true,
                ]);
            } catch (UniqueConstraintViolationException $e) {
                if (! str_contains($e->getMessage(), 'branches_code_unique')) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Unable to generate a unique branch code.');
    }

    private function ensureRolesAndPermissions(): void
    {
        foreach (self::DEFAULT_PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::query()->get());
    }
}
