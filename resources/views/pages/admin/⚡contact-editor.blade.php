<?php

use App\Models\SiteContent;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit Contact Page')] class extends Component {
    public string $subtitle = '';
    public string $email = '';
    public string $location = '';
    public string $availability = '';

    public function mount(): void
    {
        $data = SiteContent::group('contact');
        $this->subtitle = $data['subtitle'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->location = $data['location'] ?? '';
        $this->availability = $data['availability'] ?? '';
    }

    public function save(): void
    {
        $fields = $this->validate([
            'subtitle' => ['nullable', 'string', 'max:500'],
            'email' => ['nullable', 'email', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'availability' => ['nullable', 'string', 'max:150'],
        ]);

        foreach ($fields as $key => $value) {
            SiteContent::set('contact', $key, $value);
        }

        Flux::toast(variant: 'success', text: 'Contact page updated.');
    }

    public function render()
    {
        return $this->view()->layout('layouts.admin');
    }
}; ?>

<div>
    <div class="flex items-center gap-3 mb-8">
        <flux:button :href="route('admin.dashboard')" variant="ghost" icon="arrow-left" size="sm" wire:navigate />
        <div>
            <flux:heading size="xl">Contact Page</flux:heading>
            <flux:subheading>Edit the contact details shown next to the form.</flux:subheading>
        </div>
    </div>

    <form wire:submit="save"
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 space-y-5">
        <flux:textarea wire:model="subtitle" label="Intro Text" rows="2"
            placeholder="Have a question or want to work together?..." />
        <div class="grid sm:grid-cols-2 gap-5">
            <flux:input wire:model="email" type="email" label="Public Email" placeholder="you@example.com" />
            <flux:input wire:model="location" label="Location" placeholder="Jakarta, Indonesia" />
        </div>
        <flux:input wire:model="availability" label="Availability" placeholder="Open for freelance projects" />

        <flux:callout variant="secondary" icon="information-circle" class="text-sm">
            Leave a field empty to hide it. If all three are empty, the contact form is centred on its own.
        </flux:callout>

        <div class="flex justify-end gap-3 pt-2">
            <flux:button :href="route('landing.contact')" target="_blank" variant="ghost">Preview</flux:button>
            <flux:button type="submit" variant="primary">Save Changes</flux:button>
        </div>
    </form>
</div>
