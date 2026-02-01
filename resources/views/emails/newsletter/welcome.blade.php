<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Welcome to Nord Flex Newsletter</title>
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
        
        .welcome-title {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }
        
        .greeting {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 500;
        }
        
        .content {
            color: #34495e;
            line-height: 1.7;
            font-size: 16px;
        }
        
        .content p {
            margin: 0 0 18px 0;
        }
        
        .benefits-list {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
        }
        
        .benefits-list h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .benefits-list ul {
            list-style: none;
            padding: 0;
        }
        
        .benefits-list li {
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .benefits-list li:last-child {
            border-bottom: none;
        }
        
        .benefits-list li:before {
            content: "✓";
            color: #d4af37;
            font-weight: bold;
            margin-right: 10px;
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
            
            .welcome-title {
                font-size: 24px !important;
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
            <h1 class="welcome-title">Welcome to Nord Flex!</h1>
            
            <div class="greeting">
                Hello {{ $subscription->name ?? 'Nordic Fashion Enthusiast' }},
            </div>
            
            <div class="content">
                <p>Thank you for joining the Nord Flex community! We're thrilled to have you on board and can't wait to share our passion for premium Nordic fashion and lifestyle with you.</p>
                
                <div class="benefits-list">
                    <h3>What to expect from our newsletter:</h3>
                    <ul>
                        <li>Exclusive early access to new collections</li>
                        <li>Special subscriber-only discounts and offers</li>
                        <li>Nordic fashion trends and styling tips</li>
                        <li>Behind-the-scenes stories from our artisans</li>
                        <li>Seasonal style guides and inspiration</li>
                        <li>Sustainable fashion insights and practices</li>
                    </ul>
                </div>
                
                <p>Our newsletter is carefully crafted to bring you the best of Nordic design philosophy - where functionality meets beauty, and tradition embraces innovation.</p>
                
                <p>As a welcome gift, we'd love for you to explore our current collection and discover pieces that speak to your Nordic spirit.</p>
            </div>
        </div>
        
        <!-- Call-to-Action Section -->
        <div class="cta-section">
            <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/products" class="cta-button">
                Explore Our Collection
            </a>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo">NORD FLEX</div>
            <p class="footer-description">
                Discover premium Nordic fashion and accessories crafted for the modern adventurer. 
                Quality, style, and sustainability in every piece.
            </p>
            
            <div class="contact-info">
                <strong>Nord Flex</strong><br>
                Premium Nordic Fashion & Lifestyle<br>
                Email: support@nordflex.store<br>
                Customer Service: Available 24/7
            </div>
            
            <div class="unsubscribe">
                <p>You're receiving this email because you subscribed to our newsletter on {{ $subscription->subscribed_at->format('M d, Y') }}.</p>
                <p>
                    <a href="{{ $unsubscribeUrl }}">Unsubscribe</a> | 
                    <a href="{{ config('app.frontend_url', 'http://localhost:3000') }}/newsletter">Update Preferences</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>