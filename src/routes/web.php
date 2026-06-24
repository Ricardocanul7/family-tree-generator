<?php

use App\Http\Controllers\FamilyTreeController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'es', 'pl'])) {
        session()->put('locale', $locale);
        App::setLocale($locale);
    }
    return redirect()->back();
})->name('language.switch');

Route::get('/', [FamilyTreeController::class, 'index'])->name('family-tree.index');
Route::get('/api/tree/full', [FamilyTreeController::class, 'fullTree'])->name('family-tree.full');
Route::get('/api/tree/{person}', [FamilyTreeController::class, 'treeData'])->name('family-tree.data');
Route::get('/tree/{person}', [FamilyTreeController::class, 'show'])->name('family-tree.person');
