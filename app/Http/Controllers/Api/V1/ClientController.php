<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Clients
 *
 * Manage CRM clients. All clients are scoped to the authenticated user.
 */
class ClientController extends Controller
{
    private function ensureOwnership(Request $request, Client $client): void
    {
        abort_unless($client->user_id === $request->user()->id, 403);
    }

    /**
     * List clients.
     *
     * Returns a paginated list of the authenticated user's clients.
     *
     * @queryParam page int Page number. Example: 1
     *
     * @response {"data":[{"id":1,"name":"Acme Corp","email":"info@acme.test","phone":null,"company":"Acme Corp","status":"active","created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}],"links":{...},"meta":{...}}
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Client::where('user_id', $request->user()->id);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $perPage = min((int) $request->query('per_page', 10), 200);

        return ClientResource::collection($query->paginate($perPage));
    }

    /**
     * Create a client.
     *
     * @bodyParam name string required Example: Acme Corp
     * @bodyParam email string Example: info@acme.test
     * @bodyParam phone string Example: +1-555-0100
     * @bodyParam company string Example: Acme Corp
     * @bodyParam status string One of: lead, active, inactive, churned. Example: lead
     *
     * @response 201 {"data":{"id":1,"name":"Acme Corp","email":"info@acme.test","phone":null,"company":"Acme Corp","status":"lead","created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     */
    public function store(StoreClientRequest $request)
    {
        $validated             = $request->validated();
        $validated['user_id']  = $request->user()->id;

        $client = Client::create($validated);

        return (new ClientResource($client))->response()->setStatusCode(201);
    }

    /**
     * Get a client.
     *
     * @response {"data":{"id":1,"name":"Acme Corp","email":"info@acme.test","phone":null,"company":"Acme Corp","status":"active","created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function show(Request $request, Client $client): ClientResource
    {
        $this->ensureOwnership($request, $client);

        return new ClientResource($client);
    }

    /**
     * Update a client.
     *
     * @bodyParam name string Example: Acme Corp Updated
     * @bodyParam email string Example: new@acme.test
     * @bodyParam phone string Example: +1-555-0199
     * @bodyParam company string Example: Acme Corp
     * @bodyParam status string One of: lead, active, inactive, churned. Example: active
     *
     * @response {"data":{"id":1,"name":"Acme Corp Updated","email":"new@acme.test","phone":null,"company":"Acme Corp","status":"active","created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function update(UpdateClientRequest $request, Client $client): ClientResource
    {
        $this->ensureOwnership($request, $client);

        $client->update($request->validated());

        return new ClientResource($client);
    }

    /**
     * Delete a client (soft delete).
     *
     * The client is soft-deleted and can be restored via POST /clients/{id}/restore.
     *
     * @response {"message":"Client deleted"}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function destroy(Request $request, Client $client)
    {
        $this->ensureOwnership($request, $client);

        $client->delete();

        return response()->json(['message' => 'Client deleted']);
    }

    /**
     * Restore a soft-deleted client.
     *
     * @response {"data":{"id":1,"name":"Acme Corp","email":"info@acme.test","phone":null,"company":"Acme Corp","status":"active","created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     * @response 403 {"message":"This action is unauthorized."}
     * @response 404 {"message":"No query results for model [App\\Models\\Client]"}
     */
    public function restore(Request $request, int $id): ClientResource
    {
        $client = Client::withTrashed()->findOrFail($id);
        $this->ensureOwnership($request, $client);

        $client->restore();

        return new ClientResource($client);
    }
}
