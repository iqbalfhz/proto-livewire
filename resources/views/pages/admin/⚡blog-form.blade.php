<?php

use App\Models\Post;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?Post $post = null;

    public string $title = '';
    public string $slug = '';
    public string $excerpt = '';
    public string $content = '';
    public bool $is_published = false;
    public $thumbnail = null;
    public ?string $existing_thumbnail = null;

    public function mount(?Post $post = null): void
    {
        if ($post?->exists) {
            $this->post = $post;
            $this->title = $post->title;
            $this->slug = $post->slug;
            $this->excerpt = $post->excerpt ?? '';
            $this->content = $post->content;
            $this->is_published = $post->is_published;
            $this->existing_thumbnail = $post->thumbnail;
        }
    }

    public function updatedTitle(): void
    {
        if (!$this->post?->exists) {
            $this->slug = \Illuminate\Support\Str::slug($this->title);
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_published' => ['boolean'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
        ]);

        $thumbnailPath = $this->existing_thumbnail;
        if ($this->thumbnail) {
            $thumbnailPath = $this->thumbnail->store('posts', 'public');
        }

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'is_published' => $validated['is_published'],
            'thumbnail' => $thumbnailPath,
        ];

        if ($this->post?->exists) {
            $this->post->update($data);
            if ($validated['is_published'] && !$this->post->published_at) {
                $this->post->update(['published_at' => now()]);
            }
            Flux::toast(variant: 'success', text: 'Post updated.');
        } else {
            $post = Post::create(
                array_merge($data, [
                    'published_at' => $validated['is_published'] ? now() : null,
                ]),
            );
            Flux::toast(variant: 'success', text: 'Post created.');
            $this->redirectRoute('admin.blog.edit', ['post' => $post], navigate: true);
        }
    }

    public function render()
    {
        $title = $this->post?->exists ? 'Edit Post' : 'New Post';
        return $this->view()->title($title)->layout('layouts.admin');
    }
}; ?>

<div>
    <div class="flex items-center gap-3 mb-8">
        <flux:button :href="route('admin.blog.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate />
        <div>
            <flux:heading size="xl">{{ $post?->exists ? 'Edit Post' : 'New Post' }}</flux:heading>
            <flux:subheading>{{ $post?->exists ? 'Update your blog post.' : 'Write a new blog post.' }}
            </flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="space-y-5">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 space-y-5">
            <flux:input wire:model.live="title" label="Title" placeholder="Post title..." required />
            <flux:input wire:model="slug" label="Slug" placeholder="post-slug" required />
            <flux:textarea wire:model="excerpt" label="Excerpt" rows="2" placeholder="Short description..." />

            {{-- Quill Editor --}}
            <div>
                <flux:label class="mb-2 block">Content</flux:label>
                <div wire:ignore id="quill-wrapper" data-upload-url="{{ route('admin.upload-image') }}"
                    data-csrf="{{ csrf_token() }}">
                    <div id="quill-editor"></div>
                    <div id="quill-wordcount"
                        style="text-align:right;font-size:11px;color:#71717a;padding:4px 6px;border:1px solid #d4d4d8;border-top:none;border-radius:0 0 4px 4px;">
                    </div>
                </div>
                @error('content')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Thumbnail --}}
            <div>
                <flux:label>Thumbnail</flux:label>
                <div class="mt-2 flex items-center gap-4">
                    @if ($thumbnail)
                        <img src="{{ $thumbnail->temporaryUrl() }}"
                            class="w-24 h-16 object-cover rounded-lg ring-2 ring-blue-500" alt="Preview">
                    @elseif($existing_thumbnail)
                        <img src="{{ Storage::url($existing_thumbnail) }}"
                            class="w-24 h-16 object-cover rounded-lg ring-2 ring-zinc-300 dark:ring-zinc-600"
                            alt="Current thumbnail">
                    @endif
                    <flux:input type="file" wire:model="thumbnail" accept="image/*" />
                </div>
            </div>

            <div class="flex items-center gap-3">
                <flux:checkbox wire:model="is_published" id="is_published" />
                <flux:label for="is_published">Published</flux:label>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('admin.blog.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button type="submit" variant="primary">
                {{ $post?->exists ? 'Update Post' : 'Create Post' }}
            </flux:button>
        </div>
    </form>
</div>

<script>
    const wrapper = document.getElementById('quill-wrapper');
    const container = document.getElementById('quill-editor');
    const wordCountEl = document.getElementById('quill-wordcount');

    if (wrapper && container && !container._quill) {
        const uploadUrl = wrapper.dataset.uploadUrl;
        const csrfToken = wrapper.dataset.csrf;

        // ── Table Grid Picker ──────────────────────────────────────
        let tablePicker = null;
        let savedTableRange = null;

        function createTablePicker(editor) {
            const picker = document.createElement('div');
            picker.style.cssText =
                'position:fixed;background:#fff;border:1px solid #d4d4d8;border-radius:8px;padding:8px;box-shadow:0 4px 16px rgba(0,0,0,.15);z-index:9999;';

            const COLS = 7,
                ROWS = 6;
            const grid = document.createElement('div');
            grid.style.cssText = `display:grid;grid-template-columns:repeat(${COLS},22px);gap:2px;`;

            const label = document.createElement('p');
            label.style.cssText = 'text-align:center;font-size:12px;color:#71717a;margin:6px 0 0;';
            label.textContent = 'Hover to select size';

            const cells = [];
            for (let r = 0; r < ROWS; r++) {
                for (let c = 0; c < COLS; c++) {
                    const cell = document.createElement('div');
                    cell.style.cssText =
                        'width:20px;height:20px;border:1px solid #d4d4d8;border-radius:2px;cursor:pointer;background:#f9f9fb;transition:background .1s,border-color .1s;';
                    cell.dataset.r = r;
                    cell.dataset.c = c;
                    cells.push(cell);

                    cell.addEventListener('mouseenter', () => {
                        label.textContent = `${r + 1} × ${c + 1} table`;
                        cells.forEach(el => {
                            const active = +el.dataset.r <= r && +el.dataset.c <= c;
                            el.style.background = active ? '#dbeafe' : '#f9f9fb';
                            el.style.borderColor = active ? '#93c5fd' : '#d4d4d8';
                        });
                    });

                    cell.addEventListener('click', () => {
                        // Restore focus & selection so getSelection() is non-null
                        editor.focus();
                        if (savedTableRange) {
                            editor.setSelection(savedTableRange.index, savedTableRange.length);
                        }
                        editor.getModule('table').insertTable(r + 1, c + 1);
                        picker.remove();
                        tablePicker = null;
                        savedTableRange = null;
                    });

                    grid.appendChild(cell);
                }
            }

            picker.appendChild(grid);
            picker.appendChild(label);
            document.body.appendChild(picker);

            setTimeout(() => {
                document.addEventListener('click', function closePicker(e) {
                    if (!picker.contains(e.target) && !document.querySelector('.ql-table-insert')
                        ?.contains(e.target)) {
                        picker.remove();
                        tablePicker = null;
                        document.removeEventListener('click', closePicker);
                    }
                });
            }, 10);

            return picker;
        }

        // ── Table Context Menu ─────────────────────────────────────
        function showTableContextMenu(x, y, editor) {
            document.querySelector('#ql-table-ctx')?.remove();

            const menu = document.createElement('div');
            menu.id = 'ql-table-ctx';
            menu.style.cssText =
                `position:fixed;left:${x}px;top:${y}px;background:#fff;border:1px solid #d4d4d8;border-radius:8px;padding:4px;box-shadow:0 4px 12px rgba(0,0,0,.15);z-index:9999;min-width:190px;`;

            const items = [{
                    label: '↑ Insert Row Above',
                    fn: () => editor.getModule('table').insertRowAbove()
                },
                {
                    label: '↓ Insert Row Below',
                    fn: () => editor.getModule('table').insertRowBelow()
                },
                {
                    sep: true
                },
                {
                    label: '← Insert Column Left',
                    fn: () => editor.getModule('table').insertColumnLeft()
                },
                {
                    label: '→ Insert Column Right',
                    fn: () => editor.getModule('table').insertColumnRight()
                },
                {
                    sep: true
                },
                {
                    label: '✕ Delete Row',
                    fn: () => editor.getModule('table').deleteRow(),
                    danger: true
                },
                {
                    label: '✕ Delete Column',
                    fn: () => editor.getModule('table').deleteColumn(),
                    danger: true
                },
                {
                    label: '✕ Delete Table',
                    fn: () => editor.getModule('table').deleteTable(),
                    danger: true
                },
            ];

            items.forEach(item => {
                if (item.sep) {
                    const hr = document.createElement('hr');
                    hr.style.cssText = 'border:none;border-top:1px solid #f4f4f5;margin:2px 4px;';
                    menu.appendChild(hr);
                    return;
                }
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = item.label;
                btn.style.cssText =
                    `display:block;width:100%;text-align:left;padding:6px 12px;font-size:13px;border-radius:4px;cursor:pointer;background:none;border:none;color:${item.danger ? '#ef4444' : '#3f3f46'};`;
                btn.addEventListener('mouseenter', () => btn.style.background = '#f4f4f5');
                btn.addEventListener('mouseleave', () => btn.style.background = 'none');
                btn.addEventListener('click', () => {
                    item.fn();
                    menu.remove();
                });
                menu.appendChild(btn);
            });

            document.body.appendChild(menu);

            setTimeout(() => {
                document.addEventListener('click', function closeMenu(e) {
                    if (!menu.contains(e.target)) {
                        menu.remove();
                        document.removeEventListener('click', closeMenu);
                    }
                });
            }, 10);
        }

        // ── Image Upload Handler ───────────────────────────────────
        function imageHandler() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.click();
            input.onchange = async () => {
                const file = input.files[0];
                if (!file) return;
                const form = new FormData();
                form.append('image', file);
                try {
                    const res = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: form,
                    });
                    const json = await res.json();
                    const range = editor.getSelection(true);
                    editor.insertEmbed(range.index, 'image', json.url);
                    editor.setSelection(range.index + 1);
                } catch (e) {
                    console.error('Image upload failed', e);
                }
            };
        }

        // ── Quill Init ─────────────────────────────────────────────
        const editor = new Quill(container, {
            theme: 'snow',
            modules: {
                table: true,
                history: {
                    delay: 1000,
                    maxStack: 200,
                    userOnly: true
                },
                toolbar: {
                    container: [
                        [{
                            header: [1, 2, 3, 4, false]
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            color: []
                        }, {
                            background: []
                        }],
                        [{
                            script: 'sub'
                        }, {
                            script: 'super'
                        }],
                        [{
                            list: 'ordered'
                        }, {
                            list: 'bullet'
                        }, {
                            indent: '-1'
                        }, {
                            indent: '+1'
                        }],
                        [{
                            align: []
                        }],
                        ['blockquote', 'code-block'],
                        ['link', 'image', 'video', 'table-insert', 'hr-insert'],
                        ['clean', 'fullscreen-toggle'],
                    ],
                    handlers: {
                        'table-insert': function() {
                            const btn = document.querySelector('.ql-table-insert');
                            const rect = btn.getBoundingClientRect();
                            if (tablePicker) {
                                tablePicker.remove();
                                tablePicker = null;
                                return;
                            }
                            // Save selection before picker steals focus
                            savedTableRange = editor.getSelection() || {
                                index: editor.getLength() - 1,
                                length: 0
                            };
                            tablePicker = createTablePicker(editor);
                            tablePicker.style.left = rect.left + 'px';
                            tablePicker.style.top = (rect.bottom + 4) + 'px';
                        },
                        'hr-insert': function() {
                            const range = editor.getSelection(true);
                            editor.insertEmbed(range.index, 'divider', true);
                            editor.setSelection(range.index + 1, 0);
                        },
                        'fullscreen-toggle': function() {
                            wrapper.classList.toggle('ql-fullscreen');
                            const btn = document.querySelector('.ql-fullscreen-toggle');
                            const isFs = wrapper.classList.contains('ql-fullscreen');
                            if (btn) {
                                btn.title = isFs ? 'Exit Fullscreen' : 'Fullscreen';
                                btn.innerHTML = isFs ?
                                    '<svg viewBox="0 0 18 18"><path class="ql-stroke" d="M7 2H3v4M11 2h4v4M7 16H3v-4M11 16h4v-4"/></svg>' :
                                    '<svg viewBox="0 0 18 18"><path class="ql-stroke" d="M2 7V3h4M12 3h4v4M2 11v4h4M12 15h4v-4"/></svg>';
                            }
                        },
                        image: imageHandler,
                    },
                },
            },
        });

        // ── Custom button icons ────────────────────────────────────
        const tableBtn = document.querySelector('.ql-table-insert');
        if (tableBtn) {
            tableBtn.title = 'Insert Table';
            tableBtn.innerHTML =
                `<svg viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg"><rect class="ql-stroke" height="12" width="12" x="3" y="3"/><line class="ql-stroke" x1="3" x2="15" y1="9" y2="9"/><line class="ql-stroke" x1="9" x2="9" y1="3" y2="15"/></svg>`;
        }

        const hrBtn = document.querySelector('.ql-hr-insert');
        if (hrBtn) {
            hrBtn.title = 'Insert Horizontal Rule';
            hrBtn.innerHTML =
                `<svg viewBox="0 0 18 18"><line class="ql-stroke" x1="2" x2="16" y1="9" y2="9" stroke-width="2"/></svg>`;
        }

        const fsBtn = document.querySelector('.ql-fullscreen-toggle');
        if (fsBtn) {
            fsBtn.title = 'Fullscreen';
            fsBtn.innerHTML =
                `<svg viewBox="0 0 18 18"><path class="ql-stroke" d="M2 7V3h4M12 3h4v4M2 11v4h4M12 15h4v-4"/></svg>`;
        }

        // ── Table keyboard navigation (capture phase, before Quill) ──
        function getActiveTd() {
            const sel = window.getSelection();
            if (!sel || !sel.rangeCount) return null;
            const node = sel.anchorNode;
            const el = node.nodeType === 3 ? node.parentElement : node;
            return el.closest ? el.closest('td') : null;
        }

        container.addEventListener('keydown', function(e) {
            const td = getActiveTd();
            if (!td) return;

            // Enter = line break inside same cell (br)
            if (e.key === 'Enter' && !e.altKey) {
                e.preventDefault();
                e.stopPropagation();
                document.execCommand('insertLineBreak');
                // Capture HTML with <br> NOW (sync), before Quill's MutationObserver
                // normalizes and strips the <br> on the next tick.
                const htmlWithBr = editor.root.innerHTML;
                skipNextSync = true;
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    $wire.set('content', htmlWithBr);
                    skipNextSync = false;
                }, 300);
                return;
            }

            // Alt+Enter = insert new row below
            if (e.key === 'Enter' && e.altKey) {
                e.preventDefault();
                e.stopPropagation();
                editor.getModule('table').insertRowBelow();
                setTimeout(() => {
                    const tr = td.closest('tr');
                    const nextTr = tr ? tr.nextElementSibling : null;
                    const nextTd = nextTr ? nextTr.querySelector('td') : null;
                    if (nextTd) {
                        try {
                            const blot = Quill.find(nextTd);
                            if (blot) editor.setSelection(editor.getIndex(blot), 0);
                        } catch (_) {}
                    }
                }, 0);
                return;
            }

            if (e.key === 'Tab') {
                e.preventDefault();
                e.stopPropagation();
                const tds = Array.from(editor.root.querySelectorAll('td'));
                const idx = tds.indexOf(td);
                if (!e.shiftKey) {
                    if (idx < tds.length - 1) {
                        try {
                            const blot = Quill.find(tds[idx + 1]);
                            if (blot) editor.setSelection(editor.getIndex(blot), 0);
                        } catch (_) {}
                    } else {
                        editor.getModule('table').insertRowBelow();
                        setTimeout(() => {
                            const newTds = Array.from(editor.root.querySelectorAll('td'));
                            const nextTd = newTds[idx + 1];
                            if (nextTd) {
                                try {
                                    const blot = Quill.find(nextTd);
                                    if (blot) editor.setSelection(editor.getIndex(blot), 0);
                                } catch (_) {}
                            }
                        }, 0);
                    }
                } else {
                    if (idx > 0) {
                        try {
                            const blot = Quill.find(tds[idx - 1]);
                            if (blot) editor.setSelection(editor.getIndex(blot), 0);
                        } catch (_) {}
                    }
                }
            }
        }, true); // capture phase

        // ── Right-click context menu inside table ──────────────────
        container.addEventListener('contextmenu', e => {
            if (e.target.closest('td')) {
                e.preventDefault();
                showTableContextMenu(e.clientX, e.clientY, editor);
            }
        });

        // ── Word count ─────────────────────────────────────────────
        function updateWordCount() {
            if (!wordCountEl) return;
            const text = editor.getText().trim();
            const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
            const chars = Math.max(0, editor.getLength() - 1);
            wordCountEl.textContent = words + ' words \xB7 ' + chars + ' characters';
        }

        container._quill = editor;

        const initialContent = $wire.get('content');
        if (initialContent) {
            editor.root.innerHTML = initialContent;
        }

        updateWordCount();

        // ── Sync to Livewire (debounced) ───────────────────────────
        let debounceTimer;
        let skipNextSync = false;
        editor.on('text-change', () => {
            updateWordCount();
            // If we manually captured HTML (e.g. after table Enter), skip this
            // text-change so Quill's normalized version doesn't overwrite our <br>.
            if (skipNextSync) return;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                $wire.set('content', editor.root.innerHTML);
            }, 300);
        });
    }
</script>
