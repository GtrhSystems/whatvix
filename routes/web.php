<?php

use App\Http\Controllers\Web as WEB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\GoogleAuthController;



Route::patch('set-locale/{locale}', [LocaleController::class, 'update'])->name('set-locale');

// common
Route::get('/', [WEB\WebPageController::class, 'home'])->name('home');
Route::get('/about-us', [WEB\WebPageController::class, 'about']);
Route::get('/pricing', [WEB\WebPageController::class, 'pricing']);
Route::resource('/contact-us', WEB\ContactController::class)->only('index', 'store');
Route::get('/faq', [WEB\WebPageController::class, 'faq']);
Route::get('faq-category/{slug}', [WEB\WebPageController::class, 'faqCategory'])->name('faq-category');

// pages
Route::get('/team', [WEB\WebPageController::class, 'team']);
Route::get('/integrations', [WEB\WebPageController::class, 'integrations']);
Route::resource('/services', WEB\ServiceController::class)->only(['index', 'show']);
Route::get('/service-category/{slug}', [WEB\ServiceController::class, 'categoryShow'])->name('service-category');

// blogs
Route::resource('/blogs', WEB\BlogController::class)->only(['index', 'show']);
Route::get('blogs/category/{slug}', [WEB\BlogController::class, 'category'])->name('blogs.category');
Route::get('blogs/tag/{slug}', [WEB\BlogController::class, 'tag'])->name('blogs.tag');

// newsletter
Route::post('/subscribe', [WEB\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/subscribed', [WEB\NewsletterController::class, 'subscribed'])->name('newsletter.subscribed');

// others
Route::post('/credit/pay', [WEB\CreditPayController::class, 'store'])->name('credit.pay');
Route::get('/credit/pay/{status}', [WEB\CreditPayController::class, 'status']);
Route::get('/ai-tools', [WEB\WebPageController::class, 'aiTools'])->name('ai-tools.index');


Route::get('/oauth/google', [GoogleAuthController::class, 'redirectTo']);
Route::get('/oauth/google/callback', [GoogleAuthController::class, 'handleCallback']);

// custom page
Route::get('/{slug}', [WEB\WebPageController::class, 'page']);
