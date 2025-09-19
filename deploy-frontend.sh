#!/bin/bash

# MBC Finance - React Frontend Deployment Script
# This script builds your React app and deploys it to Laravel public directory

echo "🚀 MBC Finance Frontend Deployment Script"
echo "========================================"

# Configuration
REACT_PROJECT_DIR="FinanceFlow/FinanceFlow"
LARAVEL_PUBLIC_DIR="public/frontend"
BACKUP_DIR="public/frontend-backup-$(date +%Y%m%d-%H%M%S)"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if React project exists
if [ ! -d "$REACT_PROJECT_DIR" ]; then
    print_error "React project directory not found: $REACT_PROJECT_DIR"
    exit 1
fi

print_status "Found React project at: $REACT_PROJECT_DIR"

# Navigate to React project
cd "$REACT_PROJECT_DIR" || exit 1

# Check if package.json exists
if [ ! -f "package.json" ]; then
    print_error "package.json not found in React project"
    exit 1
fi

# Install dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    print_status "Installing React dependencies..."
    npm install
    if [ $? -ne 0 ]; then
        print_error "Failed to install dependencies"
        exit 1
    fi
    print_success "Dependencies installed"
fi

# Build the React application
print_status "Building React application..."
npm run build
if [ $? -ne 0 ]; then
    print_error "React build failed"
    exit 1
fi
print_success "React build completed"

# Navigate back to Laravel root
cd - > /dev/null

# Backup existing frontend if it exists
if [ -d "$LARAVEL_PUBLIC_DIR" ]; then
    print_status "Backing up existing frontend to: $BACKUP_DIR"
    sudo mv "$LARAVEL_PUBLIC_DIR" "$BACKUP_DIR"
    print_success "Backup created"
fi

# Copy built files to Laravel public directory
print_status "Deploying React build to Laravel public directory..."
sudo mkdir -p "$LARAVEL_PUBLIC_DIR"
sudo cp -r "$REACT_PROJECT_DIR/dist/public/"* "$LARAVEL_PUBLIC_DIR/"

# Set proper permissions
sudo chown -R www-data:www-data "$LARAVEL_PUBLIC_DIR"
sudo chmod -R 755 "$LARAVEL_PUBLIC_DIR"

print_success "Frontend deployed successfully!"

# Test if files were copied correctly
if [ -f "$LARAVEL_PUBLIC_DIR/index.html" ]; then
    print_success "index.html found in deployment location"
else
    print_error "index.html not found in deployment location"
fi

if [ -d "$LARAVEL_PUBLIC_DIR/assets" ]; then
    ASSET_COUNT=$(ls -1 "$LARAVEL_PUBLIC_DIR/assets" | wc -l)
    print_success "Assets directory found with $ASSET_COUNT files"
else
    print_warning "Assets directory not found"
fi

echo ""
echo "🎉 Deployment Complete!"
echo "========================================"
echo "Your React frontend has been deployed to: $LARAVEL_PUBLIC_DIR"
echo "The website should now serve your React app from: https://fix.mbcfinserv.com/"
echo ""
echo "What happens now:"
echo "✅ Laravel will serve your React app from public/frontend/"
echo "✅ Your React development files remain untouched in FinanceFlow/"
echo "✅ You can continue developing in FinanceFlow/ without affecting the live site"
echo "✅ Run this script again anytime to deploy updates"
echo ""
echo "To continue React development:"
echo "cd $REACT_PROJECT_DIR && npm run dev"
echo ""
print_status "Happy coding! 🚀"