<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment Studio & Financial Analytics</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5.3 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">

    <!-- Chart.js & SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background: #f4f6fb;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .dashboard-card {
            border: none;
            border-radius: 18px;
            color: #fff;
            transition: transform .25s ease, box-shadow .25s ease;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .06);
        }

        .dashboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, .12);
        }

        .card-total { background: linear-gradient(135deg, #4e73df, #224abe); }
        .card-success { background: linear-gradient(135deg, #1cc88a, #169b6b); }
        .card-failed { background: linear-gradient(135deg, #e74a3b, #be2617); }
        .card-pending { background: linear-gradient(135deg, #f6c23e, #d39e00); }
        .card-refunded { background: linear-gradient(135deg, #6f42c1, #4e229e); }
        .card-revenue { background: linear-gradient(135deg, #36b9cc, #258391); }

        .dashboard-card h6 {
            opacity: .85;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .dashboard-card h3 {
            font-size: 26px;
            font-weight: 700;
            margin: 0;
        }

        .chart-box {
            height: 280px;
        }

        .table-card {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .05);
            background: #fff;
        }

        .badge-subtle-success { background: #d1e7dd; color: #0f5132; }
        .badge-subtle-warning { background: #fff3cd; color: #664d03; }
        .badge-subtle-danger { background: #f8d7da; color: #842029; }
        .badge-subtle-purple { background: #e2d9f3; color: #432874; }
    </style>
</head>

<body>

    <!-- Header Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('payment.history') }}">
                <span class="badge bg-primary p-2 fs-6"><i class="fa-brands fa-stripe"></i></span> Stripe Studio & Analytics
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('webhook.history') }}" class="btn btn-outline-light btn-sm fw-semibold">
                    <i class="fa-solid fa-satellite-dish me-1"></i> Webhook Radar
                </a>
                <a href="{{ route('stripe') }}" class="btn btn-primary fw-bold">
                    <i class="fa-solid fa-plus-circle me-1"></i> Pay Now (Payment Intents)
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Financial KPI Matrix -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-2">
                <div class="card dashboard-card card-total">
                    <div class="card-body text-center p-3">
                        <h6>Total</h6>
                        <h3>{{ $totalPayments }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card dashboard-card card-success">
                    <div class="card-body text-center p-3">
                        <h6>Succeeded</h6>
                        <h3>{{ $successPayments }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card dashboard-card card-failed">
                    <div class="card-body text-center p-3">
                        <h6>Failed</h6>
                        <h3>{{ $failedPayments }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card dashboard-card card-pending">
                    <div class="card-body text-center p-3">
                        <h6>Pending</h6>
                        <h3>{{ $pendingPayments }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="card dashboard-card card-refunded">
                    <div class="card-body text-center p-3">
                        <h6>Refunded</h6>
                        <h3>{{ $refundedPayments }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="card dashboard-card card-revenue">
                    <div class="card-body text-center p-3">
                        <h6>Net Revenue</h6>
                        <h3>${{ number_format($totalRevenue, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart.js Financial Analytics Studio -->
        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><i class="fa-solid fa-chart-line text-primary me-2"></i>Monthly Revenue Velocity</span>
                        <span class="badge bg-light text-dark">USD Trend</span>
                    </div>
                    <div class="card-body chart-box">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><i class="fa-solid fa-chart-pie text-success me-2"></i>Transaction Status Ratio</span>
                        <span class="badge bg-light text-dark">Live Breakdown</span>
                    </div>
                    <div class="card-body chart-box">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table & Refund Engine Section -->
        <div class="table-card p-4">
            
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-receipt text-primary me-2"></i>Payment History & Refund Management
                    </h5>
                    <small class="text-muted">Manage 3DS charges, download PDF receipts, and trigger 1-Click refunds</small>
                </div>
                <div>
                    <a href="{{ route('stripe') }}" class="btn btn-outline-primary btn-sm fw-bold">
                        <i class="fa-solid fa-plus me-1"></i> New Transaction
                    </a>
                </div>
            </div>

            <!-- Filter Bar -->
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search name, email, ID...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="succeeded" @selected(request('status')=='succeeded')>Succeeded</option>
                        <option value="failed" @selected(request('status')=='failed')>Failed</option>
                        <option value="pending" @selected(request('status')=='pending')>Pending</option>
                        <option value="refunded" @selected(request('status')=='refunded')>Refunded</option>
                        <option value="partially_refunded" @selected(request('status')=='partially_refunded')>Partially Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="currency">
                        <option value="">All Currencies</option>
                        <option value="USD" @selected(request('currency')=='USD')>USD ($)</option>
                        <option value="INR" @selected(request('currency')=='INR')>INR (₹)</option>
                        <option value="EUR" @selected(request('currency')=='EUR')>EUR (€)</option>
                        <option value="GBP" @selected(request('currency')=='GBP')>GBP (£)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}" title="From Date">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="sort">
                        <option value="latest">Latest First</option>
                        <option value="oldest" @selected(request('sort')=='oldest')>Oldest First</option>
                        <option value="amount_high" @selected(request('sort')=='amount_high')>Amount: High to Low</option>
                        <option value="amount_low" @selected(request('sort')=='amount_low')>Amount: Low to High</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary w-100 fw-bold">Filter</button>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle border">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Amount & Currency</th>
                            <th>Status</th>
                            <th>Gateway References</th>
                            <th>Refund Action</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td class="text-muted fw-bold">{{ $payment->id }}</td>
                            <td>
                                <div class="fw-bold">{{ $payment->name }}</div>
                                <small class="text-muted">{{ $payment->email }}</small>
                            </td>
                            <td>
                                <div class="fw-bold fs-6">
                                    {{ $payment->currency == 'INR' ? '₹' : ($payment->currency == 'EUR' ? '€' : ($payment->currency == 'GBP' ? '£' : '$')) }}{{ number_format($payment->amount, 2) }}
                                    <span class="badge bg-light text-secondary border ms-1">{{ $payment->currency }}</span>
                                </div>
                                @if($payment->refunded_amount > 0)
                                <small class="text-danger fw-semibold d-block">
                                    -{{ $payment->currency == 'INR' ? '₹' : '$' }}{{ number_format($payment->refunded_amount, 2) }} Refunded
                                </small>
                                @endif
                            </td>
                            <td>
                                @if($payment->status === 'succeeded')
                                    <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Succeeded</span>
                                @elseif($payment->status === 'refunded')
                                    <span class="badge bg-purple text-white" style="background:#6f42c1;"><i class="fa-solid fa-rotate-left me-1"></i> Refunded</span>
                                @elseif($payment->status === 'partially_refunded')
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-pie-chart me-1"></i> Partial Refund</span>
                                @elseif($payment->status === 'failed')
                                    <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Failed</span>
                                @else
                                    <span class="badge bg-secondary">{{ $payment->status }}</span>
                                @endif
                            </td>
                            <td class="small">
                                @if($payment->payment_intent_id)
                                <div><span class="text-muted">PI:</span> <code>{{ Str::limit($payment->payment_intent_id, 18) }}</code></div>
                                @endif
                                @if($payment->stripe_charge_id)
                                <div><span class="text-muted">Ch:</span> <code>{{ Str::limit($payment->stripe_charge_id, 18) }}</code></div>
                                @endif
                                @if($payment->refund_id)
                                <div><span class="text-muted">Ref:</span> <code class="text-danger">{{ Str::limit($payment->refund_id, 18) }}</code></div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $remainingBalance = $payment->amount - ($payment->refunded_amount ?? 0);
                                @endphp
                                @if($payment->status === 'succeeded' || $payment->status === 'partially_refunded')
                                    @if($remainingBalance > 0)
                                    <button class="btn btn-outline-danger btn-sm refund-trigger-btn"
                                        data-id="{{ $payment->id }}"
                                        data-name="{{ $payment->name }}"
                                        data-amount="{{ $payment->amount }}"
                                        data-currency="{{ $payment->currency }}"
                                        data-remaining="{{ $remainingBalance }}">
                                        <i class="fa-solid fa-arrow-rotate-left me-1"></i> Refund
                                    </button>
                                    @else
                                    <span class="badge badge-subtle-success">Fully Refunded</span>
                                    @endif
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="text-nowrap small text-muted">
                                {{ $payment->created_at->format('d M Y') }}<br>
                                {{ $payment->created_at->format('h:i A') }}
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('payment.receipt', $payment->id) }}" class="btn btn-success btn-sm" title="PDF Receipt">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>

                                <form action="{{ route('payment.destroy', $payment->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" onclick="return confirm('Delete this payment record?')" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fa-solid fa-inbox fs-2 d-block mb-2 text-muted"></i>
                                No payment records found matching the criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} records</small>
                <div>
                    {{ $payments->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>

    </div>

    <!-- 1-Click Stripe Refund Modal -->
    <div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-arrow-rotate-left me-2"></i> Stripe 1-Click Refund Engine
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="stripeRefundForm">
                        <input type="hidden" id="refundModalPaymentId">

                        <div class="alert alert-light border mb-3">
                            <div class="small text-muted">Customer: <strong id="modalRefundCustomer" class="text-dark"></strong></div>
                            <div class="small text-muted">Available to Refund: <strong id="modalRefundRemaining" class="text-success"></strong></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Refund Option</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="refund_type" id="refFull" value="full" checked onchange="toggleRefundAmountInput()">
                                    <label class="form-check-label fw-semibold" for="refFull">Full Refund</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="refund_type" id="refPartial" value="partial" onchange="toggleRefundAmountInput()">
                                    <label class="form-check-label fw-semibold" for="refPartial">Partial Refund</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="partialAmountBlock" style="display: none;">
                            <label class="form-label fw-bold">Partial Refund Amount</label>
                            <input type="number" id="customRefundAmount" class="form-control" step="any" min="0.5" placeholder="Enter amount">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Refund Reason</label>
                            <input type="text" id="refundReasonInput" class="form-control" placeholder="Customer request / Duplicate charge" value="Requested by customer">
                        </div>

                        <button type="submit" id="confirmRefundBtn" class="btn btn-danger btn-lg w-100 fw-bold shadow-sm">
                            <i class="fa-solid fa-arrow-rotate-left me-1"></i> Process Stripe Refund
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js Scripts -->
    <script>
        // Revenue Bar Chart
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: @json($monthlyRevenue->pluck('month')),
                datasets: [{
                    label: 'Revenue ($)',
                    data: @json($monthlyRevenue->pluck('revenue')),
                    backgroundColor: 'rgba(54, 185, 204, 0.75)',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // Status Doughnut Chart
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(@json($statusChartData)),
                datasets: [{
                    data: Object.values(@json($statusChartData)),
                    backgroundColor: [
                        '#1cc88a', // Succeeded
                        '#e74a3b', // Failed
                        '#f6c23e', // Pending
                        '#6f42c1'  // Refunded
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>

    <!-- Refund Engine Logic -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        document.querySelectorAll('.refund-trigger-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const remaining = this.getAttribute('data-remaining');
                const currency = this.getAttribute('data-currency');

                document.getElementById('refundModalPaymentId').value = id;
                document.getElementById('modalRefundCustomer').innerText = name;
                document.getElementById('modalRefundRemaining').innerText = `${currency} ${remaining}`;
                document.getElementById('customRefundAmount').max = remaining;
                document.getElementById('customRefundAmount').value = remaining;

                document.getElementById('refFull').checked = true;
                toggleRefundAmountInput();

                new bootstrap.Modal(document.getElementById('refundModal')).show();
            });
        });

        function toggleRefundAmountInput() {
            const isPartial = document.getElementById('refPartial').checked;
            document.getElementById('partialAmountBlock').style.display = isPartial ? 'block' : 'none';
        }

        document.getElementById('stripeRefundForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('refundModalPaymentId').value;
            const refundType = document.querySelector('input[name="refund_type"]:checked').value;
            const refundAmount = document.getElementById('customRefundAmount').value;
            const reason = document.getElementById('refundReasonInput').value;

            const btn = document.getElementById('confirmRefundBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting to Stripe...';

            fetch(`/payment/${id}/refund`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({
                    refund_type: refundType,
                    refund_amount: refundAmount,
                    reason: reason
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-arrow-rotate-left me-1"></i> Process Stripe Refund';

                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('refundModal')).hide();
                    Swal.fire('Refund Initiated', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Refund Failed', data.message, 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-arrow-rotate-left me-1"></i> Process Stripe Refund';
                Swal.fire('Error', 'Unable to reach Stripe gateway.', 'error');
            });
        });
    </script>

</body>

</html>