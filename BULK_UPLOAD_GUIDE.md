# Enhanced CSV Bulk Upload with Image Support

## Overview
The Nordic Fashion Store now supports comprehensive bulk product upload via CSV files, including:
- Product information (name, description, price, category)
- Product variants (SKU, color, size, price differences, stock levels)
- Product images (up to 5 image URLs per product)

## CSV File Format

### Required Columns
- `name` - Product name (string, required, unique per product)
- `description` - Product description (text, nullable)
- `price` - Base product price (decimal, required)
- `category_name` - Category name (string, required, must exist in database)

### Optional Variant Columns
- `sku` - Stock Keeping Unit (string, unique across all variants)
- `color` - Product color variant (string, nullable)
- `size` - Product size variant (string, nullable)
- `price_difference` - Price adjustment from base price (decimal, default: 0.00)
- `stock` - Quantity in stock (integer, default: 0)

### Optional Image Columns
- `image_url_1` - First product image URL (string, must be valid URL)
- `image_url_2` - Second product image URL (string, must be valid URL)
- `image_url_3` - Third product image URL (string, must be valid URL)
- `image_url_4` - Fourth product image URL (string, must be valid URL)
- `image_url_5` - Fifth product image URL (string, must be valid URL)

## Example CSV Format

```csv
name,description,price,category_name,sku,color,size,price_difference,stock,image_url_1,image_url_2,image_url_3,image_url_4,image_url_5
"Nordic Wool Sweater","Premium wool sweater","89.99","Clothing","NWS-RED-M","Red","M","0.00","50","https://example.com/img1.jpg","https://example.com/img2.jpg","","",""
"Nordic Wool Sweater","Premium wool sweater","89.99","Clothing","NWS-RED-L","Red","L","5.00","30","https://example.com/img1.jpg","https://example.com/img2.jpg","","",""
"Leather Boots","Premium leather boots","149.99","Footwear","LB-BROWN-42","Brown","42","0.00","25","https://example.com/boot1.jpg","https://example.com/boot2.jpg","https://example.com/boot3.jpg","",""
```

## Features

### Product Handling
- **Duplicate Prevention**: Products with same name are detected
- **Update Mode**: Enable "Update existing" to modify existing products
- **Category Validation**: Categories must exist before product creation

### Variant Support
- **Multiple Variants**: Same product can have multiple rows for different variants
- **SKU Uniqueness**: Each variant must have a unique SKU
- **Price Flexibility**: Variants can have price differences from base price
- **Stock Management**: Individual stock levels per variant

### Image Processing
- **URL Validation**: All image URLs are validated before storage
- **Multiple Images**: Support for up to 5 images per product
- **Auto Alt Text**: Automatic generation of alt text for accessibility
- **Sort Order**: Images are stored with proper sort order (1-5)
- **Polymorphic Storage**: Images use Laravel's polymorphic relationships

### Error Handling
- **Validation**: Comprehensive validation of all fields
- **Error Reporting**: Detailed error messages with row numbers
- **Transaction Safety**: Database rollback on errors
- **Duplicate Detection**: Prevents duplicate products and variants

## Technical Implementation

### Backend Changes
1. **ProductController Enhancement**:
   - Added image processing method `processProductImages()`
   - Enhanced CSV validation for image columns
   - Integrated Image model for polymorphic relationships

2. **Database Integration**:
   - Products table: Basic product information
   - Product_variants table: SKU, color, size, stock, price differences
   - Images table: Polymorphic image storage with sort order

3. **Template Generation**:
   - Updated CSV template with all supported columns
   - Real-world examples with Nordic fashion products

### Frontend Updates
1. **Admin Dashboard**:
   - Updated CSV format instructions
   - Added image column documentation
   - Enhanced tips and examples

## Usage Instructions

### 1. Access Admin Panel
- Navigate to `/admin/login`
- Use credentials: `admin@example.com` / `admin123`

### 2. Download Template
- Go to Admin Dashboard
- Click "Download CSV Template"
- Use the template as a starting point

### 3. Prepare Your CSV
- Fill in product information
- Add variants as separate rows for same product
- Include valid image URLs (https:// recommended)
- Ensure categories exist in the system

### 4. Upload Process
- Select your CSV file
- Choose "Update existing" if modifying products
- Click "Upload Products"
- Review results for any errors

## Best Practices

### CSV Preparation
- Use UTF-8 encoding for special characters
- Enclose text fields in quotes to handle commas
- Test with small batches first
- Validate image URLs are accessible

### Image Guidelines
- Use high-quality, web-optimized images
- Ensure URLs are publicly accessible
- Prefer HTTPS URLs for security
- Keep image file sizes reasonable (< 2MB per image)

### Product Management
- Create categories before uploading products
- Use consistent naming conventions for SKUs
- Plan your variant structure (color/size combinations)
- Consider stock levels and pricing strategy

## Files Created

1. **Enhanced ProductController** (`app/Http/Controllers/Api/ProductController.php`)
   - Complete bulk upload with variants and images
   - Image processing and validation
   - Updated CSV template generation

2. **Sample CSV Files**:
   - `sample-products-with-images.csv` - Complete example with real data
   - `test-products-with-variants.csv` - Test file for variants

3. **Updated Admin Dashboard** (`src/pages/AdminDashboard.tsx`)
   - Enhanced CSV format documentation
   - Image column explanations
   - Improved user guidance

## Testing

The system has been tested with:
- ✅ Products with multiple variants
- ✅ Image URL validation and storage
- ✅ Error handling for invalid data
- ✅ Update existing products functionality
- ✅ Category validation
- ✅ SKU uniqueness enforcement

## Next Steps

1. Test the upload functionality with the provided sample CSV
2. Verify images are displaying correctly in the frontend
3. Test with your own product data
4. Monitor performance with larger CSV files
5. Consider implementing image optimization if needed

The enhanced bulk upload system now provides complete support for a professional e-commerce catalog with products, variants, and images!
