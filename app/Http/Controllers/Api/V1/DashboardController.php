<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Dashboard
 *
 * Aggregated stats for the authenticated user's CRM data.
 */
class DashboardController extends Controller
{
    /**
     * Get dashboard stats.
     *
     * Returns client counts by status, totals for contacts/notes/activities,
     * pending vs completed activities, and the 5 most recent activities.
     *
     * @response {
     *   "clients": {"total": 10, "by_status": {"lead": 3, "active": 5, "inactive": 2}},
     *   "contacts": {"total": 28},
     *   "notes": {"total": 21},
     *   "activities": {"total": 19, "pending": 7, "completed": 12},
     *   "recent_activities": [...]
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $clientQuery = Client::where('user_id', $userId);

        $clientsByStatus = (clone $clientQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $clientIds = (clone $clientQuery)->pluck('id');

        $totalContacts = Contact::whereIn('client_id', $clientIds)->count();
        $totalNotes    = Note::whereIn('client_id', $clientIds)->count();

        $totalActivities   = Activity::whereIn('client_id', $clientIds)->count();
        $pendingActivities = Activity::whereIn('client_id', $clientIds)->whereNull('completed_at')->count();

        $recentActivities = Activity::whereIn('client_id', $clientIds)
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'clients' => [
                'total'     => (clone $clientQuery)->count(),
                'by_status' => $clientsByStatus,
            ],
            'contacts' => [
                'total' => $totalContacts,
            ],
            'notes' => [
                'total' => $totalNotes,
            ],
            'activities' => [
                'total'     => $totalActivities,
                'pending'   => $pendingActivities,
                'completed' => $totalActivities - $pendingActivities,
            ],
            'recent_activities' => ActivityResource::collection($recentActivities),
        ]);
    }
}
