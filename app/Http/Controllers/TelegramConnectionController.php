<?php

namespace App\Http\Controllers;

use App\Contracts\Services\TelegramServiceInterface;
use App\Http\Requests\TelegramConnection\StoreTelegramConnectionRequest;
use App\Http\Requests\TelegramConnection\UpdateTelegramConnectionRequest;
use App\Models\TelegramConnection;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TelegramConnectionController extends Controller
{
    public function __construct(private readonly TelegramServiceInterface $telegramService, private readonly AuditLogger $auditLogger)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('telegram.index', ['connections' => TelegramConnection::query()->latest()->paginate(15)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('telegram.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTelegramConnectionRequest $request): RedirectResponse
    {
        $connection = TelegramConnection::query()->create($request->validated());
        $this->auditLogger->log('telegram.create', TelegramConnection::class, $connection->id);

        return redirect()->route('telegram-connections.index')->with('status', 'Telegram connection created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TelegramConnection $telegramConnection): View
    {
        return view('telegram.show', ['connection' => $telegramConnection]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TelegramConnection $telegramConnection): View
    {
        return view('telegram.edit', ['connection' => $telegramConnection]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTelegramConnectionRequest $request, TelegramConnection $telegramConnection): RedirectResponse
    {
        $telegramConnection->update($request->validated());
        $this->auditLogger->log('telegram.update', TelegramConnection::class, $telegramConnection->id);

        return redirect()->route('telegram-connections.index')->with('status', 'Telegram connection updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TelegramConnection $telegramConnection): RedirectResponse
    {
        $telegramConnection->delete();
        $this->auditLogger->log('telegram.delete', TelegramConnection::class, $telegramConnection->id);

        return redirect()->route('telegram-connections.index')->with('status', 'Telegram connection deleted.');
    }

    public function test(TelegramConnection $telegramConnection): RedirectResponse
    {
        $result = $this->telegramService->test($telegramConnection);
        $this->auditLogger->log('telegram.test', TelegramConnection::class, $telegramConnection->id, ['ok' => $result['ok'] ?? false]);

        return back()->with('status', ($result['ok'] ?? false) ? 'Telegram test succeeded.' : 'Telegram test failed.');
    }
}
