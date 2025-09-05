@extends('layouts.main')

@section('title', 'Bulk Email Invitation')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Settings', 'url' => route('settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Bulk Email', 'url' => '#', 'icon' => 'bx bx-envelope']
        ]" />
        
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="bx bx-envelope me-2"></i>
                            SmartFinance System Invitation
                        </h4>
                        <p class="card-text text-muted">Send invitations to use SmartFinance system</p>
                    </div>
                    <div class="card-body">
                        <form id="bulkEmailForm">
                            @csrf
                            
                            <!-- Email Content -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label for="subject" class="form-label fw-bold">Email Subject</label>
                                    <input type="text" class="form-control form-control-lg" id="subject" name="subject" 
                                           value="Karibu SmartFinance - Mfumo wa Usimamizi wa Fedha" required>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <label for="content" class="form-label fw-bold">Email Message</label>
                                    <textarea class="form-control" id="content" name="content" rows="12" required 
                                              placeholder="Enter your invitation message...">Mpendwa Mshirika,

Tunafurahi kukualika kutumia SmartFinance, mfumo wetu kamili wa usimamizi wa fedha ulioundwa kurahisisha shughuli zako.

SmartFinance inatoa:
• Usimamizi kamili wa mikopo
• Usimamizi wa uhusiano na wateja
• Ripoti za kifedha na uchambuzi
• Shughuli za matawi mengi
• Interface salama na rahisi kutumia
.
php artisan emails:send-invitations --no-interaction

Ikiwa unahitaji kuona mfano, tafadhali tembelea: https://dev.smartsoft.co.tz

Maelezo ya kuingia:
Jina la mtumiaji: 2556555778030
Nywila: 12345

Maelezo yako ya kuingia yatakupokelewa kando.

Kwa maswali au msaada, wasiliana nasi: +255 747 762 244

Kwa heshima,
Timu ya SmartFinance</textarea>
                                    <div class="form-text">This message will be sent to all recipients</div>
                                </div>
                            </div>

                            <!-- Recipients Info -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Recipients:</strong> Emails will be automatically selected from the <code>microfinances</code> database table.
                                        <br><strong>Available recipients:</strong> {{ $recipientCount ?? 0 }} contacts
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg" id="sendBtn">
                                        <i class="bx bx-send me-1"></i> Send Invitations
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="row mt-4" id="resultsSection" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-bar-chart me-2"></i>
                            Results
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="resultsContent"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mb-2">Sending Invitations...</h5>
                <p class="text-muted mb-0">Please wait while we process your emails</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Send emails
    document.getElementById('bulkEmailForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            subject: formData.get('subject'),
            content: formData.get('content'),
            company_name: 'SmartFinance',
            use_queue: false
        };

        // Show loading modal
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();

        // Disable form
        document.getElementById('sendBtn').disabled = true;

        fetch('/settings/bulk-email/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON. Server might be redirecting or returning HTML.');
            }
            return response.json();
        })
        .then(data => {
            loadingModal.hide();
            
            if (data.success) {
                showAlert('🎉 Invitations sent successfully!', 'success');
                showResults(data.results);
            } else {
                showAlert(data.message || 'Failed to send invitations.', 'danger');
                if (data.errors) {
                    console.error('Validation errors:', data.errors);
                }
            }
        })
        .catch(error => {
            loadingModal.hide();
            showAlert('❌ An error occurred while sending invitations: ' + error.message, 'danger');
            console.error('Error:', error);
        })
        .finally(() => {
            document.getElementById('sendBtn').disabled = false;
        });
    });

    function showAlert(message, type) {
        // Create a simple alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    function showResults(results) {
        const resultsSection = document.getElementById('resultsSection');
        const resultsContent = document.getElementById('resultsContent');
        
        let html = '<div class="row g-3">';
        html += '<div class="col-md-3"><div class="p-3 bg-light rounded"><strong>Total:</strong> ' + results.total + '</div></div>';
        
        if (results.successful !== undefined) {
            html += '<div class="col-md-3"><div class="p-3 bg-success bg-opacity-10 rounded text-success"><strong>Successful:</strong> ' + results.successful + '</div></div>';
            html += '<div class="col-md-3"><div class="p-3 bg-danger bg-opacity-10 rounded text-danger"><strong>Failed:</strong> ' + results.failed + '</div></div>';
        } else if (results.queued !== undefined) {
            html += '<div class="col-md-3"><div class="p-3 bg-info bg-opacity-10 rounded text-info"><strong>Queued:</strong> ' + results.queued + '</div></div>';
            html += '<div class="col-md-3"><div class="p-3 bg-danger bg-opacity-10 rounded text-danger"><strong>Failed:</strong> ' + results.failed + '</div></div>';
        }
        
        html += '</div>';
        
        if (results.errors && results.errors.length > 0) {
            html += '<div class="mt-4"><h6 class="text-danger">Errors:</h6><ul class="list-unstyled">';
            results.errors.forEach(error => {
                html += '<li class="text-danger mb-1"><i class="bx bx-x-circle me-1"></i>' + error + '</li>';
            });
            html += '</ul></div>';
        }
        
        resultsContent.innerHTML = html;
        resultsSection.style.display = 'block';
        
        // Scroll to results
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }
});
</script>
@endpush

@push('styles')
<style>
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-lg {
    padding: 12px 24px;
    font-size: 16px;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.alert-info code {
    background-color: #b8daff;
    color: #004085;
    padding: 2px 4px;
    border-radius: 3px;
}
</style>
@endpush