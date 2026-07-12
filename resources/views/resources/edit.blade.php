{{-- /*
$schema Example
[
    'name',
    'columns' => [
        'name' => 'something',
        'type' => 'unused',
        'required' => false,
        'on_index' => true,
    ],

]
*/ --}}
<h1>Edit {{ ucfirst(Str::singular($schema['name'])) }}</h1>

<form action="{{ route($schema['base_route'].'.update', $item->id) }}" method="post">
    @csrf
    @method('PUT')
    @foreach($schema['columns'] as $column)
        <div style="margin-bottom: 1rem;">
            <label for="{{ $column['name'] }}">@lang($column['name']){{ !$column['required'] ? ' (Optional)' : '' }}</label><br>
            @if($column['type'] === 'textarea' || $column['type'] === 'json')
                <textarea id="{{ $column['name'] }}" name="{{ $column['name'] }}" {{ $column['required'] ? 'required' : '' }}>{{ old($column['name'], is_array($item->{$column['name']}) ? json_encode($item->{$column['name']}) : $item->{$column['name']}) }}</textarea>
            @else
                <input id="{{ $column['name'] }}" type="{{ $column['type'] === 'text' ? 'text' : ($column['type'] === 'url' ? 'url' : 'text') }}" name="{{ $column['name'] }}" value="{{ old($column['name'], $item->{$column['name']}) }}" {{ $column['required'] ? 'required' : '' }}>
            @endif
        </div>
    @endforeach
    <button type="submit">Update</button>
</form>

<a href="{{ route($schema['base_route'].'.index') }}">Back to list</a>
