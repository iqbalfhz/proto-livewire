<?php

use App\Models\SiteContent;
use App\Support\UploadedImages;
use Flux\Flux;
use Livewire\Attributes\Locked;
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

    #[Locked]
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
            UploadedImages::replace($this->existing_photo, $path);
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
    <x-admin.page-header :back="route('admin.dashboard')" title="About Page"
        subtitle="Edit your bio, photo, and social links" />

    <form wire:submit="save"
        class="admin-panel p-6 space-y-5">
        {{-- Photo upload: the picked file is cropped to 4:5 in the browser, and only the crop is uploaded --}}
        <div x-data="photoCropper()">
            <flux:label>Profile Photo</flux:label>
            <div class="mt-2 flex items-center gap-4">
                @if ($photo)
                    <img src="{{ $photo->temporaryUrl() }}" class="w-20 h-20 rounded-xl object-cover ring-2 ring-oxblood"
                        alt="Preview">
                @elseif($existing_photo)
                    <img src="{{ Storage::disk('public')->url($existing_photo) }}"
                        class="w-20 h-20 rounded-xl object-cover ring-2 ring-zinc-200 dark:ring-zinc-700"
                        alt="Current photo">
                @else
                    <div class="w-20 h-20 bg-zinc-100 dark:bg-zinc-800 rounded-xl flex items-center justify-center">
                        <flux:icon name="user" class="size-8 text-ink-muted" />
                    </div>
                @endif
                <flux:input type="file" accept="image/jpeg,image/png,image/webp" aria-label="Profile photo"
                    x-on:change="pick($event)" />
            </div>

            <p x-show="error" x-text="error" x-cloak class="mt-2 text-sm text-red-700 dark:text-red-400"></p>

            <div x-show="cropping" x-cloak class="mt-4 space-y-3">
                <div x-ref="stage" class="h-104 overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
                <p class="text-sm text-ink-muted">Drag the frame to move it. Drag a corner inwards to get closer.</p>
                <div class="flex justify-end gap-3">
                    <flux:button type="button" variant="ghost" x-on:click="close()">Cancel</flux:button>
                    <flux:button type="button" variant="primary" x-on:click="apply()" x-bind:disabled="busy || !ready">
                        Use this crop
                    </flux:button>
                </div>
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
