@extends('layouts.app')

@section('page-title')
    {{ __('Loans - MBC Finance') }}
@endsection

@push('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/modern-loans.css') }}">
<style>
/* Override admin layout styles for loan page */
.modern-loans-page {
    margin-left: 0 !important;
    padding-left: 0 !important;
    width: 100% !important;
    max-width: 100% !important;
}

.pc-container .pc-content {
    margin-left: 0 !important;
    padding: 0 !important;
}

.page-header {
    display: none !important;
}

/* Ensure full width for modern design */
.container-fluid {
    padding: 0 !important;
}

/* Fix any admin button conflicts */
.btn-apply, .btn-explore, .btn-apply-calculator {
    all: unset;
    display: inline-block;
    padding: 0.75rem 2rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    text-align: center;
    border: none;
    outline: none;
}

.btn-apply {
    background: #f59e0b;
    color: #ffffff;
}

.btn-apply:hover {
    background: #d97706;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    color: #ffffff;
    text-decoration: none;
}

.btn-explore {
    background: #1e40af;
    color: #ffffff;
}

.btn-explore:hover {
    background: #1e3a8a;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(30, 64, 175, 0.4);
    color: #ffffff;
    text-decoration: none;
}
</style>
@endpush

@section('content')
<div class="modern-loans-page">
    <!-- Success Message -->
    @if(request()->get('success') == '1')
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin: 1rem; background: #d1fae5; color: #059669; border: 1px solid #a7f3d0; border-radius: 0.5rem;">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2" style="font-size: 1.25rem;"></i>
            <div>
                <strong>Application Submitted Successfully!</strong><br>
                <small>Our team will review your loan application and contact you within 24 hours. You can track the progress in your dashboard.</small>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="background: none; border: none; color: #059669; font-size: 1.5rem; opacity: 0.7;">×</button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin: 1rem; background: #d1fae5; color: #059669; border: 1px solid #a7f3d0; border-radius: 0.5rem;">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2" style="font-size: 1.25rem;"></i>
            <div>
                <strong>{{ session('success') }}</strong>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="background: none; border: none; color: #059669; font-size: 1.5rem; opacity: 0.7;">×</button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 1rem; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; border-radius: 0.5rem;">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-circle me-2" style="font-size: 1.25rem;"></i>
            <div>
                <strong>{{ session('error') }}</strong>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="background: none; border: none; color: #dc2626; font-size: 1.5rem; opacity: 0.7;">×</button>
    </div>
    @endif

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container-fluid">
            <div class="row align-items-center min-vh-60">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            Instant Loans Up to 
                            <span class="text-primary">₹5,00,000</span>
                        </h1>
                        <p class="hero-subtitle">Apply in minutes, get approved in hours. Choose from our flexible loan options designed for your needs.</p>
                        <div class="hero-stats">
                            <div class="stat-item">
                                <div class="stat-number">24hrs</div>
                                <div class="stat-label">Quick Approval</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">5.9%</div>
                                <div class="stat-label">Starting Interest</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">100%</div>
                                <div class="stat-label">Digital Process</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <div class="floating-card">
                            <div class="card-content">
                                <div class="card-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h4>Your Financial Growth</h4>
                                <p>Track your loan journey with us</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Loan Categories Section -->
    <section class="loan-categories-section">
        <div class="container-fluid">
            <div class="section-header text-center">
                <h2 class="section-title">Choose Your Perfect Loan</h2>
                <p class="section-subtitle">Explore our range of financial solutions tailored for your specific needs</p>
            </div>
            
            <div class="loan-grid">
                @forelse($loanTypes as $loanType)
                    <div class="loan-card" data-loan-id="{{ $loanType->id }}">
                        <div class="loan-card-inner">
                            <!-- Front of card -->
                            <div class="loan-card-front">
                                <div class="loan-icon">
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
                                <h3 class="loan-title">{{ $loanType->type }}</h3>
                                <p class="loan-description">
                                    @switch($loanType->type)
                                        @case('Personal Loan')
                                            Up to ₹{{ number_format($loanType->max_loan_amount) }} for your personal needs
                                            @break
                                        @case('Business Loan')
                                            Grow your business with up to ₹{{ number_format($loanType->max_loan_amount) }}
                                            @break
                                        @case('Home Loan')
                                            Your dream home awaits with up to ₹{{ number_format($loanType->max_loan_amount) }}
                                            @break
                                        @case('Consumer Loan')
                                            Finance your purchases with up to ₹{{ number_format($loanType->max_loan_amount) }}
                                            @break
                                        @case('Vehicle Loan')
                                            Drive your dreams with up to ₹{{ number_format($loanType->max_loan_amount) }}
                                            @break
                                        @default
                                            Flexible financing up to ₹{{ number_format($loanType->max_loan_amount) }}
                                    @endswitch
                                </p>
                                <div class="loan-cta">
                                    <button class="btn-explore" onclick="exploreLoan({{ $loanType->id }})">
                                        Explore Loan
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Back of card (shown on hover) -->
                            <div class="loan-card-back">
                                <div class="loan-highlights">
                                    <div class="highlight-item">
                                        <div class="highlight-label">Amount Range</div>
                                        <div class="highlight-value">₹{{ number_format($loanType->min_loan_amount) }} - ₹{{ number_format($loanType->max_loan_amount) }}</div>
                                    </div>
                                    <div class="highlight-item">
                                        <div class="highlight-label">Interest Rate</div>
                                        <div class="highlight-value">{{ $loanType->interest_rate }}% p.a.</div>
                                    </div>
                                    <div class="highlight-item">
                                        <div class="highlight-label">Max Tenure</div>
                                        <div class="highlight-value">{{ $loanType->max_loan_term }} {{ $loanType->loan_term_period }}</div>
                                    </div>
                                    <div class="highlight-item">
                                        <div class="highlight-label">Processing</div>
                                        <div class="highlight-value">
                                            <span class="badge-quick">Quick Approval</span>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn-apply" onclick="applyNow({{ $loanType->id }})">
                                    Apply Now
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="no-loans-message">
                        <div class="no-loans-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h3>No Loan Types Available</h3>
                        <p>Please contact our support team for available loan options.</p>
                        <button class="btn-contact">Contact Support</button>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container-fluid">
            <div class="section-header text-center">
                <h2 class="section-title">Why Choose MBC Finance?</h2>
                <p class="section-subtitle">Experience the difference with our customer-first approach</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h4>Quick Approval</h4>
                    <p>Get approved within 24 hours with our streamlined digital process</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h4>Easy EMI</h4>
                    <p>Flexible repayment options that fit your budget and lifestyle</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Transparent Process</h4>
                    <p>No hidden charges. Complete transparency throughout your loan journey</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h4>24/7 Support</h4>
                    <p>Round-the-clock customer support for all your queries and assistance</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Calculator Section -->
    <section class="quick-calculator-section">
        <div class="container-fluid">
            <div class="calculator-container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="calculator-content">
                            <h2>Calculate Your EMI</h2>
                            <p>Get an instant estimate of your monthly payments</p>
                            
                            <div class="calculator-form">
                                <div class="form-group">
                                    <label>Loan Amount</label>
                                    <div class="slider-container">
                                        <input type="range" id="quick-amount" min="50000" max="500000" value="200000" step="10000">
                                        <div class="slider-value">₹<span id="quick-amount-value">2,00,000</span></div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Tenure (Months)</label>
                                    <div class="slider-container">
                                        <input type="range" id="quick-tenure" min="6" max="60" value="24" step="1">
                                        <div class="slider-value"><span id="quick-tenure-value">24</span> months</div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Interest Rate</label>
                                    <div class="slider-container">
                                        <input type="range" id="quick-interest" min="8" max="20" value="12" step="0.5">
                                        <div class="slider-value"><span id="quick-interest-value">12.0</span>% p.a.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="calculator-result">
                            <div class="result-card">
                                <h3>Your EMI Details</h3>
                                <div class="result-item">
                                    <span class="result-label">Monthly EMI</span>
                                    <span class="result-value primary">₹<span id="quick-emi">9,567</span></span>
                                </div>
                                <div class="result-item">
                                    <span class="result-label">Total Interest</span>
                                    <span class="result-value">₹<span id="quick-interest-total">29,608</span></span>
                                </div>
                                <div class="result-item">
                                    <span class="result-label">Total Amount</span>
                                    <span class="result-value">₹<span id="quick-total">2,29,608</span></span>
                                </div>
                                <button class="btn-apply-calculator" onclick="startApplication()">
                                    Apply for This Loan
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- My Applications Section -->
    @if($myLoans->count() > 0)
    <section class="my-applications-section">
        <div class="container-fluid">
            <div class="section-header">
                <h2 class="section-title">My Loan Applications</h2>
                <p class="section-subtitle">Track your ongoing and completed applications</p>
            </div>
            
            <div class="applications-grid">
                @foreach($myLoans as $loan)
                    <div class="application-card">
                        <div class="application-header">
                            <div class="application-id">{{ loanPrefix() . $loan->loan_id }}</div>
                            <div class="application-status">
                                @switch($loan->status)
                                    @case('pending')
                                        <span class="status-badge pending">Pending Review</span>
                                        @break
                                    @case('approved')
                                        <span class="status-badge approved">Approved</span>
                                        @break
                                    @case('rejected')
                                        <span class="status-badge rejected">Rejected</span>
                                        @break
                                    @default
                                        <span class="status-badge draft">{{ ucfirst($loan->status) }}</span>
                                @endswitch
                            </div>
                        </div>
                        
                        <div class="application-details">
                            <h4>{{ !empty($loan->loanType) ? $loan->loanType->type : 'Loan Application' }}</h4>
                            <div class="detail-row">
                                <span class="detail-label">Amount:</span>
                                <span class="detail-value">{{ priceFormat($loan->amount) }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Applied:</span>
                                <span class="detail-value">{{ $loan->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        
                        <div class="application-actions">
                            <button class="btn-view" onclick="viewApplication({{ $loan->id }})">
                                <i class="fas fa-eye"></i>
                                View Details
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check for successful loan application submission
    if (sessionStorage.getItem('loanApplicationSuccess') === 'true') {
        sessionStorage.removeItem('loanApplicationSuccess');
        
        // Show success message
        const successMessage = document.createElement('div');
        successMessage.className = 'alert alert-success alert-dismissible fade show';
        successMessage.style.cssText = 'margin: 1rem; background: #d1fae5; color: #059669; border: 1px solid #a7f3d0; border-radius: 0.5rem; position: fixed; top: 80px; right: 20px; z-index: 1050; max-width: 400px;';
        successMessage.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2" style="font-size: 1.25rem;"></i>
                <div>
                    <strong>Application Submitted Successfully!</strong><br>
                    <small>Our team will review your application and contact you within 24 hours.</small>
                </div>
            </div>
            <button type="button" class="btn-close" onclick="this.parentElement.remove()" style="background: none; border: none; color: #059669; font-size: 1.5rem; opacity: 0.7;">×</button>
        `;
        
        document.body.appendChild(successMessage);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (successMessage.parentElement) {
                successMessage.remove();
            }
        }, 5000);
    }

    // Initialize quick calculator
    initQuickCalculator();
    
    // Add scroll animations
    addScrollAnimations();
});

function initQuickCalculator() {
    const amountSlider = document.getElementById('quick-amount');
    const tenureSlider = document.getElementById('quick-tenure');
    const interestSlider = document.getElementById('quick-interest');
    
    const amountValue = document.getElementById('quick-amount-value');
    const tenureValue = document.getElementById('quick-tenure-value');
    const interestValue = document.getElementById('quick-interest-value');
    
    const emiDisplay = document.getElementById('quick-emi');
    const interestTotalDisplay = document.getElementById('quick-interest-total');
    const totalDisplay = document.getElementById('quick-total');
    
    function updateCalculator() {
        const amount = parseFloat(amountSlider.value);
        const tenure = parseFloat(tenureSlider.value);
        const interest = parseFloat(interestSlider.value);
        
        // Update display values
        amountValue.textContent = formatNumber(amount);
        tenureValue.textContent = tenure;
        interestValue.textContent = interest.toFixed(1);
        
        // Calculate EMI
        const monthlyInterest = interest / 100 / 12;
        const emi = (amount * monthlyInterest * Math.pow(1 + monthlyInterest, tenure)) / 
                   (Math.pow(1 + monthlyInterest, tenure) - 1);
        
        const totalAmount = emi * tenure;
        const totalInterest = totalAmount - amount;
        
        // Update results with animation
        animateNumber(emiDisplay, Math.round(emi));
        animateNumber(interestTotalDisplay, Math.round(totalInterest));
        animateNumber(totalDisplay, Math.round(totalAmount));
    }
    
    amountSlider.addEventListener('input', updateCalculator);
    tenureSlider.addEventListener('input', updateCalculator);
    interestSlider.addEventListener('input', updateCalculator);
    
    // Initial calculation
    updateCalculator();
}

function animateNumber(element, targetValue) {
    const startValue = parseInt(element.textContent.replace(/,/g, '')) || 0;
    const increment = (targetValue - startValue) / 20;
    let currentValue = startValue;
    
    const timer = setInterval(() => {
        currentValue += increment;
        if ((increment > 0 && currentValue >= targetValue) || 
            (increment < 0 && currentValue <= targetValue)) {
            currentValue = targetValue;
            clearInterval(timer);
        }
        element.textContent = formatNumber(Math.round(currentValue));
    }, 20);
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function exploreLoan(loanId) {
    // Navigate to loan wizard
    window.location.href = `/loan/wizard/${loanId}`;
}

function applyNow(loanId) {
    // Navigate to modern wizard instead of old application
    window.location.href = `/loan/wizard/${loanId}`;
}

function startApplication() {
    // Navigate to general application with calculator values
    const amount = document.getElementById('quick-amount').value;
    const tenure = document.getElementById('quick-tenure').value;
    const interest = document.getElementById('quick-interest').value;
    
    window.location.href = `/loan/application?amount=${amount}&tenure=${tenure}&interest=${interest}`;
}

function viewApplication(loanId) {
    window.location.href = `/loan/${loanId}`;
}

function addScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe loan cards
    document.querySelectorAll('.loan-card').forEach(card => {
        observer.observe(card);
    });
    
    // Observe feature cards
    document.querySelectorAll('.feature-card').forEach(card => {
        observer.observe(card);
    });
}
</script>
@endpush