// Add these routes to your web.php routes file

// Loan Application routes
Route::get('/loans/apply', [App\Http\Controllers\LoanApplicationController::class, 'index'])->name('loan.application');
Route::post('/loans/apply/submit', [App\Http\Controllers\LoanApplicationController::class, 'apply'])->name('loan.apply');
Route::post('/loans/calculator-data', [App\Http\Controllers\LoanApplicationController::class, 'getCalculatorData'])->name('loan.calculator.data');