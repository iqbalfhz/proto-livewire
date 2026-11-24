<?php

use App\Enums\ExperienceType;
use App\Models\Experience;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Experience')] class extends Component {
    #[Locked]
    public ?int $editingId = null;

    public string $type = 'work';
    public string $title = '';
    public string $organization = '';
    public string $location = '';
    public string $started_on = '';
    public string $ended_on = '';
    public string $summary = '';

    public function save(): void
    {
        $validated = $this->validate([
            'type' => ['required', Rule::enum(ExperienceType::class)],
            'title' => ['required', 'string', 'max:150'],
            'organization' => ['required', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'started_on' => ['required', 'date'],
            'ended_on' => ['nullable', 'date', 'after_or_equal:started_on'],
            'summary' => ['nullable', 'string', 'max:2000'],
        ]);

        $data = [
            ...$validated,
            'location' => $validated['location'] ?: null,
            'ended_on' => $validated['ended_on'] ?: null,
            'summary' => $validated['summary'] ?: null,
        ];

        if ($this->editingId) {
            Experience::findOrFail($this->editingId)->update($data);
        } else {
            Experience::create($data);
        }

        Flux::toast(variant: 'success', text: $this->editingId ? 'Entry updated.' : 'Entry added.');
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $experience = Experience::findOrFail($id);

        $this->editingId = $experience->id;
        $this->type = $experience->type->value;
        $this->title = $experience->title;
        $this->organization = $experience->organization;
        $this->location = $experience->location ?? '';
        $this->started_on = $experience->started_on->format('Y-m-d');
        $this->ended_on = $experience->ended_on?->format('Y-m-d') ?? '';
        $this->summary = $experience->summary ?? '';
    }

    public function resetForm(): void
    {
        $this->reset();
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        Experience::findOrFail($id)->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        Flux::toast(variant: 'success', text: 'Entry deleted.');
    }

    public function render()
    {
        return $this->view([
            'experiences' => Experience::chronological()->get(),
            'types' => ExperienceType::cases(),
        ])->layout('layouts.admin');
    }
}; ?>

<div>
    <div class="mb-6">
        <flux:heading size="xl">Experience</flux:heading>
        <flux:subheading>Work history and education shown on your CV page.</flux:subheading>
    </div>

    <form wire:submit="save"
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 mb-6 space-y-5">
        <flux:heading>{{ $editingId ? 'Edit entry' : 'Add entry' }}</flux:heading>

        <div class="grid sm:grid-cols-3 gap-5">
            <flux:select wire:model="type" label="Type">
                @foreach ($types as $option)
                    <flux:select.option :value="$option->value">{{ $option->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input wire:model="title" label="Title" placeholder="Backend Developer" required />
            <flux:input wire:model="organization" label="Organization" placeholder="Company or school" required />
        </div>

        <div class="grid sm:grid-cols-3 gap-5">
            <flux:input wire:model="location" label="Location" placeholder="Jakarta" />
            <flux:input wire:model="started_on" type="date" label="Started" required />
            <flux:input wire:model="ended_on" type="date" label="Ended (empty = present)" />
        </div>

        <flux:textarea wire:model="summary" label="Summary" rows="3" placeholder="What you owned and delivered." />

        <div class="flex justify-end gap-3">
            @if ($editingId)
                <flux:button wire:click="resetForm" variant="ghost">Cancel</flux:button>
            @endif
            <flux:button type="submit" variant="primary">{{ $editingId ? 'Update entry' : 'Add entry' }}</flux:button>
        </div>
    </form>

    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
        @forelse ($experiences as $experience)
            <div wire:key="experience-{{ $experience->id }}"
                class="flex items-start justify-between gap-4 px-4 py-3 border-b border-zinc-100 dark:border-zinc-800 last:border-b-0">
                <div class="min-w-0">
                    <p class="font-medium">{{ $experience->title }}</p>
                    <p class="text-sm text-zinc-500">{{ $experience->organization }} · {{ $experience->period() }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-1">
                    <flux:badge size="sm" color="zinc">{{ $experience->type->label() }}</flux:badge>
                    <flux:button wire:click="edit({{ $experience->id }})" variant="ghost" size="sm" icon="pencil" />
                    <flux:modal.trigger :name="'delete-experience-'.$experience->id">
                        <flux:button variant="ghost" size="sm" icon="trash" class="text-red-500 hover:text-red-600" />
                    </flux:modal.trigger>
                </div>

                <x-admin.confirm-delete :name="'delete-experience-'.$experience->id" heading="Delete entry?"
                    :action="'delete('.$experience->id.')'">
                    This will permanently delete "{{ $experience->title }}".
                </x-admin.confirm-delete>
            </div>
        @empty
            <div class="py-16 text-center text-zinc-400">
                <flux:icon name="briefcase" class="size-10 mx-auto mb-3 opacity-40" />
                <p>No entries yet. The CV link appears once you add one.</p>
            </div>
        @endforelse
    </div>
</div>
