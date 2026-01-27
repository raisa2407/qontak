<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mekari Contacts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Mekari Qontak Contacts</h1>

        @if(isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        @if(isset($contacts) && $contacts['status'] === 'success')
            <div class="alert alert-success">
                <strong>Status:</strong> {{ $contacts['status'] }}
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5>Pagination Info</h5>
                    <p><strong>Total:</strong> {{ $contacts['meta']['pagination']['total'] }}</p>
                    <p><strong>Limit:</strong> {{ $contacts['meta']['pagination']['limit'] }}</p>
                    <p><strong>Offset:</strong> {{ $contacts['meta']['pagination']['offset'] }}</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Phone Number</th>
                            <th>Email</th>
                            <th>Code</th>
                            <th>Company</th>
                            <th>Customer Name</th>
                            <th>Valid</th>
                            <th>Blocked</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contacts['data'] as $contact)
                            <tr>
                                <td>{{ $contact['id'] }}</td>
                                <td>{{ $contact['full_name'] }}</td>
                                <td>{{ $contact['phone_number'] }}</td>
                                <td>{{ $contact['email'] ?: '-' }}</td>
                                <td>{{ $contact['code'] ?: '-' }}</td>
                                <td>{{ $contact['extra']['company'] ?? '-' }}</td>
                                <td>{{ $contact['extra']['customer_name'] ?? '-' }}</td>
                                <td>
                                    @if($contact['is_valid'])
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($contact['is_blocked'])
                                        <span class="badge bg-danger">Yes</span>
                                    @else
                                        <span class="badge bg-success">No</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($contact['created_at'])->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <h5>Raw JSON Response:</h5>
                <pre class="bg-light p-3 rounded">{{ json_encode($contacts, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>