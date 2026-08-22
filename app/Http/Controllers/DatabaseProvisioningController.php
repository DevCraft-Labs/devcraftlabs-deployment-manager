<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ProvisioningDatabaseServiceInterface;
use App\Http\Requests\ProvisioningDatabaseConnection\StoreProvisioningDatabaseConnectionRequest;
use App\Http\Requests\ProvisioningDatabaseConnection\UpdateProvisioningDatabaseConnectionRequest;
use App\Models\ProvisioningDatabaseConnection;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DatabaseProvisioningController extends Controller
{
    public function __construct(private readonly ProvisioningDatabaseServiceInterface $service, private readonly AuditLogger $auditLogger)
    {
    }

    public function index(): View
    {
        return view('provisioning.index', [
            'connections' => ProvisioningDatabaseConnection::query()->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('provisioning.create');
    }

    public function store(StoreProvisioningDatabaseConnectionRequest $request): RedirectResponse
    {
        $connection = ProvisioningDatabaseConnection::query()->create($request->validated());
        $this->auditLogger->log('db.provisioning.create', ProvisioningDatabaseConnection::class, $connection->id);

        return redirect()->route('provisioning.index')->with('status', 'Database connection created.');
    }

    public function edit(ProvisioningDatabaseConnection $connection): View
    {
        return view('provisioning.edit', compact('connection'));
    }

    public function update(UpdateProvisioningDatabaseConnectionRequest $request, ProvisioningDatabaseConnection $connection): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $connection->update($data);
        $this->auditLogger->log('db.provisioning.update', ProvisioningDatabaseConnection::class, $connection->id);

        return redirect()->route('provisioning.index')->with('status', 'Database connection updated.');
    }

    public function destroy(ProvisioningDatabaseConnection $connection): RedirectResponse
    {
        $id = $connection->id;
        $connection->delete();
        $this->auditLogger->log('db.provisioning.delete', ProvisioningDatabaseConnection::class, $id);

        return redirect()->route('provisioning.index')->with('status', 'Database connection deleted.');
    }

    public function test(ProvisioningDatabaseConnection $connection): RedirectResponse
    {
        try {
            $count = count($this->service->listDatabases($connection));
            $this->auditLogger->log('db.provisioning.test', ProvisioningDatabaseConnection::class, $connection->id, ['database_count' => $count]);

            return back()->with('status', "Connected successfully. {$count} database(s) available.");
        } catch (\Throwable $exception) {
            report($exception);
            $this->auditLogger->log('db.provisioning.test_failed', ProvisioningDatabaseConnection::class, $connection->id);

            return back()->withErrors(['connection' => 'Database connection failed. Verify the host, port, username, and password.']);
        }
    }

    public function browse(ProvisioningDatabaseConnection $connection, Request $request): View
    {
        $database = $request->query('database');
        $table = $request->query('table');
        $databases = $this->service->listDatabases($connection);
        abort_unless($database === null || in_array($database, $databases, true), 404);
        $tables = $database ? $this->service->listTables($connection, $database) : [];
        abort_unless($table === null || in_array($table, $tables, true), 404);
        $description = ($database && $table) ? $this->service->describeTable($connection, $database, $table) : ['columns' => [], 'indexes' => []];
        $rows = $table ? $this->service->tableRows($connection, $database, $table, $description) : null;

        $this->auditLogger->log('db.provisioning.browse', ProvisioningDatabaseConnection::class, $connection->id, ['database' => $database, 'table' => $table]);

        return view('provisioning.browse', compact('connection', 'databases', 'tables', 'description', 'database', 'table', 'rows'));
    }

    public function storeRow(ProvisioningDatabaseConnection $connection, Request $request): RedirectResponse
    {
        [$database, $table, $description] = $this->tableContext($connection, $request);
        $this->service->insertRow($connection, $database, $table, $description, $request->input('data', []));
        $this->auditLogger->log('db.provisioning.data.create', ProvisioningDatabaseConnection::class, $connection->id, ['database' => $database, 'table' => $table]);

        return $this->browseRedirect($connection, $database, $table, 'Row created.');
    }

    public function updateRow(ProvisioningDatabaseConnection $connection, Request $request): RedirectResponse
    {
        [$database, $table, $description] = $this->tableContext($connection, $request);
        $updated = $this->service->updateRow($connection, $database, $table, $description, $this->rowKey($request), $request->input('data', []));
        abort_unless($updated, 404, 'The selected row was not found or was not changed.');
        $this->auditLogger->log('db.provisioning.data.update', ProvisioningDatabaseConnection::class, $connection->id, ['database' => $database, 'table' => $table]);

        return $this->browseRedirect($connection, $database, $table, 'Row updated.');
    }

    public function destroyRow(ProvisioningDatabaseConnection $connection, Request $request): RedirectResponse
    {
        [$database, $table, $description] = $this->tableContext($connection, $request);
        $deleted = $this->service->deleteRow($connection, $database, $table, $description, $this->rowKey($request));
        abort_unless($deleted, 404, 'The selected row was not found.');
        $this->auditLogger->log('db.provisioning.data.delete', ProvisioningDatabaseConnection::class, $connection->id, ['database' => $database, 'table' => $table]);

        return $this->browseRedirect($connection, $database, $table, 'Row deleted.');
    }

    private function tableContext(ProvisioningDatabaseConnection $connection, Request $request): array
    {
        $database = $request->input('database');
        $table = $request->input('table');
        $databases = $this->service->listDatabases($connection);
        abort_unless($database !== null && in_array($database, $databases, true), 404);
        $tables = $this->service->listTables($connection, $database);
        abort_unless($table !== null && in_array($table, $tables, true), 404);

        return [$database, $table, $this->service->describeTable($connection, $database, $table)];
    }

    private function rowKey(Request $request): array
    {
        $key = json_decode(base64_decode((string) $request->input('key'), true), true);
        abort_unless(is_array($key), 422, 'The row identity is invalid.');

        return $key;
    }

    private function browseRedirect(ProvisioningDatabaseConnection $connection, string $database, string $table, string $status): RedirectResponse
    {
        return redirect()->route('provisioning.browse', compact('connection', 'database', 'table'))->with('status', $status);
    }
}
