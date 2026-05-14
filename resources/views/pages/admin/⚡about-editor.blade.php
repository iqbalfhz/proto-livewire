<?php

use App\Models\SiteContent;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Edit About Page')] class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $role = '';
    public string $bio = '';
    public string $resume_url = '';
    public string $github_url = '';
    public string $linkedin_url = '';
    public string $twitter_url = '';
    public $photo = null;
    public ?string $existing_photo = null;

    public function mount(): void
    {
        $data = SiteContent::group('about');
        $this->name = $data['name'] ?? '';
        $this->role = $data['role'] ?? '';
        $this->bio = $data['bio'] ?? '';
        $this->resume_url = $data['resume_url'] ?? '';
        $this->github_url = $data['github_url'] ?? '';
        $this->linkedin_url = $data['linkedin_url'] ?? '';
        $this->twitter_url = $data['twitter_url'] ?? '';
        $this->existing_photo = $data['photo'] ?? null;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['nullable', 'string', 'max:150'],
            'role' => ['nullable', 'string', 'max:150'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'resume_url' => ['nullable', 'url', 'max:500'],
            'github_url' => ['nullable', 'url', 'max:500'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'twitter_url' => ['nullable', 'url', 'max:500'],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
        ]);

        if ($this->photo) {
            $path = $this->photo->store('about', 'public');
            SiteContent::set('about', 'photo', $path);
        }

        $fields = ['name', 'role', 'bio', 'resume_url', 'github_url', 'linkedin_url', 'twitter_url'];
        foreach ($fields as $field) {
            SiteContent::set('about', $field, $validated[$field] ?? null);
        }

        $this->photo = null;
        $this->existing_photo = SiteContent::get('about', 'photo');
        Flux::toast(variant: 'success', text: 'About page updated.');
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
            <flux:heading size="xl">About Page</flux:heading>
            <flux:subheading>Edit your bio, photo, and social links.</flux:subheading>
        </div>
    </div>

    <form wire:submit="save"
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 space-y-5">
        {{-- Photo Upload --}}
        <div>
            <flux:label>Profile Photo</flux:label>
            <div class="mt-2 flex items-center gap-4">
                @if ($photo)
                    <img src="{{ $photo->temporaryUrl() }}" class="w-20 h-20 rounded-xl object-cover ring-2 ring-blue-500"
                        alt="Preview">
                @elseif($existing_photo)
                    <img src="{{ Storage::url($existing_photo) }}"
                        class="w-20 h-20 rounded-xl object-cover ring-2 ring-zinc-200 dark:ring-zinc-700"
                        alt="Current photo">
                @else
                    <div class="w-20 h-20 bg-zinc-100 dark:bg-zinc-800 rounded-xl flex items-center justify-center">
                        <flux:icon name="user" class="size-8 text-zinc-400" />
                    </div>
                @endif
                <flux:input type="file" wire:model="photo" accept="image/*" />
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <flux:input wire:model="name" label="Full Name" placeholder="John Doe" />
            <flux:input wire:model="role" label="Role / Title" placeholder="Full Stack Developer" />
        </div>

        <flux:textarea wire:model="bio" label="Bio" rows="6" placeholder="Tell your story..." />

        <flux:input wire:model="resume_url" type="url" label="Resume URL" placeholder="https://..." />

        <div class="grid sm:grid-cols-3 gap-5">
            <flux:input wire:model="github_url" type="url" label="GitHub URL"
                placeholder="https://github.com/..." />
            <flux:input wire:model="linkedin_url" type="url" label="LinkedIn URL"
                placeholder="https://linkedin.com/..." />
            <flux:input wire:model="twitter_url" type="url" label="Twitter URL" placeholder="https://x.com/..." />
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <flux:button :href="route('landing.about')" target="_blank" variant="ghost">Preview</flux:button>
            <flux:button type="submit" variant="primary">Save Changes</flux:button>
        </div>
    </form>
</div>
