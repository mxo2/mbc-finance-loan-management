#!/bin/bash

# =============================================================================
# MBC Finance - Production Deployment Script
# =============================================================================
# This script deploys the complete MBC Finance system with all customizations
# to your production server including database setup and data migration.
# =============================================================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="MBC Finance"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="backups"
LOG_FILE="deployment_${TIMESTAMP}.log"

echo -e "${BLUE}==============================================================================${NC}"
echo -e "${BLUE}🚀 ${PROJECT_NAME} - Production Deployment Script${NC}"
echo -e "${BLUE}==============================================================================${NC}"
echo -e "${GREEN}📅 Started at: $(date)${NC}"
echo -e "${GREEN}📝 Log file: ${LOG_FILE}${NC}"
echo ""

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to create backup
create_backup() {
    echo -e "${YELLOW}📦 Creating backup...${NC}"
    mkdir -p "$BACKUP_DIR"
    
    if [ -f ".env" ]; then
        cp .env "${BACKUP_DIR}/.env.backup.${TIMESTAMP}"
        log "✅ Environment file backed up"
    fi
    
    if [ -d "storage" ]; then
        tar -czf "${BACKUP_DIR}/storage_backup_${TIMESTAMP}.tar.gz" storage/
        log "✅ Storage directory backed up"
    fi
    
    if [ -d "public/upload" ]; then
        tar -czf "${BACKUP_DIR}/uploads_backup_${TIMESTAMP}.tar.gz" public/upload/
        log "✅ Upload directory backed up"
    fi
}

# Function to check system requirements
check_requirements() {
    echo -e "${YELLOW}🔍 Checking system requirements...${NC}"
    
    # Check PHP
    if command_exists php; then
        PHP_VERSION=$(php -v | head -n1 | cut -d' ' -f2 | cut -d'.' -f1,2)
        echo -e "${GREEN}✅ PHP ${PHP_VERSION} found${NC}"
        log "PHP version: ${PHP_VERSION}"
    else
        echo -e "${RED}❌ PHP not found. Please install PHP 8.1 or higher${NC}"
        exit 1
    fi
    
    # Check Composer
    if command_exists composer; then
        COMPOSER_VERSION=$(composer --version | cut -d' ' -f3)
        echo -e "${GREEN}✅ Composer ${COMPOSER_VERSION} found${NC}"
        log "Composer version: ${COMPOSER_VERSION}"
    else
        echo -e "${RED}❌ Composer not found. Please install Composer${NC}"
        exit 1
    fi
    
    # Check Node.js
    if command_exists node; then
        NODE_VERSION=$(node --version)
        echo -e "${GREEN}✅ Node.js ${NODE_VERSION} found${NC}"
        log "Node.js version: ${NODE_VERSION}"
    else
        echo -e "${RED}❌ Node.js not found. Please install Node.js 18+${NC}"
        exit 1
    fi
    
    # Check npm
    if command_exists npm; then
        NPM_VERSION=$(npm --version)
        echo -e "${GREEN}✅ npm ${NPM_VERSION} found${NC}"
        log "npm version: ${NPM_VERSION}"
    else
        echo -e "${RED}❌ npm not found. Please install npm${NC}"
        exit 1
    fi
}

# Function to setup environment
setup_environment() {
    echo -e "${YELLOW}⚙️ Setting up environment...${NC}"
    
    if [ ! -f ".env" ]; then
        if [ -f ".env.example" ]; then
            cp .env.example .env
            echo -e "${GREEN}✅ Environment file created from example${NC}"
            log "Environment file created"
        else
            echo -e "${RED}❌ No .env.example found${NC}"
            exit 1
        fi
    else
        echo -e "${GREEN}✅ Environment file already exists${NC}"
    fi
    
    # Generate application key if not set
    if ! grep -q "APP_KEY=base64:" .env; then
        php artisan key:generate --force
        echo -e "${GREEN}✅ Application key generated${NC}"
        log "Application key generated"
    fi
}

# Function to install dependencies
install_dependencies() {
    echo -e "${YELLOW}📦 Installing dependencies...${NC}"
    
    # Install PHP dependencies
    echo -e "${BLUE}Installing PHP dependencies...${NC}"
    composer install --optimize-autoloader --no-dev
    log "✅ PHP dependencies installed"
    
    # Install Node.js dependencies for FinanceFlow
    if [ -d "FinanceFlow" ]; then
        echo -e "${BLUE}Installing FinanceFlow dependencies...${NC}"
        cd FinanceFlow
        npm install
        npm run build
        cd ..
        log "✅ FinanceFlow dependencies installed and built"
    fi
    
    # Install Node.js dependencies for LoanApp-PWA
    if [ -d "LoanApp-PWA" ]; then
        echo -e "${BLUE}Installing LoanApp-PWA dependencies...${NC}"
        cd LoanApp-PWA
        npm install
        npm run build
        cd ..
        log "✅ LoanApp-PWA dependencies installed and built"
    fi
}

# Function to setup database
setup_database() {
    echo -e "${YELLOW}🗄️ Setting up database...${NC}"
    
    # Check database connection
    if php artisan migrate:status >/dev/null 2>&1; then
        echo -e "${GREEN}✅ Database connection successful${NC}"
        log "Database connection verified"
    else
        echo -e "${RED}❌ Database connection failed. Please check your .env configuration${NC}"
        echo -e "${YELLOW}Please ensure your database credentials are correct in .env file:${NC}"
        echo "DB_CONNECTION=mysql"
        echo "DB_HOST=127.0.0.1"
        echo "DB_PORT=3306"
        echo "DB_DATABASE=mbc_finance"
        echo "DB_USERNAME=your_username"
        echo "DB_PASSWORD=your_password"
        exit 1
    fi
    
    # Run migrations
    echo -e "${BLUE}Running database migrations...${NC}"
    php artisan migrate --force
    log "✅ Database migrations completed"
    
    # Seed database with default data
    echo -e "${BLUE}Seeding database with default data...${NC}"
    php artisan db:seed --force
    log "✅ Database seeded with default data"
}

# Function to setup storage and permissions
setup_storage() {
    echo -e "${YELLOW}📁 Setting up storage and permissions...${NC}"
    
    # Create storage link
    php artisan storage:link
    log "✅ Storage link created"
    
    # Set permissions
    chmod -R 775 storage bootstrap/cache
    log "✅ Storage permissions set"
    
    # Create upload directories
    mkdir -p public/upload/{logo,images,documents,profile,receipt}
    chmod -R 775 public/upload
    log "✅ Upload directories created"
}

# Function to optimize application
optimize_application() {
    echo -e "${YELLOW}⚡ Optimizing application...${NC}"
    
    # Clear and cache configurations
    php artisan config:clear
    php artisan config:cache
    log "✅ Configuration cached"
    
    # Clear and cache routes
    php artisan route:clear
    php artisan route:cache
    log "✅ Routes cached"
    
    # Clear and cache views
    php artisan view:clear
    php artisan view:cache
    log "✅ Views cached"
    
    # Optimize autoloader
    composer dump-autoload --optimize
    log "✅ Autoloader optimized"
}

# Function to create deployment summary
create_deployment_summary() {
    echo -e "${YELLOW}📋 Creating deployment summary...${NC}"
    
    SUMMARY_FILE="deployment_summary_${TIMESTAMP}.md"
    
    cat > "$SUMMARY_FILE" << EOF
# MBC Finance - Deployment Summary

**Deployment Date:** $(date)
**Deployment ID:** ${TIMESTAMP}

## 🚀 Components Deployed

### ✅ Backend (Laravel)
- **Framework:** Laravel 9.x
- **Database:** MySQL with 35+ tables
- **Features:** Complete loan management system
- **Admin Panel:** Full-featured admin dashboard
- **API:** RESTful APIs for frontend integration

### ✅ Frontend Applications

#### 🌐 FinanceFlow (Main Website)
- **Framework:** React + TypeScript + Vite
- **Styling:** Tailwind CSS
- **Features:** 
  - MBC Finance branding
  - Switching hero banners (Consumer & Personal loans)
  - Upcoming features section (12 services)
  - Consumer brands showcase
  - Mobile app interface mockup
  - Responsive design

#### 📱 LoanApp-PWA (Progressive Web App)
- **Framework:** React + TypeScript
- **Type:** Progressive Web Application
- **Features:**
  - Loan application forms
  - Customer dashboard
  - Payment tracking
  - Offline capability

## 🗄️ Database Structure

### Core Tables Created:
- **Users & Authentication:** users, password_resets, permissions
- **Loan Management:** loans, loan_types, repayments, repayment_schedules
- **Customer Data:** customers, documents, branches
- **Financial:** accounts, transactions, expenses
- **Content Management:** pages, faqs, settings, home_pages
- **System:** notifications, logs, coupons

### Default Data Seeded:
- ✅ Admin user account
- ✅ User roles and permissions
- ✅ Default loan types
- ✅ System settings
- ✅ Sample data for testing

## 🔧 Customizations Applied

### MBC Finance Branding:
- ✅ Logo replacement (Bajaj → MBC)
- ✅ Color scheme updates
- ✅ Brand messaging changes
- ✅ Custom hero banners
- ✅ Service offerings updates

### UI/UX Improvements:
- ✅ Modern card-based design
- ✅ Responsive layouts
- ✅ Interactive components
- ✅ Smooth animations
- ✅ Mobile-first approach

## 🌐 Access URLs

- **Main Website:** https://your-domain.com
- **Admin Panel:** https://your-domain.com/admin
- **PWA App:** https://your-domain.com/app
- **API Endpoints:** https://your-domain.com/api

## 🔐 Default Credentials

**Admin Account:**
- Email: admin@mbcfinance.com
- Password: [Check seeder file for default password]

⚠️ **Important:** Change default passwords immediately after deployment!

## 📋 Post-Deployment Checklist

- [ ] Update .env file with production settings
- [ ] Configure SSL certificate
- [ ] Set up email configuration
- [ ] Configure payment gateways
- [ ] Update admin credentials
- [ ] Test all functionality
- [ ] Set up monitoring and backups
- [ ] Configure domain and DNS

## 🛠️ Maintenance Commands

### Clear Cache:
\`\`\`bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
\`\`\`

### Update Application:
\`\`\`bash
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
\`\`\`

### Backup Database:
\`\`\`bash
mysqldump -u username -p database_name > backup_\$(date +%Y%m%d_%H%M%S).sql
\`\`\`

---

**Deployment completed successfully!** 🎉
EOF

    echo -e "${GREEN}✅ Deployment summary created: ${SUMMARY_FILE}${NC}"
    log "Deployment summary created"
}

# Main deployment function
main() {
    echo -e "${BLUE}Starting deployment process...${NC}"
    log "Deployment started"
    
    # Create backup
    create_backup
    
    # Check requirements
    check_requirements
    
    # Setup environment
    setup_environment
    
    # Install dependencies
    install_dependencies
    
    # Setup database
    setup_database
    
    # Setup storage
    setup_storage
    
    # Optimize application
    optimize_application
    
    # Create deployment summary
    create_deployment_summary
    
    echo ""
    echo -e "${GREEN}==============================================================================${NC}"
    echo -e "${GREEN}🎉 DEPLOYMENT COMPLETED SUCCESSFULLY! 🎉${NC}"
    echo -e "${GREEN}==============================================================================${NC}"
    echo -e "${GREEN}📅 Completed at: $(date)${NC}"
    echo -e "${GREEN}📝 Log file: ${LOG_FILE}${NC}"
    echo -e "${GREEN}📋 Summary: deployment_summary_${TIMESTAMP}.md${NC}"
    echo ""
    echo -e "${YELLOW}🔗 Next Steps:${NC}"
    echo -e "${BLUE}1. Configure your web server to point to the 'public' directory${NC}"
    echo -e "${BLUE}2. Update .env file with production database and mail settings${NC}"
    echo -e "${BLUE}3. Set up SSL certificate for HTTPS${NC}"
    echo -e "${BLUE}4. Test the application thoroughly${NC}"
    echo -e "${BLUE}5. Change default admin password${NC}"
    echo ""
    echo -e "${GREEN}🌐 Your MBC Finance system is ready for production! 🚀${NC}"
    
    log "Deployment completed successfully"
}

# Run main function
main "$@"