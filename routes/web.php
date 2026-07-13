<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;

// dashboard pages
Route::get('/', function () {
    return redirect()->route('signin');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/ecommerce', function () {
    return view('pages.dashboard.ecommerce', ['title' => 'E-commerce Dashboard']);
})->name('ecommerce');

Route::get('/analytics', function () {
    return view('pages.dashboard.analytics', ['title' => 'Analytics Dashboard']);
})->name('analytics');

Route::get('/marketing', function () {
    return view('pages.dashboard.marketing', ['title' => 'Marketing Dashboard']);
})->name('marketing');

Route::get('/crm', function () {
    return view('pages.dashboard.crm', ['title' => 'CRM Dashboard']);
})->name('crm');

Route::get('/stocks', function () {
    return view('pages.dashboard.stocks', ['title' => 'Stocks Dashboard']);
})->name('stocks');

Route::get('/saas', function () {
    return view('pages.dashboard.saas', ['title' => 'SaaS Dashboard']);
})->name('saas');

Route::get('/logistics', function () {
    return view('pages.dashboard.logistics', ['title' => 'Logistics Dashboard']);
})->name('logistics');

Route::get('/ai', function () {
    return view('pages.dashboard.ai', ['title' => 'AI Dashboard']);
})->name('ai');

Route::get('/sales', function () {
    return view('pages.dashboard.sales', ['title' => 'Sales Dashboard']);
})->name('sales');

Route::get('/finance', function () {
    return view('pages.dashboard.finance', ['title' => 'Finance Dashboard']);
})->name('finance');

// layout pages
Route::get('/layout-one', function () {
    return view('pages.layouts.layout-one', ['title' => 'Layout One']);
})->name('layout-one');

Route::get('/layout-two', function () {
    return view('pages.layouts.layout-two', ['title' => 'Layout Two']);
})->name('layout-two');

Route::get('/layout-three', function () {
    return view('pages.layouts.layout-three', ['title' => 'Layout Three']);
})->name('layout-three');

Route::get('/layout-four', function () {
    return view('pages.layouts.layout-four', ['title' => 'Layout Four']);
})->name('layout-four');

Route::get('/layout-five', function () {
    return view('pages.layouts.layout-five', ['title' => 'Layout Five']);
})->name('layout-five');

Route::get('/layout-six', function () {
    return view('pages.layouts.layout-six', ['title' => 'Layout Six']);
})->name('layout-six');

//  ai pages
Route::get('/code-generator', function () {
    return view('pages.ai.code-generator', ['title' => 'Code Generator']);
})->name('code-generator');

Route::get('/text-generator', function () {
    return view('pages.ai.text-generator', ['title' => 'Text Generator']);
})->name('text-generator');

Route::get('/image-generator', function () {
    return view('pages.ai.image-generator', ['title' => 'Image Generator']);
})->name('image-generator');

Route::get('/video-generator', function () {
    return view('pages.ai.video-generator', ['title' => 'Video Generator']);
})->name('video-generator');

Route::get('/ai-settings', function () {
    return view('pages.ai.settings', ['title' => 'AI Settings']);
})->name('ai-settings');


// ecommerce pages
Route::get('/products-list', function () {
    return view('pages.ecommerce.product-list', ['title' => 'Product List']);
})->name('products-list');

Route::get('/add-product', function () {
    return view('pages.ecommerce.add-product', ['title' => 'Add Product']);
})->name('add-product');

Route::post('/add-product', function (Illuminate\Http\Request $request) {
    return view('pages.ecommerce.add-product', ['title' => 'Add Product']);
})->name('add-product.store');

Route::get('/billing', function () {
    return view('pages.ecommerce.billing', ['title' => 'Billing']);
})->name('billing');

Route::get('/invoices', function () {
    return view('pages.ecommerce.invoices', ['title' => 'Invoices']);
})->name('invoices');

Route::get('/single-invoice', function () {
    return view('pages.ecommerce.single-invoice', ['title' => 'Single Invoice']);
})->name('single-invoice');

Route::get('/create-invoice', function () {
    return view('pages.ecommerce.create-invoice', ['title' => 'Create Invoice']);
})->name('create-invoice');

Route::get('/transactions', function () {
    return view('pages.ecommerce.transactions', ['title' => 'Transactions']);
})->name('transactions');

Route::get('/single-transaction', function () {
    return view('pages.ecommerce.single-transaction', ['title' => 'Single Transaction']);
})->name('single-transaction');

// calender pages
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Calendar']);
})->name('calendar');

// profile pages
Route::get('/profile', function () {
    return view('pages.profile', ['title' => 'Profile']);
})->name('profile');

// task pages
Route::get('/task-list', function () {
    return view('pages.task.task-list', ['title' => 'Task List']);
})->name('task-list');

Route::get('/task-kanban', function () {
    return view('pages.task.task-kanban', ['title' => 'Task Kanban']);
})->name('task-kanban');

// form pages
Route::get('/form-elements', function () {
    return view('pages.form.form-elements', ['title' => 'Form Elements']);
})->name('form-elements');

Route::get('/form-layout', function () {
    return view('pages.form.form-layout', ['title' => 'Form Layout']);
})->name('form-layout');

// tables pages
Route::get('/basic-tables', function () {
    return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
})->name('basic-tables');

Route::get('/data-tables', function () {
    return view('pages.tables.data-tables', ['title' => 'Data Tables']);
})->name('data-tables');

// pages
Route::get('/file-manager', function () {
    return view('pages.file-manager', ['title' => 'File Manager']);
})->name('file-manager');

Route::get('/pricing-tables', function () {
    return view('pages.pricing-tables', ['title' => 'Pricing Tables']);
})->name('pricing-tables');

Route::get('/faq', function () {
    return view('pages.faq', ['title' => 'Faq']);
})->name('faq');

Route::get('/blank', function () {
    return view('pages.blank', ['title' => 'Blank']);
})->name('blank');



Route::get('/integrations', function () {
    return view('pages.integrations', ['title' => 'Integrations']);
})->name('integrations');

// error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

Route::get('/error-500', function () {
    return view('pages.errors.error-500', ['title' => 'Error 500']);
})->name('error-500');

Route::get('/error-503', function () {
    return view('pages.errors.error-503', ['title' => 'Error 503']);
})->name('error-503');

// success pages
Route::get('/success', function () {
    return view('pages.success', ['title' => 'Success']);
})->name('success');

// maintenance pages
Route::get('/maintenance', function () {
    return view('pages.maintenance', ['title' => 'Maintenance']);
})->name('maintenance');

// coming soon pages
Route::get('/coming-soon', function () {
    return view('pages.coming-soon', ['title' => 'Coming Soon']);
})->name('coming-soon');

// chart pages
Route::get('/line-chart', function () {
    return view('pages.chart.line-chart', ['title' => 'Line Chart']);
})->name('line-chart');

Route::get('/bar-chart', function () {
    return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
})->name('bar-chart');

Route::get('/pie-chart', function () {
    return view('pages.chart.pie-chart', ['title' => 'Pie Chart']);
})->name('pie-chart');

Route::get('/radar-chart', function () {
    return view('pages.chart.radar-chart', ['title' => 'Radar Chart']);
})->name('radar-chart');

Route::get('/radial-chart', function () {
    return view('pages.chart.radial-chart', ['title' => 'Radial Chart']);
})->name('radial-chart');

// map pages
Route::get('/maps', function () {
    return view('pages.map.gmaps', ['title' => 'Maps']);
})->name('maps');

Route::get('/vector-maps', function () {
    return view('pages.map.vector-maps', ['title' => 'Vector Map']);
})->name('vector-maps');

Route::get('/chat', function () {
    return view('pages.chat', ['title' => 'Chat']);
})->name('chat');

// support pages
Route::get('/support-tickets', function () {
    return view('pages.support.support-tickets', ['title' => 'Support Tickets']);
})->name('support-tickets');

Route::get('/support-ticket-reply', function () {
    return view('pages.support.support-ticket-reply', ['title' => 'Support Ticket Reply']);
})->name('support-ticket-reply');

// email pages
Route::get('/inbox', function () {
    return view('pages.email.inbox', ['title' => 'Inbox']);
})->name('inbox');

    Route::get('/inbox-details', function () {
        return view('pages.email.inbox-details', ['title' => 'Inbox Details']);
    })->name('inbox-details');
});

// authentication pages


Route::get('/signin', [AuthController::class, 'showLoginForm'])->name('signin');
Route::post('/signin', [AuthController::class, 'login'])->name('signin.store');

Route::get('/signup', [AuthController::class, 'showRegistrationForm'])->name('signup');
Route::post('/signup', [AuthController::class, 'register'])->name('signup.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');



Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');

Route::get('/reset-password', function () {
    return redirect()->route('password.request');
})->name('reset-password');

Route::get('/two-step-verification', function () {
    return view('pages.auth.two-step-verification', ['title' => 'Two Step Verification']);
})->name('two-step-verification');

// ui elements pages
Route::middleware(['auth'])->group(function () {
    Route::get('/alerts', function () {
    return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
})->name('alerts');

Route::get('/avatars', function () {
    return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
})->name('avatars');

Route::get('/badge', function () {
    return view('pages.ui-elements.badges', ['title' => 'Badges']);
})->name('badges');

Route::get('/breadcrumb', function () {
    return view('pages.ui-elements.breadcrumbs', ['title' => 'Breadcrumbs']);
})->name('breadcrumbs');

Route::get('/buttons', function () {
    return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
})->name('buttons');

Route::get('/buttons-group', function () {
    return view('pages.ui-elements.buttons-group', ['title' => 'Buttons Group']);
})->name('buttons-group');

Route::get('/cards', function () {
    return view('pages.ui-elements.cards', ['title' => 'Cards']);
})->name('cards');

Route::get('/carousel', function () {
    return view('pages.ui-elements.carousel', ['title' => 'Carousel']);
})->name('carousel');

Route::get('/dropdowns', function () {
    return view('pages.ui-elements.dropdowns', ['title' => 'Dropdowns']);
})->name('dropdowns');

Route::get('/image', function () {
    return view('pages.ui-elements.images', ['title' => 'Images']);
})->name('images');

Route::get('/links', function () {
    return view('pages.ui-elements.links', ['title' => 'Links']);
})->name('links');

Route::get('/list', function () {
    return view('pages.ui-elements.list', ['title' => 'List']);
})->name('list');

Route::get('/modals', function () {
    return view('pages.ui-elements.modals', ['title' => 'Modals']);
})->name('modals');

Route::get('/notifications', function () {
    return view('pages.ui-elements.notifications', ['title' => 'Notifications']);
})->name('notifications');

Route::get('/pagination', function () {
    return view('pages.ui-elements.pagination', ['title' => 'Pagination']);
})->name('pagination');

Route::get('/popovers', function () {
    return view('pages.ui-elements.popovers', ['title' => 'Popovers']);
})->name('popovers');

Route::get('/progress-bar', function () {
    return view('pages.ui-elements.progress-bar', ['title' => 'Progress Bar']);
})->name('progress-bar');

Route::get('/ribbons', function () {
    return view('pages.ui-elements.ribbons', ['title' => 'Ribbons']);
})->name('ribbons');

Route::get('/spinners', function () {
    return view('pages.ui-elements.spinners', ['title' => 'Spinners']);
})->name('spinners');

Route::get('/tabs', function () {
    return view('pages.ui-elements.tabs', ['title' => 'Tabs']);
})->name('tabs');

Route::get('/tooltips', function () {
    return view('pages.ui-elements.tooltips', ['title' => 'Tooltips']);
})->name('tooltips');

    Route::get('/videos', function () {
        return view('pages.ui-elements.videos', ['title' => 'Videos']);
    })->name('videos');
});
