<?php

namespace App\Http\Controllers;

use App\Contracts\Services\SmtpServiceInterface;
use App\Http\Requests\SmtpProfile\StoreSmtpProfileRequest;
use App\Http\Requests\SmtpProfile\UpdateSmtpProfileRequest;
use App\Models\SmtpProfile;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmtpProfileController extends Controller
{
    public function __construct(private readonly SmtpServiceInterface $smtpService, private readonly AuditLogger $auditLogger)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('smtp.index', ['profiles' => SmtpProfile::query()->latest()->paginate(15)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('smtp.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSmtpProfileRequest $request): RedirectResponse
    {
        $profile = SmtpProfile::query()->create($request->validated());
        $this->auditLogger->log('smtp.create', SmtpProfile::class, $profile->id);

        return redirect()->route('smtp-profiles.index')->with('status', 'SMTP profile created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SmtpProfile $smtpProfile): View
    {
        return view('smtp.show', ['profile' => $smtpProfile]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SmtpProfile $smtpProfile): View
    {
        return view('smtp.edit', ['profile' => $smtpProfile]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSmtpProfileRequest $request, SmtpProfile $smtpProfile): RedirectResponse
    {
        $smtpProfile->update($request->validated());
        $this->auditLogger->log('smtp.update', SmtpProfile::class, $smtpProfile->id);

        return redirect()->route('smtp-profiles.index')->with('status', 'SMTP profile updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SmtpProfile $smtpProfile): RedirectResponse
    {
        $smtpProfile->delete();
        $this->auditLogger->log('smtp.delete', SmtpProfile::class, $smtpProfile->id);

        return redirect()->route('smtp-profiles.index')->with('status', 'SMTP profile deleted.');
    }

    public function test(Request $request, SmtpProfile $smtpProfile): RedirectResponse
    {
        $request->validate(['recipient_email' => ['required', 'email:rfc,dns']]);
        $this->smtpService->sendTestEmail($smtpProfile, $request->string('recipient_email')->toString());
        $this->auditLogger->log('smtp.test', SmtpProfile::class, $smtpProfile->id);

        return back()->with('status', 'SMTP test email sent.');
    }
}
