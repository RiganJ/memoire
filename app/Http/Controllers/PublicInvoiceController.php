<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\View\View;

class PublicInvoiceController extends Controller
{
    public function show(Invoice $invoice): View
    {
        return view('invoices.show', [
            'invoice' => $invoice->load('order.catalog', 'order.servicePackage', 'payment.paymentMethod'),
        ]);
    }
}
