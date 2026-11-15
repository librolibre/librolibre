<?php

use App\Http\Controllers\Api\Admin\BookController as AdminBookController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BookPhotoController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\IsbnController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\MyBookController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OfferController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SuggestionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserRatingController;
use Illuminate\Support\Facades\Route;



// Laravel's default route returned the raw model (status, strikes...); replaced by GET /me
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Los ids numéricos del spec; las notificaciones usan UUID.
Route::pattern('user', '[0-9]+');
Route::pattern('book', '[0-9]+');
Route::pattern('offer', '[0-9]+');
Route::pattern('report', '[0-9]+');
Route::pattern('photo', '[0-9]+');
Route::pattern('notification', '[0-9a-fA-F-]{36}');

Route::get('/health', fn () => ['status' => 'ok']);
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/languages', [LanguageController::class, 'index'])->name('languages.index');

Route::get('/auth/google/redirect', [AuthController::class, 'redirect']);
Route::get('/auth/google/callback', [AuthController::class, 'callback']);

Route::post('/auth/token', [AuthController::class, 'token']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [MeController::class, 'show']);
});


/*
|--------------------------------------------------------------------------
| Administración (Sanctum + administrador)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'can:admin'])->group(function () {

    // Users
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
    Route::post('/admin/users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('admin.users.suspend');
    Route::post('/admin/users/{user}/reactivate', [AdminUserController::class, 'reactivate'])->name('admin.users.reactivate');

    // Books
    Route::get('/admin/books', [AdminBookController::class, 'index'])->name('admin.books.index');
    Route::post('/admin/books/{book}/hide', [AdminBookController::class, 'hide'])->name('admin.books.hide');
    Route::post('/admin/books/{book}/restore', [AdminBookController::class, 'restore'])->name('admin.books.restore');

    // Reports (markAsValid y no "validate", que choca con el método validate() de los controladores base)
    Route::get('/admin/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/admin/reports/{report}', [AdminReportController::class, 'show'])->name('admin.reports.show');
    Route::post('/admin/reports/{report}/validate', [AdminReportController::class, 'markAsValid'])->name('admin.reports.validate');
    Route::post('/admin/reports/{report}/dismiss', [AdminReportController::class, 'dismiss'])->name('admin.reports.dismiss');
});

/*
|--------------------------------------------------------------------------
| Con sesión iniciada (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Profile (GET /me ya existe)
    Route::patch('/me', [MeController::class, 'update'])->name('me.update');
    Route::delete('/me', [MeController::class, 'destroy'])->name('me.destroy');
    Route::post('/me/privacy-consent', [MeController::class, 'acceptPrivacyConsent'])->name('me.privacy-consent');
    Route::post('/me/photo', [MeController::class, 'updatePhoto'])->name('me.photo');
    Route::get('/me/books', [MyBookController::class, 'index'])->name('me.books.index');

    // Reference data
    Route::get('/isbn/{isbn}', IsbnController::class)->name('isbn.show');

    // My books
    Route::post('/books', [BookController::class, 'store'])->name('books.store');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    Route::post('/books/{book}/pause', [BookController::class, 'pause'])->name('books.pause');
    Route::post('/books/{book}/resume', [BookController::class, 'resume'])->name('books.resume');
    Route::post('/books/{book}/photos', [BookPhotoController::class, 'store'])->name('books.photos.store');
    Route::delete('/books/{book}/photos/{photo}', [BookPhotoController::class, 'destroy'])->name('books.photos.destroy');

    // Offers
    Route::get('/offers', [OfferController::class, 'index'])->name('offers.index');
    Route::post('/offers', [OfferController::class, 'store'])->name('offers.store');
    Route::get('/offers/{offer}', [OfferController::class, 'show'])->name('offers.show');
    Route::post('/offers/{offer}/accept', [OfferController::class, 'accept'])->name('offers.accept');
    Route::post('/offers/{offer}/reject', [OfferController::class, 'reject'])->name('offers.reject');
    Route::post('/offers/{offer}/counteroffer', [OfferController::class, 'counteroffer'])->name('offers.counteroffer');
    Route::post('/offers/{offer}/cancel', [OfferController::class, 'cancel'])->name('offers.cancel');
    Route::post('/offers/{offer}/confirm-delivery', [OfferController::class, 'confirmDelivery'])->name('offers.confirm-delivery');

    // Chat
    Route::get('/offers/{offer}/messages', [MessageController::class, 'index'])->name('offers.messages.index');
    Route::post('/offers/{offer}/messages', [MessageController::class, 'store'])->name('offers.messages.store');
    Route::post('/offers/{offer}/messages/read', [MessageController::class, 'markAsRead'])->name('offers.messages.read');

    // Ratings
    Route::post('/offers/{offer}/rating', [RatingController::class, 'store'])->name('offers.rating.store');

    // Notifications (las rutas fijas van antes que la que lleva {notification})
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Blocks
    Route::get('/blocks', [BlockController::class, 'index'])->name('blocks.index');
    Route::post('/blocks', [BlockController::class, 'store'])->name('blocks.store');
    Route::delete('/blocks/{user}', [BlockController::class, 'destroy'])->name('blocks.destroy');

    // Reports
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

    // Suggestions
    Route::get('/suggestions', [SuggestionController::class, 'index'])->name('suggestions.index');
});

/*
|--------------------------------------------------------------------------
| Acceso opcional (visitante o con token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth.optional')->group(function () {
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/ratings', [UserRatingController::class, 'index'])->name('users.ratings.index');

    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

    Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalog/map', [CatalogController::class, 'map'])->name('catalog.map');
});


