<?php

namespace App\Http\Controllers;

use App\Contracts\Services\ProvisioningDatabaseServiceInterface;
use App\Models\ProvisioningDatabaseConnection;
use App\Support\AuditLogger;
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

    public function browse(ProvisioningDatabaseConnection $connection, Request $request): View
    {
        $database = $request->query('database');
        $table = $request->query('table');

        $databases = $this->service->listDatabases($connection);
        $tables = $database ? $this->service->listTables($connection, $database) : [];
        $description = ($database && $table) ? $this->service->describeTable($connection, $database, $table) : ['columns' => [], 'indexes' => []];

        $this->auditLogger->log('db.provisioning.browse', ProvisioningDatabaseConnection::class, $connection->id, ['database' => $database, 'table' => $table]);

        return view('provisioning.browse', compact('connection', 'databases', 'tables', 'description', 'database', 'table'));
    }
}
