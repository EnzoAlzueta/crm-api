<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Activities
 *
 * Activities (calls, emails, meetings, tasks) belong to a Client and optionally to a Contact.
 * Create via POST /clients/{client}/activities; read/update/delete via flat routes.
 */
class ActivityController extends Controller
{
    private function ensureClientOwnership(Request $request, Client $client): void
    {
        abort_unless($client->user_id === $request->user()->id, 403);
    }

    private function ensureActivityOwnership(Request $request, Activity $activity): void
    {
        $activity->loadMissing('client');
        abort_unless($activity->client->user_id === $request->user()->id, 403);
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
     * List all activities.
     *
     * Returns all activities across all clients of the authenticated user, paginated.
     *
     * @queryParam page int Page number. Example: 1
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Activity::whereHas('client', function ($q) use ($request): void {
            $q->where('user_id', $request->user()->id);
        });

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        if ($request->query('status') === 'done') {
            $query->whereNotNull('completed_at');
        } elseif ($request->query('status') === 'pending') {
            $query->whereNull('completed_at');
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        return ActivityResource::collection($query->latest()->paginate(10));
    }

    /**
     * List activities for a client.
     *
     * @urlParam client integer required The client ID. Example: 1
     * @queryParam page int Page number. Example: 1
     */
    public function indexByClient(Request $request, Client $client): AnonymousResourceCollection
    {
        $this->ensureClientOwnership($request, $client);

        return ActivityResource::collection($client->activities()->paginate(10));
    }

    /**
     * Create an activity.
     *
     * @urlParam client integer required The client ID. Example: 1
     * @bodyParam title string required Example: Follow-up call
     * @bodyParam type string Defaults to "task". One of: call, email, meeting, task. Example: call
     * @bodyParam body string Example: Discuss renewal options.
     * @bodyParam due_at string Date/time. Example: 2026-05-01 10:00:00
     * @bodyParam completed_at string Date/time. Leave null if not yet completed. Example: null
     * @bodyParam contact_id integer optional ID of a contact belonging to this client. Example: 2
     *
     * @response 201 {"data":{"id":1,"client_id":1,"contact_id":null,"type":"call","title":"Follow-up call","body":"Discuss renewal options.","due_at":"2026-05-01T10:00:00.000000Z","completed_at":null,"created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     */
    public function store(StoreActivityRequest $request, Client $client)
    {
        $this->ensureClientOwnership($request, $client);

        $validated = $request->validated();
        $this->validateContactBelongsToClient($client, $validated['contact_id'] ?? null);

        $validated['user_id'] = $request->user()->id;
        if (empty($validated['type'])) {
            $validated['type'] = 'task';
        }

        $activity = $client->activities()->create($validated);

        return (new ActivityResource($activity))->response()->setStatusCode(201);
    }

    /**
     * Get an activity.
     *
     * @response {"data":{"id":1,"client_id":1,"contact_id":null,"type":"call","title":"Follow-up call","body":null,"due_at":null,"completed_at":null,"created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function show(Request $request, Activity $activity): ActivityResource
    {
        $this->ensureActivityOwnership($request, $activity);

        return new ActivityResource($activity);
    }

    /**
     * Update an activity.
     *
     * @bodyParam title string Example: Follow-up call (rescheduled)
     * @bodyParam type string One of: call, email, meeting, task. Example: meeting
     * @bodyParam body string Example: Updated notes.
     * @bodyParam due_at string Date/time. Example: 2026-05-10 14:00:00
     * @bodyParam completed_at string Date/time to mark as completed. Example: 2026-04-13 09:00:00
     * @bodyParam contact_id integer nullable Example: 3
     *
     * @response {"data":{"id":1,"client_id":1,"contact_id":null,"type":"meeting","title":"Follow-up call (rescheduled)","body":"Updated notes.","due_at":"2026-05-10T14:00:00.000000Z","completed_at":null,"created_at":"2026-04-13T00:00:00.000000Z","updated_at":"2026-04-13T00:00:00.000000Z"}}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function update(UpdateActivityRequest $request, Activity $activity): ActivityResource
    {
        $this->ensureActivityOwnership($request, $activity);

        $validated = $request->validated();

        if (array_key_exists('contact_id', $validated)) {
            $this->validateContactBelongsToClient($activity->client, $validated['contact_id']);
        }

        $activity->update($validated);

        return new ActivityResource($activity);
    }

    /**
     * Delete an activity (soft delete).
     *
     * @response {"message":"Activity deleted"}
     * @response 403 {"message":"This action is unauthorized."}
     */
    public function destroy(Request $request, Activity $activity)
    {
        $this->ensureActivityOwnership($request, $activity);

        $activity->delete();

        return response()->json(['message' => 'Activity deleted']);
    }
}
