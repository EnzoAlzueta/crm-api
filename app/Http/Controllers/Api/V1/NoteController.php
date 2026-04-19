<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Notes
 *
 * Notes belong to a Client and optionally to a Contact of that client.
 * Create via POST /clients/{client}/notes; read/update/delete via flat routes.
 */
class NoteController extends Controller
{
    private function ensureClientOwnership(Request $request, Client $client): void
    {
        abort_unless($client->user_id === $request->user()->id, 403);
    }

    private function ensureNoteOwnership(Request $request, Note $note): void
    {
        $note->loadMissing('client');
        abort_unless($note->client->user_id === $request->user()->id, 403);
    }

    private function validateContactBelongsToClient(Client $client, ?int $contactId): void
    {
        if ($contactId === null) {
            return;
        }

        $belongs = Contact::whereKey($contactId)
            ->where('client_id', $client->id)
            ->exists();

        abort_unless($belongs, 422, 'Contact does not belong to this client.');
    }

    /**
     * List all notes.
     *
     * Returns all notes across all clients of the authenticated user, paginated.
     *
     * @queryParam page int Page number. Example: 1
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Note::whereHas('client', function ($q) use ($request): void {
            $q->where('user_id', $request->user()->id);
        });

        if ($search = $request->query('search')) {
            $query->where('body', 'like', "%{$search}%");
        }

        return NoteResource::collection($query->latest()->paginate(10));
    }

    /**
     * List notes for a client.
     *
     * @urlParam client integer required The client ID. Example: 1
     * @queryParam page int Page number. Example: 1
     */
    public function indexByClient(Request $request, Client $client): AnonymousResourceCollection
    {
        $this->ensureClientOwnership($request, $client);

        return NoteResource::collection($client->notes()->paginate(10));
    }

    /**
     * Create a note.
     *
     * @urlParam client integer required The client ID. Example: 1
     * @bodyParam body string required The note content. Example: Follow up next week.
     * @bodyParam contact_id integer optional ID of a contact belonging to this client. Example: 2
     *
     * @response 201 {"data":{"id":1,"client_id":1,"contact_id":null,"body":"Follow up next week.","created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     */
    public function store(StoreNoteRequest $request, Client $client)
    {
        $this->ensureClientOwnership($request, $client);

        $validated = $request->validated();
        $this->validateContactBelongsToClient($client, $validated['contact_id'] ?? null);

        $validated['user_id'] = $request->user()->id;
        $note                 = $client->notes()->create($validated);

        return (new NoteResource($note))->response()->setStatusCode(201);
    }

    /**
     * Get a note.
     *
     * @response {"data":{"id":1,"client_id":1,"contact_id":null,"body":"Follow up next week.","created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function show(Request $request, Note $note): NoteResource
    {
        $this->ensureNoteOwnership($request, $note);

        return new NoteResource($note);
    }

    /**
     * Update a note.
     *
     * @bodyParam body string The note content. Example: Updated note body.
     * @bodyParam contact_id integer nullable ID of a contact belonging to the note's client. Example: 2
     *
     * @response {"data":{"id":1,"client_id":1,"contact_id":null,"body":"Updated note body.","created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function update(UpdateNoteRequest $request, Note $note): NoteResource
    {
        $this->ensureNoteOwnership($request, $note);

        $validated = $request->validated();

        if (array_key_exists('contact_id', $validated)) {
            $this->validateContactBelongsToClient($note->client, $validated['contact_id']);
        }

        $note->update($validated);

        return new NoteResource($note);
    }

    /**
     * Delete a note (soft delete).
     *
     * @response {"message":"Note deleted"}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function destroy(Request $request, Note $note)
    {
        $this->ensureNoteOwnership($request, $note);

        $note->delete();

        return response()->json(['message' => 'Note deleted']);
    }
}
