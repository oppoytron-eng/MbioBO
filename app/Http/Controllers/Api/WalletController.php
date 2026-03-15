<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiPayoutRequest;
use App\Models\ApiWallet;
use App\Models\ApiWalletTransaction;
use App\Models\User;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request)
    {
        $wallet = $this->ensureWallet($request->user());

        return response()->json([
            'wallet' => $wallet->refresh(),
            'pending_payouts' => $wallet->payoutRequests()
                ->where('status', 'pending')
                ->orderByDesc('requested_at')
                ->get(),
        ]);
    }

    public function transactions(Request $request)
    {
        $wallet = $this->ensureWallet($request->user());

        return response()->json($wallet->transactions()->orderByDesc('created_at')->get());
    }

    public function requestPayout(Request $request)
    {
        $wallet = $this->ensureWallet($request->user());

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string'],
        ]);

        if ($wallet->balance < $data['amount']) {
            return response()->json(['message' => 'Solde insuffisant'], 400);
        }

        $wallet->decrement('balance', $data['amount']);

        $transaction = ApiWalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'amount' => $data['amount'],
            'source' => 'payout_request',
            'meta' => ['note' => $data['note'] ?? null],
        ]);

        $payout = ApiPayoutRequest::create([
            'wallet_id' => $wallet->id,
            'amount' => $data['amount'],
            'note' => $data['note'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Demande de retrait soumise',
            'payout_request' => $payout,
            'transaction' => $transaction,
        ], 201);
    }

    public function payoutRequests(Request $request)
    {
        $wallet = $this->ensureWallet($request->user());

        return response()->json(
            $wallet->payoutRequests()->orderByDesc('requested_at')->get()
        );
    }

    public function adminPayoutRequests(Request $request)
    {
        $this->assertAdmin($request->user());

        return response()->json(
            ApiPayoutRequest::with(['wallet.user'])->orderByDesc('requested_at')->get()
        );
    }

    public function updatePayoutStatus(Request $request, $id)
    {
        $this->assertAdmin($request->user());

        $payout = ApiPayoutRequest::with('wallet')->findOrFail($id);

        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'note' => ['nullable', 'string'],
        ]);

        $wasPending = $payout->status === 'pending';
        $payout->update([
            'status' => $data['status'],
            'note' => $data['note'] ?? $payout->note,
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        if ($wasPending && $data['status'] === 'rejected') {
            $payout->wallet->increment('balance', $payout->amount);
            ApiWalletTransaction::create([
                'wallet_id' => $payout->wallet_id,
                'type' => 'credit',
                'amount' => $payout->amount,
                'source' => 'payout_rejected',
                'meta' => ['payout_request_id' => $payout->id],
            ]);
        }

        return response()->json([
            'message' => 'Statut de la demande mis à jour',
            'payout_request' => $payout,
        ]);
    }

    private function ensureWallet(User $user): ApiWallet
    {
        return $user->apiWallet ?? ApiWallet::create(['user_id' => $user->id]);
    }

    private function assertAdmin(User $user): void
    {
        if ($user->role !== 'admin') {
            abort(403, 'Action réservée aux administrateurs');
        }
    }
}
