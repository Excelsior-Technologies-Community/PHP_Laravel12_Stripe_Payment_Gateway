<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentReceiptController extends Controller
{

    public function download($id)
    {

        $payment = Payment::findOrFail($id);

        $pdf = Pdf::loadView(

            'receipt',

            compact('payment')

        );

        return $pdf->download(

            'payment-receipt-'.$payment->id.'.pdf'

        );

    }

}