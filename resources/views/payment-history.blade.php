<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Payment History</h2>
        
        @if($payments->isEmpty())
            <div class="alert alert-info">No payments found.</div>
        @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Charge ID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->name }}</td>
                            <td>${{ number_format($payment->amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $payment->status == 'succeeded' ? 'success' : 'danger' }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td>{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                            <td><small>{{ $payment->stripe_charge_id }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        
        <a href="{{ route('stripe') }}" class="btn btn-primary">Make New Payment</a>
    </div>
</body>
</html>