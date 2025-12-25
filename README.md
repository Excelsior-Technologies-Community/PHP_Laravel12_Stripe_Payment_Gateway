# PHP_Laravel12_Stripe_Payment_Gateway

A simple and secure **Stripe Payment Gateway integration** using **Laravel 12**. This project demonstrates **one-time card payments**, payment validation, database storage, and payment history tracking. It is ideal for **beginners, interviews, and real-world projects**.

---

## Project Overview

**Application Name:** Laravel 12 Stripe Payment System
**Framework:** Laravel 12
**Backend Language:** PHP 8.1+
**Payment Gateway:** Stripe
**Frontend:** Blade + JavaScript (Stripe Elements)
**Database:** MySQL

---

## Features

* Stripe card payment integration
* Secure token-based payment processing
* Server-side validation
* Payment status storage in database
* Payment history page
* Stripe test mode support
* Clean MVC architecture
* Beginner-friendly codebase

---

## Prerequisites

* PHP 8.1 or higher
* Composer
* Laravel 12
* MySQL
* Stripe Account (Test Mode)

---

## Installation Steps

### 1. Clone Repository

```bash
git clone https://github.com/yourusername/laravel12-stripe-payment.git
cd laravel12-stripe-payment
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Stripe Keys

Update your `.env` file:

```env
STRIPE_KEY=pk_test_your_public_key_here
STRIPE_SECRET=sk_test_your_secret_key_here
```

### 5. Setup Database

```bash
php artisan migrate
```

### 6. Run Application

```bash
php artisan serve
```

Visit:

```
http://localhost:8000/stripe
```

---

## Stripe Configuration

### Create Stripe Account

* Visit Stripe Dashboard
* Sign up for a free account
* Go to **Developers → API Keys**
* Copy **Publishable Key** and **Secret Key**

### Configure `config/stripe.php`

```php
return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
];
```

---

## Database Structure

### Payments Table

```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'usd',
    stripe_charge_id VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

---

## Usage

### Basic Payment Flow

1. Visit `/stripe`
2. Enter customer name and email
3. Enter card details
4. Click **Pay $10.00**
5. Stripe processes payment
6. Success or failure message displayed
7. Payment saved in database

### Payment History

* Visit `/payment-history`
* View all completed payments

---

## API Endpoints

| Method | Endpoint         | Description          |
| ------ | ---------------- | -------------------- |
| GET    | /stripe          | Display payment form |
| POST   | /stripe          | Process payment      |
| GET    | /payment-history | View payment history |

---

## Code Examples

### Stripe Payment Controller

```php
public function stripePost(Request $request): RedirectResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'stripeToken' => 'required',
    ]);

    Stripe\Stripe::setApiKey(config('stripe.secret'));

    try {
        $charge = Stripe\Charge::create([
            'amount' => 10 * 100,
            'currency' => 'usd',
            'source' => $request->stripeToken,
            'description' => 'Payment from ' . $request->name,
            'metadata' => ['customer_name' => $request->name]
        ]);

        Payment::create([
            'name' => $request->name,
            'email' => $request->email,
            'amount' => 10.00,
            'stripe_charge_id' => $charge->id,
            'status' => $charge->status,
        ]);

        return back()->with('success', 'Payment successful!');

    } catch (\Exception $e) {
        return back()->with('error', 'Payment failed: ' . $e->getMessage());
    }
}
```

### JavaScript (Token Creation)

```javascript
function createToken() {
    stripe.createToken(cardElement).then(function(result) {
        if (result.error) {
            document.getElementById('card-errors').textContent = result.error.message;
        } else {
            document.getElementById('stripe-token-id').value = result.token.id;
            document.getElementById('checkout-form').submit();
        }
    });
}
```
---
## Screenshot
### *Home Page
<img width="1084" height="934" alt="image" src="https://github.com/user-attachments/assets/b1b943aa-03ea-41fc-bf8e-c4ded2fe4e6d" />

### *Payment History
<img width="1554" height="306" alt="image" src="https://github.com/user-attachments/assets/6102dbb2-352b-4c22-a141-983f8c380f02" />

---

## Testing

### Test Credit Cards

| Card Number         | Description   | Result    |
| ------------------- | ------------- | --------- |
| 4242 4242 4242 4242 | Visa          | Success   |
| 4000 0000 0000 0002 | Declined      | Failure   |
| 4000 0025 0000 3155 | Requires Auth | 3D Secure |
| 5555 5555 5555 4444 | Mastercard    | Success   |

**Expiry:** Any future date
**CVC:** Any 3 digits
**ZIP:** Any 5 digits

### Run Tests

```bash
php artisan test
php artisan test --filter=PaymentTest
```

---

## Project Structure

```
laravel12-stripe-payment/
├── app/
│   ├── Http/Controllers/StripePaymentController.php
│   ├── Models/Payment.php
├── config/stripe.php
├── database/migrations/
├── resources/views/
│   ├── stripe.blade.php
│   └── payment-history.blade.php
├── routes/web.php
├── tests/Feature/PaymentTest.php
├── .env.example
├── composer.json
└── README.md
```

---

## Security Notes

* Stripe tokens used instead of raw card data
* Secret keys stored in `.env`
* Server-side validation applied
* CSRF protection enabled

---

## Possible Enhancements

* Stripe Webhook integration
* Subscription payments
* Refund handling
* Admin dashboard
* Invoice email system
* API-only Stripe integration

---

## Use Cases

* Learning Stripe payment gateway
* College / MCA final year projects
* Interview demonstrations
* Freelance / startup projects

---
