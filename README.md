
# 🧠 Laravel Ultra

> A quick guide with complete usage examples and reminders for building advanced UI components using **Laravel Ultra**.

---

## 📊 Advanced Table Usage

### 🧩 From Eloquent Model

```php
use LaravelUltra\Ultra;

$table = Ultra::table(User::query())
    ->addTextColumn('id')->sortable()
    ->addTextColumn('name')->sortable()->searchable()
    ->addEmailColumn('email')->sortable()->searchable()
    ->addDateColumn('created_at')->sortable()
    ->addBooleanColumn('is_active')
    ->withPagination(15);

return $table->toResponse(request());
````

### 📦 From Array Data

```php
$data = [
    ['id' => 1, 'name' => 'John', 'email' => 'john@example.com', 'active' => true],
    ['id' => 2, 'name' => 'Jane', 'email' => 'jane@example.com', 'active' => false],
];

$table = Ultra::table($data)
    ->addTextColumn('name')
    ->addEmailColumn('email')
    ->addBooleanColumn('active')->labels('Active', 'Inactive')
    ->withPagination(10);

return $table->toResponse(request());
```

---

## 📝 Advanced Form Usage

```php
$form = Ultra::form(User::class)
    ->addText('name')->required()
    ->addEmail('email')->required()
    ->addPassword('password')->required()
    ->addText('phone')
    ->addTextarea('bio')
    ->addSelect('role', 'User Role')
        ->options([
            'admin' => 'Administrator',
            'user' => 'Regular User',
        ]);

return $form->toResponse(request());
```

### 💡 Tip:

You can bind Eloquent models directly:

```php
$form->fill($user)->saveOnSubmit();
```

---

## 🪟 Modal Usage

```php
$modal = Ultra::modal()
    ->title('Create User')
    ->content($form)
    ->size('lg')
    ->withActions(['save', 'cancel']);

return $modal->toResponse(request());
```

### Modal Sizes:

* `sm` — Small
* `md` — Medium (default)
* `lg` — Large
* `xl` — Extra Large

---

## 🔄 Inertia.js Integration

### 💻 Controller Example

```php
use Inertia\Inertia;

public function users()
{
    $table = Ultra::table(User::class)
        ->addTextColumn('name')->sortable()
        ->addEmailColumn('email')
        ->withPagination(10);

    return Inertia::render('Users/Index', [
        'table' => $table->toResponse(request())
    ]);
}
```

### 🧱 Vue Component Example

```vue
<template>
  <div>
    <table>
      <thead>
        <tr>
          <th v-for="column in table.columns" :key="column.key">
            {{ column.label }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in table.data" :key="row.id">
          <td v-for="column in table.columns" :key="column.key">
            {{ row[column.key] }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  table: Object
});
</script>
```

---

## ⚡ Quick Reference

| Component | Builder          | Example                                            |
| --------- | ---------------- | -------------------------------------------------- |
| Table     | `Ultra::table()` | `Ultra::table(User::class)->addTextColumn('name')` |
| Form      | `Ultra::form()`  | `Ultra::form(User::class)->addEmail('email')`      |
| Modal     | `Ultra::modal()` | `Ultra::modal()->title('Edit User')`               |

---

## 🧩 Common Methods

| Method               | Description              |
| -------------------- | ------------------------ |
| `sortable()`         | Enables column sorting   |
| `searchable()`       | Enables search on column |
| `required()`         | Marks field as required  |
| `withPagination(15)` | Adds pagination          |
| `withActions()`      | Adds default actions     |
| `size('lg')`         | Sets modal size          |

---

## 🧠 Remember

* ✅ Use `Ultra::table()`, `Ultra::form()`, and `Ultra::modal()` for quick UI scaffolding.
* 🔗 Integrates seamlessly with **Blade**, **Vue**, **React**, and **Inertia.js**.
* ⚙️ All components return a **response-ready object** via `->toResponse(request())`.
* 🤖 AI, Realtime, and Analytics modules can extend behavior automatically.

---

### © Laravel Ultra

Crafted for modern Laravel applications — AI-enhanced, real-time, and beautifully simple.
