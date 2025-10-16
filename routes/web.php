<?php

use Illuminate\Support\Facades\Route;
use LaravelUltra\Core\Ultra;

Route::get('/ultra-demo', function () {
    $table = Ultra::table(\App\Models\User::class)
        ->addTextColumn('name')->sortable()->searchable()
        ->addEmailColumn('email')->sortable()
        ->addDateColumn('created_at')
        ->withPagination(10);

    $form = Ultra::form(\App\Models\User::class)
        ->addText('name')->required()
        ->addEmail('email')->required()
        ->addPassword('password')->required();

    return view('ultra::demo', [
        'table' => $table->toResponse(request()),
        'form' => $form->toResponse(request()),
    ]);
});