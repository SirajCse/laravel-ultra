<?php

use Illuminate\Support\Facades\Route;
use LaravelUltra\Core\Ultra;

Route::get('/ultra-demo', function () {
    // Demo data for testing
    $demoData = [
        ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'created_at' => now()->format('Y-m-d')],
        ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'created_at' => now()->subDay()->format('Y-m-d')],
        ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'created_at' => now()->subDays(2)->format('Y-m-d')],
        ['id' => 4, 'name' => 'Alice Brown', 'email' => 'alice@example.com', 'created_at' => now()->subDays(3)->format('Y-m-d')],
        ['id' => 5, 'name' => 'Charlie Wilson', 'email' => 'charlie@example.com', 'created_at' => now()->subDays(4)->format('Y-m-d')],
    ];

    $table = Ultra::table($demoData)
        ->addTextColumn('id')->sortable()
        ->addTextColumn('name')->sortable()->searchable()
        ->addEmailColumn('email')->sortable()
        ->addDateColumn('created_at')
        ->withPagination(3);

    $form = Ultra::form()
        ->addText('name')->required()
        ->addEmail('email')->required()
        ->addPassword('password')->required();

    $modal = Ultra::modal()
        ->title('Demo Modal')
        ->content('This is a demo modal content')
        ->size('lg')
        ->withActions(['save', 'cancel']);

    return view('ultra::demo', [
        'table' => $table->toResponse(request()),
        'form' => $form->toResponse(request()),
        'modal' => $modal->toResponse(request()),
    ]);
})->name('ultra.demo');