<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Contacts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>All Contacts from All Lists</h1>
            <a href="{{ route('contacts.lists') }}" class="btn btn-secondary">Back to Lists</a>
        </div>

        @if(isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        @if(isset($allContacts) && count($allContacts) > 0)
            <div class="alert alert-info">
                <strong>Total Contacts:</strong> {{ count($allContacts) }}
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>List Name</th>
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
                        @foreach($allContacts as $contact)
                            <tr>
                                <td><span class="badge bg-primary">{{ $contact['list_name'] }}</span></td>
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
        @else
            <div class="alert alert-warning">No contacts found</div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>