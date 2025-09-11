<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MBC Finance - Get Your Dream Product Today!</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Open+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/blue-theme-override.css') }}" rel="stylesheet">
    <style>
        :root {
            --background: hsl(0, 0%, 5%); /* Dark black background */
            --foreground: hsl(0, 0%, 95%); /* Light text */
            --card: hsl(0, 0%, 10%); /* Dark card background */
            --card-foreground: hsl(0, 0%, 95%); /* Light card text */
            --primary: hsl(221.2, 83.2%, 53.3%); /* Blue primary */
            --primary-foreground: hsl(0, 0%, 100%); /* White text on blue */
            --secondary: hsl(221.2, 83.2%, 45%); /* Darker blue secondary */
            --secondary-foreground: hsl(0, 0%, 100%); /* White text */
            --muted: hsl(0, 0%, 15%); /* Dark muted background */
            --muted-foreground: hsl(0, 0%, 70%); /* Light muted text */
            --border: hsl(0, 0%, 20%); /* Dark border */
            --radius: 0.75rem;
        }
        
        body {
            font-family: 'Inter', 'Open Sans', sans-serif;
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        
        /* Header */
        .navbar {
            background: hsl(0, 0%, 5%) / 0.95 !important;
            backdrop-filter: blur(8px);
            border-bottom: 1px solid hsl(var(--border));
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: hsl(var(--primary));
            text-decoration: none;
        }
        
        .navbar-brand img {
            height: 3rem;
            width: auto;
        }
        
        .nav-link {
            font-weight: 500;
            margin: 0 1rem;
            color: hsl(var(--foreground));
            transition: color 0.3s ease;
            text-decoration: none;
            padding: 0.5rem 0;
        }
        
        .nav-link:hover {
            color: hsl(var(--primary));
        }
        
        /* Buttons */
        .btn {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: var(--radius);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: hsl(var(--primary));
            color: hsl(var(--primary-foreground));
        }
        
        .btn-primary:hover {
            background-color: hsl(var(--primary) / 0.9);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px hsl(var(--primary) / 0.3);
        }
        
        .btn-secondary {
            background-color: hsl(var(--secondary));
            color: hsl(var(--secondary-foreground));
        }
        
        .btn-secondary:hover {
            background-color: hsl(var(--secondary) / 0.9);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px hsl(var(--secondary) / 0.3);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid hsl(var(--primary-foreground) / 0.8);
            color: hsl(var(--primary-foreground));
        }
        
        .btn-outline:hover {
            background-color: hsl(var(--primary-foreground));
            color: hsl(var(--primary));
            transform: translateY(-2px);
        }
        
        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.125rem;
        }
        
        /* Hero Section */
        .gradient-bg {
            background: linear-gradient(135deg, hsl(0, 0%, 8%) 0%, hsl(221.2, 83.2%, 15%) 50%, hsl(0, 0%, 5%) 100%);
        }
        
        .hero {
            position: relative;
            overflow: hidden;
            padding: 5rem 0;
            color: hsl(var(--primary-foreground));
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            z-index: 1;
        }
        
        .hero .container {
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }
        
        .hero h1 .text-yellow {
            color: #fbbf24;
        }
        
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            line-height: 1.6;
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
        
        /* Sections */
        .section-padding {
            padding: 5rem 0;
        }
        
        .feature-card {
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: var(--radius);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .text-yellow {
            color: #fbbf24;
        }
        
        .text-muted {
            color: hsl(var(--muted-foreground));
        }
        
        .product-card {
            transition: transform 0.3s ease;
            border: 2px solid #e9ecef;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
        }
        
        /* Footer */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 50px 0 20px;
        }
        
        .footer h5 {
            color: #ffc107;
            margin-bottom: 20px;
        }
        
        .footer a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer a:hover {
            color: #ffc107;
        }
        
        /* Responsive */
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
    </style>
</head>

<body>
    <!-- Header -->
    <header class="navbar">
        <div class="container">
            <nav class="d-flex align-items-center justify-content-between w-100">
                <a class="navbar-brand" href="#">
                    <img src="{{ asset('assets/images/logo_mbc.png') }}" alt="MBC Finance Logo" 
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div style="display: none;">MBC Finance</div>
                </a>
                
                <div class="d-none d-md-flex align-items-center gap-4">
                    <a href="#home" class="nav-link">Home</a>
                    <a href="#services" class="nav-link">Services</a>
                    <a href="#calculator" class="nav-link">Calculator</a>
                    <a href="#apply" class="nav-link">Apply</a>
                    <a href="#contact" class="nav-link">Contact</a>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    @if (Auth::check())
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                    @endif
                </div>
                
                <!-- Mobile menu button -->
                <button class="d-md-none btn" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav">
                    <i class="fas fa-bars"></i>
                </button>
            </nav>
            
            <!-- Mobile navigation -->
            <div class="collapse d-md-none" id="mobileNav">
                <div class="d-flex flex-column gap-3 mt-3 pt-3 border-top">
                    <a href="#home" class="nav-link">Home</a>
                    <a href="#services" class="nav-link">Services</a>
                    <a href="#calculator" class="nav-link">Calculator</a>
                    <a href="#apply" class="nav-link">Apply</a>
                    <a href="#contact" class="nav-link">Contact</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero gradient-bg">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1>Get Your Dream Product<br><span class="text-yellow">Today!</span></h1>
                        <p>Instant consumer loans up to ₹50,000 for mobiles, TVs, and motorcycles. Fast approval, easy EMIs, zero hidden charges.</p>
                        
                        <div class="d-flex flex-column flex-sm-row gap-3 mb-4">
                            <a href="#apply" class="btn btn-secondary btn-lg">
                                <i class="fas fa-rocket me-2"></i>
                                Apply for Loan
                            </a>
                            <a href="#calculator" class="btn btn-outline btn-lg">
                                <i class="fas fa-calculator me-2"></i>
                                EMI Calculator
                            </a>
                        </div>
                        
                        <div class="row text-center mt-4">
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 class="text-yellow mb-1">₹50K</h3>
                                    <small class="opacity-75">Max Loan</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 class="text-yellow mb-1">2 Min</h3>
                                    <small class="opacity-75">Approval</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-item">
                                    <h3 class="text-yellow mb-1">0%</h3>
                                    <small class="opacity-75">Hidden Fees</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image text-center">
                        <div class="d-flex justify-content-center align-items-center gap-4">
                            <i class="fas fa-mobile-alt" style="font-size: 6rem; opacity: 0.8;"></i>
                            <i class="fas fa-tv" style="font-size: 5rem; opacity: 0.6;"></i>
                            <i class="fas fa-motorcycle" style="font-size: 6rem; opacity: 0.7;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- EMI Calculator -->
    <section class="section-padding bg-light" id="calculator">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-5">
                        <h2>EMI Calculator</h2>
                        <p class="lead">Calculate your monthly installments and plan your budget</p>
                    </div>
                    <div class="card shadow">
                        <div class="card-body p-4">
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
                                        <p class="h4 text-primary" id="monthlyEMI">₹4,375</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h5>Interest Rate</h5>
                                        <p class="h4 text-primary">5% p.m.</p>
                                    </div>
                                    <div class="col-md-4">
                                        <h5>Total Repayment</h5>
                                        <p class="h4 text-primary" id="totalRepayment">₹26,250</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="section-padding" id="how-it-works">
        <div class="container">
            <div class="text-center mb-5">
                <h2>How It Works</h2>
                <p class="lead">Get your loan in just 4 simple steps</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-laptop"></i>
                        </div>
                        <h4>1. Apply Online</h4>
                        <p>Fill in a quick application with your basic details in under 2 minutes.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4>2. Get Approved</h4>
                        <p>Instant eligibility check and approval based on your profile and requirements.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h4>3. Shop Your Product</h4>
                        <p>Use the loan to buy your dream mobile, TV, or motorcycle from any store.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h4>4. Repay in EMIs</h4>
                        <p>Manageable monthly installments that don't burden your pocket.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- What Can You Buy -->
    <section class="section-padding bg-light" id="products">
        <div class="container">
            <div class="text-center mb-5">
                <h2>What Can You Buy?</h2>
                <p class="lead">Finance your favorite products with our instant loans</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="product-card text-center p-4 border rounded bg-white">
                        <div class="mb-3">
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
                    <div class="product-card text-center p-4 border rounded bg-white">
                        <div class="mb-3">
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
                    <div class="product-card text-center p-4 border rounded bg-white">
                        <div class="mb-3">
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

    <!-- Features Section -->
    <section id="services" class="section-padding">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Why Choose MBC Finance?</h2>
                <p class="text-muted">We make borrowing simple, fast, and transparent with features designed for your convenience.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card text-center p-4 h-100">
                        <div class="feature-icon mb-3 mx-auto" style="background: hsl(var(--primary) / 0.1); color: hsl(var(--primary));">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h4 class="mb-3">Instant Approval</h4>
                        <p class="text-muted">Get approved within minutes with our AI-powered eligibility check. No waiting, no delays.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center p-4 h-100">
                        <div class="feature-icon mb-3 mx-auto" style="background: hsl(var(--secondary) / 0.1); color: hsl(var(--secondary));">
                            <i class="fas fa-coins"></i>
                        </div>
                        <h4 class="mb-3">Flexible Amounts</h4>
                        <p class="text-muted">Borrow anywhere from ₹5,000 to ₹50,000 based on your specific needs and eligibility.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center p-4 h-100">
                        <div class="feature-icon mb-3 mx-auto" style="background: hsl(var(--primary) / 0.1); color: hsl(var(--primary));">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h4 class="mb-3">Easy EMIs</h4>
                        <p class="text-muted">Choose from flexible repayment plans that fit your budget and lifestyle perfectly.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center p-4 h-100">
                        <div class="feature-icon mb-3 mx-auto" style="background: hsl(var(--secondary) / 0.1); color: hsl(var(--secondary));">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h4 class="mb-3">Zero Hidden Charges</h4>
                        <p class="text-muted">Complete transparency in pricing. What you see is exactly what you pay - no surprises.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center p-4 h-100">
                        <div class="feature-icon mb-3 mx-auto" style="background: hsl(var(--primary) / 0.1); color: hsl(var(--primary));">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h4 class="mb-3">Quick Disbursal</h4>
                        <p class="text-muted">Loan amount credited instantly to your account for immediate purchase of your dream product.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center p-4 h-100">
                        <div class="feature-icon mb-3 mx-auto" style="background: hsl(var(--secondary) / 0.1); color: hsl(var(--secondary));">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4 class="mb-3">Trusted Service</h4>
                        <p class="text-muted">Join thousands of satisfied customers who trust us for their consumer financing needs.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Application Form -->
    <section class="section-padding bg-light" id="apply-form">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-5">
                        <h2>Apply for Instant Loan</h2>
                        <p class="lead">Fill in your details to get started</p>
                    </div>
                    <div class="card shadow">
                        <div class="card-body p-4">
                            <form action="{{ route('front.apply') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Full Name *</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address *</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number *</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="loan_amount" class="form-label">Loan Amount *</label>
                                            <select class="form-control" id="loan_amount" name="loan_amount" required>
                                                <option value="">Select Amount</option>
                                                <option value="5000">₹5,000</option>
                                                <option value="10000">₹10,000</option>
                                                <option value="15000">₹15,000</option>
                                                <option value="20000">₹20,000</option>
                                                <option value="25000">₹25,000</option>
                                                <option value="30000">₹30,000</option>
                                                <option value="40000">₹40,000</option>
                                                <option value="50000">₹50,000</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="loan_purpose" class="form-label">Loan Purpose *</label>
                                            <select class="form-control" id="loan_purpose" name="loan_purpose" required>
                                                <option value="">Select Purpose</option>
                                                <option value="mobile">Mobile Phone</option>
                                                <option value="television">Television</option>
                                                <option value="motorcycle">Motorcycle</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">Apply Now</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>MBC Finance</h5>
                    <p>Making your dreams affordable with instant consumer loans. Fast, secure, and transparent financing solutions.</p>
                    <div class="social-links">
                        <a href="#" class="me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="#calculator">EMI Calculator</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Products</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">Mobile Loans</a></li>
                        <li><a href="#">TV Loans</a></li>
                        <li><a href="#">Motorcycle Loans</a></li>
                        <li><a href="#">Personal Loans</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone me-2"></i> +91 9876543210</li>
                        <li><i class="fas fa-envelope me-2"></i> info@mbcfinance.com</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> Mumbai, India</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; 2024 MBC Finance. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="me-3">Privacy Policy</a>
                    <a href="#" class="me-3">Terms of Service</a>
                    <a href="#">Support</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- EMI Calculator Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loanAmountSlider = document.getElementById('loanAmount');
            const loanTermSlider = document.getElementById('loanTerm');
            const loanAmountValue = document.getElementById('loanAmountValue');
            const loanTermValue = document.getElementById('loanTermValue');
            const monthlyEMI = document.getElementById('monthlyEMI');
            const totalRepayment = document.getElementById('totalRepayment');
            
            function calculateEMI() {
                const principal = parseInt(loanAmountSlider.value);
                const tenure = parseInt(loanTermSlider.value);
                const rate = 5; // 5% per month
                
                const monthlyRate = rate / 100;
                const emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, tenure)) / 
                           (Math.pow(1 + monthlyRate, tenure) - 1);
                const total = emi * tenure;
                
                loanAmountValue.textContent = '₹' + principal.toLocaleString();
                loanTermValue.textContent = tenure;
                monthlyEMI.textContent = '₹' + Math.round(emi).toLocaleString();
                totalRepayment.textContent = '₹' + Math.round(total).toLocaleString();
            }
            
            loanAmountSlider.addEventListener('input', calculateEMI);
            loanTermSlider.addEventListener('input', calculateEMI);
            
            // Initial calculation
            calculateEMI();
            
            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>