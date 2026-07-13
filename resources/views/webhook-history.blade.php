<!DOCTYPE html>
<html>

<head>

    <title>Webhook History</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>


<body class="bg-light">


    <div class="container mt-5">


        <h2 class="mb-4">
            Stripe Webhook History
        </h2>



        <div class="card shadow">


            <div class="card-body">


                <form method="GET" class="row g-3 mb-4">


                    <div class="col-md-5">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search Event ID / Type"
                            value="{{ request('search') }}">

                    </div>



                    <div class="col-md-4">


                        <select name="type" class="form-select">


                            <option value="">
                                All Events
                            </option>


                            @foreach($eventTypes as $type)

                            <option
                                value="{{ $type }}"
                                @if(request('type')==$type)
                                selected
                                @endif>

                                {{ $type }}

                            </option>

                            @endforeach


                        </select>


                    </div>



                    <div class="col-md-3">

                        <button class="btn btn-dark">
                            Search
                        </button>


                        <a href="{{ route('webhook.history') }}"
                            class="btn btn-secondary">

                            Reset

                        </a>


                    </div>


                </form>





                <div class="table-responsive">


                    <table class="table table-bordered table-hover">


                        <thead class="table-dark">


                            <tr>

                                <th>ID</th>

                                <th>Event ID</th>

                                <th>Event Type</th>

                                <th>Status</th>

                                <th>Date</th>

                                <th>Payload</th>

                            </tr>


                        </thead>



                        <tbody>


                            @forelse($webhooks as $webhook)


                            <tr>


                                <td>
                                    {{ $webhook->id }}
                                </td>


                                <td>
                                    {{ $webhook->event_id }}
                                </td>


                                <td>

                                    <span class="badge bg-primary">

                                        {{ $webhook->event_type }}

                                    </span>

                                </td>


                                <td>

                                    <span class="badge bg-success">

                                        {{ $webhook->status }}

                                    </span>

                                </td>


                                <td>

                                    {{ $webhook->created_at->format('d M Y h:i A') }}

                                </td>


                                <td>


                                    <button
                                        class="btn btn-sm btn-info text-white"
                                        data-bs-toggle="modal"
                                        data-bs-target="#payload{{ $webhook->id }}">

                                        View JSON

                                    </button>



                                    <div class="modal fade"
                                        id="payload{{ $webhook->id }}">


                                        <div class="modal-dialog modal-lg">


                                            <div class="modal-content">


                                                <div class="modal-header">


                                                    <h5>
                                                        {{ $webhook->event_type }}
                                                    </h5>


                                                    <button
                                                        class="btn-close"
                                                        data-bs-dismiss="modal">

                                                    </button>


                                                </div>


                                                <div class="modal-body">


                                                    <pre>
                                                    {{ json_encode($webhook->payload, JSON_PRETTY_PRINT) }}
                                                    </pre>


                                                </div>


                                            </div>


                                        </div>


                                    </div>



                                </td>


                            </tr>


                            @empty


                            <tr>

                                <td colspan="6" class="text-center">

                                    No webhook records found

                                </td>

                            </tr>


                            @endforelse



                        </tbody>


                    </table>


                </div>


                <div class="d-flex justify-content-between align-items-center mt-4">

                    <div class="text-muted">

                        Showing
                        {{ $webhooks->firstItem() ?? 0 }}
                        to
                        {{ $webhooks->lastItem() ?? 0 }}
                        of
                        {{ $webhooks->total() }}
                        webhook events

                    </div>


                    <div>

                        {{ $webhooks->links() }}

                    </div>

                </div>


            </div>

        </div>


    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>