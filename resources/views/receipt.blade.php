<!DOCTYPE html>

<html>

<head>

    <title>Payment Receipt</title>


    <style>
        body {

            font-family: Arial;

        }


        .container {

            border: 1px solid #ddd;

            padding: 30px;

        }


        h2 {

            text-align: center;

        }


        table {

            width: 100%;

            margin-top: 20px;

        }


        td {

            padding: 10px;

        }
    </style>


</head>


<body>


    <div class="container">


        <h2>
            Payment Receipt
        </h2>


        <table>


            <tr>

                <td>
                    Customer Name
                </td>

                <td>
                    {{ $payment->name }}
                </td>

            </tr>



            <tr>

                <td>
                    Email
                </td>

                <td>
                    {{ $payment->email }}
                </td>

            </tr>



            <tr>

                <td>
                    Amount
                </td>

                <td>
                    ${{ $payment->amount }}
                </td>

            </tr>



            <tr>

                <td>
                    Currency
                </td>

                <td>
                    {{ strtoupper($payment->currency) }}
                </td>

            </tr>



            <tr>

                <td>
                    Status
                </td>

                <td>
                    {{ $payment->status }}
                </td>

            </tr>



            <tr>

                <td>
                    Stripe Transaction ID
                </td>

                <td>
                    {{ $payment->stripe_charge_id }}
                </td>

            </tr>



            <tr>

                <td>
                    Payment Date
                </td>

                <td>
                    {{ $payment->created_at }}
                </td>

            </tr>



        </table>



        <h3>
            Thank you for your payment
        </h3>


    </div>


</body>

</html>