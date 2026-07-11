<footer>
    <div class="debug-data">
        <code>
            @auth
                "auth": true,
                "username": "{{ auth()->user()->username }}",
                "user_id": "{{ auth()->id() }}"
            @endauth
        </code>
    </div>
</footer>