@extends('layouts.app')

@section('page-title')
    {{ __('Loan Application Wizard - MBC Finance') }}
@endsection

@push('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/loan-wizard.css') }}">
@endpush

@section('content')
<div class="loan-wizard-page">
    <!-- Progress Header -->
    <div class="wizard-header">
        <div class="container-fluid">
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-steps">
                    <div class="step active" data-step="1">
                        <div class="step-circle">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="step-label">Configure Loan</div>
                    </div>
                    <div class="step" data-step="2">
                        <div class="step-circle">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="step-label">Personal Details</div>
                    </div>
                    <div class="step" data-step="3">
                        <div class="step-circle">
                            <i class="fas fa-file-upload"></i>
                        </div>
                        <div class="step-label">Upload Documents</div>
                    </div>
                    <div class="step" data-step="4">
                        <div class="step-circle">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="step-label">Review & Submit</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Wizard Content -->
    <div class="wizard-content">
        <div class="container-fluid">
            <!-- Step 1: Loan Configuration -->
            <div class="wizard-step active" id="step1">
                <div class="step-header">
                    <h2>Configure Your {{ $loanType->type }}</h2>
                    <p>Customize your loan amount, tenure, and see real-time EMI calculations</p>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="configuration-panel">
                            <!-- Loan Type Info -->
                            <div class="loan-type-info">
                                <div class="loan-type-icon">
                                    @switch($loanType->type)
                                        @case('Personal Loan')
                                            <i class="fas fa-user"></i>
                                            @break
                                        @case('Business Loan')
                                            <i class="fas fa-briefcase"></i>
                                            @break
                                        @case('Home Loan')
                                            <i class="fas fa-home"></i>
                                            @break
                                        @case('Consumer Loan')
                                            <i class="fas fa-shopping-bag"></i>
                                            @break
                                        @case('Vehicle Loan')
                                            <i class="fas fa-car"></i>
                                            @break
                                        @default
                                            <i class="fas fa-money-bill-wave"></i>
                                    @endswitch
                                </div>
                                <div class="loan-type-details">
                                    <h3>{{ $loanType->type }}</h3>
                                    <p>{{ $loanType->notes ?: 'Flexible financing solution tailored to your needs' }}</p>
                                </div>
                            </div>

                            <!-- Loan Configuration Form -->
                            <div class="configuration-form">
                                <div class="form-section">
                                    <label class="form-label">
                                        <span class="label-text">Loan Amount</span>
                                        <span class="label-value">₹<span id="amountDisplay">{{ number_format(($loanType->min_loan_amount + $loanType->max_loan_amount) / 2) }}</span></span>
                                    </label>
                                    <div class="slider-group">
                                        <input type="range" 
                                               id="loanAmount" 
                                               min="{{ $loanType->min_loan_amount }}" 
                                               max="{{ $loanType->max_loan_amount }}" 
                                               value="{{ ($loanType->min_loan_amount + $loanType->max_loan_amount) / 2 }}" 
                                               step="5000"
                                               class="styled-slider">
                                        <div class="slider-labels">
                                            <span>₹{{ number_format($loanType->min_loan_amount) }}</span>
                                            <span>₹{{ number_format($loanType->max_loan_amount) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <label class="form-label">
                                        <span class="label-text">Loan Tenure</span>
                                        <span class="label-value"><span id="tenureDisplay">24</span> months</span>
                                    </label>
                                    <div class="slider-group">
                                        <input type="range" 
                                               id="loanTenure" 
                                               min="6" 
                                               max="{{ $loanType->max_loan_term }}" 
                                               value="24" 
                                               step="1"
                                               class="styled-slider">
                                        <div class="slider-labels">
                                            <span>6 months</span>
                                            <span>{{ $loanType->max_loan_term }} {{ $loanType->loan_term_period }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <label class="form-label">
                                        <span class="label-text">Interest Rate</span>
                                        <span class="label-value">{{ $loanType->interest_rate }}% p.a.</span>
                                        <span class="info-icon" data-bs-toggle="tooltip" title="{{ ucfirst(str_replace('_', ' ', $loanType->interest_type)) }} interest rate">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                    </label>
                                    <div class="interest-info">
                                        <div class="interest-type">
                                            {{ \App\Models\LoanType::$interestType[$loanType->interest_type] ?? ucfirst(str_replace('_', ' ', $loanType->interest_type)) }}
                                        </div>
                                    </div>
                                </div>

                                @if($loanType->penalties > 0)
                                <div class="form-section">
                                    <label class="form-label">
                                        <span class="label-text">Late Payment Penalty</span>
                                        @if($loanType->penalty_type === 'percentage')
                                            <span class="label-value">{{ $loanType->penalties }}%</span>
                                        @else
                                            <span class="label-value">₹{{ number_format($loanType->penalties) }}</span>
                                        @endif
                                        <span class="info-icon" data-bs-toggle="tooltip" title="Applied on overdue EMI payments">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                    </label>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- EMI Calculator Results -->
                        <div class="calculator-results">
                            <div class="results-card">
                                <h3>Your EMI Breakdown</h3>
                                
                                <div class="emi-display">
                                    <div class="emi-amount">
                                        <span class="currency">₹</span>
                                        <span class="amount" id="emiAmount">0</span>
                                        <span class="frequency">/month</span>
                                    </div>
                                </div>

                                <div class="breakdown-chart">
                                    <canvas id="emiChart" width="200" height="200"></canvas>
                                    <div class="chart-center">
                                        <div class="center-amount">₹<span id="chartTotalAmount">0</span></div>
                                        <div class="center-label">Total Amount</div>
                                    </div>
                                </div>

                                <div class="breakdown-details">
                                    <div class="detail-row">
                                        <span class="detail-label">
                                            <span class="color-indicator principal"></span>
                                            Principal Amount
                                        </span>
                                        <span class="detail-value">₹<span id="principalAmount">0</span></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">
                                            <span class="color-indicator interest"></span>
                                            Total Interest
                                        </span>
                                        <span class="detail-value">₹<span id="totalInterest">0</span></span>
                                    </div>
                                    <div class="detail-row total">
                                        <span class="detail-label">Total Repayment</span>
                                        <span class="detail-value">₹<span id="totalRepayment">0</span></span>
                                    </div>
                                </div>

                                <div class="features-list">
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>No prepayment charges</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Flexible EMI options</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Quick disbursal</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button class="btn-next" onclick="nextStep()">
                        Continue with this Configuration
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 2: Personal Details -->
            <div class="wizard-step" id="step2">
                <div class="step-header">
                    <h2>Tell Us About Yourself</h2>
                    <p>Provide your personal and employment details for loan processing</p>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="details-form">
                            <form id="personalDetailsForm">
                                <div class="form-section">
                                    <h4>Personal Information</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Full Name <span class="required">*</span></label>
                                                <input type="text" name="full_name" value="{{ Auth::user()->name ?? '' }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Email Address <span class="required">*</span></label>
                                                <input type="email" name="email" value="{{ Auth::user()->email ?? '' }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone Number <span class="required">*</span></label>
                                                <input type="tel" name="phone" value="{{ Auth::user()->phone ?? '' }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Date of Birth <span class="required">*</span></label>
                                                <input type="date" name="date_of_birth" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>PAN Number <span class="required">*</span></label>
                                                <input type="text" name="pan_number" placeholder="ABCDE1234F" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Aadhaar Number <span class="required">*</span></label>
                                                <input type="text" name="aadhaar_number" placeholder="1234 5678 9012" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h4>Employment Details</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Employment Type <span class="required">*</span></label>
                                                <select name="employment_type" required>
                                                    <option value="">Select Employment Type</option>
                                                    <option value="salaried">Salaried</option>
                                                    <option value="self_employed">Self Employed</option>
                                                    <option value="business">Business Owner</option>
                                                    <option value="freelancer">Freelancer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Monthly Income <span class="required">*</span></label>
                                                <input type="number" name="monthly_income" placeholder="50000" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Company/Organization <span class="required">*</span></label>
                                                <input type="text" name="company_name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Work Experience (Years) <span class="required">*</span></label>
                                                <input type="number" name="work_experience" min="0" max="50" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h4>Address Information</h4>
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label>Complete Address <span class="required">*</span></label>
                                                <textarea name="address" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>City <span class="required">*</span></label>
                                                <input type="text" name="city" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>PIN Code <span class="required">*</span></label>
                                                <input type="text" name="pincode" pattern="[0-9]{6}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-section">
                                    <h4>Loan Purpose</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Purpose of Loan <span class="required">*</span></label>
                                                <select name="loan_purpose" required>
                                                    <option value="">Select Purpose</option>
                                                    <option value="education">Education</option>
                                                    <option value="medical">Medical Expenses</option>
                                                    <option value="debt_consolidation">Debt Consolidation</option>
                                                    <option value="home_renovation">Home Renovation</option>
                                                    <option value="wedding">Wedding</option>
                                                    <option value="travel">Travel</option>
                                                    <option value="business">Business</option>
                                                    <option value="vehicle">Vehicle Purchase</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Preferred Branch</label>
                                                <select name="branch_id">
                                                    <option value="">Select Branch</option>
                                                    @foreach($branches ?? [] as $branch)
                                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button class="btn-prev" onclick="prevStep()">
                        <i class="fas fa-arrow-left"></i>
                        Back
                    </button>
                    <button class="btn-next" onclick="nextStep()">
                        Continue to Documents
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 3: Document Upload -->
            <div class="wizard-step" id="step3">
                <div class="step-header">
                    <h2>Upload Required Documents</h2>
                    <p>Please upload clear, legible copies of the required documents</p>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="documents-section">
                            <div class="required-docs">
                                <h4>Required Documents</h4>
                                <div class="doc-grid">
                                    <div class="doc-upload-card">
                                        <div class="doc-icon">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                        <h5>Aadhaar Card (Front)</h5>
                                        <p>Clear photo of front side</p>
                                        <div class="upload-area" data-doc="aadhaar_front">
                                            <input type="file" id="aadhaar_front" accept="image/*,.pdf" required>
                                            <div class="upload-content">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <span>Click to upload or drag & drop</span>
                                                <small>JPG, PNG, PDF (Max 2MB)</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="doc-upload-card">
                                        <div class="doc-icon">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                        <h5>Aadhaar Card (Back)</h5>
                                        <p>Clear photo of back side</p>
                                        <div class="upload-area" data-doc="aadhaar_back">
                                            <input type="file" id="aadhaar_back" accept="image/*,.pdf" required>
                                            <div class="upload-content">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <span>Click to upload or drag & drop</span>
                                                <small>JPG, PNG, PDF (Max 2MB)</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="doc-upload-card">
                                        <div class="doc-icon">
                                            <i class="fas fa-credit-card"></i>
                                        </div>
                                        <h5>PAN Card</h5>
                                        <p>Clear photo of PAN card</p>
                                        <div class="upload-area" data-doc="pan_card">
                                            <input type="file" id="pan_card" accept="image/*,.pdf" required>
                                            <div class="upload-content">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <span>Click to upload or drag & drop</span>
                                                <small>JPG, PNG, PDF (Max 2MB)</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="doc-upload-card">
                                        <div class="doc-icon">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </div>
                                        <h5>Income Proof</h5>
                                        <p>Salary slip or ITR</p>
                                        <div class="upload-area" data-doc="income_proof">
                                            <input type="file" id="income_proof" accept="image/*,.pdf">
                                            <div class="upload-content">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <span>Click to upload or drag & drop</span>
                                                <small>JPG, PNG, PDF (Max 2MB)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="upload-notes">
                                <div class="notes-card">
                                    <h5><i class="fas fa-info-circle"></i> Upload Guidelines</h5>
                                    <ul>
                                        <li>Ensure all documents are clear and legible</li>
                                        <li>File size should not exceed 2MB per document</li>
                                        <li>Accepted formats: JPG, PNG, PDF</li>
                                        <li>Make sure all text and details are visible</li>
                                        <li>Documents should be recent and valid</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button class="btn-prev" onclick="prevStep()">
                        <i class="fas fa-arrow-left"></i>
                        Back
                    </button>
                    <button class="btn-next" onclick="nextStep()">
                        Review Application
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 4: Review & Submit -->
            <div class="wizard-step" id="step4">
                <div class="step-header">
                    <h2>Review Your Application</h2>
                    <p>Please review all details before submitting your loan application</p>
                </div>
                
                <div class="row">
                    <div class="col-lg-8">
                        <div class="review-sections">
                            <!-- Loan Configuration Review -->
                            <div class="review-card">
                                <h4><i class="fas fa-calculator"></i> Loan Configuration</h4>
                                <div class="review-content">
                                    <div class="review-item">
                                        <span class="label">Loan Type:</span>
                                        <span class="value">{{ $loanType->type }}</span>
                                    </div>
                                    <div class="review-item">
                                        <span class="label">Loan Amount:</span>
                                        <span class="value">₹<span id="reviewAmount">0</span></span>
                                    </div>
                                    <div class="review-item">
                                        <span class="label">Tenure:</span>
                                        <span class="value"><span id="reviewTenure">0</span> months</span>
                                    </div>
                                    <div class="review-item">
                                        <span class="label">Interest Rate:</span>
                                        <span class="value">{{ $loanType->interest_rate }}% p.a.</span>
                                    </div>
                                    <div class="review-item">
                                        <span class="label">Monthly EMI:</span>
                                        <span class="value highlight">₹<span id="reviewEMI">0</span></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Details Review -->
                            <div class="review-card">
                                <h4><i class="fas fa-user"></i> Personal Information</h4>
                                <div class="review-content" id="personalReview">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>

                            <!-- Documents Review -->
                            <div class="review-card">
                                <h4><i class="fas fa-file-upload"></i> Uploaded Documents</h4>
                                <div class="review-content" id="documentsReview">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="submission-summary">
                            <div class="summary-card">
                                <h4>Application Summary</h4>
                                <div class="summary-item">
                                    <span class="label">Loan Amount</span>
                                    <span class="value">₹<span id="summaryAmount">0</span></span>
                                </div>
                                <div class="summary-item">
                                    <span class="label">Monthly EMI</span>
                                    <span class="value">₹<span id="summaryEMI">0</span></span>
                                </div>
                                <div class="summary-item">
                                    <span class="label">Total Interest</span>
                                    <span class="value">₹<span id="summaryInterest">0</span></span>
                                </div>
                                <div class="summary-item total">
                                    <span class="label">Total Repayment</span>
                                    <span class="value">₹<span id="summaryTotal">0</span></span>
                                </div>

                                <div class="terms-section">
                                    <div class="terms-checkbox">
                                        <label>
                                            <input type="checkbox" id="termsAccepted" required>
                                            <span class="checkmark"></span>
                                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a>
                                        </label>
                                    </div>
                                </div>

                                <button class="btn-submit" id="submitApplication" disabled>
                                    <i class="fas fa-paper-plane"></i>
                                    Submit Application
                                </button>

                                <div class="submission-note">
                                    <p><i class="fas fa-info-circle"></i> Your application will be reviewed within 24 hours. You'll receive updates via SMS and email.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button class="btn-prev" onclick="prevStep()">
                        <i class="fas fa-arrow-left"></i>
                        Back to Documents
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Loan Agreement</h6>
                <p>By submitting this application, you agree to enter into a loan agreement with MBC Finance subject to approval and verification of documents.</p>
                
                <h6>2. Interest and Charges</h6>
                <p>The interest rate quoted is indicative and may vary based on your credit profile and loan assessment. All applicable charges will be clearly disclosed before loan disbursal.</p>
                
                <h6>3. Repayment Terms</h6>
                <p>EMI payments must be made on the due dates as per the repayment schedule. Late payment charges will apply for overdue amounts.</p>
                
                <h6>4. Documentation</h6>
                <p>All documents submitted must be genuine and valid. False information may lead to application rejection and legal action.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Wizard state management
let currentStep = 1;
let loanData = {
    loanTypeId: {{ $loanType->id }},
    amount: {{ ($loanType->min_loan_amount + $loanType->max_loan_amount) / 2 }},
    tenure: 24,
    interestRate: {{ $loanType->interest_rate }},
    emi: 0,
    totalInterest: 0,
    totalAmount: 0,
    personalDetails: {},
    documents: {}
};

document.addEventListener('DOMContentLoaded', function() {
    initializeWizard();
    initializeSliders();
    initializeFileUploads();
    calculateEMI();
});

function initializeWizard() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Terms checkbox handler
    document.getElementById('termsAccepted').addEventListener('change', function() {
        document.getElementById('submitApplication').disabled = !this.checked;
    });

    // Submit button handler
    document.getElementById('submitApplication').addEventListener('click', submitApplication);
}

function initializeSliders() {
    const amountSlider = document.getElementById('loanAmount');
    const tenureSlider = document.getElementById('loanTenure');
    const amountDisplay = document.getElementById('amountDisplay');
    const tenureDisplay = document.getElementById('tenureDisplay');

    amountSlider.addEventListener('input', function() {
        loanData.amount = parseInt(this.value);
        amountDisplay.textContent = formatNumber(loanData.amount);
        calculateEMI();
    });

    tenureSlider.addEventListener('input', function() {
        loanData.tenure = parseInt(this.value);
        tenureDisplay.textContent = loanData.tenure;
        calculateEMI();
    });
}

function calculateEMI() {
    const principal = loanData.amount;
    const tenure = loanData.tenure;
    const annualRate = loanData.interestRate;
    
    const monthlyRate = annualRate / 100 / 12;
    const emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, tenure)) / 
                (Math.pow(1 + monthlyRate, tenure) - 1);
    
    loanData.emi = Math.round(emi);
    loanData.totalAmount = Math.round(emi * tenure);
    loanData.totalInterest = loanData.totalAmount - principal;

    updateEMIDisplay();
    updateChart();
}

function updateEMIDisplay() {
    document.getElementById('emiAmount').textContent = formatNumber(loanData.emi);
    document.getElementById('chartTotalAmount').textContent = formatNumber(loanData.totalAmount);
    document.getElementById('principalAmount').textContent = formatNumber(loanData.amount);
    document.getElementById('totalInterest').textContent = formatNumber(loanData.totalInterest);
    document.getElementById('totalRepayment').textContent = formatNumber(loanData.totalAmount);
}

function updateChart() {
    const ctx = document.getElementById('emiChart').getContext('2d');
    
    if (window.emiChart) {
        window.emiChart.destroy();
    }
    
    window.emiChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Principal', 'Interest'],
            datasets: [{
                data: [loanData.amount, loanData.totalInterest],
                backgroundColor: ['#1e40af', '#ef4444'],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function nextStep() {
    if (validateCurrentStep()) {
        if (currentStep === 2) {
            collectPersonalDetails();
        } else if (currentStep === 3) {
            collectDocuments();
        }
        
        if (currentStep < 4) {
            currentStep++;
            showStep(currentStep);
            updateProgress();
            
            if (currentStep === 4) {
                populateReviewSection();
            }
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
        updateProgress();
    }
}

function showStep(step) {
    document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
    document.getElementById(`step${step}`).classList.add('active');
    
    document.querySelectorAll('.step').forEach(el => el.classList.remove('active', 'completed'));
    
    for (let i = 1; i < step; i++) {
        document.querySelector(`[data-step="${i}"]`).classList.add('completed');
    }
    document.querySelector(`[data-step="${step}"]`).classList.add('active');
}

function updateProgress() {
    const progress = ((currentStep - 1) / 3) * 100;
    document.getElementById('progressFill').style.width = progress + '%';
}

function validateCurrentStep() {
    if (currentStep === 2) {
        const form = document.getElementById('personalDetailsForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }
    } else if (currentStep === 3) {
        const requiredDocs = ['aadhaar_front', 'aadhaar_back', 'pan_card'];
        for (let doc of requiredDocs) {
            if (!document.getElementById(doc).files.length) {
                alert(`Please upload ${doc.replace('_', ' ').toUpperCase()}`);
                return false;
            }
        }
    }
    return true;
}

function collectPersonalDetails() {
    const form = document.getElementById('personalDetailsForm');
    const formData = new FormData(form);
    loanData.personalDetails = Object.fromEntries(formData);
}

function collectDocuments() {
    const docs = ['aadhaar_front', 'aadhaar_back', 'pan_card', 'income_proof'];
    loanData.documents = {};
    
    docs.forEach(doc => {
        const file = document.getElementById(doc).files[0];
        if (file) {
            loanData.documents[doc] = file;
        }
    });
}

function populateReviewSection() {
    // Update loan configuration in review
    document.getElementById('reviewAmount').textContent = formatNumber(loanData.amount);
    document.getElementById('reviewTenure').textContent = loanData.tenure;
    document.getElementById('reviewEMI').textContent = formatNumber(loanData.emi);
    
    // Update summary
    document.getElementById('summaryAmount').textContent = formatNumber(loanData.amount);
    document.getElementById('summaryEMI').textContent = formatNumber(loanData.emi);
    document.getElementById('summaryInterest').textContent = formatNumber(loanData.totalInterest);
    document.getElementById('summaryTotal').textContent = formatNumber(loanData.totalAmount);
    
    // Personal details review
    const personalReview = document.getElementById('personalReview');
    personalReview.innerHTML = Object.keys(loanData.personalDetails).map(key => {
        const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        return `<div class="review-item"><span class="label">${label}:</span><span class="value">${loanData.personalDetails[key]}</span></div>`;
    }).join('');
    
    // Documents review
    const docsReview = document.getElementById('documentsReview');
    docsReview.innerHTML = Object.keys(loanData.documents).map(key => {
        const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        return `<div class="review-item"><span class="label">${label}:</span><span class="value">✓ Uploaded</span></div>`;
    }).join('');
}

function submitApplication() {
    // Create FormData for submission
    const formData = new FormData();
    
    // Add loan configuration
    formData.append('loan_type', loanData.loanTypeId);
    formData.append('amount', loanData.amount);
    formData.append('loan_terms', loanData.tenure);
    formData.append('loan_term_period', 'months');
    
    // Add personal details
    Object.keys(loanData.personalDetails).forEach(key => {
        formData.append(key, loanData.personalDetails[key]);
    });
    
    // Add documents
    Object.keys(loanData.documents).forEach(key => {
        if (loanData.documents[key]) {
            formData.append(key, loanData.documents[key]);
        }
    });
    
    // Add CSRF token
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    // Submit the application
    document.getElementById('submitApplication').disabled = true;
    document.getElementById('submitApplication').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    
    fetch('{{ route("loan.submit-application") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Store success message in sessionStorage for cross-page persistence
            sessionStorage.setItem('loanApplicationSuccess', 'true');
            window.location.href = '{{ route("loan.index") }}';
        } else {
            alert('Error submitting application: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting your application. Please try again.');
    })
    .finally(() => {
        document.getElementById('submitApplication').disabled = false;
        document.getElementById('submitApplication').innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application';
    });
}

function initializeFileUploads() {
    const uploadAreas = document.querySelectorAll('.upload-area');
    
    uploadAreas.forEach(area => {
        const input = area.querySelector('input[type="file"]');
        const content = area.querySelector('.upload-content');
        
        // Click to upload
        area.addEventListener('click', () => input.click());
        
        // Drag and drop
        area.addEventListener('dragover', (e) => {
            e.preventDefault();
            area.classList.add('dragover');
        });
        
        area.addEventListener('dragleave', () => {
            area.classList.remove('dragover');
        });
        
        area.addEventListener('drop', (e) => {
            e.preventDefault();
            area.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                input.files = files;
                handleFileUpload(area, files[0]);
            }
        });
        
        // File input change
        input.addEventListener('change', function() {
            if (this.files.length > 0) {
                handleFileUpload(area, this.files[0]);
            }
        });
    });
}

function handleFileUpload(area, file) {
    const content = area.querySelector('.upload-content');
    const maxSize = 2 * 1024 * 1024; // 2MB
    
    if (file.size > maxSize) {
        alert('File size must be less than 2MB');
        return;
    }
    
    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) {
        alert('Only JPG, PNG, and PDF files are allowed');
        return;
    }
    
    // Show uploaded file info
    content.innerHTML = `
        <i class="fas fa-check-circle" style="color: #059669;"></i>
        <span style="color: #059669;">${file.name}</span>
        <small>File uploaded successfully</small>
    `;
    area.classList.add('uploaded');
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>
@endpush