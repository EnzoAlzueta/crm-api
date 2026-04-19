<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Client;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Contacts
 *
 * Contacts belong to a Client. Create via POST /clients/{client}/contacts; read/update/delete via flat routes.
 */
class ContactController extends Controller
{
    private function ensureClientOwnership(Request $request, Client $client): void
    {
        abort_unless($client->user_id === $request->user()->id, 403);
    }

    private function ensureContactOwnership(Request $request, Contact $contact): void
    {
        $contact->loadMissing('client');
        abort_unless($contact->client->user_id === $request->user()->id, 403);
    }

    /**
     * List all contacts.
     *
     * Returns all contacts across all clients of the authenticated user, paginated.
     *
     * @queryParam page int Page number. Example: 1
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Contact::whereHas('client', function ($q) use ($request): void {
            $q->where('user_id', $request->user()->id);
        });

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        return ContactResource::collection($query->paginate(10));
    }

    /**
     * List contacts for a client.
     *
     * @urlParam client integer required The client ID. Example: 1
     * @queryParam page int Page number. Example: 1
     */
    public function indexByClient(Request $request, Client $client): AnonymousResourceCollection
    {
        $this->ensureClientOwnership($request, $client);

        return ContactResource::collection($client->contacts()->paginate(10));
    }

    /**
     * Create a contact.
     *
     * @urlParam client integer required The client ID. Example: 1
     * @bodyParam name string required Example: Jane Doe
     * @bodyParam email string Example: jane@acme.test
     * @bodyParam phone string Example: +1-555-0101
     * @bodyParam position string Example: CTO
     *
     * @response 201 {"data":{"id":1,"client_id":1,"name":"Jane Doe","email":"jane@acme.test","phone":null,"position":"CTO","created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     */
    public function store(StoreContactRequest $request, Client $client)
    {
        $this->ensureClientOwnership($request, $client);

        $contact = $client->contacts()->create($request->validated());

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    /**
     * Get a contact.
     *
     * @response {"data":{"id":1,"client_id":1,"name":"Jane Doe","email":"jane@acme.test","phone":null,"position":"CTO","created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function show(Request $request, Contact $contact): ContactResource
    {
        $this->ensureContactOwnership($request, $contact);

        return new ContactResource($contact);
    }

    /**
     * Update a contact.
     *
     * @bodyParam name string Example: Jane Smith
     * @bodyParam email string Example: jane.smith@acme.test
     * @bodyParam phone string Example: +1-555-0202
     * @bodyParam position string Example: VP Engineering
     *
     * @response {"data":{"id":1,"client_id":1,"name":"Jane Smith","email":"jane.smith@acme.test","phone":null,"position":"VP Engineering","created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        $this->ensureContactOwnership($request, $contact);

        $contact->update($request->validated());

        return new ContactResource($contact);
    }

    /**
     * Delete a contact (soft delete).
     *
     * @response {"message":"Contact deleted"}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function destroy(Request $request, Contact $contact)
    {
        $this->ensureContactOwnership($request, $contact);

        $contact->delete();

        return response()->json(['message' => 'Contact deleted']);
    }
}
