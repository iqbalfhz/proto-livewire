<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GithubContributionsService
{
    public function getContributions(string $username): array
    {
        $cacheKey = "github_contributions_{$username}";

        return Cache::remember($cacheKey, now()->addHour(), function () use ($username) {
            return $this->fetchFromApi($username);
        });
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

        $response = Http::withToken($token)
            ->post('https://api.github.com/graphql', [
                'query'     => $query,
                'variables' => ['username' => $username],
            ]);

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

    public function getColorClass(int $count): string
    {
        return match (true) {
            $count === 0  => 'bg-zinc-100 dark:bg-zinc-800',
            $count <= 3   => 'bg-green-200 dark:bg-green-900',
            $count <= 9   => 'bg-green-400 dark:bg-green-700',
            $count <= 19  => 'bg-green-600 dark:bg-green-500',
            default       => 'bg-green-800 dark:bg-green-400',
        };
    }
}
