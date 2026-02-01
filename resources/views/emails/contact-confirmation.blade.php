<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thank you for contacting Nord Flex</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            color: white;
        }
        .content {
            background: #fff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .message-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #fbbf24;
            margin: 20px 0;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 12px;
            color: #6c757d;
            text-align: center;
        }
        .contact-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .contact-info h3 {
            margin-top: 0;
            color: #495057;
        }
        .contact-info p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Thank You for Contacting Nord Flex!</h1>
        <p>We've received your message and will get back to you soon.</p>
    </div>

    <div class="content">
        <p>Dear {{ $contactData['firstName'] }},</p>

        <p>Thank you for reaching out to us! We've successfully received your message and our team will review it shortly.</p>

        <div class="message-box">
            <h3>Your Message Details:</h3>
            <p><strong>Subject:</strong> {{ $contactData['subject'] }}</p>
            <p><strong>Message:</strong></p>
            <p>{{ $contactData['message'] }}</p>
        </div>

        <p>We typically respond to inquiries within 24-48 hours during business days. If you have an urgent matter, please don't hesitate to call us directly.</p>

        <div class="contact-info">
            <h3>📞 Need Immediate Assistance?</h3>
            <p><strong>Phone:</strong> +358 2 123 456 789</p>
            <p><strong>Email:</strong> support@nordflex.store</p>
            <p><strong>Business Hours:</strong> Monday - Friday, 9:00 AM - 6:00 PM (EET)</p>
        </div>

        <p>In the meantime, feel free to explore our website for more information about our products and services.</p>

        <p>Best regards,<br>
        The Nord Flex Team</p>
    </div>

    <div class="footer">
        <p>This is an automated confirmation email. Please do not reply to this message.</p>
        <p>Nord Flex - Premium Leather Products</p>
        <p>© {{ date('Y') }} Nord Flex. All rights reserved.</p>
    </div>
</body>
</html>
