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
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div>
                <flux:heading size="xl">Messages</flux:heading>
                <flux:subheading>Contact form submissions.</flux:subheading>
            </div>
            @if ($unreadCount > 0)
                <flux:badge color="red">{{ $unreadCount }} unread</flux:badge>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
        @if ($messages->isEmpty())
            <div class="py-16 text-center text-zinc-400">
                <flux:icon name="envelope" class="size-10 mx-auto mb-3 opacity-40" />
                <p>No messages yet.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700 text-xs text-zinc-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">From</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Subject</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Date</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($messages as $message)
                        <tr
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition {{ $message->read_at ? '' : 'bg-blue-50/40 dark:bg-blue-900/10' }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if (!$message->read_at)
                                        <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></span>
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
                                class="px-4 py-3 hidden md:table-cell text-zinc-600 dark:text-zinc-400 max-w-xs truncate">
                                {{ $message->subject }}
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-zinc-500 text-xs">
                                {{ $message->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <flux:button wire:click="viewMessage({{ $message->id }})" variant="ghost"
                                        size="sm" icon="eye" />
                                    @if (!$message->read_at)
                                        <flux:tooltip content="Mark as read">
                                            <flux:button wire:click="markAsRead({{ $message->id }})" variant="ghost"
                                                size="sm" icon="check" />
                                        </flux:tooltip>
                                    @endif
                                    <flux:modal.trigger :name="'delete-msg-'.$message->id">
                                        <flux:button variant="ghost" size="sm" icon="trash"
                                            class="text-red-500 hover:text-red-600" />
                                    </flux:modal.trigger>
                                </div>

                                <flux:modal :name="'delete-msg-'.$message->id" class="max-w-sm">
                                    <div class="space-y-4">
                                        <flux:heading>Delete Message?</flux:heading>
                                        <flux:subheading>Message from {{ $message->name }} will be permanently deleted.
                                        </flux:subheading>
                                        <div class="flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button variant="filled">Cancel</flux:button>
                                            </flux:modal.close>
                                            <flux:button wire:click="delete({{ $message->id }})" variant="danger">
                                                Delete</flux:button>
                                        </div>
                                    </div>
                                </flux:modal>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700">
                {{ $messages->links() }}
            </div>
        @endif
    </div>

    {{-- Message Detail Modal --}}
    @if ($showModal && $viewing)
        <flux:modal name="view-message" :show="$showModal" wire:close="closeModal" class="max-w-lg">
            <div class="space-y-4">
                <div class="flex items-start justify-between">
                    <flux:heading>{{ $viewing->subject }}</flux:heading>
                    <flux:badge color="{{ $viewing->read_at ? 'zinc' : 'blue' }}" size="sm">
                        {{ $viewing->read_at ? 'Read' : 'Unread' }}
                    </flux:badge>
                </div>

                <div class="text-sm space-y-1 bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3">
                    <p><span class="text-zinc-500">From:</span> <span class="font-medium">{{ $viewing->name }}</span>
                    </p>
                    <p><span class="text-zinc-500">Email:</span> <a href="mailto:{{ $viewing->email }}"
                            class="text-blue-500 hover:underline">{{ $viewing->email }}</a></p>
                    <p><span class="text-zinc-500">Date:</span> {{ $viewing->created_at->format('M d, Y H:i') }}</p>
                </div>

                <div
                    class="text-sm leading-relaxed whitespace-pre-wrap bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">
                    {{ $viewing->message }}</div>

                <div class="flex justify-between">
                    <flux:button href="mailto:{{ $viewing->email }}?subject=Re: {{ $viewing->subject }}"
                        icon="paper-airplane" variant="ghost" size="sm">Reply</flux:button>
                    <div class="flex gap-2">
                        <flux:modal.trigger :name="'delete-msg-'.$viewing->id">
                            <flux:button variant="ghost" size="sm" icon="trash" class="text-red-500" />
                        </flux:modal.trigger>
                        <flux:button wire:click="closeModal" variant="filled" size="sm">Close</flux:button>
                    </div>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
