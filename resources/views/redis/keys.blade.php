@extends('layouts.app')

@section('content')
<style>[x-cloak] { display: none !important; }</style>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">{{ $profile->name }} · Key Browser</h4>
        <small class="text-secondary">{{ $profile->host }}:{{ $profile->port }} · DB {{ $profile->database }}</small>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('redis-profiles.index') }}">Redis Profiles</a>
</div>

<div class="card" x-data="redisKeyBrowser({{ $profile->id }})" x-init="load()">
    <div class="card-header d-flex gap-2 align-items-center">
        <input type="text" class="form-control" placeholder="Search keys (substring match)…" x-model="search" @input.debounce.400ms="load()">
        <button class="btn btn-outline-secondary" @click="load()" :disabled="loading">Refresh</button>
    </div>

    <div class="card-body p-0" style="max-height: 65vh; overflow-y: auto;">
        <div class="p-3 text-secondary" x-show="loading">Loading keys…</div>
        <div class="p-3 text-secondary" x-show="!loading && visibleRows.length === 0">No keys found.</div>

        <template x-for="row in visibleRows" :key="row.path + (row.isKey ? ':leaf' : '')">
            <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom key-row"
                 :style="{ paddingLeft: (16 + row.level * 20) + 'px' }">
                <span class="text-secondary" style="width: 1rem; cursor: pointer;" @click="row.hasChildren ? toggle(row.path) : null" x-show="row.hasChildren" x-text="expanded.has(row.path) ? '▾' : '▸'"></span>
                <span style="width: 1rem;" x-show="!row.hasChildren"></span>
                <span :class="row.isKey ? 'text-primary' : 'fw-semibold'"
                      style="cursor: pointer;"
                      @click="row.isKey ? openKey(row.fullKey) : toggle(row.path)"
                      x-text="row.name"></span>
                <span class="badge bg-secondary ms-auto" x-show="row.isKey" x-text="row.fullKey"></span>
            </div>
        </template>
    </div>

    <div class="card-footer d-flex justify-content-between text-secondary small">
        <span x-text="visibleRows.filter(r => r.isKey).length + ' key(s) visible · ' + keys.length + ' scanned'"></span>
        <span class="text-warning" x-show="truncated">Result truncated — refine your search for a complete list.</span>
    </div>
</div>

<!-- Key detail / edit modal -->
<div class="modal" tabindex="-1" x-show="selectedKey" x-cloak style="display: none; background: rgba(0,0,0,.5);" @keydown.escape.window="closeModal()">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" @click.outside="closeModal()">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-break" x-text="selectedKey"></h5>
                <button type="button" class="btn-close" @click="closeModal()"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" x-show="error" x-text="error"></div>
                <template x-if="detail">
                    <div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <input class="form-control" type="text" :value="detail.type" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">TTL (seconds) — leave blank to keep the current expiry</label>
                                <input class="form-control" type="number" min="1" x-model="editTtl" :placeholder="detail.ttl >= 0 ? detail.ttl + 's remaining' : 'no expiry'">
                            </div>
                        </div>
                        <div class="mb-3" x-show="detail.editable">
                            <label class="form-label">
                                Value
                                <span class="text-secondary" x-show="detail.type !== 'string'">(JSON — object for hash/zset, array for list/set)</span>
                            </label>
                            <textarea class="form-control font-monospace" rows="14" x-model="editValue"></textarea>
                        </div>
                        <div class="alert alert-secondary" x-show="!detail.editable">
                            Editing is not supported for Redis type "<span x-text="detail.type"></span>". Showing raw value below.
                            <pre class="mt-2 mb-0" x-text="JSON.stringify(detail.value, null, 2)"></pre>
                        </div>
                    </div>
                </template>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                @can('connections.delete')
                <button type="button" class="btn btn-outline-danger" @click="removeKey()" :disabled="saving">Delete Key</button>
                @endcan
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" @click="closeModal()">Close</button>
                    @can('connections.update')
                    <button type="button" class="btn btn-primary" x-show="detail && detail.editable" @click="save()" :disabled="saving">Save</button>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('redisKeyBrowser', (profileId) => ({
        profileId,
        search: '',
        keys: [],
        truncated: false,
        loading: false,
        expanded: new Set(),
        selectedKey: null,
        detail: null,
        editValue: '',
        editTtl: '',
        saving: false,
        error: null,

        get dataUrl() {
            return `/redis-profiles/${this.profileId}/keys/data`;
        },
        get valueUrl() {
            return `/redis-profiles/${this.profileId}/keys/value`;
        },

        async load() {
            this.loading = true;
            try {
                const { data } = await axios.get(this.dataUrl, { params: { search: this.search || undefined } });
                this.keys = data.keys;
                this.truncated = data.truncated;
            } finally {
                this.loading = false;
            }
        },

        get tree() {
            const root = {};
            for (const key of this.keys) {
                const parts = key.split(':');
                let node = root;
                let pathAcc = '';
                parts.forEach((part, i) => {
                    pathAcc = pathAcc ? pathAcc + ':' + part : part;
                    if (!node[part]) {
                        node[part] = { __path: pathAcc, __children: {}, __isKey: false, __fullKey: null };
                    }
                    if (i === parts.length - 1) {
                        node[part].__isKey = true;
                        node[part].__fullKey = key;
                    }
                    node = node[part].__children;
                });
            }
            return root;
        },

        get visibleRows() {
            const rows = [];
            const walk = (node, level) => {
                Object.keys(node).sort().forEach((name) => {
                    const item = node[name];
                    const hasChildren = Object.keys(item.__children).length > 0;
                    rows.push({
                        name,
                        path: item.__path,
                        level,
                        hasChildren,
                        isKey: item.__isKey,
                        fullKey: item.__fullKey,
                    });
                    if (hasChildren && this.expanded.has(item.__path)) {
                        walk(item.__children, level + 1);
                    }
                });
            };
            walk(this.tree, 0);
            return rows;
        },

        toggle(path) {
            if (this.expanded.has(path)) {
                this.expanded.delete(path);
            } else {
                this.expanded.add(path);
            }
            this.expanded = new Set(this.expanded);
        },

        async openKey(key) {
            this.selectedKey = key;
            this.detail = null;
            this.error = null;
            this.editTtl = '';
            try {
                const { data } = await axios.get(this.valueUrl, { params: { key } });
                this.detail = data;
                this.editValue = data.type === 'string'
                    ? (data.value ?? '')
                    : JSON.stringify(data.value, null, 2);
            } catch (e) {
                this.error = e.response?.data?.message || 'Failed to load key.';
            }
        },

        closeModal() {
            this.selectedKey = null;
            this.detail = null;
            this.error = null;
        },

        async save() {
            if (!this.detail) return;
            this.error = null;

            let value;
            try {
                value = this.detail.type === 'string' ? this.editValue : JSON.parse(this.editValue);
            } catch (e) {
                this.error = 'Value is not valid JSON.';
                return;
            }

            this.saving = true;
            try {
                await axios.put(this.valueUrl, {
                    key: this.selectedKey,
                    type: this.detail.type,
                    value,
                    ttl: this.editTtl ? parseInt(this.editTtl, 10) : null,
                });
                await this.load();
                this.closeModal();
            } catch (e) {
                this.error = e.response?.data?.message || 'Failed to save key.';
            } finally {
                this.saving = false;
            }
        },

        async removeKey() {
            if (!this.selectedKey || !confirm(`Delete key "${this.selectedKey}"? This cannot be undone.`)) return;
            this.saving = true;
            try {
                await axios.delete(this.valueUrl, { data: { key: this.selectedKey } });
                await this.load();
                this.closeModal();
            } catch (e) {
                this.error = e.response?.data?.message || 'Failed to delete key.';
            } finally {
                this.saving = false;
            }
        },
    }));
});

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
</script>
@endpush
