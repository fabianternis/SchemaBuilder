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

@if(isset($items) && $items->count() > 0)
<table>
    <tr>
        @foreach($schema['columns'] as $column)
        <th>{{ $column['name'] }}</th>
        @endforeach
        {{--
        <th><a href="{{ route($schema['base_route'].'.show') }}">Show</a></th>
        <th><a href="{{ route($schema['base_route'].'.edit') }}">Edit</a></th>
        <th>DELETE (not for now)</th>
        --}}
        <th>Actions</th>
    </tr>
    @foreach($items as $item)
    <tr>
        @foreach($schema['columns'] as $column)
            @if($column['on_index'])
                @if($column['type'] == 'textarea')
                    {{-- <td>{{ substr($item[$column['name']], 0 , 12) }}</td> --}}
                    <td>{{ Str::limit($item->{$column['name']}, 12) }}</td>
                @else
                    <td>{{ $item->{$column['name']} }}</td>
                @endif
            @endif
        @endforeach

        <td><a href="{{ route($schema['base_route'].'.show', $item->id) }}">Show</a> <a href="{{ route($schema['base_route'].'.edit', $item->id) }}">Edit</a> <a>DELETE</a></td>
    </tr>
    @endforeach
</table>
@else
you seem not to have any {{ $schema['name'] }}s
<a href="{{ route($schema['base_route'].'.create') }}">Create one</a>
@endif