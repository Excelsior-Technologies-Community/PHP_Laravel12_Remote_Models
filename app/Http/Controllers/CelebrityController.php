<?php

namespace App\Http\Controllers;

use App\Models\Celebrity;
use App\Models\HostCelebrity;
use App\Models\SyncHistory;
use Illuminate\Http\Request;

class CelebrityController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Remote Host API
    |--------------------------------------------------------------------------
    */

    public function remoteSync(Request $request)
    {
        if (
            $request->header('X-API-KEY')
            != config('remote-models.api-key')
        ) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'data' => HostCelebrity::all()
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Local Consumer API
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        Celebrity::syncRemote();

        $query = Celebrity::query();

        // Search
        if ($request->filled('search')) {

            $query->where(function ($q) use ($request) {

                $q->where(
                    'name',
                    'like',
                    '%' . $request->search . '%'
                )
                ->orWhere(
                    'profession',
                    'like',
                    '%' . $request->search . '%'
                );
            });
        }

        // Profession Filter
        if ($request->filled('profession')) {

            $query->where(
                'profession',
                $request->profession
            );
        }

        $celebrities = $query->paginate(4);

        return response()->json($celebrities);
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics API
    |--------------------------------------------------------------------------
    */

    public function stats()
    {
        Celebrity::syncRemote();

        return response()->json([

            'total_celebrities' => Celebrity::count(),

            'actors' => Celebrity::where(
                'profession',
                'Actor'
            )->count(),

            'football_players' => Celebrity::where(
                'profession',
                'Football Player'
            )->count(),

            'last_sync' => SyncHistory::latest()
                ->first()?->synced_at,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Sync History API
    |--------------------------------------------------------------------------
    */

    public function syncHistory()
    {
        return response()->json(
            SyncHistory::latest()
                ->paginate(10)
        );
    }
}