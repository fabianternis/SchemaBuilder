<h1>Table: {{ $table_name }}</h1>
<p>Project: <a href="/schema/{{ $project_slug }}">{{ $project_slug }}</a></p>
<p>Database: <a href="/schema/{{ $project_slug }}/{{ $database_name }}">{{ $database_name }}</a></p>
<hr>
<ul>
    <li><a href="/schema/{{ $project_slug }}/{{ $database_name }}/{{ $table_name }}/id">id (Column)</a></li>
    <li><a href="/schema/{{ $project_slug }}/{{ $database_name }}/{{ $table_name }}/name">name (Column)</a></li>
</ul>
