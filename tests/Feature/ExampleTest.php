<?php

test('returns a successful response', function () {
    $this->get('/')->assertOk();
});

test('every public page renders', function (string $route) {
    $this->get(route($route))->assertOk();
})->with([
    'landing.home',
    'landing.blog',
    'landing.projects',
    'landing.about',
    'landing.skills',
    'landing.contact',
]);
