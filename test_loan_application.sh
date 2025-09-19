#!/bin/bash

# This script tests if loan types are visible for the test user
# and verifies the modern loan application page is working

echo "Testing Modern Loan Application Page and Permissions"
echo "---------------------------------------------------"

# Check if the user is logged in
curl -s -c cookies.txt https://fix.mbcfinserv.com/login > /dev/null

# Attempt to login as test user
echo "Logging in as test user..."
curl -s -b cookies.txt -c cookies.txt https://fix.mbcfinserv.com/login \
  -d "email=testuser@example.com" \
  -d "password=testpassword" \
  -d "_token=$(grep XSRF-TOKEN cookies.txt | cut -f 7)" > /dev/null

# Check the loan application page
echo "Accessing modern loan application page..."
RESULT=$(curl -s -b cookies.txt https://fix.mbcfinserv.com/loan/application)

# Check if the page contains loan types
if echo "$RESULT" | grep -q "loan-type"; then
    echo "✅ SUCCESS: Loan types are visible to the test user"
else
    echo "❌ ERROR: Loan types are not visible to the test user"
fi

# Check if the calculator is present
if echo "$RESULT" | grep -q "calculator-section"; then
    echo "✅ SUCCESS: Loan calculator is working"
else
    echo "❌ ERROR: Loan calculator is not loading properly"
fi

# Check if the application form is present
if echo "$RESULT" | grep -q "application-form"; then
    echo "✅ SUCCESS: Loan application form is available"
else
    echo "❌ ERROR: Loan application form is not loading properly"
fi

# Clean up
rm cookies.txt

echo ""
echo "Test completed. Please manually verify the loan application page at:"
echo "https://fix.mbcfinserv.com/loan/application"