<?php

use App\Infrastructure\Http\Controllers\AuthController;
use App\Infrastructure\Http\Controllers\BookController;
use App\Infrastructure\Http\Controllers\CapacityController;
use App\Infrastructure\Http\Controllers\CopyController;
use App\Infrastructure\Http\Controllers\FineController;
use App\Infrastructure\Http\Controllers\HealthController;
use App\Infrastructure\Http\Controllers\LoanController;
use App\Infrastructure\Http\Controllers\ReaderController;
use App\Infrastructure\Http\Controllers\SettingsController;
use App\Infrastructure\Http\Controllers\ShelfController;
use App\Infrastructure\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthController::class);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // CU-01
    Route::middleware('permission:SHELF_MANAGE')->group(function () {
        Route::get('shelves', [ShelfController::class, 'index']);
        Route::post('shelves', [ShelfController::class, 'store']);
    });

    // CU-02
    Route::post('books', [BookController::class, 'store'])->middleware('permission:BOOK_REGISTER');

    // CU-03 and CU-04
    Route::middleware('permission:COPY_MANAGE')->group(function () {
        Route::post('books/{isbn}/copies', [CopyController::class, 'store']);
        Route::put('copies/{code}/location', [CopyController::class, 'place']);
    });

    // CU-05
    Route::get('capacity', CapacityController::class)->middleware('permission:CAPACITY_VIEW');

    // CU-06
    Route::post('readers', [ReaderController::class, 'store'])->middleware('permission:READER_REGISTER');

    // CU-07, CU-08 and CU-09 (with CU-14 and CU-15)
    Route::middleware('permission:LOAN_MANAGE')->group(function () {
        Route::post('loans', [LoanController::class, 'store']);
        Route::post('loans/{copyCode}/renewal', [LoanController::class, 'renew']);
        Route::post('returns', [LoanController::class, 'returnCopy']);
    });

    // CU-10
    Route::post('fines/{id}/payment', [FineController::class, 'pay'])
        ->whereNumber('id')
        ->middleware('permission:FINE_REGISTER_PAYMENT');

    // CU-11
    Route::get('readers/{identification}/history', [ReaderController::class, 'history'])
        ->middleware('permission:LOAN_VIEW');

    // CU-12
    Route::middleware('permission:USER_MANAGE')->group(function () {
        Route::get('users', [UserController::class, 'index']);
        Route::post('users', [UserController::class, 'store']);
        Route::put('users/{id}', [UserController::class, 'update'])->whereNumber('id');
        Route::put('users/{id}/roles', [UserController::class, 'roles'])->whereNumber('id');
    });

    // CU-13
    Route::middleware('permission:SETTINGS_UPDATE')->group(function () {
        Route::get('settings', [SettingsController::class, 'show']);
        Route::put('settings', [SettingsController::class, 'update']);
    });
});
