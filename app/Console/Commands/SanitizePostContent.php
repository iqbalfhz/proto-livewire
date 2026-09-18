<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\HtmlSanitizer;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SanitizePostContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:sanitize
                            {--force : Write the cleaned content back instead of only reporting it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run existing post bodies through the HTML sanitiser, stripping editor and paste artefacts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $changed = [];
        $bytesBefore = 0;
        $bytesAfter = 0;

        Post::select(['id', 'title', 'content'])
            ->chunkById(50, function ($posts) use (&$changed, &$bytesBefore, &$bytesAfter) {
                foreach ($posts as $post) {
                    $original = (string) $post->getRawOriginal('content');
                    $cleaned = HtmlSanitizer::clean($original);

                    if ($cleaned === $original) {
                        continue;
                    }

                    $changed[] = [
                        'id' => $post->id,
                        'title' => $post->title,
                        'before' => strlen($original),
                        'after' => strlen($cleaned),
                    ];

                    $bytesBefore += strlen($original);
                    $bytesAfter += strlen($cleaned);
                }
            });

        if ($changed === []) {
            $this->components->info('Every post is already clean.');

            return self::SUCCESS;
        }

        $this->table(
            ['Post', 'Before', 'After', 'Saved'],
            array_map(fn (array $row) => [
                Str::limit($row['title'], 44),
                number_format($row['before']).' B',
                number_format($row['after']).' B',
                number_format($row['before'] - $row['after']).' B',
            ], $changed),
        );

        if (! $this->option('force')) {
            $this->components->warn(sprintf(
                '%d post(s) would change, shedding %s. Re-run with --force to write them back.',
                count($changed),
                number_format($bytesBefore - $bytesAfter).' bytes',
            ));

            return self::SUCCESS;
        }

        foreach ($changed as $row) {
            $post = Post::find($row['id']);

            if (! $post) {
                continue;
            }

            // The mutator sanitises on assignment. Timestamps stay put: tidying
            // artefacts is not an edit, and bumping updated_at would tell every
            // crawler that the whole archive changed today.
            $post->content = $post->getRawOriginal('content');
            $post->timestamps = false;
            $post->save();
        }

        $this->components->info(sprintf(
            'Cleaned %d post(s), shedding %s.',
            count($changed),
            number_format($bytesBefore - $bytesAfter).' bytes',
        ));

        return self::SUCCESS;
    }
}
