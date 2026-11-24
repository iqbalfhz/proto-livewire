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

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $contributions = $this->fetchFromApi($username);

        // A failed fetch is retried soon instead of hiding the graph for an hour.
        Cache::put($cacheKey, $contributions, $contributions['weeks'] === [] ? now()->addMinutes(5) : now()->addHour());

        return $contributions;
    }

    private function fetchFromApi(string $username): array
    {
        $token = config('services.github.token');

        if (! $token) {
            return $this->emptyResponse();
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
            return $this->emptyResponse();
        }

        if (! $response->successful()) {
            return $this->emptyResponse();
        }

        $calendar = $response->json('data.user.contributionsCollection.contributionCalendar');

        if (! $calendar) {
            return $this->emptyResponse();
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
