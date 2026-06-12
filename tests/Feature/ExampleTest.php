<?php

test('the application redirects to login when guest', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});
