<?php

use App\Services\GithubContributionsService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

function fakeContributionCalendar(int $total = 1234): array
{
    return [
        'data' => [
            'user' => [
                'contributionsCollection' => [
                    'contributionCalendar' => [
                        'totalContributions' => $total,
                        'weeks' => [
                            ['contributionDays' => [
                                ['contributionCount' => 0, 'date' => '2026-01-05', 'weekday' => 1],
                                ['contributionCount' => 7, 'date' => '2026-01-06', 'weekday' => 2],
                            ]],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

test('no request is made when the integration is not configured', function () {
    config(['services.github.token' => null]);

    expect(app(GithubContributionsService::class)->getContributions('iqbalfhz'))
        ->toBe(['total' => 0, 'weeks' => []]);
});

test('the contribution calendar is parsed from the api response', function () {
    config(['services.github.token' => 'fake-token']);
    Http::fake(['api.github.com/*' => Http::response(fakeContributionCalendar())]);

    $data = app(GithubContributionsService::class)->getContributions('iqbalfhz');

    expect($data['total'])->toBe(1234)
        ->and($data['weeks'])->toHaveCount(1)
        ->and($data['weeks'][0]['contributionDays'][1]['contributionCount'])->toBe(7);
});

test('a failed api call degrades gracefully instead of breaking the page', function () {
    config(['services.github.token' => 'fake-token']);
    Http::fake(['api.github.com/*' => Http::response(status: 401)]);

    expect(app(GithubContributionsService::class)->getContributions('iqbalfhz'))
        ->toBe(['total' => 0, 'weeks' => []]);
});

test('the calendar is cached instead of refetched', function () {
    config(['services.github.token' => 'fake-token', 'cache.default' => 'array']);
    Http::fake(['api.github.com/*' => Http::response(fakeContributionCalendar())]);

    $service = app(GithubContributionsService::class);
    $service->getContributions('iqbalfhz');
    $service->getContributions('iqbalfhz');

    Http::assertSentCount(1);
});

test('the about page renders the contribution graph', function () {
    config([
        'services.github.token' => 'fake-token',
        'services.github.username' => 'iqbalfhz',
    ]);
    Http::fake(['api.github.com/*' => Http::response(fakeContributionCalendar(987))]);

    $this->get(route('landing.about'))
        ->assertOk()
        ->assertSee('987 contributions in the last year');
});

test('the about page renders without the integration configured', function () {
    config(['services.github.username' => null]);

    $this->get(route('landing.about'))
        ->assertOk()
        ->assertDontSee('contributions in the last year');
});
