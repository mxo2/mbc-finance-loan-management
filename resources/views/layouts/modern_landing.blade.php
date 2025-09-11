<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MBC Finance - Instant Consumer Loans Made Easy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/blue-theme-override.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e88e5;
            --secondary-color: #1976d2;
            --dark-color: #333;
            --light-color: #f8f9fa;
            --success-color: #1e88e5;
            --gradient-start: #1e88e5;
            --gradient-end: #1976d2;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        /* Header */
        .navbar {
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        .nav-link {
            font-weight: 500;
            margin: 0 10px;
            color: var(--dark-color);
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px 25px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
            padding: 12px 25px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-warning:hover {
            background-color: #ffb300;
            border-color: #ffb300;
            color: #000;
            transform: translateY(-2px);
        }
        
        .btn-outline-light {
            border: 2px solid rgba(255,255,255,0.8);
            color: white;
            padding: 10px 25px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-light:hover {
            background-color: white;
            color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            padding: 120px 0;
            position: relative;
            overflow: hidden;
            color: white;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="%23ffffff" opacity="0.1"><polygon points="0,0 1000,0 1000,100 0,80"/></svg>') no-repeat bottom;
            background-size: cover;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 35px;
            color: rgba(255,255,255,0.9);
            font-weight: 400;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-stats .stat-item h3 {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .hero-stats .stat-item small {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .hero-features {
            font-size: 1.1rem;
        }
        
        .hero-features i {
            font-size: 1.2rem;
        }
        
        .hero-image-container {
            position: relative;
            text-align: center;
        }
        
        .hero-image {
            max-width: 100%;
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1));
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .hero-stats .stat-item h3 {
                font-size: 1.5rem;
            }
            
            .btn-lg {
                padding: 10px 20px;
                font-size: 1rem;
            }
        }
        
        /* How It Works */
        .how-it-works {
            padding: 80px 0;
            background-color: #fff;
        }
        
        .step-card {
            text-align: center;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            height: 100%;
            background-color: #fff;
        }
        
        .step-card:hover {
            transform: translateY(-10px);
        }
        
        .step-icon {
            width: 80px;
            height: 80px;
            background-color: var(--light-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--primary-color);
            font-size: 2rem;
        }
        
        /* Features */
        .features {
            padding: 80px 0;
            background-color: var(--light-color);
        }
        
        .feature-card {
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 100%;
            background-color: #fff;
            transition: all 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        /* Why Choose Us */
        .why-choose {
            padding: 80px 0;
            background-color: #fff;
        }
        
        .choose-card {
            text-align: center;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 100%;
            background-color: #fff;
            transition: all 0.3s;
        }
        
        .choose-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .choose-icon {
            color: var(--secondary-color);
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        /* Apply Now */
        .apply-now {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, #003d99 100%);
            color: #fff;
            text-align: center;
        }
        
        .apply-now h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        /* FAQ */
        .faq {
            padding: 80px 0;
            background-color: var(--light-color);
        }
        
        .accordion-button:not(.collapsed) {
            background-color: var(--light-color);
            color: var(--primary-color);
        }
        
        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(0,0,0,.125);
        }
        
        /* Footer */
        footer {
            background-color: var(--dark-color);
            color: #fff;
            padding: 60px 0 20px;
        }
        
        .footer-links h5 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .footer-links ul {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: #fff;
        }
        
        .social-icons a {
            color: #fff;
            font-size: 1.5rem;
            margin-right: 15px;
            transition: color 0.3s;
        }
        
        .social-icons a:hover {
            color: var(--secondary-color);
        }
        
        .copyright {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        /* Loan Calculator */
        .calculator {
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .range-slider {
            width: 100%;
        }
        
        .calculator-result {
            background-color: var(--light-color);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero {
                padding: 60px 0;
                text-align: center;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero-image {
                margin-top: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('assets/images/mbc-logo.svg') }}" alt="MBC Finance" height="50">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#calculator">EMI Calculator</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#brands">Brands</a>
                    </li>
                </ul>
                <div class="ms-lg-3 mt-3 mt-lg-0">
                    @if (Auth::check())
                        <a href="{{ route('dashboard') }}" class="btn btn-primary me-2">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1>Get Your Dream Product<br><span class="text-warning">Today!</span></h1>
                        <p class="lead">Instant consumer loans up to ₹50,000 for mobiles, TVs, and motorcycles. Fast approval, easy EMIs, zero hidden charges.</p>
                        
                        <div class="hero-stats mb-4">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h3 class="text-warning mb-0">₹50K</h3>
                                        <small>Max Loan</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h3 class="text-warning mb-0">2 Min</h3>
                                        <small>Approval</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <h3 class="text-warning mb-0">0%</h3>
                                        <small>Hidden Fees</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="hero-features mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle text-warning me-2"></i>
                                <span>Instant Approval</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-shield-alt text-warning me-2"></i>
                                <span>100% Secure</span>
                            </div>
                        </div>
                        
                        <div class="hero-buttons">
                            <a href="#apply-now" class="btn btn-warning btn-lg me-3 px-4">Apply Now</a>
                            <a href="#how-it-works" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image-container">
                        <img src="{{ asset('assets/images/hero-image.svg') }}" alt="Loan Application" class="hero-image img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Loan Calculator -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="calculator">
                        <h3 class="text-center mb-4">Loan Calculator</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="loanAmount" class="form-label">Loan Amount (₹)</label>
                                    <input type="range" class="form-range" min="5000" max="50000" step="1000" id="loanAmount" value="25000">
                                    <div class="d-flex justify-content-between">
                                        <span>₹5,000</span>
                                        <span id="loanAmountValue">₹25,000</span>
                                        <span>₹50,000</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="loanTerm" class="form-label">Loan Term (months)</label>
                                    <input type="range" class="form-range" min="3" max="12" step="1" id="loanTerm" value="6">
                                    <div class="d-flex justify-content-between">
                                        <span>3</span>
                                        <span id="loanTermValue">6</span>
                                        <span>12</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="calculator-result">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <h5>Monthly EMI</h5>
                                    <p class="h4" id="monthlyEMI">₹4,375</p>
                                </div>
                                <div class="col-md-4">
                                    <h5>Interest Rate</h5>
                                    <p class="h4">5% p.m.</p>
                                </div>
                                <div class="col-md-4">
                                    <h5>Total Repayment</h5>
                                    <p class="h4" id="totalRepayment">₹26,250</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">How It Works</h2>
                <p class="lead">Get your loan in 4 simple steps</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <h4>Apply Online</h4>
                        <p>Fill out our simple online application form in minutes</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4>Get Approved</h4>
                        <p>Receive instant approval with minimal documentation</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h4>Shop Your Dream Product</h4>
                        <p>Use your loan to purchase the product you desire</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="step-card">
                        <div class="step-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h4>Repay in Easy EMIs</h4>
                        <p>Enjoy flexible repayment options that fit your budget</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose MBC Finance -->
    <section class="py-5 bg-primary text-white" id="why-choose">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Why Choose MBC Finance?</h2>
                <p class="lead">We make borrowing simple, fast, and transparent with features designed for your convenience.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-item text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-bolt fa-3x text-warning"></i>
                        </div>
                        <h4>Instant Approval</h4>
                        <p>Get approved within minutes with our AI-powered eligibility check. No waiting, no delays.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-coins fa-3x text-warning"></i>
                        </div>
                        <h4>Flexible Amounts</h4>
                        <p>Borrow anywhere from ₹5,000 to ₹50,000 based on your specific needs and eligibility.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-calendar-alt fa-3x text-warning"></i>
                        </div>
                        <h4>Easy EMIs</h4>
                        <p>Choose from flexible repayment plans that fit your budget and lifestyle perfectly.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-eye fa-3x text-warning"></i>
                        </div>
                        <h4>Zero Hidden Charges</h4>
                        <p>Complete transparency in pricing. What you see is exactly what you pay - no surprises.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-rocket fa-3x text-warning"></i>
                        </div>
                        <h4>Quick Disbursal</h4>
                        <p>Loan amount credited instantly to your account for immediate purchase of your dream product.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-item text-center">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-handshake fa-3x text-warning"></i>
                        </div>
                        <h4>Trusted Service</h4>
                        <p>Join thousands of satisfied customers who trust us for their consumer financing needs.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- What Can You Buy -->
    <section class="py-5" id="products">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">What Can You Buy?</h2>
                <p class="lead">Finance your favorite products with our instant loans</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="product-card text-center p-4 border rounded">
                        <div class="product-icon mb-3">
                            <i class="fas fa-mobile-alt fa-4x text-primary"></i>
                        </div>
                        <h4>📱 Mobile Phones</h4>
                        <p>Get the latest smartphones from top brands like iPhone, Samsung, OnePlus, and more.</p>
                        <ul class="list-unstyled">
                            <li>✓ Latest models available</li>
                            <li>✓ All major brands</li>
                            <li>✓ EMI starting ₹500/month</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-card text-center p-4 border rounded">
                        <div class="product-icon mb-3">
                            <i class="fas fa-tv fa-4x text-primary"></i>
                        </div>
                        <h4>📺 Television</h4>
                        <p>Upgrade your entertainment with Smart TVs, LED, OLED displays from leading brands.</p>
                        <ul class="list-unstyled">
                            <li>✓ Smart TV features</li>
                            <li>✓ 32" to 75" sizes</li>
                            <li>✓ EMI starting ₹800/month</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-card text-center p-4 border rounded">
                        <div class="product-icon mb-3">
                            <i class="fas fa-motorcycle fa-4x text-primary"></i>
                        </div>
                        <h4>🏍️ Motorcycles</h4>
                        <p>Own your dream bike with easy financing options for all popular motorcycle brands.</p>
                        <ul class="list-unstyled">
                            <li>✓ All bike models</li>
                            <li>✓ Popular brands</li>
                            <li>✓ EMI starting ₹1,200/month</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features bg-light py-5" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Popular Brands We Finance</h2>
                <p class="lead">Get instant loans for your favorite brands across mobiles, televisions, and motorcycles</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h4>Instant Loan Approval</h4>
                        <p>Get your loan approved within minutes, not days</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-sliders-h"></i>
                        </div>
                        <h4>Flexible Loan Amounts</h4>
                        <p>Borrow anywhere from ₹5,000 to ₹50,000 based on your needs</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h4>Easy EMI Options</h4>
                        <p>Choose repayment terms from 3 to 12 months</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-eye-slash"></i>
                        </div>
                        <h4>Zero Hidden Charges</h4>
                        <p>Transparent fee structure with no surprise costs</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h4>Quick Disbursal</h4>
                        <p>Receive funds directly to the merchant for your purchase</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4>Minimal Documentation</h4>
                        <p>Simple paperwork with digital KYC process</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="why-choose" id="why-choose">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Why Choose MBC Finance?</h2>
                <p class="lead">We're committed to making consumer financing accessible and hassle-free</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="choose-card">
                        <div class="choose-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Trusted & Reliable</h4>
                        <p>Join thousands of satisfied customers who trust MBC Finance for their financial needs</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="choose-card">
                        <div class="choose-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h4>Instant & Hassle-Free</h4>
                        <p>Our streamlined process ensures you get funds when you need them most</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="choose-card">
                        <div class="choose-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Consumer-Focused</h4>
                        <p>Designed specifically for consumer purchases like electronics, appliances, and more</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Apply Now -->
    <section class="apply-now" id="apply-now">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2>Ready to Get Started?</h2>
                    <p class="lead mb-4">Apply now and get your loan approved in minutes</p>
                    <form action="{{ route('modern.apply') }}" method="POST" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-lg" placeholder="Full Name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="form-control form-control-lg" placeholder="Email Address" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <input type="tel" class="form-control form-control-lg" placeholder="Phone Number" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <input type="number" class="form-control form-control-lg" placeholder="Loan Amount" name="amount" min="5000" max="50000" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-secondary btn-lg">Apply Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq" id="faq">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Frequently Asked Questions</h2>
                <p class="lead">Find answers to common questions about our loan services</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        @if(count($FAQs) > 0)
                            @foreach($FAQs as $index => $faq)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $index }}">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $index }}">
                                            {{ $faq->question }}
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $index }}" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            {!! $faq->description !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        What is the maximum loan amount I can get?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        You can apply for loans up to ₹50,000 depending on your eligibility and requirements.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        What documents do I need to apply?
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        You'll need your ID proof (Aadhaar/PAN), address proof, and income proof documents. Our digital KYC process makes document submission easy and hassle-free.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        How long does the approval process take?
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Our approval process is instant. Once you submit your application with all required documents, you can receive approval within minutes.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        How are the EMIs calculated?
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        EMIs are calculated based on the loan amount, interest rate, and tenure. You can use our loan calculator to estimate your monthly payments before applying.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <img src="{{ asset('assets/images/logo-white.png') }}" alt="MBC Finance Logo" class="mb-4" style="height: 40px;">
                    <p>MBC Finance provides instant consumer loans for your everyday needs. Get approved quickly and shop for your favorite products with easy EMI options.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 footer-links">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="#">Home</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#features">What We Offer</a></li>
                        <li><a href="#why-choose">Why Choose Us</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 footer-links">
                    <h5>Legal</h5>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Refund Policy</a></li>
                        <li><a href="#">Documentation</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4 mb-4 footer-links">
                    <h5>Contact Us</h5>
                    <ul>
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Finance Street, Mumbai, India</li>
                        <li><i class="fas fa-phone me-2"></i> +91 1234567890</li>
                        <li><i class="fas fa-envelope me-2"></i> support@mbcfinance.com</li>
                    </ul>
                </div>
            </div>
            <div class="text-center copyright">
                <p>&copy; {{ date('Y') }} MBC Finance. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Loan Calculator
        const loanAmountSlider = document.getElementById('loanAmount');
        const loanTermSlider = document.getElementById('loanTerm');
        const loanAmountValue = document.getElementById('loanAmountValue');
        const loanTermValue = document.getElementById('loanTermValue');
        const monthlyEMI = document.getElementById('monthlyEMI');
        const totalRepayment = document.getElementById('totalRepayment');
        
        function updateCalculator() {
            const amount = parseInt(loanAmountSlider.value);
            const months = parseInt(loanTermSlider.value);
            const interestRate = 0.05; // 5% per month
            
            // Simple interest calculation for demonstration
            const interest = amount * interestRate * months;
            const total = amount + interest;
            const emi = total / months;
            
            loanAmountValue.textContent = '₹' + amount.toLocaleString();
            loanTermValue.textContent = months;
            monthlyEMI.textContent = '₹' + Math.round(emi).toLocaleString();
            totalRepayment.textContent = '₹' + Math.round(total).toLocaleString();
        }
        
        loanAmountSlider.addEventListener('input', updateCalculator);
        loanTermSlider.addEventListener('input', updateCalculator);
        
        // Initialize calculator
        updateCalculator();
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>