<?php

use App\Http\Controllers\ProfileController;
use App\Models\ChatConversation;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ContactController;
use Symfony\Component\HttpFoundation\Response;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Servicios
Route::get('/servicios',  fn () => Inertia::render('Services'))->name('services');
Route::get('/productos',  fn () => Inertia::render('Products'))->name('products');

// Software / SAT
Route::get('/icb-sat', fn () => Inertia::render('IcbSat'))->name('icb-sat');
Route::redirect('/sat-it', '/icb-sat'); // compatibilidad URL antigua

// Legal
Route::get('/aviso-legal',            fn () => Inertia::render('Legal/AvisoLegal'))->name('legal.aviso');
Route::get('/politica-de-privacidad', fn () => Inertia::render('Legal/PoliticaPrivacidad'))->name('legal.privacidad');
Route::get('/politica-de-cookies',    fn () => Inertia::render('Legal/PoliticaCookies'))->name('legal.cookies');

Route::post('/contact', [ContactController::class, 'send'])
    ->middleware('throttle:contact')
    ->name('contact.send');

// Email resume link — one-time-use signed URL.
// Security properties:
//   • No visitor token in the URL (nonce only — not the token)
//   • New token returned ONLY in the URL fragment (never query string, never logged)
//   • Nonce stored hashed; invalidated on first use
//   • Response: Referrer-Policy: no-referrer + Cache-Control: no-store
Route::get('/chat/resume/{uuid}', function (Request $request, string $uuid) {
    // 1. Laravel signed URL validation (signature + expiry)
    if (! $request->hasValidSignature()) {
        return response()->view('chat.resume-error', [], Response::HTTP_GONE);
    }

    $plainNonce = (string) $request->query('nonce', '');
    $conv       = ChatConversation::where('uuid', $uuid)->first();

    // 2. Nonce must exist and match (and conversation must exist)
    if (
        $conv === null
        || blank($conv->resume_nonce_hash)
        || ! hash_equals((string) $conv->resume_nonce_hash, hash('sha256', $plainNonce))
    ) {
        return response()->view('chat.resume-error', [], Response::HTTP_GONE);
    }

    // 3. Invalidate nonce (one-time use) and issue a new token
    $newRawToken = bin2hex(random_bytes(32));
    $conv->update([
        'resume_nonce_hash'  => null,                           // consume nonce
        'resume_token_hash'  => hash('sha256', $newRawToken),   // new token for device B
    ]);

    // 4. Redirect — token in fragment only (never reaches server, never in logs)
    // Restrict page_url to own domain to prevent open-redirect token theft.
    $rawBase  = strtok($conv->page_url ?: '/', '?');
    $appUrl   = rtrim((string) config('app.url'), '/');
    $base     = str_starts_with((string) $rawBase, $appUrl) ? $rawBase : $appUrl . '/';
    $fragment = 'chat=' . $uuid . '.' . $newRawToken;

    return redirect("{$base}#{$fragment}")
        ->withHeaders([
            'Referrer-Policy' => 'no-referrer',
            'Cache-Control'   => 'no-store, no-cache, must-revalidate',
        ]);
})->name('chat.resume');

require __DIR__.'/auth.php';
