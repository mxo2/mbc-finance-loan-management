# MBC Finance Loan Application Page - Installation Guide

This guide provides instructions for integrating the modern loan application page with interactive calculator into your MBC Finance application.

## Files Overview

1. **Blade Template**:
   - `resources/views/loan/application.blade.php` - Main view file for the loan application page

2. **CSS Files**:
   - `public/css/loan-application.css` - Main styles for the loan application page
   - `public/css/loan-application-responsive.css` - Responsive styles for various device sizes

3. **JavaScript Files**:
   - `public/js/loan-calculator.js` - Core calculator functionality
   - `public/js/loan-application-animations.js` - Animations and UI enhancements

4. **Controller**:
   - `app/Http/Controllers/LoanApplicationController.php` - Controller for handling the loan application

5. **Routes**:
   - Routes to be added to your web.php file (see routes-to-add.php)

## Installation Steps

### 1. Copy Files to Their Locations

Ensure all files are copied to their correct locations in your project structure.

### 2. Add Required Routes

Add the following routes to your `routes/web.php` file:

```php
// Loan Application routes
Route::get('/loans/apply', [App\Http\Controllers\LoanApplicationController::class, 'index'])->name('loan.application');
Route::post('/loans/apply/submit', [App\Http\Controllers\LoanApplicationController::class, 'apply'])->name('loan.apply');
Route::post('/loans/calculator-data', [App\Http\Controllers\LoanApplicationController::class, 'getCalculatorData'])->name('loan.calculator.data');
```

### 3. Update Your Layout File

Add the following CSS and JavaScript links to your main layout file (`resources/views/layouts/app.blade.php`):

```html
<!-- In the head section -->
<link rel="stylesheet" href="{{ asset('css/loan-application.css') }}">
<link rel="stylesheet" href="{{ asset('css/loan-application-responsive.css') }}">

<!-- Before closing body tag -->
<script src="{{ asset('js/loan-calculator.js') }}"></script>
<script src="{{ asset('js/loan-application-animations.js') }}"></script>
```

### 4. Add Required Assets

1. You'll need a loan hero image. Create or download an SVG/PNG image and place it at:
   - `public/img/loan-hero-image.svg`

2. Ensure Font Awesome is included in your project for the icons, or replace with your preferred icon set.

### 5. Update Navigation

Add a link to the loan application page in your navigation menu:

```html
<a class="nav-link" href="{{ route('loan.application') }}">Apply for Loan</a>
```

## Customization Options

### Colors and Branding

1. Modify the color variables in `loan-application.css` to match your brand:

```css
:root {
    --primary-color: #1a73e8;     /* MBC Blue - Change to your brand color */
    --primary-dark: #0d47a1;      /* Darker shade */
    --primary-light: #e8f0fe;     /* Lighter shade */
    /* ... other colors */
}
```

### Loan Parameters

1. Update the default loan parameters in the controller (`LoanApplicationController.php`):

```php
$defaults = [
    'min_loan_amount' => 10000,   // Change to your minimum loan amount
    'max_loan_amount' => 30000,   // Change to your maximum loan amount
    'default_loan_amount' => 20000, // Default selected amount
    'interest_rate' => 18,        // Default interest rate
    'interest_type' => 'fixed',   // Interest type
    'max_loan_term' => 96,        // Maximum loan term in months
    'penalties' => 1              // Penalty percentage
];
```

2. Update the corresponding HTML attributes in the blade template:

```html
<input type="range" class="form-range" id="loan-amount-slider" min="10000" max="30000" step="500" value="20000">
```

### Content

1. Modify the text content in `application.blade.php` to match your loan offerings and company messaging.

2. Update the FAQ section with your specific loan-related questions and answers.

## Notes

- The page uses responsive design and will adapt to all device sizes.
- JavaScript functionality requires a modern browser.
- The loan calculator uses a standard EMI formula for calculations.