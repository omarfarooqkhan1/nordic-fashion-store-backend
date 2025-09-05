# Production Deployment Guide - Local Storage Migration

## Overview
This guide covers all the changes and steps required to deploy the local storage migration to production on Hostinger. The migration replaces Cloudinary with local image storage for cost savings and better control.

## Pre-Deployment Checklist

### 1. Code Changes Verification
- [x] LocalImageService implemented
- [x] ProductController updated
- [x] ProductReviewController updated
- [x] LocalStorageController created
- [x] API routes updated
- [x] Storage link created locally

### 2. Dependencies
Ensure these packages are installed:
```bash
composer require intervention/image
```

## Production Environment Setup

### 1. Environment Configuration
Update your production `.env` file with these settings:

```bash
# Application Settings
APP_NAME="Nordic Skin"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration (keep your existing settings)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_production_db
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# File Storage Settings
FILESYSTEM_DISK=public

# Image Processing Settings
IMAGE_DRIVER=gd
IMAGE_QUALITY=85

# Session and Cache
SESSION_DRIVER=database
CACHE_STORE=database
```

### 2. File Permissions Setup
Run these commands on your Hostinger server:

```bash
# Navigate to your application directory
cd /path/to/your/laravel/app

# Create storage link
php artisan storage:link

# Set proper permissions
chmod -R 755 storage/
chmod -R 755 storage/app/public/
chmod -R 755 storage/app/public/images/

# Create images directory structure
mkdir -p storage/app/public/images/products
mkdir -p storage/app/public/images/reviews
mkdir -p storage/app/public/images/blogs

# Set ownership (adjust user/group as needed)
chown -R www-data:www-data storage/
# OR for shared hosting:
chown -R your_username:your_username storage/
```

### 3. PHP Configuration
Update your PHP settings in Hostinger control panel or `php.ini`:

```ini
# File Upload Settings
upload_max_filesize = 20M
post_max_size = 25M
max_file_uploads = 20

# Execution Settings
max_execution_time = 300
memory_limit = 256M

# Image Processing
extension=gd
extension=imagick  # Optional, for better image processing

# OPcache for Performance
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
```

### 4. Web Server Configuration

#### For Apache (cPanel/Shared Hosting)
Add to your `.htaccess` file in the `public` directory:

```apache
# Allow access to storage files
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle storage files
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^storage/(.*)$ storage/$1 [L]
    
    # Standard Laravel routing
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

# Security for storage directory
<Directory "storage">
    Options -Indexes
    AllowOverride None
</Directory>
```

#### For Nginx (VPS/Dedicated)
Add to your nginx configuration:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/your/laravel/app/public;
    index index.php;

    # Handle storage files
    location /storage {
        alias /path/to/your/laravel/app/storage/app/public;
        try_files $uri $uri/ =404;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Standard Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Deployment Steps

### 1. Upload Code Changes
```bash
# Upload all modified files to production:
# - app/Services/LocalImageService.php
# - app/Http/Controllers/Api/ProductController.php
# - app/Http/Controllers/Api/ProductReviewController.php
# - app/Http/Controllers/Api/LocalStorageController.php
# - routes/api.php
# - config/image.php
```

### 2. Install Dependencies
```bash
# On your production server
composer install --no-dev --optimize-autoloader
```

### 3. Run Database Migrations
```bash
php artisan migrate --force
```

### 4. Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

### 5. Set Up Storage
```bash
# Create storage link
php artisan storage:link

# Verify storage link exists
ls -la public/storage
# Should show: storage -> ../storage/app/public
```

## Post-Deployment Testing

### 1. API Endpoint Tests
Test these endpoints to ensure they work:

```bash
# Test storage usage
curl -X GET https://yourdomain.com/api/local-storage/usage

# Test product image upload (use Postman or similar)
curl -X POST https://yourdomain.com/api/products/1/images \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "image=@test-image.jpg" \
  -F "alt_text=Test image"

# Test review image upload
curl -X POST https://yourdomain.com/api/products/1/reviews \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "rating=5" \
  -F "review_text=Great product!" \
  -F "media[]=@review-image.jpg"
```

### 2. Frontend Functionality Tests
- [ ] Upload product images from admin panel
- [ ] Upload review images from customer interface
- [ ] Test bulk product upload with images
- [ ] Verify images display correctly on frontend
- [ ] Test image deletion functionality

### 3. Storage Verification
```bash
# Check if images are being stored
ls -la storage/app/public/images/

# Check storage usage via API
curl https://yourdomain.com/api/local-storage/usage
```

## Maintenance & Monitoring

### 1. Automated Cleanup (Cron Job)
Set up a cron job to clean old images:

```bash
# Add to crontab (runs daily at 2 AM)
0 2 * * * cd /path/to/your/laravel/app && php artisan tinker --execute="app(\App\Services\LocalImageService::class)->cleanupOldImages(30);" >> /var/log/image-cleanup.log 2>&1
```

**Via Hostinger cPanel:**
1. Go to Cron Jobs in cPanel
2. Add new cron job:
   - Minute: 0
   - Hour: 2
   - Day: *
   - Month: *
   - Weekday: *
   - Command: `cd /path/to/your/app && php artisan tinker --execute="app(\App\Services\LocalImageService::class)->cleanupOldImages(30);"`

### 2. Storage Monitoring
Create a monitoring script to check storage usage:

```bash
#!/bin/bash
# storage-monitor.sh
cd /path/to/your/laravel/app
USAGE=$(php artisan tinker --execute="echo json_encode(app(\App\Services\LocalImageService::class)->getStorageUsage());")
echo "$(date): Storage usage: $USAGE" >> /var/log/storage-monitor.log

# Alert if storage exceeds 4GB
SIZE_GB=$(echo $USAGE | grep -o '"total_size_gb":[0-9.]*' | cut -d: -f2)
if (( $(echo "$SIZE_GB > 4" | bc -l) )); then
    echo "WARNING: Storage usage is $SIZE_GB GB" | mail -s "Storage Alert" admin@yourdomain.com
fi
```

### 3. Backup Strategy
Set up regular backups of your images:

```bash
#!/bin/bash
# backup-images.sh
DATE=$(date +%Y%m%d_%H%M%S)
tar -czf /backups/images_$DATE.tar.gz storage/app/public/images/
find /backups -name "images_*.tar.gz" -mtime +30 -delete
```

## Troubleshooting

### Common Issues & Solutions

#### 1. Images Not Displaying
```bash
# Check if storage link exists
ls -la public/storage

# Recreate storage link
php artisan storage:link

# Check permissions
chmod -R 755 storage/app/public/
```

#### 2. Upload Failures
```bash
# Check PHP upload limits
php -i | grep upload_max_filesize
php -i | grep post_max_size

# Check directory permissions
ls -la storage/app/public/images/
```

#### 3. Permission Denied Errors
```bash
# Fix ownership
chown -R www-data:www-data storage/
# OR for shared hosting:
chown -R your_username:your_username storage/

# Fix permissions
chmod -R 755 storage/
```

#### 4. Storage Link Issues
```bash
# Remove existing link
rm public/storage

# Recreate link
php artisan storage:link

# Verify link
ls -la public/storage
```

### Error Logs
Monitor these log files:
- `storage/logs/laravel.log` - Application logs
- `/var/log/apache2/error.log` - Web server errors
- `/var/log/nginx/error.log` - Nginx errors (if applicable)

## Performance Optimization

### 1. Image Optimization
The LocalImageService automatically:
- Resizes images to max 1200x1200px
- Compresses to 85% quality
- Converts to optimal format (JPEG/WebP)
- Auto-rotates based on EXIF data

### 2. Caching
Add browser caching for images:

```apache
# In .htaccess
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
</IfModule>
```

### 3. CDN Integration (Optional)
For better performance, consider integrating with a CDN:

```php
// In LocalImageService, modify getOptimizedUrl method
public function getOptimizedUrl(string $publicId, array $transformations = []): string
{
    $baseUrl = config('app.cdn_url', config('app.url')) . '/storage';
    return $baseUrl . '/' . $publicId;
}
```

## Security Considerations

### 1. File Access Control
```apache
# In storage/app/public/.htaccess
Options -Indexes
<Files "*.php">
    Order Deny,Allow
    Deny from all
</Files>
```

### 2. File Type Validation
The LocalImageService already validates file types:
- Only allows: JPEG, PNG, GIF, WebP
- Rejects executable files
- Validates MIME types

### 3. Path Traversal Protection
- All file paths are sanitized
- Filenames are generated securely
- No user input in file paths

## Rollback Plan

If you need to revert to Cloudinary:

### 1. Quick Rollback
```bash
# Revert controller changes
git checkout HEAD~1 app/Http/Controllers/Api/ProductController.php
git checkout HEAD~1 app/Http/Controllers/Api/ProductReviewController.php

# Update imports
# Change: use App\Services\LocalImageService;
# To: use App\Services\CloudinaryService;

# Restore original upload methods
# (Use git diff to see changes and revert)
```

### 2. Database Cleanup
```bash
# No database changes were made, so no cleanup needed
```

### 3. File Cleanup
```bash
# Remove local images (optional)
rm -rf storage/app/public/images/

# Remove storage link
rm public/storage
```

## Support & Maintenance

### Monitoring Endpoints
- `GET /api/local-storage/usage` - Storage statistics
- `POST /api/local-storage/cleanup` - Manual cleanup
- `DELETE /api/local-storage/image` - Delete specific image

### Contact Information
- **Technical Issues**: Check logs first, then contact development team
- **Storage Issues**: Monitor usage via API endpoint
- **Performance Issues**: Check PHP configuration and server resources

---

## Summary

This migration provides:
- ✅ **Cost Savings**: No more Cloudinary subscription fees
- ✅ **Full Control**: Complete control over image storage and delivery
- ✅ **Better Performance**: Direct server delivery, optimized images
- ✅ **Scalability**: Easy to backup, migrate, and scale
- ✅ **Production Ready**: Optimized for Hostinger shared hosting

The migration is **completely transparent** to the frontend and maintains full API compatibility.

