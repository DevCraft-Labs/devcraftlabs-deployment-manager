<div class="row g-3 mb-3">
<div class="col-md-6"><label class="form-label">Connection Name</label><input class="form-control" name="name" value="{{ old('name', $connection->name ?? '') }}" required></div>
<div class="col-md-6"><label class="form-label">Chat ID</label><input class="form-control" name="chat_id" value="{{ old('chat_id', $connection->chat_id ?? '') }}" required></div>
<div class="col-md-6"><label class="form-label">Bot Token</label><input class="form-control" name="bot_token" value="{{ old('bot_token', $connection->bot_token ?? '') }}" required></div>
<div class="col-md-6"><label class="form-label">Allowed Chat IDs</label><input class="form-control" name="allowed_chat_ids" value="{{ old('allowed_chat_ids', $connection->allowed_chat_ids ?? '') }}"></div>
<div class="col-md-6"><label class="form-label">Webhook Secret</label><input class="form-control" name="webhook_secret" value="{{ old('webhook_secret', $connection->webhook_secret ?? '') }}"></div>
<div class="col-md-12"><label class="form-label">Description</label><textarea class="form-control" name="description">{{ old('description', $connection->description ?? '') }}</textarea></div>
<div class="col-md-2 form-check ms-2"><input class="form-check-input" type="checkbox" name="status" value="1" @checked(old('status', $connection->status ?? true))><label class="form-check-label">Active</label></div>
</div>
