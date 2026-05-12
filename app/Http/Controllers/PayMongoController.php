<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Notification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayMongoController extends Controller
{
    /**
     * Create a mock PayMongo payment session and redirect user
     */
    public function createPayment(Request $request, Transaction $txn)
    {
        $user = auth()->user();

        if ($txn->member_id !== $user->id) {
            abort(403);
        }

        if ((float) ($txn->outstanding_fine ?? 0) <= 0) {
            return back()->with('error', 'There is no outstanding fine for this transaction.');
        }

        $method = $request->input('payment_method', 'gcash');
        if (!in_array($method, ['gcash', 'paymaya'])) {
            return back()->with('error', 'Invalid payment method.');
        }

        $amount = round($txn->outstanding_fine, 2);

        // Generate mock PayMongo reference
        $reference = 'PAY-' . strtoupper($method) . '-' . now()->format('YmdHis') . '-' . $txn->id;
        $notes = trim(($txn->notes ?? '') .
            ($request->filled('contact_number') ? "\n[Payment contact: {$request->contact_number}]" : '')
        );

        $txn->update([
            'paymongo_reference' => $reference,
            'payment_method' => $method,
            'notes' => $notes,
        ]);

        ActivityLog::log(
            $user->id,
            'payment',
            "Initiated {$method} payment of ₱" . number_format($amount, 2) . " for TXN-{$txn->id}",
            'pending'
        );

        // Simulate PayMongo checkout page redirect
        return redirect()->route('paymongo.checkout', ['txn' => $txn, 'method' => $method]);
    }

    /**
     * Show mock PayMongo checkout page
     */
    public function checkout(Request $request, Transaction $txn)
    {
        $method = $request->input('method', 'gcash');
        $reference = $txn->paymongo_reference;

        if (!$reference) {
            return redirect()->route('portal.fines')->with('error', 'Invalid payment session.');
        }

        $amount = round($txn->outstanding_fine, 2);
        $contact = $request->input('contact_number');

        return view('paymongo.checkout', compact('txn', 'method', 'reference', 'amount', 'contact'));
    }

    /**
     * Handle successful payment return from PayMongo
     */
    public function success(Request $request, Transaction $txn)
    {
        $user = auth()->user();

        if ($txn->member_id !== $user->id) {
            abort(403);
        }

        if ($txn->fine_paid) {
            return redirect()->route('portal.fines')
                ->with('toast_success', 'Payment already processed.');
        }

        DB::transaction(function () use ($txn, $user) {
            $txn->completeFinePayment($txn->payment_method ?? 'gcash');

            Notification::create([
                'user_id' => $user->id,
                'type' => 'payment_success',
                'title' => 'Payment Successful',
                'message' => "Payment of ₱" . number_format($txn->fine, 2) . " for {$txn->book?->title} was successful.",
                'data' => ['transaction_id' => $txn->id, 'amount' => $txn->fine],
            ]);

            ActivityLog::log(
                $user->id,
                'payment',
                "Completed payment of ₱" . number_format($txn->fine, 2) . " for TXN-{$txn->id} via PayMongo",
                'success'
            );
        });

        return redirect()->route('portal.fines')
            ->with('toast_success', 'Payment of ₱' . number_format($txn->fine, 2) . ' successful!');
    }

    /**
     * Handle failed payment return from PayMongo
     */
    public function failed(Request $request, Transaction $txn)
    {
        ActivityLog::log(
            auth()->id(),
            'payment',
            "Failed/cancelled payment for TXN-{$txn->id}",
            'failed'
        );

        return redirect()->route('portal.fines')
            ->with('error', 'Payment was cancelled or failed. You can try again.');
    }
}

