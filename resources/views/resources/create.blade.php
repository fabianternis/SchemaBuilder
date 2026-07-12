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

<form action="{{ route($schema['base_route'].'.store') }}" method="post">
@csrf
    @foreach($schema['columns'] as $column)
    <label for="{{ $column['name'] }}">@lang($column['name']){{ !$column['required'] ? ' (Optional)' : '' }}</label>
    <input type="{{ $column['type'] }}" name="{{ $column['name'] }}"{{ $column['required'] ? 'required' : '' }}>
    @endforeach
    <input type="submit" value="HEHE">
</form>