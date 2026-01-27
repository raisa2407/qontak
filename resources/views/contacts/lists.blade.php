<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Lists</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Contact Lists</h1>
            <div>
                <a href="{{ route('contacts.all') }}" class="btn btn-primary me-2">View All Contacts</a>
                <a href="{{ route('mekari.logout') }}" class="btn btn-danger">Logout</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        @if(isset($contactLists) && $contactLists['status'] === 'success')
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Total Contacts</th>
                            <th>Success</th>
                            <th>Failed</th>
                            <th>Progress</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contactLists['data'] as $list)
                            <tr>
                                <td>{{ substr($list['id'], 0, 8) }}...</td>
                                <td>{{ $list['name'] }}</td>
                                <td><span class="badge bg-primary">{{ $list['contacts_count'] ?? 0 }}</span></td>
                                <td><span class="badge bg-success">{{ $list['contacts_count_success'] ?? 0 }}</span></td>
                                <td><span class="badge bg-danger">{{ $list['contacts_count_failed'] ?? 0 }}</span></td>
                                <td>
                                    @if(isset($list['progress']) && $list['progress'] === 'success')
                                        <span class="badge bg-success">{{ $list['progress'] }}</span>
                                    @else
                                        <span class="badge bg-warning">{{ $list['progress'] ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($list['created_at'])->format('d M Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('contacts.list.show', $list['id']) }}" class="btn btn-sm btn-info" title="View List Details">Details</a>
                                        <a href="{{ route('contacts.index', $list['id']) }}" class="btn btn-sm btn-primary" title="View Contacts">Contacts</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <h5>Raw JSON Response:</h5>
                <pre class="bg-light p-3 rounded">{{ json_encode($contactLists, JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>