@extends('layouts.layout')

@section('title', 'Room Messages')

@section('content')
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('rooms.show', $id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Room
        </a>
        <div class="btn-group">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#botMessageModal">
                <i class="bi bi-robot me-2"></i>Send Bot Message
            </button>
            <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#interactiveMessageModal">
                <i class="bi bi-chat-square-dots me-2"></i>Send Interactive
            </button>
            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#hsmMessageModal">
                <i class="bi bi-envelope me-2"></i>Send HSM
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card" style="height: calc(100vh - 250px);">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-chat-left-text me-2"></i>Messages
                    @if (isset($messages['meta']['pagination']['total']))
                        <span class="badge bg-light text-dark ms-2">{{ $messages['meta']['pagination']['total'] }} total</span>
                    @endif
                </div>

                <div class="card-body p-0 overflow-auto" id="messageContainer" style="height: calc(100% - 140px);">
                    @if (isset($messages['data']) && count($messages['data']) > 0)
                        <div class="p-3">
                            @foreach ($messages['data'] as $message)
                                @php
                                    $isAgent = in_array($message['participant_type'] ?? '', ['agent', 'bot', 'system']);
                                    $alignmentClass = $isAgent ? 'justify-content-end' : 'justify-content-start';
                                    $bgClass = $isAgent ? 'bg-primary text-white' : 'bg-light';
                                    $participantType = $message['participant_type'] ?? 'unknown';
                                    $messageType = $message['type'] ?? 'text';
                                    $senderName = $message['sender']['name'] ?? 'Unknown';
                                @endphp

                                <div class="d-flex {{ $alignmentClass }} mb-3 message-bubble">
                                    <div style="max-width: 70%;">
                                        <div class="d-flex align-items-center mb-1 {{ $isAgent ? 'justify-content-end' : '' }}">
                                            <small class="text-muted">
                                                @if ($participantType == 'agent')
                                                    <i class="bi bi-person-badge"></i> {{ $senderName }}
                                                @elseif($participantType == 'bot')
                                                    <i class="bi bi-robot"></i> {{ $senderName }}
                                                @elseif($participantType == 'system')
                                                    <i class="bi bi-gear"></i> {{ $senderName }}
                                                @else
                                                    <i class="bi bi-person"></i> {{ $senderName }}
                                                @endif
                                            </small>
                                        </div>

                                        <div class="p-3 rounded {{ $bgClass }}" style="word-wrap: break-word;">
                                            @if ($messageType == 'text')
                                                {!! nl2br(e($message['text'] ?? 'No text content')) !!}
                                                
                                                @if (isset($message['buttons']) && count($message['buttons']) > 0)
                                                    <div class="mt-3 pt-2 border-top">
                                                        @foreach ($message['buttons'] as $button)
                                                            @if ($button['type'] == 'LIST_BUTTON')
                                                                <div class="badge bg-secondary mb-1">{{ $button['text'] }}</div>
                                                            @elseif($button['type'] == 'LIST_ROW')
                                                                <div class="small mt-1">• {{ $button['title'] }}</div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @elseif($messageType == 'image')
                                                @if (isset($message['file']['url']))
                                                    <img src="{{ $message['file']['url'] }}" class="img-fluid rounded mb-2" style="max-width: 300px;" alt="Image">
                                                @endif
                                                @if (isset($message['text']) && $message['text'])
                                                    <div class="mt-2">{!! nl2br(e($message['text'])) !!}</div>
                                                @endif
                                            @elseif($messageType == 'document')
                                                <div>
                                                    <i class="bi bi-file-earmark-text fs-1"></i>
                                                    <div class="mt-2">
                                                        <strong>{{ $message['file']['filename'] ?? 'Document' }}</strong>
                                                        @if (isset($message['file']['size']))
                                                            <br><small>{{ round($message['file']['size'] / 1024, 2) }} KB</small>
                                                        @endif
                                                        @if (isset($message['file']['url']))
                                                            <br><a href="{{ $message['file']['url'] }}" target="_blank" class="text-white text-decoration-underline">Download</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @elseif($messageType == 'video')
                                                @if (isset($message['file']['url']))
                                                    <video controls style="max-width: 300px;" class="rounded">
                                                        <source src="{{ $message['file']['url'] }}" type="video/mp4">
                                                    </video>
                                                @endif
                                            @elseif($messageType == 'audio' || $messageType == 'voice')
                                                @if (isset($message['file']['url']))
                                                    <audio controls class="w-100">
                                                        <source src="{{ $message['file']['url'] }}" type="audio/mpeg">
                                                    </audio>
                                                @endif
                                            @elseif($messageType == 'system')
                                                <div class="text-center">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    {{ $message['text'] ?? 'System message' }}
                                                </div>
                                            @else
                                                <span class="badge bg-secondary">{{ strtoupper($messageType) }}</span>
                                                <div class="mt-2">{{ $message['text'] ?? 'No preview available' }}</div>
                                            @endif

                                            @if (isset($message['status']) && !in_array($participantType, ['system']))
                                                <div class="mt-2 text-end">
                                                    <small>
                                                        @if ($message['status'] == 'sent' || $message['status'] == 'created')
                                                            <i class="bi bi-check"></i>
                                                        @elseif($message['status'] == 'delivered')
                                                            <i class="bi bi-check-all"></i>
                                                        @elseif($message['status'] == 'read')
                                                            <i class="bi bi-check-all text-info"></i>
                                                        @endif
                                                    </small>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="mt-1 {{ $isAgent ? 'text-end' : '' }}">
                                            <small class="text-muted">
                                                {{ isset($message['created_at']) ? \Carbon\Carbon::parse($message['created_at'])->format('d M Y H:i') : '' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center">
                                <i class="bi bi-chat-left-text display-1 text-muted"></i>
                                <h5 class="mt-3 text-muted">No messages yet</h5>
                                <p class="text-muted">Start a conversation</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-white border-top p-3">
                    <form action="{{ route('rooms.send-whatsapp', $id) }}" method="POST" enctype="multipart/form-data" id="messageForm">
                        @csrf

                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('fileInput').click()">
                                <i class="bi bi-paperclip"></i>
                            </button>

                            <select class="form-select" name="type" id="messageType" style="max-width: 120px;" onchange="handleTypeChange(this)">
                                <option value="text">Text</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                                <option value="audio">Audio</option>
                                <option value="document">Document</option>
                                <option value="voice">Voice</option>
                            </select>

                            <input type="text" class="form-control" name="text" id="messageText" placeholder="Type your message..." autocomplete="off" required>

                            <input type="file" class="d-none" name="file" id="fileInput" accept="*/*" onchange="handleFileSelect(this)">

                            <button class="btn btn-success" type="submit">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>

                        <div id="filePreview" class="mt-2" style="display: none;">
                            <div class="alert alert-info d-flex align-items-center justify-content-between mb-0">
                                <div>
                                    <i class="bi bi-file-earmark"></i>
                                    <span id="fileName"></span>
                                </div>
                                <button type="button" class="btn-close" onclick="removeFile()"></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="botMessageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-robot me-2"></i>Send Bot Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('rooms.send-whatsapp-bot', $id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Message Type</label>
                            <select class="form-select" name="type" id="botMessageType" required onchange="handleBotTypeChange(this)">
                                <option value="text">Text</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                                <option value="audio">Audio</option>
                                <option value="document">Document</option>
                                <option value="voice">Voice</option>
                            </select>
                        </div>

                        <div class="mb-3" id="botTextInput">
                            <label class="form-label">Message Text</label>
                            <textarea class="form-control" name="text" rows="4" placeholder="Enter your message..." required></textarea>
                        </div>

                        <div class="mb-3 d-none" id="botFileInput">
                            <label class="form-label">Upload File</label>
                            <input type="file" class="form-control" name="file">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Created At (Optional)</label>
                            <input type="datetime-local" class="form-control" name="created_at">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Send Bot Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="interactiveMessageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-chat-square-dots me-2"></i>Send Interactive Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('rooms.send-interactive', $id) }}" method="POST">
                    @csrf

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Interactive Type</label>
                            <select class="form-select" name="type" required>
                                <option value="button">Button</option>
                                <option value="list">List</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Body Text</label>
                            <textarea class="form-control" name="interactive[body]" rows="3" required placeholder="Enter the main message body..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Header (Optional)</label>
                            <input type="text" class="form-control" name="interactive[header]" placeholder="Header text...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Footer (Optional)</label>
                            <input type="text" class="form-control" name="interactive[footer]" placeholder="Footer text...">
                        </div>

                        <div class="alert alert-info">
                            <small><i class="bi bi-info-circle me-2"></i>
                                For buttons/lists configuration, please refer to WhatsApp Business API documentation.
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Send Interactive
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="hsmMessageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-envelope me-2"></i>Send HSM Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('rooms.send-hsm', $id) }}" method="POST">
                    @csrf

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Message Template ID</label>
                            <input type="text" class="form-control" name="message_template_id" required placeholder="Enter template ID...">
                            <small class="text-muted">Enter the approved WhatsApp message template ID</small>
                        </div>

                        <div class="alert alert-warning">
                            <small><i class="bi bi-exclamation-triangle me-2"></i>
                                Make sure the template is approved in your WhatsApp Business Account.
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-send me-2"></i>Send HSM
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .message-bubble {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #messageContainer::-webkit-scrollbar {
            width: 6px;
        }

        #messageContainer::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #messageContainer::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        #messageContainer::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endpush

@push('scripts')
    <script>
        window.addEventListener('load', function() {
            const container = document.getElementById('messageContainer');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });

        function handleTypeChange(select) {
            const messageText = document.getElementById('messageText');
            const fileInput = document.getElementById('fileInput');

            if (select.value === 'text') {
                messageText.required = true;
                messageText.placeholder = 'Type your message...';
                fileInput.required = false;
            } else {
                messageText.required = false;
                messageText.placeholder = 'Caption (optional)...';
                fileInput.required = true;
            }
        }

        function handleFileSelect(input) {
            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                const fileName = document.getElementById('fileName');
                const filePreview = document.getElementById('filePreview');
                const messageType = document.getElementById('messageType');

                if (fileName) {
                    fileName.textContent = file.name;
                }

                if (filePreview) {
                    filePreview.style.display = 'block';
                }

                if (messageType && messageType.value === 'text') {
                    const ext = file.name.split('.').pop().toLowerCase();

                    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(ext)) {
                        messageType.value = 'image';
                    } else if (['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'].includes(ext)) {
                        messageType.value = 'video';
                    } else if (['mp3', 'wav', 'ogg', 'm4a', 'aac'].includes(ext)) {
                        messageType.value = 'audio';
                    } else {
                        messageType.value = 'document';
                    }

                    handleTypeChange(messageType);
                }
            }
        }

        function removeFile() {
            const fileInput = document.getElementById('fileInput');
            const filePreview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            const messageType = document.getElementById('messageType');

            if (fileInput) {
                fileInput.value = '';
            }

            if (filePreview) {
                filePreview.style.display = 'none';
            }

            if (fileName) {
                fileName.textContent = '';
            }

            if (messageType) {
                messageType.value = 'text';
                handleTypeChange(messageType);
            }
        }

        function handleBotTypeChange(select) {
            const botTextInput = document.getElementById('botTextInput');
            const botFileInput = document.getElementById('botFileInput');

            if (select.value === 'text') {
                botTextInput.classList.remove('d-none');
                botFileInput.classList.add('d-none');
                botTextInput.querySelector('textarea').required = true;
                botFileInput.querySelector('input').required = false;
            } else {
                botTextInput.classList.add('d-none');
                botFileInput.classList.remove('d-none');
                botTextInput.querySelector('textarea').required = false;
                botFileInput.querySelector('input').required = true;
            }
        }
    </script>
@endpush