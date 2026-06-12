<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceApplication;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, ServiceApplication $application)
    {
        $data = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:pending,paid,failed,refunded,cancelled'],
            'transaction_reference' => ['nullable', 'string', 'max:120'],
            'remarks' => ['nullable', 'string'],
        ]);

        return Payment::create($data + [
            'receipt_no' => 'REC-'.now()->format('YmdHis'),
            'application_id' => $application->id,
            'person_id' => $application->person_id,
            'received_by' => $request->user()->id,
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ]);
    }
}
