<!DOCTYPE html>
<html>
<head>
    <title>Laravel Ultra Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-container { margin: 20px 0; }
        .form-container { max-width: 500px; margin: 20px 0; }
        .modal-demo { margin: 20px 0; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">🚀 Laravel Ultra Demo</h1>

    <!-- Table Demo -->
    <div class="table-container">
        <h2>📊 Table Component</h2>
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        @foreach($table['columns'] as $column)
                            <th>
                                {{ $column['label'] }}
                                @if($column['sortable'])
                                    <small class="text-muted">↕️</small>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($table['data'] as $row)
                        <tr>
                            @foreach($table['columns'] as $column)
                                <td>{{ $row[$column['key']] ?? '' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                @if(isset($table['meta']['pagination']))
                    <nav>
                        <ul class="pagination">
                            @php $pagination = $table['meta']['pagination']; @endphp
                            <li class="page-item disabled">
                                <span class="page-text">
                                    Showing {{ (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 }}
                                    to {{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }}
                                    of {{ $pagination['total'] }} entries
                                </span>
                            </li>
                        </ul>
                    </nav>
                @endif
            </div>
        </div>
    </div>

    <!-- Form Demo -->
    <div class="form-container">
        <h2>📝 Form Component</h2>
        <div class="card">
            <div class="card-body">
                <form>
                    @foreach($form['fields'] as $field)
                        <div class="mb-3">
                            <label class="form-label">
                                {{ $field['label'] }}
                                @if($field['required'])
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <input
                                    type="{{ $field['type'] }}"
                                    name="{{ $field['name'] }}"
                                    class="form-control"
                                    {{ $field['required'] ? 'required' : '' }}>
                        </div>
                    @endforeach
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Demo -->
    <div class="modal-demo">
        <h2>🪟 Modal Component</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $modal['title'] }}</h5>
                <p class="card-text">{{ $modal['content'] }}</p>
                <p><strong>Size:</strong> {{ $modal['size'] }}</p>
                <p><strong>Actions:</strong> {{ implode(', ', $modal['actions']) }}</p>
                <button class="btn btn-info" onclick="alert('Modal would open here!')">
                    Open Modal
                </button>
            </div>
        </div>
    </div>

    <!-- Usage Instructions -->
    <div class="mt-5">
        <h3>💡 Usage Example</h3>
        <pre class="bg-light p-3 rounded"><code>
            // Table Usage
            $table = Ultra::table($data)
                ->addTextColumn('name')->sortable()->searchable()
                ->addEmailColumn('email')
                ->addDateColumn('created_at')
                ->withPagination(10);

            // Form Usage
            $form = Ultra::form()
                ->addText('name')->required()
                ->addEmail('email')->required()
                ->addPassword('password');

            return view('your-view', [
                'table' => $table->toResponse(request())
            ]);
            </code></pre>
    </div>
</div>
</body>
</html>