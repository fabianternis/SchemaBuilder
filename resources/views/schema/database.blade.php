<h1>Database: {{ $database_name }}</h1>
<p>Project: <a href="/schema/{{ $project_slug }}">{{ $project_slug }}</a></p>
<hr>
<ul>
    <li><a href="/schema/{{ $project_slug }}/{{ $database_name }}/users">users (Table)</a></li>
    <li><a href="/schema/{{ $project_slug }}/{{ $database_name }}/posts">posts (Table)</a></li>
</ul>
