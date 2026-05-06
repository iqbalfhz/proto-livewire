<?php

use App\Models\SiteContent;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit Home Page')] class extends Component {
    public string $badge = '';
    public string $headline = '';
    public string $subheadline = '';
    public string $cta_primary = '';
    public string $cta_secondary = '';
    public string $cta_section_text = '';

    public function mount(): void
    {
        $data = SiteContent::group('home');
        $this->badge = $data['badge'] ?? '';
        $this->headline = $data['headline'] ?? '';
        $this->subheadline = $data['subheadline'] ?? '';
        $this->cta_primary = $data['cta_primary'] ?? '';
        $this->cta_secondary = $data['cta_secondary'] ?? '';
        $this->cta_section_text = $data['cta_section_text'] ?? '';
    }

    public function save(): void
    {
        $fields = $this->validate([
            'badge' => ['nullable', 'string', 'max:100'],
            'headline' => ['nullable', 'string', 'max:255'],
            'subheadline' => ['nullable', 'string', 'max:500'],
            'cta_primary' => ['nullable', 'string', 'max:100'],
            'cta_secondary' => ['nullable', 'string', 'max:100'],
            'cta_section_text' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($fields as $key => $value) {
            SiteContent::set('home', $key, $value);
        }

        Flux::toast(variant: 'success', text: 'Home page updated.');
    }

    public function render()
    {
        return $this->view()->layout('layouts.admin');
    }
}; ?>

<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-8">
        <flux:button :href="route('admin.dashboard')" variant="ghost" icon="arrow-left" size="sm" wire:navigate />
        <div>
            <flux:heading size="xl">Home Page</flux:heading>
            <flux:subheading>Edit hero section content.</flux:subheading>
        </div>
    </div>

    <form wire:submit="save"
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 space-y-5">
        <flux:input wire:model="badge" label="Badge Text" placeholder="Available for work" />
        <flux:input wire:model="headline" label="Headline (HTML allowed)"
            placeholder="Hi, I'm a <span class='text-blue-600'>Developer</span>" />
        <flux:textarea wire:model="subheadline" label="Subheadline" rows="3"
            placeholder="I build modern web applications..." />
        <div class="grid sm:grid-cols-2 gap-5">
            <flux:input wire:model="cta_primary" label="Primary CTA Button" placeholder="View Projects" />
            <flux:input wire:model="cta_secondary" label="Secondary CTA Button" placeholder="Get In Touch" />
        </div>
        <flux:textarea wire:model="cta_section_text" label="Bottom CTA Section Text" rows="2"
            placeholder="Have a project in mind?..." />

        <div class="flex justify-end gap-3 pt-2">
            <flux:button :href="route('landing.home')" target="_blank" variant="ghost">Preview</flux:button>
            <flux:button type="submit" variant="primary">Save Changes</flux:button>
        </div>
    </form>
</div>
