<?php

namespace LaravelUltra\Examples;

use App\Http\Controllers\Controller;
use LaravelUltra\Core\Ultra;

class ExampleController extends Controller
{
    public function usersTable()
    {
        $demoData = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'status' => 'active', 'created_at' => now()],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'status' => 'inactive', 'created_at' => now()->subDay()],
            ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com', 'status' => 'active', 'created_at' => now()->subDays(2)],
        ];

        $table = Ultra::table($demoData)
            ->addTextColumn('id')->sortable()
            ->addTextColumn('name')->sortable()->searchable()
            ->addEmailColumn('email')->sortable()
            ->addTextColumn('status')
            ->addDateColumn('created_at')->sortable()
            ->withPagination(10);

        return $table->toResponse(request());
    }

    public function userForm()
    {
        $form = Ultra::form()
            ->addText('name')->required()
            ->addEmail('email')->required()
            ->addPassword('password')->required()
            ->addText('phone')
            ->addText('address');

        return $form->toResponse(request());
    }
}