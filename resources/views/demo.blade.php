<!DOCTYPE html>
<html>
<head>
    <title>Laravel Ultra Demo</title>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>
<body>
<h1>Laravel Ultra Demo</h1>

<div id="app">
    <h2>Table Component</h2>
    <div>
        <table border="1">
            <thead>
            <tr>
                @foreach($table['columns'] as $column)
                    <th>{{ $column['label'] }}</th>
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
    </div>

    <h2>Form Component</h2>
    <form>
        @foreach($form['fields'] as $field)
            <div>
                <label>{{ $field['label'] }}{{ $field['required'] ? '*' : '' }}</label>
                <input
                        type="{{ $field['type'] }}"
                        name="{{ $field['name'] }}"
                        {{ $field['required'] ? 'required' : '' }}>
            </div>
        @endforeach
        <button type="submit">Submit</button>
    </form>
</div>
</body>
</html>