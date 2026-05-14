<?php

use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Project;
use App\Models\Skill;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Admin Dashboard')] class extends Component {
    public int $postCount = 0;
    public int $publishedPostCount = 0;
    public int $projectCount = 0;
    public int $skillCount = 0;
    public int $unreadMessageCount = 0;
    public int $totalMessageCount = 0;
    public \Illuminate\Support\Collection $recentUnreadMessages;

    public function mount(): void
    {
        $this->postCount = Post::count();
        $this->publishedPostCount = Post::where('is_published', true)->count();
        $this->projectCount = Project::count();
        $this->skillCount = Skill::count();
        $this->unreadMessageCount = ContactMessage::unread()->count();
        $this->totalMessageCount = ContactMessage::count();
        $this->recentUnreadMessages = ContactMessage::unread()->latest()->limit(5)->get();
    }

    public function render()
    {
        return $this->view()->layout('layouts.admin');
    }
}; ?>

<div>
    <flux:heading size="xl" class="mb-1">Dashboard</flux:heading>
    <flux:subheading class="mb-8">Overview of your portfolio website.</flux:subheading>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
        <a href="{{ route('admin.blog.index') }}" wire:navigate
            class="group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 hover:border-blue-400 dark:hover:border-blue-500 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                    <flux:icon name="document-text" class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <span class="text-xs text-zinc-400">{{ $publishedPostCount }} published</span>
            </div>
            <p class="text-2xl font-bold mb-1">{{ $postCount }}</p>
            <p class="text-sm text-zinc-500">Blog Posts</p>
        </a>

        <a href="{{ route('admin.projects.index') }}" wire:navigate
            class="group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 hover:border-purple-400 dark:hover:border-purple-500 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                    <flux:icon name="folder" class="size-5 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
            <p class="text-2xl font-bold mb-1">{{ $projectCount }}</p>
            <p class="text-sm text-zinc-500">Projects</p>
        </a>

        <a href="{{ route('admin.skills.index') }}" wire:navigate
            class="group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 hover:border-amber-400 dark:hover:border-amber-500 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                    <flux:icon name="wrench-screwdriver" class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
            </div>
            <p class="text-2xl font-bold mb-1">{{ $skillCount }}</p>
            <p class="text-sm text-zinc-500">Skills</p>
        </a>

        <a href="{{ route('admin.messages.index') }}" wire:navigate
            class="group bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5 hover:border-green-400 dark:hover:border-green-500 transition">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                    <flux:icon name="envelope" class="size-5 text-green-600 dark:text-green-400" />
                </div>
                @if ($unreadMessageCount > 0)
                    <flux:badge color="red" size="sm">{{ $unreadMessageCount }} new</flux:badge>
                @endif
            </div>
            <p class="text-2xl font-bold mb-1">{{ $totalMessageCount }}</p>
            <p class="text-sm text-zinc-500">Messages</p>
        </a>
    </div>

    <div class="grid md:grid-cols-2 gap-5">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
                <flux:heading>Quick Actions</flux:heading>
            </div>
            <div class="space-y-2">
                <flux:button :href="route('admin.blog.create')" variant="ghost" class="w-full justify-start"
                    icon="plus" wire:navigate>
                    New Blog Post
                </flux:button>
                <flux:button :href="route('admin.projects.create')" variant="ghost" class="w-full justify-start"
                    icon="plus" wire:navigate>
                    New Project
                </flux:button>
                <flux:button :href="route('admin.home')" variant="ghost" class="w-full justify-start" icon="pencil"
                    wire:navigate>
                    Edit Home Page
                </flux:button>
                <flux:button :href="route('admin.about')" variant="ghost" class="w-full justify-start" icon="pencil"
                    wire:navigate>
                    Edit About Page
                </flux:button>
            </div>
        </div>

        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-5">
            <div class="flex items-center justify-between mb-4">
                <flux:heading>Unread Messages</flux:heading>
                <flux:button :href="route('admin.messages.index')" variant="ghost" size="sm" wire:navigate>View all
                </flux:button>
            </div>
            @if ($recentUnreadMessages->isEmpty())
                <p class="text-sm text-zinc-400 py-6 text-center">No unread messages.</p>
            @else
                <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($recentUnreadMessages as $msg)
                        <a href="{{ route('admin.messages.index') }}" wire:navigate
                            class="flex items-start gap-3 py-3 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 -mx-2 px-2 rounded-lg transition">
                            <div
                                class="w-8 h-8 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center shrink-0 mt-0.5">
                                <span
                                    class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">{{ strtoupper(substr($msg->name, 0, 1)) }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <span
                                        class="text-sm font-medium text-zinc-800 dark:text-zinc-100 truncate">{{ $msg->name }}</span>
                                    <span
                                        class="text-xs text-zinc-400 shrink-0">{{ $msg->created_at->diffForHumans() }}</span>
                                </div>
                                @if ($msg->subject)
                                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 truncate">
                                        {{ $msg->subject }}</p>
                                @endif
                                <p class="text-xs text-zinc-400 truncate">{{ Str::limit($msg->message, 60) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
                @if ($unreadMessageCount > 5)
                    <p class="text-xs text-zinc-400 text-center mt-3">+{{ $unreadMessageCount - 5 }} more unread</p>
                @endif
            @endif
        </div>
    </div>
</div>
