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
    </tr>
    @endforeach
</table>
@else
you seem not to have any {{ $schema['name'] }}s
@endif