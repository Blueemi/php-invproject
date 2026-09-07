<?php

namespace App\Http\Controllers\Invoice;

use App\Enums\InvoiceStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentStatusController extends Controller
{
    public function __invoke(Request $request, string $id): RedirectResponse
    {
        $request->validate(['paid' => ['required', 'boolean']]);

        $paid = $request->boolean('paid');

        Invoice::findOrFail($id)->update([
            'paid' => $paid,
            'status' => $paid ? InvoiceStatusEnum::Paid->value : InvoiceStatusEnum::Unpaid->value,
        ]);

        return redirect()->route('invoices.index');
    }
}
