#!/bin/bash

# =============================================================================
# MBC Finance - Production Deployment Script
# =============================================================================
# This script deploys the complete MBC Finance system with all customizations
# to a production server including database setup and file uploads.
# =============================================================================

set -e  # Exit on any error

# Configuration
SERVER_USER="your_server_user"
SERVER_HOST="your_server_ip"
SERVER_PATH="/var/www/mbc-finance"
DB_NAME="mbc_finance"
DB_USER="your_db_user"
DB_PASS="your_db_password"
DOMAIN="your-domain.com"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}==============================================================================${NC}"
echo -e "${BLUE}                    MBC Finance - Production Deployment${NC}"
echo -e "${BLUE}==============================================================================${NC}"

# Function to print status
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if required tools are installed
check_requirements() {
    print_status "Checking requirements..."
    
    command -v rsync >/dev/null 2>&1 || { print_error "rsync is required but not installed. Aborting."; exit 1; }
    command -v ssh >/dev/null 2>&1 || { print_error "ssh is required but not installed. Aborting."; exit 1; }
    command -v mysql >/dev/null 2>&1 || { print_error "mysql client is required but not installed. Aborting."; exit 1; }
    
    print_status "All requirements satisfied."
}

# Create deployment package
create_deployment_package() {
    print_status "Creating deployment package..."
    
    # Create temporary deployment directory
    DEPLOY_DIR="/tmp/mbc-finance-deploy-$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$DEPLOY_DIR"
    
    # Copy Laravel backend
    print_status "Packaging Laravel backend..."
    rsync -av --exclude='node_modules' --exclude='.git' --exclude='storage/logs/*' \
          --exclude='bootstrap/cache/*' --exclude='.env' \
          ./ "$DEPLOY_DIR/backend/"
    
    # Copy FinanceFlow frontend
    print_status "Packaging FinanceFlow frontend..."
    if [ -d "FinanceFlow" ]; then
        rsync -av --exclude='node_modules' --exclude='.git' --exclude='dist' \
              FinanceFlow/ "$DEPLOY_DIR/frontend/"
    fi
    
    # Copy LoanApp PWA
    print_status "Packaging LoanApp PWA..."
    if [ -d "LoanApp-PWA" ]; then
        rsync -av --exclude='node_modules' --exclude='.git' --exclude='dist' \
              LoanApp-PWA/ "$DEPLOY_DIR/pwa/"
    fi
    
    echo "$DEPLOY_DIR"
}

# Generate database dump with current data
generate_database_dump() {
    print_status "Generating database dump..."
    
    # Create database dump directory
    mkdir -p "$DEPLOY_DIR/database"
    
    # Export current database structure and data
    if [ -f ".env" ]; then
        DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
        DB_PORT=$(grep DB_PORT .env | cut -d '=' -f2)
        DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2)
        DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
        DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)
        
        if [ ! -z "$DB_DATABASE" ]; then
            print_status "Exporting database: $DB_DATABASE"
            mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
                     "$DB_DATABASE" > "$DEPLOY_DIR/database/current_data.sql"
            print_status "Database dump created: current_data.sql"
        fi
    fi
    
    # Copy migration files
    cp -r database/migrations "$DEPLOY_DIR/database/"
    cp -r database/seeders "$DEPLOY_DIR/database/"
}

# Create deployment documentation
create_deployment_docs() {
    print_status "Creating deployment documentation..."
    
    cat > "$DEPLOY_DIR/DEPLOYMENT_NOTES.md" << EOF
# MBC Finance - Deployment Package

Generated on: $(date)
Deployment ID: $(basename $DEPLOY_DIR)

## Package Contents

### Backend (Laravel)
- Complete MBC Finance loan management system
- Database migrations and seeders
- All customizations and configurations
- Multi-language support (10+ languages)
- Role-based access control

### Frontend (FinanceFlow)
- Modern React TypeScript interface
- MBC Finance branding and customizations
- Switching hero banners (Consumer & Personal loans)
- Upcoming features section (12 services)
- Brand partners with circular logos
- Mobile app interface mockup
- Responsive Tailwind CSS design

### PWA (LoanApp)
- Progressive Web Application
- Mobile-first design
- Offline capabilities
- Push notifications support

## Key Customizations Made

### Frontend Customizations:
1. **Hero Section**: Added switching banners for Consumer loans (₹50K) and Personal loans (₹2L)
2. **Upcoming Features**: Created section with 12 MBC services and promotional cards
3. **Brand Partners**: Implemented consumer brands with circular logo design
4. **Mobile Interface**: Custom credit line app mockup with proper dimensions
5. **Product Categories**: Enhanced with real product images and descriptions
6. **MBC Branding**: Replaced all Bajaj references with MBC Finance branding
7. **Responsive Design**: Optimized for all device sizes with modern UI/UX

### Backend Features:
- Loan management system
- Customer management
- Repayment tracking
- Document management
- Financial reporting
- Multi-branch support
- API endpoints for PWA integration

## Database Schema

The system includes 35+ database tables:
- User management (users, roles, permissions)
- Loan system (loans, repayments, schedules, cycles)
- Customer data (customers, documents, branches)
- Financial (accounts, transactions, expenses)
- Content management (pages, FAQs, settings)
- System (notifications, logs, coupons)

## Deployment Instructions

### Server Requirements
- PHP 8.1+ with extensions (mysql, mbstring, xml, etc.)
- MySQL 5.7+ or MariaDB 10.3+
- Node.js 18.x+ and npm
- Web server (Apache/Nginx)
- SSL certificate (required for PWA)

### Installation Steps

1. **Upload Files**:
   \`\`\`bash
   rsync -av backend/ user@server:/var/www/mbc-finance/
   \`\`\`

2. **Install Dependencies**:
   \`\`\`bash
   cd /var/www/mbc-finance
   composer install --optimize-autoloader --no-dev
   \`\`\`

3. **Configure Environment**:
   \`\`\`bash
   cp .env.example .env
   php artisan key:generate
   # Edit .env with production settings
   \`\`\`

4. **Setup Database**:
   \`\`\`bash
   mysql -u root -p -e "CREATE DATABASE mbc_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   php artisan migrate
   php artisan db:seed
   \`\`\`

5. **Set Permissions**:
   \`\`\`bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   php artisan storage:link
   \`\`\`

6. **Build Frontend**:
   \`\`\`bash
   cd frontend && npm install && npm run build
   cd ../pwa && npm install && npm run build
   \`\`\`

## Production URLs
- Main Application: https://$DOMAIN
- Admin Dashboard: https://$DOMAIN/admin
- API Endpoints: https://$DOMAIN/api
- PWA: https://$DOMAIN/app

## Support
For deployment support, refer to INSTALLATION.md or contact the development team.
EOF

    print_status "Deployment documentation created."
}

# Create changes log
create_changes_log() {
    print_status "Creating changes log..."
    
    cat > "$DEPLOY_DIR/CHANGES_LOG.md" << EOF
# MBC Finance - Changes and Customizations Log

## Frontend Changes (FinanceFlow)

### 1. Hero Section Updates
- **File**: client/src/components/hero-section.tsx
- **Changes**: 
  - Added switching banner system with 5-second intervals
  - Banner 1: "Instant Consumer Loans up to ₹50,000"
  - Banner 2: "Personal Loan up to 2 lakh"
  - Dynamic content rendering with gradients and stats
  - Interactive banner indicators with click functionality

### 2. Upcoming Features Section
- **File**: client/src/components/upcoming-features-section.tsx
- **Changes**:
  - Created new section with 12 MBC services
  - Services: MBC Pay, UPI, EMI Card, EMI Store, MBC Prime, Credit Score, etc.
  - 3 promotional cards: Insta EMI Card, Fixed Deposits, Personal Loan
  - Modern card-based layout with hover effects

### 3. Consumer Brands Section
- **File**: client/src/components/consumer-brands-section.tsx
- **Changes**:
  - Implemented circular logo design with orange borders
  - Added brand partners: Samsung, Apple, LG, Sony, HP, Dell, etc.
  - Responsive grid layout with hover animations

### 4. Mobile App Section
- **File**: client/src/components/mobile-app-section.tsx
- **Changes**:
  - Custom credit line app interface mockup
  - Proper mobile dimensions (375x812px)
  - Status bar with time, signal, battery indicators
  - App header with back button and title
  - EMI cards with amounts and details
  - Credit balance section
  - Active periods display
  - Bottom navigation bar

### 5. Product Categories
- **File**: client/src/components/product-categories-section.tsx
- **Changes**:
  - Enhanced with real product images
  - Detailed product descriptions
  - Feature lists for each category
  - Improved styling and layout

### 6. Header and Footer Updates
- **Files**: 
  - client/src/components/header.tsx
  - client/src/components/footer.tsx
- **Changes**:
  - Updated MBC Finance branding
  - Replaced Bajaj references
  - Added MBC logo and contact information
  - Removed "Licensed by RBI" section

### 7. Home Page Integration
- **File**: client/src/pages/home.tsx
- **Changes**:
  - Added UpcomingFeaturesSection import and component
  - Integrated all new sections in proper order
  - Maintained responsive layout

## Backend Customizations

### 1. Database Schema
- **Location**: database/migrations/
- **Tables Created**: 35+ tables for complete loan management
- **Key Tables**:
  - loans, customers, repayments, repayment_schedules
  - loan_types, branches, documents, accounts
  - transactions, expenses, users, roles, permissions

### 2. Models and Controllers
- **Location**: app/Models/ and app/Http/Controllers/
- **Features**:
  - Complete CRUD operations for all entities
  - Role-based access control
  - API endpoints for PWA integration
  - Multi-language support

### 3. Configuration Updates
- **Files**: config/app.php, .env.example
- **Changes**:
  - Updated application name to "MBC Finance"
  - Configured database connections
  - Set up mail and cache configurations

## PWA Application (LoanApp)

### 1. Dashboard Interface
- **File**: src/pages/Home.tsx
- **Features**:
  - Loan overview cards
  - Quick actions menu
  - Recent transactions
  - Payment reminders

### 2. Loan Management
- **Files**: src/pages/ directory
- **Features**:
  - Loan application form
  - Repayment schedule view
  - Payment tracking
  - Document upload

## Assets and Branding

### 1. Logo Updates
- **Files**: 
  - client/public/logo_mbc.jpg
  - client/public/logo_mbc.png
- **Changes**: Added MBC Finance logos

### 2. Styling Updates
- **File**: client/src/index.css
- **Changes**: 
  - Updated color schemes
  - Added custom animations
  - Responsive design improvements

## Configuration Files

### 1. Build Configuration
- **Files**: 
  - vite.config.ts
  - tailwind.config.js
  - package.json
- **Changes**:
  - Optimized build settings
  - Updated dependencies
  - Added development scripts

### 2. Git Configuration
- **File**: .gitignore
- **Changes**: 
  - Added appropriate ignore patterns
  - Excluded build artifacts and dependencies

## Summary of Changes

- **Total Files Modified**: 15+ files
- **New Components Created**: 3 major components
- **Database Tables**: 35+ tables
- **Frontend Features**: 7 major sections updated/created
- **Branding**: Complete MBC Finance rebrand
- **Responsive Design**: Mobile-first approach
- **Modern UI/UX**: Tailwind CSS with animations

All changes maintain backward compatibility and follow best practices for maintainability and scalability.
EOF

    print_status "Changes log created."
}

# Create server setup script
create_server_setup_script() {
    print_status "Creating server setup script..."
    
    cat > "$DEPLOY_DIR/server-setup.sh" << 'EOF'
#!/bin/bash

# MBC Finance - Server Setup Script
# Run this script on the production server

set -e

print_status() {
    echo -e "\033[0;32m[INFO]\033[0m $1"
}

print_status "Setting up MBC Finance on production server..."

# Install PHP dependencies
print_status "Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev

# Setup environment
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
    print_status "Environment file created. Please configure database settings in .env"
fi

# Set permissions
print_status "Setting file permissions..."
chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache

# Create storage link
php artisan storage:link

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_status "Server setup completed!"
print_status "Next steps:"
print_status "1. Configure .env file with production settings"
print_status "2. Run: php artisan migrate"
print_status "3. Run: php artisan db:seed"
print_status "4. Build frontend applications"
EOF

    chmod +x "$DEPLOY_DIR/server-setup.sh"
}

# Upload to server
upload_to_server() {
    print_status "Uploading to production server..."
    
    # Create backup of existing installation
    ssh "$SERVER_USER@$SERVER_HOST" "if [ -d '$SERVER_PATH' ]; then mv '$SERVER_PATH' '$SERVER_PATH.backup.$(date +%Y%m%d_%H%M%S)'; fi"
    
    # Upload backend
    print_status "Uploading backend files..."
    rsync -avz --progress "$DEPLOY_DIR/backend/" "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/"
    
    # Upload frontend builds
    if [ -d "$DEPLOY_DIR/frontend" ]; then
        print_status "Uploading frontend files..."
        rsync -avz --progress "$DEPLOY_DIR/frontend/" "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/public/frontend/"
    fi
    
    if [ -d "$DEPLOY_DIR/pwa" ]; then
        print_status "Uploading PWA files..."
        rsync -avz --progress "$DEPLOY_DIR/pwa/" "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/public/pwa/"
    fi
    
    # Upload database files
    rsync -avz --progress "$DEPLOY_DIR/database/" "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/database_deploy/"
    
    # Upload setup script
    scp "$DEPLOY_DIR/server-setup.sh" "$SERVER_USER@$SERVER_HOST:$SERVER_PATH/"
    
    print_status "Upload completed!"
}

# Setup database on server
setup_remote_database() {
    print_status "Setting up database on server..."
    
    ssh "$SERVER_USER@$SERVER_HOST" << EOF
cd $SERVER_PATH

# Create database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import current data if available
if [ -f "database_deploy/current_data.sql" ]; then
    echo "Importing current database..."
    mysql -u $DB_USER -p$DB_PASS $DB_NAME < database_deploy/current_data.sql
else
    echo "Running fresh migrations..."
    php artisan migrate
    php artisan db:seed
fi

# Run server setup
./server-setup.sh
EOF
}

# Main deployment function
main() {
    print_status "Starting MBC Finance deployment..."
    
    # Check if configuration is set
    if [ "$SERVER_USER" = "your_server_user" ] || [ "$SERVER_HOST" = "your_server_ip" ]; then
        print_error "Please configure server settings at the top of this script."
        exit 1
    fi
    
    check_requirements
    
    DEPLOY_DIR=$(create_deployment_package)
    generate_database_dump
    create_deployment_docs
    create_changes_log
    create_server_setup_script
    
    print_status "Deployment package created at: $DEPLOY_DIR"
    
    # Ask for confirmation
    read -p "Do you want to upload to server now? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        upload_to_server
        
        read -p "Do you want to setup database on server? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            setup_remote_database
        fi
    fi
    
    print_status "Deployment completed!"
    print_status "Package location: $DEPLOY_DIR"
    print_status "Documentation: $DEPLOY_DIR/DEPLOYMENT_NOTES.md"
    print_status "Changes log: $DEPLOY_DIR/CHANGES_LOG.md"
    
    # Cleanup option
    read -p "Do you want to cleanup temporary files? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        rm -rf "$DEPLOY_DIR"
        print_status "Temporary files cleaned up."
    fi
}

# Run main function
main "$@"