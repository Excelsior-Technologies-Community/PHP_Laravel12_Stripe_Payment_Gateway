<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background: #f5f7fb;
            font-family: 'Segoe UI', sans-serif;
        }

        .page-title {
            font-size: 32px;
            font-weight: 700;
            color: #212529;
        }

        .dashboard-card {

            border: none;
            border-radius: 18px;
            color: #fff;
            transition: .3s;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);

        }

        .dashboard-card:hover {

            transform: translateY(-5px);

        }

        .card-total {

            background: linear-gradient(135deg, #4e73df, #224abe);

        }

        .card-success {

            background: linear-gradient(135deg, #1cc88a, #169b6b);

        }

        .card-failed {

            background: linear-gradient(135deg, #e74a3b, #be2617);

        }

        .card-pending {

            background: linear-gradient(135deg, #f6c23e, #d39e00);

        }

        .card-revenue {

            background: linear-gradient(135deg, #36b9cc, #258391);

        }

        .dashboard-card h6 {

            opacity: .9;
            font-size: 14px;

        }

        .dashboard-card h2 {

            font-size: 32px;
            font-weight: 700;

        }

        .filter-card {

            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, .08);

        }

        .table-card {

            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .08);

        }

        .table thead {

            background: #0d6efd;
            color: white;

        }

        .table th {

            border: none;
            padding: 15px;

        }

        .table td {

            vertical-align: middle;
            padding: 15px;

        }

        .badge {

            padding: 8px 15px;
            font-size: 13px;
            border-radius: 30px;

        }

        .btn {

            border-radius: 10px;

        }

        .btn-primary {

            padding: 10px 20px;

        }

        .form-control,
        .form-select {

            border-radius: 10px;

        }

        .pagination .page-link {

            border-radius: 8px;
            margin: 0 3px;

        }

        .page-item.active .page-link {

            background: #0d6efd;
            border-color: #0d6efd;

        }
    </style>

</head>

<body>
    <div class="container py-4">
        <div>

            <h2 class="page-title">
                <i class="fa-solid fa-credit-card"></i>
                Payment Dashboard
            </h2>

            <p class="text-muted">
                Manage Stripe Payments Easily
            </p>

        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card dashboard-card card-total">
                    <div class="card-body text-center">
                        <h6>Total</h6>
                        <h3>{{ $totalPayments }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card dashboard-card card-success">
                    <div class="card-body text-center">
                        <h6>Success</h6>
                        <h3>{{ $successPayments }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card dashboard-card card-failed">
                    <div class="card-body text-center">
                        <h6>Failed</h6>
                        <h3>{{ $failedPayments }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card dashboard-card card-pending">
                    <div class="card-body text-center">
                        <h6>Pending</h6>
                        <h3>{{ $pendingPayments }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card card-revenue">
                    <div class="card-body text-center">
                        <h6>Total Revenue</h6>
                        <h3>${{ number_format($totalRevenue, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3"><input class="form-control" name="search" value="{{ request('search') }}"
                    placeholder="Search"></div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="succeeded" @selected(request('status') == 'succeeded')>Succeeded</option>
                    <option value="failed" @selected(request('status') == 'failed')>Failed</option>
                    <option value="pending" @selected(request('status') == 'pending')>Pending</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" class="form-control" name="from_date"
                    value="{{ request('from_date') }}"></div>
            <div class="col-md-2"><input type="date" class="form-control" name="to_date"
                    value="{{ request('to_date') }}"></div>
            <div class="col-md-2">
                <select class="form-select" name="sort">
                    <option value="latest">Latest</option>
                    <option value="oldest" @selected(request('sort') == 'oldest')>Oldest</option>
                    <option value="amount_high" @selected(request('sort') == 'amount_high')>Amount High</option>
                    <option value="amount_low" @selected(request('sort') == 'amount_low')>Amount Low</option>
                </select>
            </div>
            <div class="col-md-1"><button class="btn btn-success w-100">Go</button></div>
        </form>

        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Charge</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->id }}</td>
                        <td>{{ $payment->name }}</td>
                        <td>{{ $payment->email }}</td>
                        <td>${{ number_format($payment->amount, 2) }}</td>
                        <td><span
                                class="badge bg-{{ $payment->status == 'succeeded' ? 'success' : 'danger' }}">{{ $payment->status }}</span>
                        </td>
                        <td>{{ $payment->created_at->format('d M Y h:i A') }}</td>
                        <td>{{ $payment->stripe_charge_id }}</td>
                        <td>
                            <form method="POST" action="{{ route('payment.destroy', $payment->id) }}" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No payments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($payments->lastPage() > 1)
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination">

                        @for ($i = 1; $i <= $payments->lastPage(); $i++)
                            <li class="page-item {{ $payments->currentPage() == $i ? 'active' : '' }}">
                                <a class="page-link" href="{{ $payments->url($i) }}">
                                    {{ $i }}
                                </a>
                            </li>
                        @endfor

                    </ul>
                </nav>
            </div>
        @endif

    </div>
    <script>
        document.querySelectorAll('.delete-form').forEach(f => {
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                Swal.fire({ title: 'Delete payment?', icon: 'warning', showCancelButton: true }).then(r => { if (r.isConfirmed) f.submit(); });
            });
        });
    </script>
</body>

</html>