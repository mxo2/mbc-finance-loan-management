#!/bin/bash

# =============================================================================
# MBC Finance - Database Export Script
# =============================================================================
# This script exports the complete database with all customizations and data
# for deployment to production server
# =============================================================================

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
EXPORT_DIR="database_export_${TIMESTAMP}"
LOG_FILE="database_export_${TIMESTAMP}.log"

echo -e "${BLUE}==============================================================================${NC}"
echo -e "${BLUE}🗄️ MBC Finance - Database Export Script${NC}"
echo -e "${BLUE}==============================================================================${NC}"
echo -e "${GREEN}📅 Started at: $(date)${NC}"
echo ""

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Function to load environment variables
load_env() {
    if [ -f ".env" ]; then
        export $(cat .env | grep -v '^#' | grep -v '^$' | xargs)
        echo -e "${GREEN}✅ Environment variables loaded${NC}"
        log "Environment variables loaded from .env"
    else
        echo -e "${RED}❌ .env file not found${NC}"
        exit 1
    fi
}

# Function to create export directory
create_export_dir() {
    mkdir -p "$EXPORT_DIR"
    echo -e "${GREEN}✅ Export directory created: ${EXPORT_DIR}${NC}"
    log "Export directory created: ${EXPORT_DIR}"
}

# Function to export database structure
export_structure() {
    echo -e "${YELLOW}📋 Exporting database structure...${NC}"
    
    mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
        --no-data \
        --routines \
        --triggers \
        --single-transaction \
        "$DB_DATABASE" > "${EXPORT_DIR}/01_structure.sql"
    
    echo -e "${GREEN}✅ Database structure exported${NC}"
    log "Database structure exported to 01_structure.sql"
}

# Function to export core data
export_core_data() {
    echo -e "${YELLOW}👥 Exporting core system data...${NC}"
    
    # Export users, roles, and permissions
    mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
        --no-create-info \
        --single-transaction \
        "$DB_DATABASE" \
        users roles permissions role_has_permissions model_has_roles model_has_permissions \
        > "${EXPORT_DIR}/02_users_and_permissions.sql"
    
    echo -e "${GREEN}✅ Users and permissions exported${NC}"
    log "Users and permissions exported to 02_users_and_permissions.sql"
}

# Function to export settings and configuration
export_settings() {
    echo -e "${YELLOW}⚙️ Exporting settings and configuration...${NC}"
    
    # Export settings, home pages, and CMS content
    mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
        --no-create-info \
        --single-transaction \
        "$DB_DATABASE" \
        settings home_pages pages faqs auth_pages \
        > "${EXPORT_DIR}/03_settings_and_cms.sql"
    
    echo -e "${GREEN}✅ Settings and CMS content exported${NC}"
    log "Settings and CMS content exported to 03_settings_and_cms.sql"
}

# Function to export loan system data
export_loan_data() {
    echo -e "${YELLOW}💰 Exporting loan system data...${NC}"
    
    # Export loan types, branches, and related configuration
    mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
        --no-create-info \
        --single-transaction \
        "$DB_DATABASE" \
        loan_types branches document_types account_types \
        > "${EXPORT_DIR}/04_loan_system_config.sql"
    
    echo -e "${GREEN}✅ Loan system configuration exported${NC}"
    log "Loan system configuration exported to 04_loan_system_config.sql"
}

# Function to export customer and transaction data
export_business_data() {
    echo -e "${YELLOW}👤 Exporting customer and business data...${NC}"
    
    # Export customers, loans, and transactions (if any)
    mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
        --no-create-info \
        --single-transaction \
        "$DB_DATABASE" \
        customers loans repayments repayment_schedules \
        accounts transactions expenses documents loan_documents \
        > "${EXPORT_DIR}/05_business_data.sql"
    
    echo -e "${GREEN}✅ Customer and business data exported${NC}"
    log "Customer and business data exported to 05_business_data.sql"
}

# Function to export additional data
export_additional_data() {
    echo -e "${YELLOW}📊 Exporting additional system data...${NC}"
    
    # Export notifications, logs, and other system data
    mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
        --no-create-info \
        --single-transaction \
        "$DB_DATABASE" \
        notifications logged_histories coupons coupon_histories \
        contacts notice_boards subscriptions package_transactions \
        > "${EXPORT_DIR}/06_additional_data.sql"
    
    echo -e "${GREEN}✅ Additional system data exported${NC}"
    log "Additional system data exported to 06_additional_data.sql"
}

# Function to create complete backup
create_complete_backup() {
    echo -e "${YELLOW}💾 Creating complete database backup...${NC}"
    
    mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
        --single-transaction \
        --routines \
        --triggers \
        "$DB_DATABASE" > "${EXPORT_DIR}/complete_backup.sql"
    
    echo -e "${GREEN}✅ Complete database backup created${NC}"
    log "Complete database backup created as complete_backup.sql"
}

# Function to create import script
create_import_script() {
    echo -e "${YELLOW}📝 Creating import script...${NC}"
    
    cat > "${EXPORT_DIR}/import_database.sh" << 'EOF'
#!/bin/bash

# =============================================================================
# MBC Finance - Database Import Script
# =============================================================================
# This script imports the exported database to your production server
# =============================================================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}==============================================================================${NC}"
echo -e "${BLUE}📥 MBC Finance - Database Import Script${NC}"
echo -e "${BLUE}==============================================================================${NC}"

# Check if .env file exists
if [ ! -f "../.env" ]; then
    echo -e "${RED}❌ .env file not found in parent directory${NC}"
    echo -e "${YELLOW}Please ensure you're running this script from the database export directory${NC}"
    exit 1
fi

# Load environment variables
export $(cat ../.env | grep -v '^#' | grep -v '^$' | xargs)

echo -e "${YELLOW}🔍 Database Configuration:${NC}"
echo -e "${BLUE}Host: ${DB_HOST}${NC}"
echo -e "${BLUE}Port: ${DB_PORT}${NC}"
echo -e "${BLUE}Database: ${DB_DATABASE}${NC}"
echo -e "${BLUE}Username: ${DB_USERNAME}${NC}"
echo ""

# Confirm import
read -p "$(echo -e "${YELLOW}Are you sure you want to import the database? This will overwrite existing data! (y/N): ${NC}")" -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${RED}Import cancelled${NC}"
    exit 1
fi

echo -e "${YELLOW}📥 Starting database import...${NC}"

# Method 1: Import step by step (recommended)
echo -e "${BLUE}Choose import method:${NC}"
echo -e "${BLUE}1. Step-by-step import (recommended)${NC}"
echo -e "${BLUE}2. Complete backup import${NC}"
read -p "$(echo -e "${YELLOW}Enter your choice (1 or 2): ${NC}")" choice

if [ "$choice" = "1" ]; then
    echo -e "${YELLOW}📋 Importing database structure...${NC}"
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < 01_structure.sql
    echo -e "${GREEN}✅ Database structure imported${NC}"
    
    echo -e "${YELLOW}👥 Importing users and permissions...${NC}"
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < 02_users_and_permissions.sql
    echo -e "${GREEN}✅ Users and permissions imported${NC}"
    
    echo -e "${YELLOW}⚙️ Importing settings and CMS...${NC}"
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < 03_settings_and_cms.sql
    echo -e "${GREEN}✅ Settings and CMS imported${NC}"
    
    echo -e "${YELLOW}💰 Importing loan system configuration...${NC}"
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < 04_loan_system_config.sql
    echo -e "${GREEN}✅ Loan system configuration imported${NC}"
    
    echo -e "${YELLOW}👤 Importing business data...${NC}"
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < 05_business_data.sql
    echo -e "${GREEN}✅ Business data imported${NC}"
    
    echo -e "${YELLOW}📊 Importing additional data...${NC}"
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < 06_additional_data.sql
    echo -e "${GREEN}✅ Additional data imported${NC}"
    
elif [ "$choice" = "2" ]; then
    echo -e "${YELLOW}💾 Importing complete backup...${NC}"
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < complete_backup.sql
    echo -e "${GREEN}✅ Complete backup imported${NC}"
else
    echo -e "${RED}Invalid choice. Exiting.${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}==============================================================================${NC}"
echo -e "${GREEN}🎉 DATABASE IMPORT COMPLETED SUCCESSFULLY! 🎉${NC}"
echo -e "${GREEN}==============================================================================${NC}"
echo -e "${GREEN}📅 Completed at: $(date)${NC}"
echo ""
echo -e "${YELLOW}🔗 Next Steps:${NC}"
echo -e "${BLUE}1. Run 'php artisan migrate' to ensure all migrations are applied${NC}"
echo -e "${BLUE}2. Clear application cache: 'php artisan cache:clear'${NC}"
echo -e "${BLUE}3. Test the application functionality${NC}"
echo -e "${BLUE}4. Update admin credentials if needed${NC}"
echo ""
EOF

    chmod +x "${EXPORT_DIR}/import_database.sh"
    echo -e "${GREEN}✅ Import script created${NC}"
    log "Import script created as import_database.sh"
}

# Function to create export summary
create_export_summary() {
    echo -e "${YELLOW}📋 Creating export summary...${NC}"
    
    cat > "${EXPORT_DIR}/README.md" << EOF
# MBC Finance - Database Export

**Export Date:** $(date)
**Export ID:** ${TIMESTAMP}
**Database:** ${DB_DATABASE}

## 📁 Export Contents

### 🗄️ Database Files

1. **01_structure.sql** - Complete database structure (tables, indexes, constraints)
2. **02_users_and_permissions.sql** - User accounts, roles, and permissions
3. **03_settings_and_cms.sql** - System settings, homepage content, CMS pages
4. **04_loan_system_config.sql** - Loan types, branches, document types
5. **05_business_data.sql** - Customers, loans, transactions, documents
6. **06_additional_data.sql** - Notifications, logs, coupons, contacts
7. **complete_backup.sql** - Full database backup (alternative import option)

### 🛠️ Import Tools

- **import_database.sh** - Automated import script for production deployment
- **README.md** - This documentation file

## 🚀 How to Import on Production Server

### Prerequisites

1. MySQL/MariaDB server installed and running
2. Database created: \`CREATE DATABASE mbc_finance;\`
3. Database user with full privileges
4. Laravel application deployed with correct .env configuration

### Import Steps

#### Option 1: Using Import Script (Recommended)

\`\`\`bash
# 1. Upload this entire export directory to your server
# 2. Navigate to the export directory
cd database_export_${TIMESTAMP}

# 3. Make import script executable
chmod +x import_database.sh

# 4. Run the import script
./import_database.sh
\`\`\`

#### Option 2: Manual Import

\`\`\`bash
# Import structure
mysql -u username -p database_name < 01_structure.sql

# Import data (in order)
mysql -u username -p database_name < 02_users_and_permissions.sql
mysql -u username -p database_name < 03_settings_and_cms.sql
mysql -u username -p database_name < 04_loan_system_config.sql
mysql -u username -p database_name < 05_business_data.sql
mysql -u username -p database_name < 06_additional_data.sql
\`\`\`

#### Option 3: Complete Backup Import

\`\`\`bash
# Import everything at once
mysql -u username -p database_name < complete_backup.sql
\`\`\`

## 🔧 Post-Import Steps

1. **Run Laravel migrations** (to ensure all migrations are marked as run):
   \`\`\`bash
   php artisan migrate --force
   \`\`\`

2. **Clear application cache**:
   \`\`\`bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   \`\`\`

3. **Generate application key** (if not already set):
   \`\`\`bash
   php artisan key:generate --force
   \`\`\`

4. **Create storage link**:
   \`\`\`bash
   php artisan storage:link
   \`\`\`

5. **Set proper permissions**:
   \`\`\`bash
   chmod -R 775 storage bootstrap/cache
   \`\`\`

## 📊 Database Statistics

EOF

    # Add table counts to summary
    echo "### Table Counts" >> "${EXPORT_DIR}/README.md"
    echo "" >> "${EXPORT_DIR}/README.md"
    
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" \
        -e "SELECT table_name as 'Table', table_rows as 'Rows' FROM information_schema.tables WHERE table_schema = '$DB_DATABASE' ORDER BY table_name;" \
        >> "${EXPORT_DIR}/README.md"
    
    cat >> "${EXPORT_DIR}/README.md" << EOF

## 🎨 MBC Finance Customizations Included

### Frontend Customizations
- ✅ MBC Finance branding (replaced Bajaj references)
- ✅ Custom hero banners with switching functionality
- ✅ Upcoming features section (12 services)
- ✅ Consumer brands showcase with circular logos
- ✅ Mobile app interface mockup
- ✅ Responsive design improvements
- ✅ Modern UI components and animations

### Backend Customizations
- ✅ Complete loan management system
- ✅ Customer management
- ✅ Document handling
- ✅ Payment tracking
- ✅ Admin dashboard
- ✅ API endpoints for frontend integration

## 🔐 Default Credentials

**Admin Account:**
- Check the users table for admin credentials
- Default admin email is typically set during seeding
- **Important:** Change default passwords after import!

## 📞 Support

If you encounter any issues during import:
1. Check database connection settings in .env
2. Ensure database user has sufficient privileges
3. Verify MySQL/MariaDB version compatibility
4. Check server logs for detailed error messages

---

**Export completed successfully!** 🎉

This export contains all your MBC Finance customizations and data.
EOF

    echo -e "${GREEN}✅ Export summary created${NC}"
    log "Export summary created as README.md"
}

# Function to create archive
create_archive() {
    echo -e "${YELLOW}📦 Creating compressed archive...${NC}"
    
    tar -czf "${EXPORT_DIR}.tar.gz" "$EXPORT_DIR"
    
    echo -e "${GREEN}✅ Archive created: ${EXPORT_DIR}.tar.gz${NC}"
    log "Archive created: ${EXPORT_DIR}.tar.gz"
    
    # Show archive size
    ARCHIVE_SIZE=$(du -h "${EXPORT_DIR}.tar.gz" | cut -f1)
    echo -e "${BLUE}📊 Archive size: ${ARCHIVE_SIZE}${NC}"
    log "Archive size: ${ARCHIVE_SIZE}"
}

# Main export function
main() {
    echo -e "${BLUE}Starting database export process...${NC}"
    log "Database export started"
    
    # Load environment
    load_env
    
    # Create export directory
    create_export_dir
    
    # Export database components
    export_structure
    export_core_data
    export_settings
    export_loan_data
    export_business_data
    export_additional_data
    
    # Create complete backup
    create_complete_backup
    
    # Create import script
    create_import_script
    
    # Create documentation
    create_export_summary
    
    # Create archive
    create_archive
    
    echo ""
    echo -e "${GREEN}==============================================================================${NC}"
    echo -e "${GREEN}🎉 DATABASE EXPORT COMPLETED SUCCESSFULLY! 🎉${NC}"
    echo -e "${GREEN}==============================================================================${NC}"
    echo -e "${GREEN}📅 Completed at: $(date)${NC}"
    echo -e "${GREEN}📁 Export directory: ${EXPORT_DIR}${NC}"
    echo -e "${GREEN}📦 Archive file: ${EXPORT_DIR}.tar.gz${NC}"
    echo -e "${GREEN}📝 Log file: ${LOG_FILE}${NC}"
    echo ""
    echo -e "${YELLOW}🚀 Ready for Production Deployment:${NC}"
    echo -e "${BLUE}1. Upload ${EXPORT_DIR}.tar.gz to your production server${NC}"
    echo -e "${BLUE}2. Extract: tar -xzf ${EXPORT_DIR}.tar.gz${NC}"
    echo -e "${BLUE}3. Run: cd ${EXPORT_DIR} && ./import_database.sh${NC}"
    echo -e "${BLUE}4. Follow the post-import steps in README.md${NC}"
    echo ""
    echo -e "${GREEN}🗄️ Your complete MBC Finance database is ready for deployment! 🚀${NC}"
    
    log "Database export completed successfully"
}

# Run main function
main "$@"