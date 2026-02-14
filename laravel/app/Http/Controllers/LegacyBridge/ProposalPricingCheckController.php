<?php

namespace App\Http\Controllers\LegacyBridge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProposalPricingCheckController extends Controller
{
    public function __invoke(Request $request)
    {
        if (filter_var(env('FORCE_LARAVEL_PROPOSAL_PRICING_FAIL', false), FILTER_VALIDATE_BOOLEAN)) {
            return response('', 500);
        }

        $this->bootstrapLegacySession();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        if (!isset($_SESSION['seller_user_name']) || $_SESSION['seller_user_name'] === '') {
            return $this->loginRedirect();
        }

        try {
            $settings = DB::connection('legacy')
                ->table('general_settings')
                ->select('edited_proposals')
                ->first();
        } catch (\Throwable $e) {
            return response('', 500);
        }

        if ($settings && (int) $settings->edited_proposals === 0) {
            return $this->jsonFalse();
        }

        $proposalId = $request->input('proposal_id');
        if (!is_numeric($proposalId)) {
            return $this->jsonFalse();
        }
        $proposalId = (int) $proposalId;

        try {
            $proposal = DB::connection('legacy')
                ->table('proposals')
                ->select('proposal_status', 'proposal_price', 'delivery_id', 'proposal_revisions')
                ->where('proposal_id', $proposalId)
                ->first();
        } catch (\Throwable $e) {
            return response('', 500);
        }

        if (!$proposal) {
            return $this->jsonFalse();
        }

        if (in_array($proposal->proposal_status, ['pending', 'draft', 'modification'], true)) {
            return $this->jsonFalse();
        }

        $diff = false;

        if ($request->has('proposal_price')) {
            $data = [
                'proposal_price' => $request->input('proposal_price'),
                'proposal_revisions' => $request->input('proposal_revisions'),
                'delivery_id' => $request->input('delivery_id'),
            ];

            $current = [
                'proposal_price' => (string) $proposal->proposal_price,
                'proposal_revisions' => (string) $proposal->proposal_revisions,
                'delivery_id' => (string) $proposal->delivery_id,
            ];

            if (array_diff_assoc($current, $data) || array_diff_assoc($data, $current)) {
                $diff = true;
            }
        }

        if ($request->has('proposal_packages')) {
            $packages = $request->input('proposal_packages');
            if (is_array($packages)) {
                $packages = array_values($packages);
                try {
                    $dbPackages = DB::connection('legacy')
                        ->table('proposal_packages')
                        ->select('package_id', 'description', 'revisions', 'delivery_time', 'price')
                        ->where('proposal_id', $proposalId)
                        ->orderBy('package_id')
                        ->get()
                        ->map(function ($row) {
                            return [
                                'package_id' => (int) $row->package_id,
                                'description' => (string) $row->description,
                                'revisions' => (string) $row->revisions,
                                'delivery_time' => (string) $row->delivery_time,
                                'price' => (string) $row->price,
                            ];
                        })
                        ->values()
                        ->toArray();
                } catch (\Throwable $e) {
                    return response('', 500);
                }

                $count = min(count($dbPackages), count($packages));
                for ($i = 0; $i < $count; $i++) {
                    $posted = $packages[$i];
                    $current = $dbPackages[$i] ?? [];
                    $normalizedPosted = [
                        'description' => isset($posted['description']) ? (string) $posted['description'] : '',
                        'revisions' => isset($posted['revisions']) ? (string) $posted['revisions'] : '',
                        'delivery_time' => isset($posted['delivery_time']) ? (string) $posted['delivery_time'] : '',
                        'price' => isset($posted['price']) ? (string) $posted['price'] : '',
                    ];
                    $normalizedCurrent = [
                        'description' => $current['description'] ?? '',
                        'revisions' => $current['revisions'] ?? '',
                        'delivery_time' => $current['delivery_time'] ?? '',
                        'price' => $current['price'] ?? '',
                    ];
                    if (array_diff_assoc($normalizedCurrent, $normalizedPosted) || array_diff_assoc($normalizedPosted, $normalizedCurrent)) {
                        $diff = true;
                        break;
                    }
                }
            }
        }

        return response(json_encode($diff), 200, ['Content-Type' => 'application/json; charset=UTF-8']);
    }

    private function bootstrapLegacySession(): void
    {
        $legacyBase = realpath(base_path('..'));
        $bootstrap = $legacyBase !== false
            ? $legacyBase . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'Platform' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'session_bootstrap.php'
            : null;

        if ($bootstrap && is_file($bootstrap) && !function_exists('blc_bootstrap_session')) {
            require_once $bootstrap;
        }

        if (function_exists('blc_bootstrap_session')) {
            blc_bootstrap_session();
        }
    }

    private function loginRedirect()
    {
        $body = "<script>window.open('../login','_self')</script>";
        return response($body, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    private function jsonFalse()
    {
        return response(json_encode(false), 200, ['Content-Type' => 'application/json; charset=UTF-8']);
    }
}
