<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GithubContributionsService
{
    public function getContributions(string $username): array
    {
        $cacheKey = "github_contributions_{$username}";

        // Stale data is served while it refreshes after the response; a failed refresh keeps the last good copy.
        return Cache::flexible(
            $cacheKey,
            [3600, 86400],
            fn () => $this->fetchFromApi($username) ?? Cache::get($cacheKey, $this->emptyResponse()),
        );
    }

    /**
     * @return array{total: int, weeks: array<int, mixed>}|null
     */
    private function fetchFromApi(string $username): ?array
    {
        $token = config('services.github.token');

        if (! $token) {
            return null;
        }

        $query = <<<'GQL'
        query($username: String!) {
            user(login: $username) {
                contributionsCollection {
                    contributionCalendar {
                        totalContributions
                        weeks {
                            contributionDays {
                                contributionCount
                                date
                                weekday
                            }
                        }
                    }
                }
            }
        }
        GQL;

        try {
            $response = Http::withToken($token)
                ->timeout(5)
                ->post('https://api.github.com/graphql', [
                    'query' => $query,
                    'variables' => ['username' => $username],
                ]);
        } catch (ConnectionException) {
            return null;
        }

        $calendar = $response->successful()
            ? $response->json('data.user.contributionsCollection.contributionCalendar')
            : null;

        if (! $calendar) {
            return null;
        }

        return [
            'total' => $calendar['totalContributions'],
            'weeks' => $calendar['weeks'],
        ];
    }

    private function emptyResponse(): array
    {
        return ['total' => 0, 'weeks' => []];
    }
}
