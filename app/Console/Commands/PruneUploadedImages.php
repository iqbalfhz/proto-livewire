<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Project;
use App\Models\SiteContent;
use App\Support\UploadedImages;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class PruneUploadedImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:prune
                            {--force : Actually delete the files instead of only listing them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find uploaded images no record points at any more, and optionally delete them';

    /** Directories the admin panel uploads into. */
    protected const MANAGED_DIRECTORIES = ['posts', 'posts/content', 'projects', 'about'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $disk = Storage::disk('public');
        $referenced = $this->referencedPaths();

        $orphans = collect(static::MANAGED_DIRECTORIES)
            ->flatMap(fn (string $directory) => $disk->files($directory))
            ->reject(fn (string $path) => $referenced->contains($path))
            ->reject(fn (string $path) => str_starts_with(basename($path), '.'))
            ->values();

        if ($orphans->isEmpty()) {
            $this->components->info('No orphaned images found.');

            return self::SUCCESS;
        }

        $bytes = $orphans->sum(fn (string $path) => $disk->size($path));

        $this->table(
            ['File', 'Size'],
            $orphans->map(fn (string $path) => [$path, $this->humanBytes($disk->size($path))])->all()
        );

        if (! $this->option('force')) {
            $this->components->warn(sprintf(
                '%d orphaned image(s) using %s. Re-run with --force to delete them.',
                $orphans->count(),
                $this->humanBytes($bytes),
            ));

            return self::SUCCESS;
        }

        UploadedImages::delete(...$orphans->all());

        $this->components->info(sprintf(
            'Deleted %d orphaned image(s), freeing %s.',
            $orphans->count(),
            $this->humanBytes($bytes),
        ));

        return self::SUCCESS;
    }

    /**
     * Every image path still pointed at by a record.
     *
     * @return Collection<int, string>
     */
    protected function referencedPaths(): Collection
    {
        $inline = Post::whereNotNull('content')
            ->pluck('content')
            ->flatMap(fn (string $content) => UploadedImages::embeddedIn($content));

        return Post::pluck('thumbnail')
            ->concat(Project::pluck('image'))
            ->concat([SiteContent::get('about', 'photo')])
            ->concat($inline)
            ->filter()
            ->unique()
            ->values();
    }

    protected function humanBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $units = ['KB', 'MB', 'GB'];
        $value = $bytes / 1024;
        $unit = 0;

        while ($value >= 1024 && $unit < count($units) - 1) {
            $value /= 1024;
            $unit++;
        }

        return round($value, 1).' '.$units[$unit];
    }
}
