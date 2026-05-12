<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PortalRenewController extends Controller
{
    public function showForm(Transaction $txn)
    {
        if ($txn->member_id !== auth()->id()) {
            abort(403);
        }

        $loanDays = $txn->book->category->loan_period_days ?? 14;
        $proposedDueDate = $txn->due_date->copy()->addDays($loanDays);

        return view('portal.renew-request', compact('txn', 'proposedDueDate'));
    }

    public function request(Request $request, Transaction $origTxn)
    {
        $request->validate([
            'due_date' => 'required|date|after:today',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($origTxn->member_id !== auth()->id()) {
            abort(403);
        }

        if (!$origTxn->canRequestRenew()) {
            return back()->with('error', 'Cannot request renewal. Already requested or not eligible.');
        }

        $proposedDueDate = Carbon::parse($request->due_date);

        $newTxn = Transaction::create([
            'member_id' => $origTxn->member_id,
            'book_id' => $origTxn->book_id,
            'issued_by' => auth()->id(),
            'original_transaction_id' => $origTxn->id,
            'action' => 'renew_request',
            'status' => 'renew_requested',
            'issued_date' => today(),
            'due_date' => $proposedDueDate,
            'fine' => 0,
            'fine_paid' => false,
            'notes' => trim(($request->notes ?? '') . "\nRenewal request for original TXN #{$origTxn->id} ({$origTxn->book->title})"),
            'max_renewals' => $origTxn->max_renewals,
            'renewal_count' => $origTxn->renewal_count,
        ]);

        return redirect()->route('portal.transactions')
            ->with('toast_success', "Renewal request submitted for '{$origTxn->book->title}'. Awaiting approval. Proposed due: {$proposedDueDate->format('M j, Y')}");
    }
}
