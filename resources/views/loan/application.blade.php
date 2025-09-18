@extends('layouts.app')

@section('page-title')
    {{ __('Apply for Personal Loan - MBC Finance') }}
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/loan-application.css') }}">
@endpush

@section('content')
    <div class="loan-application-page">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 hero-content">
                        <h1 class="mb-3">Get Instant Personal Loan</h1>
                        <p class="lead mb-4">Access funds quickly with competitive rates and flexible repayment options tailored to your needs.</p>
                        <div class="hero-features">
                            <div class="feature-item">
                                <i class="fas fa-bolt"></i>
                                <span>Quick Approval</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Minimal Documentation</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-lock"></i>
                                <span>100% Secure</span>
                            </div>
                        </div>
                        <a href="#calculator" class="btn btn-primary btn-lg mt-4 hero-cta">Calculate Your Loan</a>
                    </div>
                    <div class="col-lg-6 hero-image">
                        <img src="{{ asset('img/loan-hero-image.svg') }}" alt="Personal Loan" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>

        <!-- Loan Calculator Section -->
        <div id="calculator" class="calculator-section">
            <div class="container">
                <div class="section-title text-center">
                    <h2>Calculate Your Loan</h2>
                    <p>Adjust the parameters to see your monthly payments and total repayment amount</p>
                </div>
                <div class="row">
                    <div class="col-lg-7">
                        <div class="calculator-card">
                            <h3>Loan Parameters</h3>
                            <div class="calculator-form">
                                <!-- Loan Type Selection (if multiple loan types available) -->
                                @if(isset($loanTypes) && count($loanTypes) > 1)
                                <div class="form-group">
                                    <label for="loan-type">Loan Type</label>
                                    <select class="form-select" id="loan-type">
                                        @foreach($loanTypes as $type)
                                            <option value="{{ $type->id }}" 
                                                data-min="{{ $type->min_loan_amount }}" 
                                                data-max="{{ $type->max_loan_amount }}"
                                                data-interest="{{ $type->interest_rate }}"
                                                data-term="{{ $type->max_loan_term }}"
                                                data-term-period="{{ $type->loan_term_period }}"
                                                data-interest-type="{{ $type->interest_type }}">
                                                {{ $type->type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                
                                <!-- Loan Amount -->
                                <div class="form-group">
                                    <label for="loan-amount">Loan Amount (₹)</label>
                                    <div class="range-input-group">
                                        <input type="range" class="form-range" id="loan-amount-slider" 
                                            min="{{ $defaults['min_loan_amount'] ?? 10000 }}" 
                                            max="{{ $defaults['max_loan_amount'] ?? 30000 }}" 
                                            step="1000" 
                                            value="{{ $defaults['default_loan_amount'] ?? 20000 }}">
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="loan-amount-input" 
                                                min="{{ $defaults['min_loan_amount'] ?? 10000 }}" 
                                                max="{{ $defaults['max_loan_amount'] ?? 30000 }}" 
                                                value="{{ $defaults['default_loan_amount'] ?? 20000 }}">
                                        </div>
                                    </div>
                                    <div class="range-labels">
                                        <span>₹{{ number_format($defaults['min_loan_amount'] ?? 10000) }}</span>
                                        <span>₹{{ number_format($defaults['max_loan_amount'] ?? 30000) }}</span>
                                    </div>
                                </div>
                                
                                <!-- Loan Term -->
                                <div class="form-group">
                                    <label for="loan-term">Loan Term (months)</label>
                                    <div class="range-input-group">
                                        <input type="range" class="form-range" id="loan-term-slider" min="3" max="{{ $defaults['max_loan_term'] ?? 60 }}" step="1" value="12">
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="loan-term-input" min="3" max="{{ $defaults['max_loan_term'] ?? 60 }}" value="12">
                                            <span class="input-group-text">months</span>
                                        </div>
                                    </div>
                                    <div class="range-labels">
                                        <span>3 months</span>
                                        <span>{{ $defaults['max_loan_term'] ?? 60 }} months</span>
                                    </div>
                                </div>
                                
                                <!-- Interest Rate -->
                                <div class="form-group">
                                    <label for="interest-rate">Interest Rate</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="interest-rate" value="{{ $defaults['interest_rate'] ?? 18 }}" readonly>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="info-text">
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $defaults['interest_type'] ?? 'fixed rate')) }}</span>
                                        <i class="fas fa-info-circle" data-bs-toggle="tooltip" title="Interest rate of {{ $defaults['interest_rate'] ?? 18 }}% per annum"></i>
                                    </div>
                                </div>
                                
                                <!-- Calculate Button -->
                                <div class="d-grid gap-2 mt-4">
                                    <button id="calculate-btn" class="btn btn-primary btn-lg">Calculate EMI</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-5">
                        <div class="result-card">
                            <h3>Loan Summary</h3>
                            <div class="result-item">
                                <span>Monthly EMI:</span>
                                <span id="monthly-emi" class="result-value">₹0.00</span>
                            </div>
                            <div class="result-item">
                                <span>Total Interest Payable:</span>
                                <span id="total-interest" class="result-value">₹0.00</span>
                            </div>
                            <div class="result-item">
                                <span>Total Amount:</span>
                                <span id="total-amount" class="result-value">₹0.00</span>
                            </div>
                            <div class="emi-breakdown">
                                <div class="breakdown-chart">
                                    <div class="chart-segment principal" id="principal-segment" style="width: 0%;">
                                        <span>Principal</span>
                                    </div>
                                    <div class="chart-segment interest" id="interest-segment" style="width: 0%;">
                                        <span>Interest</span>
                                    </div>
                                </div>
                                <div class="breakdown-labels">
                                    <div class="label-item">
                                        <span class="color-box principal"></span>
                                        <span id="principal-label">Principal: ₹0 (0%)</span>
                                    </div>
                                    <div class="label-item">
                                        <span class="color-box interest"></span>
                                        <span id="interest-label">Interest: ₹0 (0%)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-4">
                                <a href="#application-form" class="btn btn-primary btn-lg">Apply Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Why Choose Us -->
        <div class="features-section">
            <div class="container">
                <div class="section-title text-center">
                    <h2>Why Choose MBC Finance</h2>
                    <p>Experience the MBC Finance advantage with our hassle-free loan process</p>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h4>Instant Approval</h4>
                            <p>Get loan approval within minutes with our advanced processing system</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h4>Easy EMI</h4>
                            <p>Flexible repayment options to suit your financial needs and budget</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h4>Transparent Charges</h4>
                            <p>No hidden fees or charges with complete transparency throughout</p>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <h4>Quick Disbursal</h4>
                            <p>Funds credited to your account within 24 hours of approval</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Apply Process -->
        <div class="process-section">
            <div class="container">
                <div class="section-title text-center">
                    <h2>Simple Application Process</h2>
                    <p>Complete your loan application in 3 easy steps</p>
                </div>
                <div class="process-steps">
                    <div class="process-step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Apply Online</h4>
                            <p>Fill out our simple online application form with your details</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Document Verification</h4>
                            <p>Upload required documents for quick verification</p>
                        </div>
                    </div>
                    <div class="process-step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Get Funds</h4>
                            <p>Approved funds transferred directly to your bank account</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Form Section -->
        <div id="application-form" class="application-form-section py-5">
            <div class="container">
                <div class="section-title text-center mb-5">
                    <h2>Apply for a Loan Now</h2>
                    <p>Fill in your details to complete your loan application</p>
                </div>
                
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('loan.submit-application') }}" method="POST" enctype="multipart/form-data" id="loan-application-form">
                            @csrf
                            
                            <!-- Hidden fields for loan details -->
                            <input type="hidden" name="loan_type" id="form-loan-type" value="{{ $defaults['loan_type_id'] ?? '' }}">
                            <input type="hidden" name="amount" id="form-amount" value="{{ $defaults['default_loan_amount'] ?? 20000 }}">
                            <input type="hidden" name="loan_terms" id="form-loan-terms" value="12">
                            <input type="hidden" name="loan_term_period" id="form-loan-term-period" value="months">
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h5 class="card-title">Loan Summary</h5>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Loan Amount:</span>
                                                <span class="fw-bold">₹<span id="summary-amount">{{ number_format($defaults['default_loan_amount'] ?? 20000) }}</span></span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Loan Term:</span>
                                                <span class="fw-bold"><span id="summary-term">12</span> months</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Interest Rate:</span>
                                                <span class="fw-bold"><span id="summary-interest">{{ $defaults['interest_rate'] ?? 18 }}</span>% p.a.</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Monthly EMI:</span>
                                                <span class="fw-bold text-primary">₹<span id="summary-emi">0.00</span></span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Total Amount:</span>
                                                <span class="fw-bold">₹<span id="summary-total">0.00</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <div class="form-group mb-3">
                                        <label for="purpose_of_loan" class="form-label">Purpose of Loan <span class="text-danger">*</span></label>
                                        <select name="purpose_of_loan" id="purpose_of_loan" class="form-select" required>
                                            <option value="">Select Purpose</option>
                                            <option value="Education">Education</option>
                                            <option value="Medical Expenses">Medical Expenses</option>
                                            <option value="Debt Consolidation">Debt Consolidation</option>
                                            <option value="Home Renovation">Home Renovation</option>
                                            <option value="Wedding">Wedding</option>
                                            <option value="Travel">Travel</option>
                                            <option value="Business">Business</option>
                                            <option value="Vehicle Purchase">Vehicle Purchase</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="branch_id" class="form-label">Preferred Branch <span class="text-danger">*</span></label>
                                        <select name="branch_id" id="branch_id" class="form-select" required>
                                            <option value="">Select Branch</option>
                                            @foreach($branches ?? [] as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="referral_code" class="form-label">Referral Code (if any)</label>
                                        <input type="text" name="referral_code" id="referral_code" class="form-control" placeholder="Enter referral code if you have one">
                                    </div>
                                </div>
                                
                                <div class="col-12 mb-4">
                                    <div class="form-group">
                                        <label for="notes" class="form-label">Additional Information</label>
                                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Any additional information you'd like to share with us"></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-12 mb-4">
                                    <h5 class="border-bottom pb-2 mb-3">Required Documents</h5>
                                    <p class="text-muted mb-4">Please upload clear, legible scanned copies or photos of the following documents:</p>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="aadhaar_card_front" class="form-label">Aadhaar Card (Front) <span class="text-danger">*</span></label>
                                                <input type="file" name="aadhaar_card_front" id="aadhaar_card_front" class="form-control" required accept="image/jpeg,image/png,application/pdf">
                                                <small class="form-text text-muted">Max file size: 2MB. Formats: JPG, PNG, PDF</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="aadhaar_card_back" class="form-label">Aadhaar Card (Back) <span class="text-danger">*</span></label>
                                                <input type="file" name="aadhaar_card_back" id="aadhaar_card_back" class="form-control" required accept="image/jpeg,image/png,application/pdf">
                                                <small class="form-text text-muted">Max file size: 2MB. Formats: JPG, PNG, PDF</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="pan_card" class="form-label">PAN Card <span class="text-danger">*</span></label>
                                                <input type="file" name="pan_card" id="pan_card" class="form-control" required accept="image/jpeg,image/png,application/pdf">
                                                <small class="form-text text-muted">Max file size: 2MB. Formats: JPG, PNG, PDF</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Additional Documents Section (Optional) -->
                                <div class="col-12 mb-4">
                                    <h5 class="border-bottom pb-2 mb-3">Additional Documents (Optional)</h5>
                                    <div class="row align-items-end document-row mb-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document_type[0]" class="form-label">Document Type</label>
                                                <select name="document_type[0]" class="form-select">
                                                    <option value="">Select Document Type</option>
                                                    @foreach($documentTypes ?? [] as $id => $name)
                                                        <option value="{{ $id }}">{{ $name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="document[0]" class="form-label">Upload File</label>
                                                <input type="file" name="document[0]" class="form-control" accept="image/jpeg,image/png,application/pdf">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="description[0]" class="form-label">Description</label>
                                                <input type="text" name="description[0]" class="form-control" placeholder="Short description">
                                            </div>
                                        </div>
                                    </div>
                                    <div id="more-documents"></div>
                                    <button type="button" id="add-document" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-plus"></i> Add Another Document
                                    </button>
                                </div>
                                
                                <div class="col-12 mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms-check" required>
                                        <label class="form-check-label" for="terms-check">
                                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a> and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">privacy policy</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">Submit Application</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <div class="container">
                <div class="section-title text-center">
                    <h2>Frequently Asked Questions</h2>
                    <p>Find answers to common questions about our personal loans</p>
                </div>
                <div class="accordion" id="loanFaqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                What documents do I need to apply?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#loanFaqAccordion">
                            <div class="accordion-body">
                                To apply for a loan with MBC Finance, you need to submit your Aadhaar Card (front and back), PAN Card, and proof of income (such as salary slips or tax returns). Additional documents may be required based on the loan amount and purpose.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                How long does the approval process take?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#loanFaqAccordion">
                            <div class="accordion-body">
                                Our typical approval process takes just a few minutes to a few hours, depending on verification requirements. Once approved, disbursement usually happens within 24 hours.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                Can I repay my loan earlier than the scheduled term?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#loanFaqAccordion">
                            <div class="accordion-body">
                                Yes, you can repay your loan before the end of the term. There may be a nominal foreclosure fee of 2% on the outstanding principal amount.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                What is the minimum credit score required?
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#loanFaqAccordion">
                            <div class="accordion-body">
                                We typically require a minimum credit score of 650 for loan approval, but we also consider other factors such as income stability and existing debt obligations.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Loan Eligibility</h6>
                    <p>To be eligible for a loan, you must be at least 21 years of age, have a valid Aadhaar card, PAN card, and meet our credit requirements. MBC Finance reserves the right to approve or reject loan applications based on our internal credit assessment.</p>
                    
                    <h6>2. Loan Repayment</h6>
                    <p>You agree to repay the loan amount along with interest and any applicable fees according to the repayment schedule provided upon loan approval. Late payments may incur additional charges as specified in the loan agreement.</p>
                    
                    <h6>3. Document Verification</h6>
                    <p>All documents submitted as part of your loan application must be genuine and valid. Submission of false or fraudulent documents will result in immediate rejection of your application and may lead to legal action.</p>
                    
                    <h6>4. Interest Rates and Fees</h6>
                    <p>Interest rates are determined based on our assessment of your credit profile and the loan type. All fees associated with the loan will be clearly communicated to you before loan disbursal.</p>
                    
                    <h6>5. Privacy</h6>
                    <p>We respect your privacy and will use your personal information only for purposes related to your loan application and account management, in accordance with our privacy policy.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Privacy Policy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="privacyModalLabel">Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Information We Collect</h6>
                    <p>We collect personal information such as your name, address, contact details, employment information, and financial details necessary for processing your loan application and managing your account.</p>
                    
                    <h6>2. How We Use Your Information</h6>
                    <p>We use your information to process your loan application, manage your account, communicate with you about your loan, and comply with legal and regulatory requirements.</p>
                    
                    <h6>3. Information Sharing</h6>
                    <p>We may share your information with credit bureaus, regulatory authorities, and service providers who assist us in processing loan applications and servicing accounts. We do not sell your personal information to third parties.</p>
                    
                    <h6>4. Data Security</h6>
                    <p>We implement appropriate security measures to protect your personal information from unauthorized access, disclosure, alteration, and destruction.</p>
                    
                    <h6>5. Your Rights</h6>
                    <p>You have the right to access, correct, and delete your personal information, subject to legal restrictions. To exercise these rights, please contact our customer service.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Get DOM elements
        const loanAmountSlider = document.getElementById('loan-amount-slider');
        const loanAmountInput = document.getElementById('loan-amount-input');
        const loanTermSlider = document.getElementById('loan-term-slider');
        const loanTermInput = document.getElementById('loan-term-input');
        const interestRateInput = document.getElementById('interest-rate');
        const calculateBtn = document.getElementById('calculate-btn');
        const loanTypeSelect = document.getElementById('loan-type');
        
        // Sync sliders and input fields
        loanAmountSlider.addEventListener('input', function() {
            loanAmountInput.value = this.value;
            updateSummaryAmount(this.value);
        });
        
        loanAmountInput.addEventListener('input', function() {
            if (this.value < parseInt(loanAmountSlider.min)) {
                this.value = loanAmountSlider.min;
            } else if (this.value > parseInt(loanAmountSlider.max)) {
                this.value = loanAmountSlider.max;
            }
            loanAmountSlider.value = this.value;
            updateSummaryAmount(this.value);
        });
        
        loanTermSlider.addEventListener('input', function() {
            loanTermInput.value = this.value;
            document.getElementById('summary-term').textContent = this.value;
            document.getElementById('form-loan-terms').value = this.value;
        });
        
        loanTermInput.addEventListener('input', function() {
            if (this.value < parseInt(loanTermSlider.min)) {
                this.value = loanTermSlider.min;
            } else if (this.value > parseInt(loanTermSlider.max)) {
                this.value = loanTermSlider.max;
            }
            loanTermSlider.value = this.value;
            document.getElementById('summary-term').textContent = this.value;
            document.getElementById('form-loan-terms').value = this.value;
        });
        
        // Update loan type based on selection
        if (loanTypeSelect) {
            loanTypeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                
                // Update min/max loan amount
                const minAmount = parseInt(selectedOption.getAttribute('data-min'));
                const maxAmount = parseInt(selectedOption.getAttribute('data-max'));
                const defaultAmount = Math.round((minAmount + maxAmount) / 2);
                
                loanAmountSlider.min = minAmount;
                loanAmountSlider.max = maxAmount;
                loanAmountSlider.value = defaultAmount;
                loanAmountInput.value = defaultAmount;
                
                // Update loan term
                const maxTerm = parseInt(selectedOption.getAttribute('data-term'));
                loanTermSlider.max = maxTerm;
                if (parseInt(loanTermSlider.value) > maxTerm) {
                    loanTermSlider.value = maxTerm;
                    loanTermInput.value = maxTerm;
                }
                
                // Update interest rate
                interestRateInput.value = selectedOption.getAttribute('data-interest');
                document.getElementById('summary-interest').textContent = selectedOption.getAttribute('data-interest');
                
                // Update form values
                document.getElementById('form-loan-type').value = this.value;
                document.getElementById('form-amount').value = loanAmountInput.value;
                document.getElementById('form-loan-terms').value = loanTermInput.value;
                document.getElementById('form-loan-term-period').value = selectedOption.getAttribute('data-term-period') || 'months';
                
                updateSummaryAmount(loanAmountInput.value);
            });
        }
        
        // Function to update summary amount with formatting
        function updateSummaryAmount(amount) {
            document.getElementById('summary-amount').textContent = numberWithCommas(amount);
            document.getElementById('form-amount').value = amount;
        }
        
        // Format numbers with commas
        function numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
        
        // Calculate EMI function
        function calculateEMI() {
            const loanAmount = parseFloat(loanAmountInput.value);
            const loanTerm = parseInt(loanTermInput.value);
            const interestRate = parseFloat(interestRateInput.value);
            const loanTypeId = loanTypeSelect ? loanTypeSelect.value : document.getElementById('form-loan-type').value;
            
            // AJAX call to backend
            fetch('{{ route("loan.calculate-emi") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    loan_amount: loanAmount,
                    loan_term: loanTerm,
                    interest_rate: interestRate,
                    loan_type_id: loanTypeId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                
                // Update the result values
                document.getElementById('monthly-emi').textContent = '₹' + numberWithCommas(data.emi.toFixed(2));
                document.getElementById('total-interest').textContent = '₹' + numberWithCommas(data.total_interest.toFixed(2));
                document.getElementById('total-amount').textContent = '₹' + numberWithCommas(data.total_amount.toFixed(2));
                
                // Update summary section
                document.getElementById('summary-emi').textContent = numberWithCommas(data.emi.toFixed(2));
                document.getElementById('summary-total').textContent = numberWithCommas(data.total_amount.toFixed(2));
                
                // Calculate percentages for the chart
                const principalPercentage = Math.round((loanAmount / data.total_amount) * 100);
                const interestPercentage = 100 - principalPercentage;
                
                // Update chart segments
                document.getElementById('principal-segment').style.width = principalPercentage + '%';
                document.getElementById('interest-segment').style.width = interestPercentage + '%';
                
                // Update labels
                document.getElementById('principal-label').textContent = 'Principal: ₹' + numberWithCommas(loanAmount.toFixed(2)) + ' (' + principalPercentage + '%)';
                document.getElementById('interest-label').textContent = 'Interest: ₹' + numberWithCommas(data.total_interest.toFixed(2)) + ' (' + interestPercentage + '%)';
            })
            .catch(error => {
                console.error('Error calculating EMI:', error);
                alert('There was an error calculating the EMI. Please try again.');
            });
        }
        
        // Add document button functionality
        let docCounter = 1;
        document.getElementById('add-document').addEventListener('click', function() {
            const docContainer = document.getElementById('more-documents');
            const docRow = document.createElement('div');
            docRow.className = 'row align-items-end document-row mb-3';
            docRow.innerHTML = `
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="document_type[${docCounter}]" class="form-label">Document Type</label>
                        <select name="document_type[${docCounter}]" class="form-select">
                            <option value="">Select Document Type</option>
                            @foreach($documentTypes ?? [] as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="document[${docCounter}]" class="form-label">Upload File</label>
                        <input type="file" name="document[${docCounter}]" class="form-control" accept="image/jpeg,image/png,application/pdf">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="description[${docCounter}]" class="form-label">Description</label>
                        <input type="text" name="description[${docCounter}]" class="form-control" placeholder="Short description">
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger remove-doc-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            docContainer.appendChild(docRow);
            docCounter++;
            
            // Add remove button functionality
            docRow.querySelector('.remove-doc-btn').addEventListener('click', function() {
                docRow.remove();
            });
        });
        
        // Calculate button click event
        calculateBtn.addEventListener('click', calculateEMI);
        
        // Calculate EMI on initial load
        calculateEMI();
    });
</script>
@endsection