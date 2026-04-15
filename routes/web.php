<?php

use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\LoadDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->prefix('admin')->name('admin.exports.')->group(function (): void {
    Route::get('/export/portfolio-analysis', [ExportController::class, 'portfolioAnalysis'])->name('portfolio');
    Route::get('/export/collection-details', [ExportController::class, 'collectionDetails'])->name('collection');
    Route::get('/export/reconciliation', [ExportController::class, 'reconciliation'])->name('reconciliation');
});

Route::middleware('auth')->prefix('admin')->name('admin.loads.')->group(function (): void {
    Route::get('/portfolio-loads/template', [LoadDownloadController::class, 'portfolioTemplate'])->name('portfolio.template');
    Route::get('/collection-loads/template', [LoadDownloadController::class, 'collectionTemplate'])->name('collection.template');
    Route::get('/portfolio-loads/{portfolioLoad}/errors', [LoadDownloadController::class, 'portfolioErrors'])->name('portfolio.errors');
    Route::get('/collection-loads/{collectionLoad}/errors', [LoadDownloadController::class, 'collectionErrors'])->name('collection.errors');
    Route::get('/portfolio-loads/{portfolioLoad}/source', [LoadDownloadController::class, 'portfolioSource'])->name('portfolio.source');
    Route::get('/collection-loads/{collectionLoad}/source', [LoadDownloadController::class, 'collectionSource'])->name('collection.source');
});
