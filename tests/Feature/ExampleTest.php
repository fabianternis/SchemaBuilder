<?php

it('returns a successful response from the home page', function () {
    $response = $this->get('/home');
    $response->assertStatus(200);
});

it('root redirects guests to home page', function () {
    $response = $this->get('/');
    $response->assertRedirect(route('pages.home'));
});
