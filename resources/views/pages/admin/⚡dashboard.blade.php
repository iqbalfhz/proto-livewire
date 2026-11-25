<?php

use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Project;
use App\Models\Skill;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Admin Dashboard')] class extends Component {
    public function render()
    {
        $unreadMessageCount = ContactMessage::unread()->count();

        return $this->view([
            'stats' => [
                ['label' => 'Posts', 'count' => Post::count(), 'note' => Post::where('is_published', true)->count() . ' published', 'route' => 'admin.blog.index'],
                ['label' => 'Projects', 'count' => Project::count(), 'note' => Project::featured()->count() . ' featured', 'route' => 'admin.projects.index'],
                ['label' => 'Skills', 'count' => Skill::count(), 'note' => null, 'route' => 'admin.skills.index'],
                ['label' => 'Messages', 'count' => ContactMessage::count(), 'note' => $unreadMessageCount ? "{$unreadMessageCount} unread" : null, 'route' => 'admin.messages.index', 'alert' => $unreadMessageCount > 0],
            ],
            'quickActions' => [
                ['label' => 'New post', 'icon' => 'plus', 'route' => 'admin.blog.create'],
                ['label' => 'New project', 'icon' => 'plus', 'route' => 'admin.projects.create'],
                ['label' => 'Edit home page', 'icon' => 'pencil', 'route' => 'admin.home'],
                ['label' => 'Edit about page', 'icon' => 'pencil', 'route' => 'admin.about'],
            ],
            'unreadMessageCount' => $unreadMessageCount,
            'recentUnreadMessages' => ContactMessage::unread()->latest()->limit(5)->get(),
        ])->layout('layouts.admin');
    }
}; ?>

<div>
    <x-admin.page-header title="Dashboard" subtitle="Overview of your portfolio" />

    <div class="mb-10 grid grid-cols-2 gap-px border border-rule bg-rule lg:grid-cols-4">
        @foreach ($stats as $stat)
            <a href="{{ route($stat['route']) }}" wire:navigate
                class="block bg-paper-raised p-5 transition-colors hover:bg-paper-sunk">
                <span class="label block">{{ $stat['label'] }}</span>
                <span class="display display-md mt-3 block">{{ $stat['count'] }}</span>
                <span @class([
                    'mt-1 block min-h-4 text-xs',
                    'text-oxblood' => $stat['alert'] ?? false,
                    'text-ink-muted' => !($stat['alert'] ?? false),
                ])>{{ $stat['note'] }}</span>
            </a>
        @endforeach
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <section class="admin-panel p-5">
            <h2 class="label mb-4">Quick actions</h2>
            <div class="flex flex-col items-start gap-1">
                @foreach ($quickActions as $action)
                    <flux:button :href="route($action['route'])" :icon="$action['icon']" variant="ghost" wire:navigate>
                        {{ $action['label'] }}
                    </flux:button>
                @endforeach
            </div>
        </section>

        <section class="admin-panel p-5">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h2 class="label">Unread messages</h2>
                <flux:button :href="route('admin.messages.index')" variant="ghost" size="sm" wire:navigate>
                    View all
                </flux:button>
            </div>

            @forelse ($recentUnreadMessages as $message)
                <a href="{{ route('admin.messages.index') }}" wire:navigate
                    class="block border-t border-rule py-3 transition-colors first-of-type:border-t-0 hover:text-oxblood">
                    <span class="flex items-baseline justify-between gap-3">
                        <span class="truncate font-medium">{{ $message->name }}</span>
                        <span class="shrink-0 text-xs text-ink-muted">{{ $message->created_at->diffForHumans() }}</span>
                    </span>
                    <span class="mt-0.5 block truncate text-sm text-ink-muted">
                        {{ $message->subject ?: Str::limit($message->message, 60) }}
                    </span>
                </a>
            @empty
                <p class="py-6 text-center text-sm text-ink-muted">No unread messages.</p>
            @endforelse

            @if ($unreadMessageCount > 5)
                <p class="mt-3 text-center text-xs text-ink-muted">+{{ $unreadMessageCount - 5 }} more unread</p>
            @endif
        </section>
    </div>
</div>
