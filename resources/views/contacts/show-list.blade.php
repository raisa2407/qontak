<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact List Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Contact List Detail</h1>
            <a href="{{ route('contacts.lists') }}" class="btn btn-secondary">Back to Lists</a>
        </div>

        @if(isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        @if(isset($contactList) && $contactList['status'] === 'success')
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">{{ $contactList['data']['name'] }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Basic Information</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="200">ID:</th>
                                    <td>{{ $contactList['data']['id'] }}</td>
                                </tr>
                                <tr>
                                    <th>Organization ID:</th>
                                    <td>{{ $contactList['data']['organization_id'] }}</td>
                                </tr>
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $contactList['data']['name'] }}</td>
                                </tr>
                                <tr>
                                    <th>Source Type:</th>
                                    <td><span class="badge bg-info">{{ $contactList['data']['source_type'] }}</span></td>
                                </tr>
                                <tr>
                                    <th>Source ID:</th>
                                    <td>{{ $contactList['data']['source_id'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Progress:</th>
                                    <td>
                                        @if($contactList['data']['progress'] === 'success')
                                            <span class="badge bg-success">{{ $contactList['data']['progress'] }}</span>
                                        @else
                                            <span class="badge bg-warning">{{ $contactList['data']['progress'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5>Statistics</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="200">Total Contacts:</th>
                                    <td><span class="badge bg-primary">{{ $contactList['data']['contacts_count'] }}</span></td>
                                </tr>
                                <tr>
                                    <th>Success:</th>
                                    <td><span class="badge bg-success">{{ $contactList['data']['contacts_count_success'] }}</span></td>
                                </tr>
                                <tr>
                                    <th>Failed:</th>
                                    <td><span class="badge bg-danger">{{ $contactList['data']['contacts_count_failed'] }}</span></td>
                                </tr>
                            </table>

                            <h5 class="mt-4">Timestamps</h5>
                            <table class="table table-sm">
                                <tr>
                                    <th width="200">Created At:</th>
                                    <td>{{ \Carbon\Carbon::parse($contactList['data']['created_at'])->format('d M Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At:</th>
                                    <td>{{ \Carbon\Carbon::parse($contactList['data']['updated_at'])->format('d M Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Finished At:</th>
                                    <td>{{ \Carbon\Carbon::parse($contactList['data']['finished_at'])->format('d M Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Contact Variables</h5>
                            @if(!empty($contactList['data']['contact_variables']))
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach($contactList['data']['contact_variables'] as $variable)
                                        <span class="badge bg-secondary">{{ $variable }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No variables defined</p>
                            @endif
                        </div>
                    </div>

                    @if(!empty($contactList['data']['error_messages']))
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5>Error Messages</h5>
                                <div class="alert alert-warning">
                                    <pre>{{ json_encode($contactList['data']['error_messages'], JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('contacts.index', $contactList['data']['id']) }}" class="btn btn-primary">
                            <i class="bi bi-people"></i> View Contacts in This List
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Raw JSON Response</h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-3 rounded">{{ json_encode($contactList, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>