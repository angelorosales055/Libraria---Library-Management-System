<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class CirculationFineAmountController extends Controller
{
    public function __invoke(Request $request, Transaction $txn)
    {
        if (!in_array($txn->status, ['active', 'overdue'])) {
            abort(404);
        }

        $fine = $txn->calculateFine(now());

        return response()->json([
            'fine' => $fine
        ]);
    }
}

