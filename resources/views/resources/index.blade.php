{{ /*
$schema Example
[
    'name',
    'columns' => [
        'name' => 'something',
        'type' => 'unused',
        'required' => false,
    ],

]
*/ }}

@if(isset($items))
<table>
    <th>
        @foreach($schema['columns'] as $column)
        <td>{{ $column['name'] }}</td>
        @endforeach
    </th>
    @foreach($items as $item)
    <tr>
        @foreach($schema['columns'] as $column)
        <td>{{ $item[$column['name']] }}</td>
        @endforeach
    </tr>
    @endforeach
</table>
@else
you seem not to have any {{ $schema['name'] }}schema
@endif