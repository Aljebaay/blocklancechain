<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ProposalManagementController - admin proposal management.
 * Replaces: app/Modules/Platform/admin/view_proposals*.php
 */
class ProposalManagementController extends Controller
{
    /**
     * List proposals with filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Proposal::with('seller');

        // Status filter
        if ($request->has('status')) {
            $query->where('proposal_status', $request->input('status'));
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('proposal_title', 'LIKE', "%{$search}%");
        }

        // Featured filter
        if ($request->boolean('featured')) {
            $query->where('proposal_featured', 'yes');
        }

        // Top-rated filter
        if ($request->boolean('top_rated')) {
            $query->where('proposal_top_rated', 'yes');
        }

        $proposals = $query->orderByDesc('proposal_id')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $proposals->items(),
            'pagination' => [
                'current_page' => $proposals->currentPage(),
                'last_page' => $proposals->lastPage(),
                'total' => $proposals->total(),
            ],
        ]);
    }

    /**
     * Update proposal status (approve/pause/trash).
     */
    public function updateStatus(Request $request, int $proposalId): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:active,paused,pending,trash'],
        ]);

        $updated = Proposal::where('proposal_id', $proposalId)
            ->update(['proposal_status' => $validated['status']]);

        if (!$updated) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(int $proposalId): JsonResponse
    {
        $proposal = Proposal::find($proposalId);

        if (!$proposal) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        $proposal->update([
            'proposal_featured' => $proposal->proposal_featured === 'yes' ? 'no' : 'yes',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Toggle top-rated status.
     */
    public function toggleTopRated(int $proposalId): JsonResponse
    {
        $proposal = Proposal::find($proposalId);

        if (!$proposal) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        $proposal->update([
            'proposal_top_rated' => $proposal->proposal_top_rated === 'yes' ? 'no' : 'yes',
        ]);

        return response()->json(['success' => true]);
    }
}
