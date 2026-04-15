<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::post('locale', function (Request $request) {
    $locale = (string) $request->string('locale');

    abort_unless(array_key_exists($locale, config('app.supported_locales', [])), 404);

    $request->session()->put('locale', $locale);

    return back();
})->name('locale.switch');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', \App\Livewire\Dashboard::class)->name('dashboard');

    // POS / Checkout
    Route::get('/pos', \App\Livewire\Pos\Checkout::class)->name('pos')
        ->middleware('can:create sales');

    // Products
    Route::get('/products', \App\Livewire\Products\ProductList::class)->name('products.index')
        ->middleware('can:view products');
    Route::get('/products/create', \App\Livewire\Products\ProductForm::class)->name('products.create')
        ->middleware('can:create products');
    Route::get('/products/{product}/edit', \App\Livewire\Products\ProductForm::class)->name('products.edit')
        ->middleware('can:edit products');

    // Categories
    Route::get('/categories', \App\Livewire\Categories\CategoryList::class)->name('categories.index')
        ->middleware('can:view categories');

    // Inventory
    Route::get('/inventory', \App\Livewire\Inventory\InventoryList::class)->name('inventory.index')
        ->middleware('can:view inventory');
    Route::get('/inventory/movements', \App\Livewire\Inventory\MovementList::class)->name('inventory.movements')
        ->middleware('can:view inventory');

    // Sales / Orders
    Route::get('/sales', \App\Livewire\Sales\SaleList::class)->name('sales.index')
        ->middleware('can:view sales');
    Route::get('/sales/{sale}', \App\Livewire\Sales\SaleDetail::class)->name('sales.show')
        ->middleware('can:view sales');

    // Suppliers
    Route::get('/suppliers', \App\Livewire\Suppliers\SupplierList::class)->name('suppliers.index')
        ->middleware('can:view suppliers');

    // Purchase Orders
    Route::get('/purchases', \App\Livewire\Purchases\PurchaseList::class)->name('purchases.index')
        ->middleware('can:view purchases');
    Route::get('/purchases/create', \App\Livewire\Purchases\PurchaseForm::class)->name('purchases.create')
        ->middleware('can:create purchases');
    Route::get('/purchases/{purchaseOrder}/edit', \App\Livewire\Purchases\PurchaseForm::class)->name('purchases.edit')
        ->middleware('can:edit purchases');
    Route::get('/purchases/{purchaseOrder}', \App\Livewire\Purchases\PurchaseDetail::class)->name('purchases.show')
        ->middleware('can:view purchases');

    // Customers
    Route::get('/customers', \App\Livewire\Customers\CustomerList::class)->name('customers.index')
        ->middleware('can:view customers');

    // Discounts
    Route::get('/discounts', \App\Livewire\Discounts\DiscountList::class)->name('discounts.index')
        ->middleware('can:view discounts');

    // Expenses
    Route::get('/expenses', \App\Livewire\Expenses\ExpenseList::class)->name('expenses.index')
        ->middleware('can:view expenses');

    // Reports
    Route::get('/reports', \App\Livewire\Reports\ReportDashboard::class)->name('reports.index')
        ->middleware('can:view reports');
    Route::get('/reports/sales', \App\Livewire\Reports\SalesReport::class)->name('reports.sales')
        ->middleware('can:view reports');
    Route::get('/reports/inventory', \App\Livewire\Reports\InventoryReport::class)->name('reports.inventory')
        ->middleware('can:view reports');
    Route::get('/reports/profit-loss', \App\Livewire\Reports\ProfitLoss::class)->name('reports.profit_loss')
        ->middleware('can:view reports');

    // Users
    Route::get('/users', \App\Livewire\Users\UserList::class)->name('users.index')
        ->middleware('can:view users');

    // Branches
    Route::get('/branches', \App\Livewire\Branches\BranchList::class)->name('branches.index')
        ->middleware('can:view branches');

    // Settings
    Route::get('/settings', \App\Livewire\Settings\GeneralSettings::class)->name('settings')
        ->middleware('can:manage settings');

    // Profile
    Route::get('/profile', \App\Livewire\Profile\UserProfile::class)->name('profile');
});
