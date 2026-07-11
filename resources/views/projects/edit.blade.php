<h1>Edit Project {{ $id }}</h1>
<form action="/projects/{{ $id }}" method="POST">
    @csrf
    @method('PUT')
    <button>Update</button>
</form>
