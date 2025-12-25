<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel 12 Stripe Payment Integration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .payment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .payment-body {
            padding: 30px;
        }
        #card-element {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 12px;
            transition: border-color 0.3s;
        }
        #card-element.StripeElement--focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        #pay-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: transform 0.3s;
        }
        #pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        #pay-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .payment-amount {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="payment-card">
                <div class="payment-header">
                    <h2>💳 Stripe Payment Gateway</h2>
                    <p class="mb-0">Secure payment processing with Stripe</p>
                </div>
                
                <div class="payment-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ✅ {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ❌ {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="payment-amount">$10.00 USD</div>
                    
                    <form id="checkout-form" method="post" action="{{ route('stripe.post') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Enter your name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Card Information</label>
                            <div id="card-element" class="form-control"></div>
                            <div id="card-errors" class="text-danger mt-2"></div>
                        </div>

                        <input type="hidden" name="stripeToken" id="stripe-token-id">
                        
                        <button id="pay-btn" class="btn btn-lg w-100" type="button" onclick="createToken()">
                            <span id="button-text">Pay $10.00</span>
                            <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        </button>

                        <div class="text-center mt-4">
                            <p class="text-muted small">
                                <i class="fas fa-lock"></i> Your payment is secured with Stripe
                            </p>
                            <div class="d-flex justify-content-center gap-3">
                                <img src="https://cdn.worldvectorlogo.com/logos/visa-1.svg" alt="Visa" height="30">
                                <img src="https://cdn.worldvectorlogo.com/logos/mastercard-2.svg" alt="Mastercard" height="30">
                                <img src="https://cdn.worldvectorlogo.com/logos/american-express-2.svg" alt="Amex" height="30">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4 text-white">
                <p>Test Card: <code>4242 4242 4242 4242</code></p>
                <p>Exp: Any future date | CVC: Any 3 digits</p>
                <a href="/" class="text-white">← Back to Home</a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://js.stripe.com/v3/"></script>
<script type="text/javascript">
    // Initialize Stripe
    var stripe = Stripe('{{ config("stripe.key") }}');
    var elements = stripe.elements();
    
    // Custom styling
    var style = {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };
    
    // Create card element
    var cardElement = elements.create('card', { style: style });
    cardElement.mount('#card-element');
    
    // Handle real-time validation errors
    cardElement.on('change', function(event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
    
    // Create token function
    function createToken() {
        var payBtn = document.getElementById('pay-btn');
        var buttonText = document.getElementById('button-text');
        var spinner = document.getElementById('spinner');
        
        // Disable button and show spinner
        payBtn.disabled = true;
        buttonText.classList.add('d-none');
        spinner.classList.remove('d-none');
        
        stripe.createToken(cardElement).then(function(result) {
            if (result.error) {
                // Re-enable button
                payBtn.disabled = false;
                buttonText.classList.remove('d-none');
                spinner.classList.add('d-none');
                
                // Show error
                document.getElementById('card-errors').textContent = result.error.message;
            } else {
                // Set token value and submit form
                document.getElementById('stripe-token-id').value = result.token.id;
                document.getElementById('checkout-form').submit();
            }
        });
    }
    
    // Handle form submission via Enter key
    document.querySelector('input[name="name"]').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            createToken();
        }
    });
</script>

</body>
</html>