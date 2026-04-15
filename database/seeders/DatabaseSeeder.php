<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions
        $permissions = [
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
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions(Permission::whereNotIn('name', [
            'delete sales', 'manage branches', 'manage settings',
            'view users', 'create users', 'edit users', 'delete users',
        ])->get());

        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'view dashboard', 'view products', 'view categories',
            'create sales', 'view sales',
            'view customers', 'create customers',
            'view inventory',
        ]);

        // Default branch
        $branch = Branch::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'Main Branch',
                'address' => '123 Main Street',
                'city' => 'New York',
                'phone' => '+1-555-0100',
                'email' => 'main@wehancepos.com',
                'currency' => 'USD',
                'currency_symbol' => '$',
                'tax_rate' => 10,
                'receipt_footer' => 'Thank you for your business!',
                'is_active' => true,
            ]
        );

        AppSetting::setValue('app_name', 'WehancePOS');
        AppSetting::setValue('enable_loyalty', false);
        AppSetting::setValue('loyalty_rate', 1);

        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@wehancepos.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'branch_id' => $branch->id,
                'phone' => '+1-555-0001',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles('super_admin');

        $managerUser = User::firstOrCreate(
            ['email' => 'manager@wehancepos.com'],
            [
                'name' => 'Branch Manager',
                'password' => Hash::make('password'),
                'branch_id' => $branch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $managerUser->syncRoles('manager');

        $cashierUser = User::firstOrCreate(
            ['email' => 'cashier@wehancepos.com'],
            [
                'name' => 'Cashier One',
                'password' => Hash::make('password'),
                'branch_id' => $branch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $cashierUser->syncRoles('cashier');

        // Units
        foreach ([
            ['name' => 'Piece', 'abbreviation' => 'pcs'],
            ['name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['name' => 'Gram', 'abbreviation' => 'g'],
            ['name' => 'Liter', 'abbreviation' => 'L'],
            ['name' => 'Box', 'abbreviation' => 'box'],
            ['name' => 'Pack', 'abbreviation' => 'pack'],
        ] as $unit) {
            Unit::firstOrCreate(['abbreviation' => $unit['abbreviation']], $unit);
        }

        // Categories
        $electronics = Category::firstOrCreate(['slug' => 'electronics'], [
            'name' => 'Electronics', 'color' => '#3B82F6', 'sort_order' => 1, 'is_active' => true,
        ]);
        $food = Category::firstOrCreate(['slug' => 'food-beverages'], [
            'name' => 'Food & Beverages', 'color' => '#10B981', 'sort_order' => 2, 'is_active' => true,
        ]);
        Category::firstOrCreate(['slug' => 'clothing'], [
            'name' => 'Clothing', 'color' => '#F59E0B', 'sort_order' => 3, 'is_active' => true,
        ]);

        // Supplier
        $supplier = Supplier::firstOrCreate(['email' => 'supplier@techcorp.com'], [
            'name' => 'John Smith', 'company' => 'TechCorp Ltd',
            'phone' => '+1-555-1000', 'is_active' => true,
        ]);

        $pcsUnit = Unit::where('abbreviation', 'pcs')->first();
        $packUnit = Unit::where('abbreviation', 'pack')->first();

        foreach ([
            [
                'category_id' => $electronics->id, 'unit_id' => $pcsUnit->id,
                'supplier_id' => $supplier->id, 'name' => 'Wireless Mouse', 'slug' => 'wireless-mouse',
                'barcode' => '8901234567890', 'sku' => 'ELEC-001',
                'cost_price' => 8.00, 'selling_price' => 19.99, 'tax_rate' => 10,
                'min_stock_alert' => 5, 'is_active' => true,
            ],
            [
                'category_id' => $electronics->id, 'unit_id' => $pcsUnit->id,
                'supplier_id' => $supplier->id, 'name' => 'USB-C Hub', 'slug' => 'usb-c-hub',
                'barcode' => '8901234567891', 'sku' => 'ELEC-002',
                'cost_price' => 12.00, 'selling_price' => 34.99, 'tax_rate' => 10,
                'min_stock_alert' => 3, 'is_active' => true,
            ],
            [
                'category_id' => $food->id, 'unit_id' => $packUnit->id,
                'name' => 'Organic Coffee', 'slug' => 'organic-coffee',
                'barcode' => '8901234567892', 'sku' => 'FOOD-001',
                'cost_price' => 5.00, 'selling_price' => 12.99, 'tax_rate' => 5,
                'min_stock_alert' => 10, 'is_active' => true,
            ],
        ] as $productData) {
            $product = Product::firstOrCreate(['slug' => $productData['slug']], $productData);
            Inventory::firstOrCreate(
                ['product_id' => $product->id, 'product_variant_id' => null, 'branch_id' => $branch->id],
                ['quantity' => 50]
            );
        }

        // Customers
        Customer::firstOrCreate(['email' => 'walkin@wehancepos.com'], [
            'name' => 'Walk-in Customer', 'is_active' => true,
        ]);
        Customer::firstOrCreate(['email' => 'jane.doe@example.com'], [
            'name' => 'Jane Doe', 'phone' => '+1-555-2001', 'city' => 'New York',
            'loyalty_points' => 150, 'group' => 'VIP', 'is_active' => true,
        ]);

        // Expense categories
        foreach (['Rent', 'Utilities', 'Salaries', 'Marketing', 'Supplies', 'Maintenance', 'Other'] as $cat) {
            ExpenseCategory::firstOrCreate(['name' => $cat]);
        }

        $this->command->info('✅ Seeded! Login: admin@wehancepos.com / password');
    }
}
