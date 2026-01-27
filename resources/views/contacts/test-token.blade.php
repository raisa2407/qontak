<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test All Auth Methods</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Test All Authentication Methods</h1>

        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5>Environment Variables</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="200">MEKARI_REFRESH_TOKEN:</th>
                        <td><code>{{ $env['refresh_token'] ?? 'NOT SET' }}</code></td>
                    </tr>
                    <tr>
                        <th>MEKARI_USERNAME:</th>
                        <td><code>{{ $env['username'] ?? 'NOT SET' }}</code></td>
                    </tr>
                    <tr>
                        <th>MEKARI_PASSWORD:</th>
                        <td><code>{{ $env['password'] ?? 'NOT SET' }}</code></td>
                    </tr>
                    <tr>
                        <th>MEKARI_CLIENT_ID:</th>
                        <td><code>{{ $env['client_id'] ?? 'NOT SET' }}</code></td>
                    </tr>
                    <tr>
                        <th>MEKARI_CLIENT_SECRET:</th>
                        <td><code>{{ $env['client_secret'] ?? 'NOT SET' }}</code></td>
                    </tr>
                </table>
            </div>
        </div>

        @foreach($results as $method => $result)
            <div class="card mb-3">
                <div class="card-header {{ isset($result['success']) && $result['success'] ? 'bg-success' : 'bg-danger' }} text-white">
                    <h5>{{ strtoupper(str_replace('_', ' ', $method)) }}</h5>
                </div>
                <div class="card-body">
                    @if(isset($result['error']))
                        <div class="alert alert-danger">
                            <strong>Exception:</strong> {{ $result['error'] }}
                        </div>
                    @else
                        <p><strong>Status Code:</strong> 
                            <span class="badge {{ $result['success'] ? 'bg-success' : 'bg-danger' }}">
                                {{ $result['status'] }}
                            </span>
                        </p>
                        <p><strong>Success:</strong> {{ $result['success'] ? 'YES ✓✓✓' : 'NO ✗' }}</p>
                        
                        @if($result['success'])
                            <div class="alert alert-success">
                                <h4>🎉🎉🎉 THIS METHOD WORKS! 🎉🎉🎉</h4>
                                <p>Use this configuration in your app!</p>
                            </div>

                            @if(isset($result['body']['access_token']))
                                <div class="alert alert-info">
                                    <strong>Access Token:</strong> {{ substr($result['body']['access_token'], 0, 50) }}...<br>
                                    <strong>Token Type:</strong> {{ $result['body']['token_type'] ?? 'N/A' }}<br>
                                    <strong>Expires In:</strong> {{ $result['body']['expires_in'] ?? 'N/A' }} seconds
                                </div>
                            @endif
                        @endif
                        
                        <h6>Response Body:</h6>
                        <pre class="bg-light p-3 rounded">{{ json_encode($result['body'], JSON_PRETTY_PRINT) }}</pre>
                        
                        <h6>Raw Response:</h6>
                        <pre class="bg-light p-3 rounded">{{ $result['raw'] }}</pre>
                    @endif
                </div>
            </div>
        @endforeach

        <div class="alert alert-warning">
            <h6>Instructions:</h6>
            <ol>
                <li>Set your credentials in .env file</li>
                <li>Look for the green card that says "THIS METHOD WORKS!"</li>
                <li>Tell me which method works and I'll update the service to use that method</li>
            </ol>
        </div>
    </div>
</body>
</html>