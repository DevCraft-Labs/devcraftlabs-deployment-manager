<?php

namespace App\Http\Controllers;

use App\Contracts\Services\TemporaryFileClipboardServiceInterface;
use App\Http\Requests\FileClipboard\StoreFileClipboardRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileClipboardController extends Controller
{
    public function __construct(private readonly TemporaryFileClipboardServiceInterface $fileClipboardService)
    {
    }

    public function create(): View
    {
        return view('file-clipboard.create', [
            'maxFileSizeKb' => (int) config('clipboard.max_file_size_kb', 10240),
        ]);
    }

    public function store(StoreFileClipboardRequest $request): RedirectResponse
    {
        $entry = $this->fileClipboardService->create($request->file('file'));

        return redirect()->route('file-clipboard.show', $entry['identifier'])->with('status', 'File uploaded. It expires in 5 minutes.');
    }

    public function show(string $fileClipboard): View
    {
        $entry = $this->fileClipboardService->find($fileClipboard);
        abort_unless($entry, 404, 'This file has expired or was deleted.');

        return view('file-clipboard.show', ['identifier' => $fileClipboard, 'entry' => $entry]);
    }

    public function download(string $fileClipboard): StreamedResponse
    {
        $entry = $this->fileClipboardService->find($fileClipboard);
        abort_unless($entry, 404, 'This file has expired or was deleted.');

        $path = $this->fileClipboardService->resolvePath($fileClipboard);
        abort_unless($path, 404, 'This file has expired or was deleted.');

        return Storage::disk(config('clipboard.file_disk', 'local'))->download($path, $entry['original_name']);
    }

    public function destroy(string $fileClipboard): RedirectResponse
    {
        $this->fileClipboardService->delete($fileClipboard);

        return redirect()->route('file-clipboard.create')->with('status', 'File deleted.');
    }
}
