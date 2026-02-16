<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Services\ProposalService;
use App\Services\SiteSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ProposalController - handles proposal CRUD and display.
 * Replaces: app/Modules/Platform/proposals/*.php
 */
class ProposalController extends Controller
{
    public function __construct(
        private readonly ProposalService $proposalService,
        private readonly AuthService $authService,
        private readonly SiteSettingsService $settingsService,
    ) {}

    /**
     * Show a single proposal.
     * Replaces: app/Modules/Platform/proposals/proposal.php
     * URL: /proposals/{username}/{proposal_url}
     */
    public function show(string $username, string $proposalUrl): View
    {
        $proposal = $this->proposalService->getProposalBySlug($username, $proposalUrl);

        if (!$proposal) {
            abort(404);
        }

        // Increment views
        $proposal->increment('proposal_views');

        return view('proposals.show', [
            'proposal' => $proposal,
            'seller' => $proposal->seller,
            'settings' => $this->settingsService->getGeneralSettings(),
            'currentSeller' => $this->authService->currentSeller(),
        ]);
    }

    /**
     * Show seller's proposals list.
     * Replaces: app/Modules/Platform/proposals/view_proposals.php
     */
    public function myProposals(): View
    {
        $seller = $this->authService->currentSeller();

        if (!$seller) {
            abort(403);
        }

        $proposals = $this->proposalService->getProposalsBySeller($seller->seller_id);

        return view('proposals.my-proposals', [
            'proposals' => $proposals,
            'seller' => $seller,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Show the create proposal form.
     * Replaces: app/Modules/Platform/proposals/create_proposal.php
     */
    public function create(): View
    {
        $seller = $this->authService->currentSeller();

        if (!$seller) {
            abort(403);
        }

        return view('proposals.create', [
            'seller' => $seller,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Show the edit proposal form.
     * Replaces: app/Modules/Platform/proposals/edit_proposal.php
     */
    public function edit(int $proposalId): View
    {
        $seller = $this->authService->currentSeller();

        if (!$seller) {
            abort(403);
        }

        $proposal = $this->proposalService->getProposalById($proposalId);

        if (!$proposal || $proposal->proposal_seller_id !== $seller->seller_id) {
            abort(404);
        }

        return view('proposals.edit', [
            'proposal' => $proposal,
            'seller' => $seller,
            'settings' => $this->settingsService->getGeneralSettings(),
        ]);
    }

    /**
     * Pause a proposal.
     * Replaces: app/Modules/Platform/proposals/pause_proposal.php
     */
    public function pause(int $proposalId): JsonResponse
    {
        $seller = $this->authService->currentSeller();

        if (!$seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $proposal = $this->proposalService->getProposalById($proposalId);

        if (!$proposal || $proposal->proposal_seller_id !== $seller->seller_id) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        $this->proposalService->pauseProposal($proposalId);

        return response()->json(['success' => true]);
    }

    /**
     * Activate a proposal.
     * Replaces: app/Modules/Platform/proposals/activate_proposal.php
     */
    public function activate(int $proposalId): JsonResponse
    {
        $seller = $this->authService->currentSeller();

        if (!$seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $proposal = $this->proposalService->getProposalById($proposalId);

        if (!$proposal || $proposal->proposal_seller_id !== $seller->seller_id) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        $this->proposalService->activateProposal($proposalId);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a proposal.
     * Replaces: app/Modules/Platform/proposals/delete_proposal.php
     */
    public function destroy(int $proposalId): JsonResponse
    {
        $seller = $this->authService->currentSeller();

        if (!$seller) {
            return response()->json(['success' => false, 'error' => 'unauthorized'], 403);
        }

        $proposal = $this->proposalService->getProposalById($proposalId);

        if (!$proposal || $proposal->proposal_seller_id !== $seller->seller_id) {
            return response()->json(['success' => false, 'error' => 'not_found'], 404);
        }

        $this->proposalService->deleteProposal($proposalId);

        return response()->json(['success' => true]);
    }
}
