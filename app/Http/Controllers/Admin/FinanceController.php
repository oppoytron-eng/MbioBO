<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chauffeur;
use App\Models\Course;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function transactions()
    {
        $courses = Course::with(['client.utilisateur', 'chauffeur.utilisateur'])
            ->orderByDesc('termine_le')
            ->paginate(20);

        return view('admin.finances.transactions', compact('courses'));
    }

    public function chauffeurBalance(Chauffeur $chauffeur)
    {
        $completed = Course::where('chauffeur_id', $chauffeur->id)
            ->where('est_terminee', true)
            ->sum('prix_final');

        $withdrawn = WithdrawalRequest::where('chauffeur_id', $chauffeur->id)
            ->where('status', 'approved')
            ->sum('amount');

        $pending = WithdrawalRequest::where('chauffeur_id', $chauffeur->id)
            ->where('status', 'pending')
            ->get();

        return view('admin.finances.balance', compact('chauffeur', 'completed', 'withdrawn', 'pending'));
    }

    public function withdrawals()
    {
        $withdrawals = WithdrawalRequest::with('chauffeur.utilisateur')
            ->orderByDesc('requested_at')
            ->paginate(20);

        return view('admin.finances.withdrawals', compact('withdrawals'));
    }

    public function approveWithdrawal(WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('status', 'La demande a déjà été traitée.');
        }

        $withdrawal->update([
            'status' => 'approved',
            'processed_at' => now(),
            'processed_by' => 'admin',
        ]);

        return back()->with('status', 'Demande de retrait validée.');
    }

    public function rejectWithdrawal(WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('status', 'La demande a déjà été traitée.');
        }

        $withdrawal->update([
            'status' => 'rejected',
            'processed_at' => now(),
            'processed_by' => 'admin',
        ]);

        return back()->with('status', 'Demande de retrait rejetée.');
    }
}
