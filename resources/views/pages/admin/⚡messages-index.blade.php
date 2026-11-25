<?php

use App\Models\ContactMessage;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Messages')] class extends Component {
    use WithPagination;

    public ?ContactMessage $viewing = null;
    public bool $showModal = false;

    public function viewMessage(int $id): void
    {
        $this->viewing = ContactMessage::findOrFail($id);
        $this->showModal = true;

        if (!$this->viewing->read_at) {
            $this->viewing->markAsRead();
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->viewing = null;
    }

    public function markAsRead(int $id): void
    {
        ContactMessage::findOrFail($id)->markAsRead();
        Flux::toast(variant: 'success', text: 'Marked as read.');
    }

    public function delete(int $id): void
    {
        ContactMessage::findOrFail($id)->delete();
        if ($this->viewing?->id === $id) {
            $this->closeModal();
        }
        Flux::toast(variant: 'success', text: 'Message deleted.');
    }

    public function render()
    {
        $messages = ContactMessage::latest()->paginate(20);
        $unreadCount = ContactMessage::unread()->count();

        return $this->view(['messages' => $messages, 'unreadCount' => $unreadCount])->layout('layouts.admin');
    }
}; ?>

<div>
    <x-admin.page-header title="Messages" subtitle="Contact form submissions">
        @if ($unreadCount > 0)
            <flux:badge color="red">{{ $unreadCount }} unread</flux:badge>
        @endif
    </x-admin.page-header>

    <div class="admin-panel overflow-hidden">
        @if ($messages->isEmpty())
            <x-admin.empty-state icon="envelope">No messages yet.</x-admin.empty-state>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>From</th>
                        <th class="hidden md:table-cell">Subject</th>
                        <th class="hidden sm:table-cell">Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($messages as $message)
                        <tr @class(['bg-oxblood/5' => !$message->read_at])>
                            <td>
                                <div class="flex items-center gap-2">
                                    @if (!$message->read_at)
                                        <span class="size-2 shrink-0 rounded-full bg-oxblood"></span>
                                    @endif
                                    <div>
                                        <p
                                            class="font-medium {{ $message->read_at ? '' : 'text-zinc-900 dark:text-zinc-100' }}">
                                            {{ $message->name }}</p>
                                        <p class="text-xs text-zinc-400">{{ $message->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="hidden md:table-cell text-zinc-600 dark:text-zinc-400 max-w-xs truncate">
                                {{ $message->subject }}
                            </td>
                            <td class="hidden sm:table-cell text-zinc-500 text-xs">
                                {{ $message->created_at->format('M d, Y H:i') }}
                            </td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <flux:button wire:click="viewMessage({{ $message->id }})" variant="ghost"
                                        size="sm" icon="eye" aria-label="View message" />
                                    @if (!$message->read_at)
                                        <flux:tooltip content="Mark as read">
                                            <flux:button wire:click="markAsRead({{ $message->id }})" variant="ghost"
                                                size="sm" icon="check" aria-label="Mark as read" />
                                        </flux:tooltip>
                                    @endif
                                    <x-admin.delete-trigger :name="'delete-msg-'.$message->id" label="Delete message" />
                                </div>

                                <x-admin.confirm-delete :name="'delete-msg-'.$message->id" heading="Delete Message?" :action="'delete('.$message->id.')'">
                                    Message from {{ $message->name }} will be permanently deleted.
                                </x-admin.confirm-delete>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($messages->hasPages())
                <div class="admin-panel-footer">{{ $messages->links() }}</div>
            @endif
        @endif
    </div>

    {{-- Message Detail Modal --}}
    @if ($showModal && $viewing)
        <flux:modal name="view-message" :show="$showModal" wire:close="closeModal" class="max-w-lg">
            <div class="space-y-4">
                <div class="flex items-start justify-between">
                    <flux:heading>{{ $viewing->subject }}</flux:heading>
                    <flux:badge :color="$viewing->read_at ? 'zinc' : 'red'" size="sm">
                        {{ $viewing->read_at ? 'Read' : 'Unread' }}
                    </flux:badge>
                </div>

                <div class="text-sm space-y-1 bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                    <p><span class="text-zinc-500">From:</span> <span class="font-medium">{{ $viewing->name }}</span>
                    </p>
                    <p><span class="text-zinc-500">Email:</span> <a href="mailto:{{ $viewing->email }}"
                            class="text-oxblood underline-offset-4 hover:underline">{{ $viewing->email }}</a></p>
                    <p><span class="text-zinc-500">Date:</span> {{ $viewing->created_at->format('M d, Y H:i') }}</p>
                </div>

                <div
                    class="text-sm leading-relaxed whitespace-pre-wrap bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                    {{ $viewing->message }}</div>

                <div class="flex justify-between">
                    <flux:button
                        href="mailto:{{ rawurlencode($viewing->email) }}?subject={{ rawurlencode('Re: ' . $viewing->subject) }}"
                        icon="paper-airplane" variant="ghost" size="sm">Reply</flux:button>
                    <div class="flex gap-2">
                        <x-admin.delete-trigger :name="'delete-msg-'.$viewing->id" label="Delete message" />
                        <flux:button wire:click="closeModal" variant="filled" size="sm">Close</flux:button>
                    </div>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
