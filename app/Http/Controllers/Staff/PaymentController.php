<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ServiceApplication;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');

        $payments = Payment::with(['person', 'application', 'service', 'method', 'receiver'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($dateFrom, fn ($query) => $query->whereDate('payment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('payment_date', '<=', $dateTo))
            ->latest('payment_date')
            ->paginate(15)
            ->withQueryString();

        return view('staff.payments.index', [
            'payments' => $payments,
            'status' => $status,
            'dateFrom' => $dateFrom?->format('Y-m-d'),
            'dateTo' => $dateTo?->format('Y-m-d'),
            'summaryTotal' => (clone $payments->getCollection())->sum('amount'),
        ]);
    }

    public function store(Request $request, ServiceApplication $application): RedirectResponse
    {
        $data = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:unpaid,paid,partially_paid,refunded'],
            'payment_date' => ['nullable', 'date'],
            'transaction_reference' => ['nullable', 'string', 'max:120'],
            'remarks' => ['nullable', 'string'],
        ]);

        $paymentDate = isset($data['payment_date']) ? Carbon::parse($data['payment_date']) : now();

        $payment = Payment::create($data + [
            'receipt_no' => $this->generateReceiptNumber(),
            'application_id' => $application->id,
            'person_id' => $application->person_id,
            'service_id' => $application->service_id,
            'received_by' => $request->user()->id,
            'payment_date' => $paymentDate,
            'paid_at' => $data['status'] === 'paid' ? $paymentDate : null,
        ]);

        app(NotificationService::class)->assignedOfficer(
            $application->loadMissing('assignedOfficer'),
            'Payment added',
            "{$payment->receipt_no} was added to {$application->application_no}.",
            'payment_added'
        );
        app(NotificationService::class)->managers(
            'Payment added',
            "{$payment->receipt_no} was added to {$application->application_no}.",
            'payment_added',
            $application
        );

        return redirect()->route('staff.payments.show', $payment)->with('success', 'Payment saved successfully.');
    }

    public function show(Payment $payment): View
    {
        return view('staff.payments.show', [
            'payment' => $payment->load(['person', 'application', 'service', 'method', 'receiver']),
        ]);
    }

    public function receipt(Payment $payment): View
    {
        return view('staff.payments.receipt', [
            'payment' => $payment->load(['person', 'application', 'service', 'method', 'receiver']),
        ]);
    }

    public function receiptPdf(Payment $payment)
    {
        return Pdf::loadView('staff.payments.receipt', [
            'payment' => $payment->load(['person', 'application', 'service', 'method', 'receiver']),
            'pdf' => true,
        ])->download($payment->receipt_no.'.pdf');
    }

    public function reportPdf(Request $request)
    {
        $status = $request->string('status')->toString();
        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');

        $payments = Payment::with(['person', 'application', 'service', 'method'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($dateFrom, fn ($query) => $query->whereDate('payment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($query) => $query->whereDate('payment_date', '<=', $dateTo))
            ->latest('payment_date')
            ->get();

        return Pdf::loadView('staff.payments.report', compact('payments', 'status', 'dateFrom', 'dateTo'))
            ->download('payment-report.pdf');
    }

    private function generateReceiptNumber(): string
    {
        $prefix = 'REC-'.now()->format('Y').'-';
        $next = Payment::where('receipt_no', 'like', $prefix.'%')->count() + 1;

        do {
            $number = $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (Payment::where('receipt_no', $number)->exists());

        return $number;
    }

    public static function activePaymentMethods()
    {
        return PaymentMethod::where('is_active', true)->orderBy('name')->get();
    }
}
