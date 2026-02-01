<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'Nord Flex Newsletter' }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        
        /* Main styles */
        body {
            background-color: #f4f4f4;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            width: 100% !important;
            min-width: 100%;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            padding: 40px 30px;
            text-align: center;
        }
        
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 8px;
            text-decoration: none;
            display: inline-block;
        }
        
        .tagline {
            color: #ecf0f1;
            font-size: 14px;
            margin: 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        /* Content sections */
        .content-section {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .main-content {
            color: #34495e;
            line-height: 1.7;
            font-size: 16px;
        }
        
        .main-content h1 {
            color: #2c3e50;
            font-size: 28px;
            margin: 30px 0 20px 0;
            font-weight: 600;
            line-height: 1.3;
        }
        
        .main-content h2 {
            color: #2c3e50;
            font-size: 24px;
            margin: 25px 0 15px 0;
            font-weight: 600;
            line-height: 1.3;
        }
        
        .main-content h3 {
            color: #34495e;
            font-size: 20px;
            margin: 20px 0 12px 0;
            font-weight: 600;
        }
        
        .main-content p {
            margin: 0 0 18px 0;
        }
        
        .main-content ul, .main-content ol {
            margin: 0 0 18px 20px;
        }
        
        .main-content li {
            margin-bottom: 8px;
        }
        
        .main-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
            display: block;
        }
        
        /* Call-to-action button */
        .cta-section {
            text-align: center;
            padding: 30px;
            background-color: #f8f9fa;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #d4af37 0%, #b8941f 100%);
            color: #ffffff !important;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(212, 175, 55, 0.4);
        }
        
        /* Product showcase */
        .product-grid {
            display: table;
            width: 100%;
            margin: 30px 0;
        }
        
        .product-item {
            display: table-cell;
            width: 50%;
            padding: 15px;
            vertical-align: top;
        }
        
        .product-image {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        
        .product-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 6px;
        }
        
        .product-price {
            font-size: 18px;
            font-weight: 700;
            color: #d4af37;
        }
        
        /* Divider */
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #bdc3c7, transparent);
            margin: 40px 0;
        }
        
        /* Footer */
        .footer {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 40px 30px;
            text-align: center;
        }
        
        .footer-logo {
            font-size: 24px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 15px;
        }
        
        .footer-description {
            font-size: 14px;
            color: #bdc3c7;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        
        .social-links {
            margin: 25px 0;
        }
        
        .social-link {
            display: inline-block;
            margin: 0 12px;
            padding: 10px;
            background-color: #34495e;
            border-radius: 50%;
            text-decoration: none;
            color: #ffffff;
            font-size: 16px;
            width: 40px;
            height: 40px;
            line-height: 20px;
            transition: background-color 0.3s ease;
        }
        
        .social-link:hover {
            background-color: #d4af37;
        }
        
        .footer-links {
            margin: 25px 0;
        }
        
        .footer-link {
            color: #d4af37;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .footer-link:hover {
            color: #ffffff;
        }
        
        .contact-info {
            font-size: 13px;
            color: #95a5a6;
            margin: 20px 0;
            line-height: 1.4;
        }
        
        .unsubscribe {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #34495e;
            font-size: 12px;
            color: #95a5a6;
            line-height: 1.4;
        }
        
        .unsubscribe a {
            color: #d4af37;
            text-decoration: none;
        }
        
        .unsubscribe a:hover {
            text-decoration: underline;
        }
        
        /* Responsive design */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }
            
            .header, .content-section, .cta-section, .footer {
                padding: 25px 20px !important;
            }
            
            .logo {
                font-size: 28px !important;
            }
            
            .main-content h1 {
                font-size: 24px !important;
            }
            
            .main-content h2 {
                font-size: 20px !important;
            }
            
            .product-item {
                display: block !important;
                width: 100% !important;
                margin-bottom: 20px;
            }
            
            .footer-link {
                display: block;
                margin: 8px 0;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .email-container {
                background-color: #ffffff !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}" class="logo">
                🏔️ NORD FLEX
            </a>
            <p class="tagline">Premium Nordic Fashion & Lifestyle</p>
        </div>
        
        <!-- Main Content -->
        <div class="content-section">
            <div class="greeting">
                Hello {{ $subscription->name ?? 'Nordic Fashion Enthusiast' }},
            </div>
            
            <div class="main-content">
                {!! $emailContent !!}
            </div>
        </div>
        
        <!-- Call-to-Action Section -->
        <div class="cta-section">
            <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/products" class="cta-button">
                Shop Our Collection
            </a>
        </div>
        
        <!-- Divider -->
        <div class="divider"></div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo">NORD FLEX</div>
            <p class="footer-description">
                Discover premium Nordic fashion and accessories crafted for the modern adventurer. 
                Quality, style, and sustainability in every piece.
            </p>
            
            <div class="social-links">
                <a href="#" class="social-link">📘</a>
                <a href="#" class="social-link">📷</a>
                <a href="#" class="social-link">🐦</a>
                <a href="#" class="social-link">📌</a>
            </div>
            
            <div class="footer-links">
                <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}" class="footer-link">Shop</a>
                <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/blog" class="footer-link">Blog</a>
                <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/about" class="footer-link">About</a>
                <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/contact" class="footer-link">Contact</a>
            </div>
            
            <div class="contact-info">
                <strong>Nord Flex</strong><br>
                Premium Nordic Fashion & Lifestyle<br>
                Email: support@nordflex.store<br>
                Customer Service: Available 24/7
            </div>
            
            <div class="unsubscribe">
                <p>You're receiving this email because you subscribed to our newsletter.</p>
                <p>
                    <a href="{{ route('newsletter.unsubscribe', ['email' => $subscription->email]) }}">Unsubscribe</a> | 
                    <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/newsletter">Update Preferences</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>