# Image and Video Dimensions Guide

This document outlines the recommended dimensions for all image types and videos used in the Nordic Fashion Store.

## Product Images

### 1. Main Images (Product Gallery)
- **Recommended Size**: 1200 x 1600 pixels (3:4 aspect ratio)
- **Minimum Size**: 800 x 1067 pixels
- **Maximum Size**: 2400 x 3200 pixels
- **Format**: JPG, PNG, WebP
- **Usage**: Primary product images shown in the main gallery
- **Display Context**: Product detail page main carousel, product cards
- **Notes**: These are the hero images customers see first. Should be high quality with clean background.

### 2. Detailed Images (Desktop)
- **Recommended Size**: 2560 x 1080 pixels (21:9 ultrawide aspect ratio)
- **Minimum Size**: 1920 x 810 pixels
- **Maximum Size**: 3440 x 1440 pixels
- **Format**: JPG, PNG, WebP
- **Usage**: Detailed product shots showing texture, stitching, materials
- **Display Context**: Product detail page - detailed section (desktop view)
- **Notes**: Wide format allows for panoramic product views and detail shots

### 3. Mobile Detailed Images
- **Recommended Size**: 1080 x 608 pixels (16:9 aspect ratio)
- **Minimum Size**: 720 x 405 pixels
- **Maximum Size**: 1920 x 1080 pixels
- **Format**: JPG, PNG, WebP
- **Usage**: Mobile-optimized detailed product shots
- **Display Context**: Product detail page - detailed section (mobile view)
- **Notes**: Optimized for mobile screens, smaller file size for faster loading

### 4. Styling Images
- **Recommended Size**: 1080 x 1350 pixels (4:5 aspect ratio - Instagram portrait)
- **Minimum Size**: 720 x 900 pixels
- **Maximum Size**: 1440 x 1800 pixels
- **Format**: JPG, PNG, WebP
- **Usage**: Lifestyle shots showing product in use/styled
- **Display Context**: Product detail page - "How Others Are Styling This" section
- **Notes**: Portrait orientation works best for showing full outfits and styling

### 5. Thumbnail Images
- **Recommended Size**: 400 x 400 pixels (1:1 aspect ratio)
- **Minimum Size**: 200 x 200 pixels
- **Maximum Size**: 600 x 600 pixels
- **Format**: JPG, PNG, WebP
- **Usage**: Product listing pages, cart items, order history
- **Display Context**: Product grid, thumbnails in gallery
- **Notes**: Square format for consistency across the site

### 6. Size Guide Image
- **Recommended Size**: 1200 x 1600 pixels (3:4 aspect ratio)
- **Minimum Size**: 800 x 1067 pixels
- **Maximum Size**: 2000 x 2667 pixels
- **Format**: JPG, PNG
- **Usage**: Size guide modal/popup
- **Display Context**: Product detail page - size guide button
- **Notes**: Should be clear and readable with measurement information

### 7. Hero/Banner Images
- **Recommended Size**: 1920 x 1080 pixels (16:9 aspect ratio)
- **Minimum Size**: 1280 x 720 pixels
- **Maximum Size**: 3840 x 2160 pixels (4K)
- **Format**: JPG, PNG, WebP
- **Usage**: Homepage hero slider, category banners
- **Display Context**: Homepage, category pages
- **Notes**: Wide format for full-width banners

### 8. Blog Images
- **Recommended Size**: 1200 x 630 pixels (1.91:1 aspect ratio - Open Graph)
- **Minimum Size**: 800 x 420 pixels
- **Maximum Size**: 1920 x 1008 pixels
- **Format**: JPG, PNG, WebP
- **Usage**: Blog post featured images
- **Display Context**: Blog listing, blog detail pages
- **Notes**: Optimized for social media sharing

## Videos

### 1. Variant Videos
- **Recommended Resolution**: 1080 x 1920 pixels (9:16 vertical - Instagram/TikTok format)
- **Alternative Resolution**: 1080 x 1350 pixels (4:5 portrait)
- **Minimum Resolution**: 720 x 1280 pixels
- **Maximum Resolution**: 1080 x 1920 pixels
- **Format**: MP4 (H.264 codec)
- **Frame Rate**: 30 fps or 60 fps
- **Bitrate**: 5-10 Mbps
- **Duration**: 10-30 seconds recommended
- **Max File Size**: 50 MB (configured in backend)
- **Usage**: Product demonstration videos per variant color
- **Display Context**: Product detail page - styling section
- **Notes**: Vertical format optimized for mobile viewing, shows product in motion

### 2. Hero Videos (if implemented)
- **Recommended Resolution**: 1920 x 1080 pixels (16:9 landscape)
- **Minimum Resolution**: 1280 x 720 pixels
- **Maximum Resolution**: 3840 x 2160 pixels (4K)
- **Format**: MP4 (H.264 codec)
- **Frame Rate**: 30 fps
- **Bitrate**: 8-15 Mbps
- **Duration**: 5-15 seconds (looping)
- **Max File Size**: 100 MB
- **Usage**: Homepage hero section background videos
- **Display Context**: Homepage
- **Notes**: Should be optimized for autoplay and looping

## File Size Guidelines

### Images
- **Main/Gallery Images**: 200-500 KB (compressed)
- **Detailed Images**: 300-800 KB (compressed)
- **Thumbnails**: 50-100 KB (compressed)
- **Hero/Banner**: 400-1000 KB (compressed)

### Videos
- **Variant Videos**: 5-50 MB
- **Hero Videos**: 10-100 MB

## Compression Guidelines

1. **JPG**: Use 80-85% quality for best balance of size and quality
2. **PNG**: Use for images requiring transparency
3. **WebP**: Recommended for modern browsers (30% smaller than JPG)
4. **Video**: Use H.264 codec with medium compression preset

## Aspect Ratio Summary

| Image Type | Aspect Ratio | Dimensions Example |
|-----------|--------------|-------------------|
| Main Images | 3:4 (Portrait) | 1200 x 1600 |
| Detailed (Desktop) | 21:9 (Ultrawide) | 2560 x 1080 |
| Detailed (Mobile) | 16:9 (Landscape) | 1080 x 608 |
| Styling Images | 4:5 (Portrait) | 1080 x 1350 |
| Thumbnails | 1:1 (Square) | 400 x 400 |
| Hero/Banner | 16:9 (Landscape) | 1920 x 1080 |
| Variant Videos | 9:16 (Vertical) | 1080 x 1920 |

## Best Practices

1. **Always use high-quality source images** - downscaling is better than upscaling
2. **Maintain consistent aspect ratios** within each category
3. **Optimize images before upload** using tools like TinyPNG, ImageOptim, or Squoosh
4. **Use WebP format** when possible for better compression
5. **Test on multiple devices** to ensure images look good on all screen sizes
6. **Keep file sizes reasonable** for faster page load times
7. **Use descriptive alt text** for all images for SEO and accessibility
8. **Consider lazy loading** for images below the fold

## Tools for Image Optimization

- **TinyPNG**: https://tinypng.com/
- **Squoosh**: https://squoosh.app/
- **ImageOptim** (Mac): https://imageoptim.com/
- **GIMP** (Free): https://www.gimp.org/
- **Adobe Photoshop**: Professional image editing
- **Canva**: Easy design tool with preset dimensions

## Video Optimization Tools

- **HandBrake**: Free video transcoder
- **FFmpeg**: Command-line video processing
- **Adobe Premiere Pro**: Professional video editing
- **DaVinci Resolve**: Free professional video editor
