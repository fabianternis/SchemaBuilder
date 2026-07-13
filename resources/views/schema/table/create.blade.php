@extends('layouts.app')

@section('content')
<form action="{{ route('schema.table.store', [$project, $database]) }}" method="post">
@csrf

    input
</form>
@endsection