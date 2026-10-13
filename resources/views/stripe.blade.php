<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Stripe Payment Studio</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    
    <!-- Stripe.js v3 -->
    <script src="https://js.stripe.com/v3/"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background: linear-gradient(135deg, #1e1e2f 0%, #2d2b55 50%, #1e1e2f 100%);
            min-height: 100vh;
            color: #333;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .checkout-container {
            max-width: 600px;
            margin: 40px auto;
        }

        .checkout-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            border: none;
        }

        .checkout-header {
            background: linear-gradient(135deg, #635bff 0%, #00d4aa 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .chip-amount {
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 12px;
            padding: 8px 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .chip-amount.active, .chip-amount:hover {
            border-color: #635bff;
            background: #635bff;
            color: white;
        }

        #payment-element {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            background: #ffffff;
        }

        .btn-pay {
            background: linear-gradient(135deg, #635bff 0%, #4b45d6 100%);
            color: white;
            font-size: 1.15rem;
            font-weight: 700;
            border-radius: 12px;
            padding: 14px;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 91, 255, 0.45);
            color: white;
        }

        .btn-pay:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <div class="container checkout-container">
        
        <div class="d-flex justify-content-between align-items-center mb-3 text-white">
            <a href="{{ route('payment.history') }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard & Analytics
            </a>
            <span class="badge bg-light text-dark px-3 py-2 rounded-pill fw-semibold">
                <i class="fa-brands fa-stripe text-primary fa-lg me-1"></i> Payment Intents v3
            </span>
        </div>

        <div class="checkout-card">
            <div class="checkout-header">
                <h3 class="fw-bold mb-1"><i class="fa-solid fa-shield-halved me-2"></i>Stripe Payment Studio</h3>
                <p class="mb-0 text-white-50">Secure multi-currency payment with 3D Secure verification</p>
            </div>

            <div class="p-4">
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form id="payment-intent-form">
                    
                    <!-- Amount & Currency Selector -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Payment Amount & Currency</label>
                        
                        <!-- Quick Chips -->
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="chip-amount" onclick="selectAmount(10)">10</button>
                            <button type="button" class="chip-amount active" onclick="selectAmount(25)">25</button>
                            <button type="button" class="chip-amount" onclick="selectAmount(50)">50</button>
                            <button type="button" class="chip-amount" onclick="selectAmount(100)">100</button>
                            <button type="button" class="chip-amount" onclick="selectAmount(500)">500</button>
                        </div>

                        <div class="row g-2">
                            <div class="col-8">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light fw-bold" id="currency-symbol">$</span>
                                    <input type="number" id="amount-input" class="form-control fw-bold" value="25" min="1" step="any" required>
                                </div>
                            </div>
                            <div class="col-4">
                                <select id="currency-select" class="form-select form-select-lg fw-bold" onchange="updateCurrencySymbol()">
                                    <option value="usd" selected>USD ($)</option>
                                    <option value="inr">INR (₹)</option>
                                    <option value="eur">EUR (€)</option>
                                    <option value="gbp">GBP (£)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Customer Information -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Customer Information</label>
                        <div class="mb-2">
                            <input type="text" id="cust-name" class="form-control" placeholder="Full Name (e.g. Alexander Pierce)" value="Alexander Pierce" required>
                        </div>
                        <div>
                            <input type="email" id="cust-email" class="form-control" placeholder="Email Address (e.g. alex@example.com)" value="alex@example.com" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Card / Payment Element</label>
                        
                        <!-- Stripe Elements Container -->
                        <div id="payment-element" class="shadow-sm">
                            <div class="text-center py-3 text-muted">
                                <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                Loading Stripe Elements...
                            </div>
                        </div>
                        <div id="payment-errors" class="text-danger small mt-2 fw-semibold" role="alert"></div>
                    </div>

                    <button type="submit" id="submit-btn" class="btn btn-pay w-100 shadow">
                        <i class="fa-solid fa-lock me-2"></i> Pay <span id="pay-btn-label">$25.00 USD</span>
                    </button>

                    <div class="text-center mt-3 text-muted small">
                        <i class="fa-solid fa-shield-check text-success me-1"></i> 256-Bit SSL Encrypted | Powered by Stripe API
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Stripe Logic Script -->
    <script>
        const stripeKey = "{{ config('stripe.key') }}";
        const stripe = Stripe(stripeKey);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        let elements;
        let paymentElement;
        let currentClientSecret = null;
        let currentIntentId = null;

        function updateCurrencySymbol() {
            const curr = document.getElementById('currency-select').value;
            const symbols = { 'usd': '$', 'inr': '₹', 'eur': '€', 'gbp': '£' };
            document.getElementById('currency-symbol').innerText = symbols[curr] || '$';
            updatePayButtonLabel();
            initializePaymentElement(); // re-init with new currency
        }

        function selectAmount(val) {
            document.querySelectorAll('.chip-amount').forEach(el => el.classList.remove('active'));
            event.target.classList.add('active');
            document.getElementById('amount-input').value = val;
            updatePayButtonLabel();
            initializePaymentElement(); // re-init with new amount
        }

        function updatePayButtonLabel() {
            const amount = document.getElementById('amount-input').value || 0;
            const currency = document.getElementById('currency-select').value.toUpperCase();
            const symbol = document.getElementById('currency-symbol').innerText;
            document.getElementById('pay-btn-label').innerText = `${symbol}${parseFloat(amount).toFixed(2)} ${currency}`;
        }

        document.getElementById('amount-input').addEventListener('input', function() {
            updatePayButtonLabel();
        });

        document.getElementById('amount-input').addEventListener('change', function() {
            initializePaymentElement();
        });

        // Initialize Stripe Payment Element via Backend PaymentIntent
        async function initializePaymentElement() {
            const amount = document.getElementById('amount-input').value;
            const currency = document.getElementById('currency-select').value;
            const name = document.getElementById('cust-name').value;
            const email = document.getElementById('cust-email').value;

            const elementContainer = document.getElementById('payment-element');
            elementContainer.innerHTML = '<div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Preparing Secure Payment Element...</div>';

            try {
                const response = await fetch("{{ route('stripe.payment-intent') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({
                        amount: amount,
                        currency: currency,
                        name: name,
                        email: email
                    })
                });

                const data = await response.json();

                if (data.error) {
                    elementContainer.innerHTML = `<div class="alert alert-danger mb-0 small"><i class="fa-solid fa-triangle-exclamation me-1"></i> ${data.error}</div>`;
                    return;
                }

                currentClientSecret = data.clientSecret;
                currentIntentId = data.paymentIntentId;

                elements = stripe.elements({
                    clientSecret: currentClientSecret,
                    appearance: {
                        theme: 'stripe',
                        variables: {
                            colorPrimary: '#635bff',
                            borderRadius: '10px'
                        }
                    }
                });

                elementContainer.innerHTML = '';
                paymentElement = elements.create("payment");
                paymentElement.mount("#payment-element");

            } catch (err) {
                console.error(err);
                elementContainer.innerHTML = '<div class="alert alert-warning mb-0 small">Check your Stripe API keys in .env. Falling back to local test sandbox.</div>';
            }
        }

        // Handle Form Submission
        document.getElementById('payment-intent-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submit-btn');
            const errorElement = document.getElementById('payment-errors');
            errorElement.innerText = '';

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing Payment...';

            const name = document.getElementById('cust-name').value;
            const email = document.getElementById('cust-email').value;
            const amount = document.getElementById('amount-input').value;
            const currency = document.getElementById('currency-select').value;

            // Confirm via Stripe.js
            if (elements && currentClientSecret) {
                const { error, paymentIntent } = await stripe.confirmPayment({
                    elements,
                    confirmParams: {
                        return_url: window.location.origin + "{{ route('payment.history') }}",
                        payment_method_data: {
                            billing_details: {
                                name: name,
                                email: email
                            }
                        }
                    },
                    redirect: 'if_required'
                });

                if (error) {
                    errorElement.innerText = error.message;
                    submitBtn.disabled = false;
                    updatePayButtonLabel();
                    return;
                }

                if (paymentIntent && paymentIntent.status === 'succeeded') {
                    // Save to backend database
                    await fetch("{{ route('stripe.confirm-payment') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken
                        },
                        body: JSON.stringify({
                            payment_intent_id: paymentIntent.id,
                            name: name,
                            email: email,
                            amount: amount,
                            currency: currency
                        })
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Successful!',
                        text: `Transaction ID: ${paymentIntent.id}`,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = "{{ route('payment.history') }}";
                    });
                }
            } else {
                // Sandbox simulation fallback
                const simulatedId = 'pi_sim_' + Math.random().toString(36).substr(2, 9);
                await fetch("{{ route('stripe.confirm-payment') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({
                        payment_intent_id: simulatedId,
                        name: name,
                        email: email,
                        amount: amount,
                        currency: currency
                    })
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Payment Completed (Simulated)',
                    text: `Payment Intent ID: ${simulatedId}`,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "{{ route('payment.history') }}";
                });
            }
        });

        // Load initially
        window.onload = function() {
            updatePayButtonLabel();
            initializePaymentElement();
        };
    </script>

</body>

</html>