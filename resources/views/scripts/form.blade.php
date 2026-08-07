<div class="row g-3 mb-3">
<div class="col-md-6"><label class="form-label">Script Name</label><input class="form-control" name="name" value="{{ old('name', $script->name ?? '') }}" required></div>
<div class="col-md-6"><label class="form-label">Working Directory</label><input id="working_directory" class="form-control" name="working_directory" value="{{ old('working_directory', $script->working_directory ?? base_path()) }}" required></div>
<div class="col-md-12"><label class="form-label">Description</label><textarea class="form-control" name="description">{{ old('description', $script->description ?? '') }}</textarea></div>
<div class="col-md-6"><label class="form-label">Server Health Monitoring URL</label><input class="form-control" type="url" name="health_check_url" value="{{ old('health_check_url', $script->health_check_url ?? '') }}" placeholder="https://example.com"><div class="form-text">The deployment page checks this Laravel application over HTTP. Any 2xx or 3xx response is Running.</div></div>
<div class="col-md-6"><label class="form-label">Application Log Folder</label><input id="log_directory" class="form-control" name="log_directory" value="{{ old('log_directory', $script->log_directory ?? '') }}" placeholder="/path/to/project/storage/logs"><div class="form-text">Defaults to the working directory plus <code>/storage/logs</code>. Downloaded as a ZIP from the script details page.</div></div>
<div class="col-md-3"><label class="form-label">Timeout (sec)</label><input class="form-control" type="number" name="timeout" value="{{ old('timeout', $script->timeout ?? 300) }}"></div>
<div class="col-md-3"><label class="form-label">Telegram Profile</label><select class="form-select" name="telegram_connection_id"><option value="">None</option>@foreach($telegramConnections as $connection)<option value="{{ $connection->id }}" @selected(old('telegram_connection_id', $script->telegram_connection_id ?? '') == $connection->id)>{{ $connection->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">SMTP Profile</label><select class="form-select" name="smtp_profile_id"><option value="">None</option>@foreach($smtpProfiles as $profile)<option value="{{ $profile->id }}" @selected(old('smtp_profile_id', $script->smtp_profile_id ?? '') == $profile->id)>{{ $profile->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Redis Profile</label><select class="form-select" name="redis_profile_id"><option value="">None</option>@foreach($redisProfiles as $profile)<option value="{{ $profile->id }}" @selected(old('redis_profile_id', $script->redis_profile_id ?? '') == $profile->id)>{{ $profile->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Cron Expression</label><input class="form-control" name="cron_expression" value="{{ old('cron_expression', $script->cron_expression ?? '') }}" placeholder="*/5 * * * *"></div>
<div class="col-md-2 form-check ms-2"><input class="form-check-input" type="checkbox" name="cron_enabled" value="1" @checked(old('cron_enabled', $script->cron_enabled ?? false))><label class="form-check-label">Cron Enabled</label></div>
<div class="col-md-2 form-check ms-2"><input class="form-check-input" type="checkbox" name="active" value="1" @checked(old('active', $script->active ?? true))><label class="form-check-label">Active</label></div>
<div class="col-md-12">
    <label class="form-label">Script Content</label>
    <div id="editor" style="height:380px;border:1px solid #ddd;"></div>
    <textarea id="script_content" name="script_content" class="d-none">{{ old('script_content', $script->script_content ?? '#!/bin/bash') }}</textarea>
</div>
</div>
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs/loader.min.js"></script>
<script>
const workingDirectory = document.getElementById('working_directory');
const logDirectory = document.getElementById('log_directory');
const setDefaultLogDirectory = () => {
    if (!logDirectory.value || logDirectory.dataset.autoFilled === 'true') {
        logDirectory.value = workingDirectory.value.replace(/[\\/]+$/, '') + '/storage/logs';
        logDirectory.dataset.autoFilled = 'true';
    }
};
setDefaultLogDirectory();
workingDirectory.addEventListener('input', setDefaultLogDirectory);
logDirectory.addEventListener('input', () => { logDirectory.dataset.autoFilled = 'false'; });
require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.52.2/min/vs' }});
require(['vs/editor/editor.main'], function () {
    const target = document.getElementById('script_content');
    const editor = monaco.editor.create(document.getElementById('editor'), {
        value: target.value,
        language: 'shell',
        theme: 'vs-dark',
        automaticLayout: true,
        minimap: { enabled: false }
    });
    editor.onDidChangeModelContent(function () { target.value = editor.getValue(); });
});
</script>
@endpush
