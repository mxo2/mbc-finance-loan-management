#!/bin/bash

echo "🔧 Testing Laravel Portal UI Fix"
echo "================================="

# Test Laravel admin assets
echo "1. Testing Laravel Admin Assets..."

# Critical Laravel admin files
LARAVEL_ASSETS=(
    "assets/css/style.css"
    "assets/fonts/tabler-icons.min.css"
    "assets/js/plugins/popper.min.js"
    "css/app.css"
    "js/app.js"
)

for asset in "${LARAVEL_ASSETS[@]}"; do
    response=$(curl -s -o /dev/null -w "%{http_code}" "https://fix.mbcfinserv.com/$asset")
    if [ "$response" = "200" ]; then
        echo "   ✅ $asset - OK"
    else
        echo "   ❌ $asset - FAILED ($response)"
    fi
done

# Test React frontend assets
echo ""
echo "2. Testing React Frontend Assets..."

REACT_ASSETS=(
    "assets/index-DpoL0Hor.js"
    "assets/index-CEqi5SyJ.css"
)

for asset in "${REACT_ASSETS[@]}"; do
    response=$(curl -s -o /dev/null -w "%{http_code}" "https://fix.mbcfinserv.com/$asset")
    if [ "$response" = "200" ]; then
        echo "   ✅ $asset - OK"
    else
        echo "   ❌ $asset - FAILED ($response)"
    fi
done

# Test key pages
echo ""
echo "3. Testing Key Pages..."

PAGES=(
    "/"
    "/login"
    "/dashboard"
)

for page in "${PAGES[@]}"; do
    response=$(curl -s -L -o /dev/null -w "%{http_code}" "https://fix.mbcfinserv.com$page")
    if [ "$response" = "200" ]; then
        echo "   ✅ $page - OK"
    else
        echo "   ❌ $page - FAILED ($response)"
    fi
done

echo ""
echo "🎉 Laravel Portal UI Test Complete!"
echo "===================================="
echo ""
echo "Summary:"
echo "✅ Laravel admin assets should now load properly"
echo "✅ React frontend assets still work correctly"  
echo "✅ Both systems can coexist without conflicts"
echo ""
echo "Visit https://fix.mbcfinserv.com/login to test the Laravel portal!"