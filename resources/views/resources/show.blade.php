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

<h1>{{ ucfirst(Str::singular($schema['name'])) }} Details</h1>
<div>
    @foreach($schema['columns'] as $column)
        <p>
            <strong>@lang($column['name']):</strong> 
            @if(is_array($item->{$column['name']}))
                <pre>{{ json_encode($item->{$column['name']}, JSON_PRETTY_PRINT) }}</pre>
            @else
                {{ $item->{$column['name']} }}
            @endif
        </p>
    @endforeach
</div>

<a href="{{ route($schema['base_route'].'.index') }}">Back to list</a>
<a href="{{ route($schema['base_route'].'.edit', $item->id) }}">Edit</a>