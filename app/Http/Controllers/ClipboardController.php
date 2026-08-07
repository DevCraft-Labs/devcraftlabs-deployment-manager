<?php

namespace App\Http\Controllers;

use App\Contracts\Services\TemporaryClipboardServiceInterface;
use App\Http\Requests\Clipboard\StoreClipboardRequest;
use App\Http\Requests\Clipboard\UpdateClipboardRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClipboardController extends Controller
{
    public function __construct(private readonly TemporaryClipboardServiceInterface $clipboardService)
    {
    }

    public function create(): View
    {
        return view('clipboard.create');
    }

    public function store(StoreClipboardRequest $request): RedirectResponse
    {
        $identifier = $this->clipboardService->create($request->string('content')->toString());

        return redirect()->route('clipboard.show', $identifier)->with('status', 'Clipboard created. It expires in 5 minutes.');
    }

    public function show(string $clipboard): View
    {
        $entry = $this->clipboardService->find($clipboard);
        abort_unless($entry, 404, 'This clipboard has expired or was deleted.');

        return view('clipboard.show', ['identifier' => $clipboard, 'entry' => $entry]);
    }

    public function update(UpdateClipboardRequest $request, string $clipboard): RedirectResponse
    {
        abort_unless($this->clipboardService->update($clipboard, $request->string('content')->toString()), 404, 'This clipboard has expired or was deleted.');

        return back()->with('status', 'Clipboard updated. Its 5-minute lifetime restarted.');
    }

    public function destroy(string $clipboard): RedirectResponse
    {
        $this->clipboardService->delete($clipboard);

        return redirect()->route('clipboard.create')->with('status', 'Clipboard deleted.');
    }
}