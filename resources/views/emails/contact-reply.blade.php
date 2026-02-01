<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reply from Nord Flex Support</title>
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
        .original-message {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #e9ecef;
            margin: 20px 0;
        }
        .reply-message {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
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
        <h1>Reply from Nord Flex Support</h1>
        <p>We've responded to your inquiry</p>
    </div>

    <div class="content">
        <p>Dear {{ $contactForm->first_name }} {{ $contactForm->last_name }},</p>

        <p>Thank you for contacting Nord Flex. We've reviewed your message and are happy to provide you with a response.</p>

        <div class="original-message">
            <h3>📧 Your Original Message:</h3>
            <p><strong>Subject:</strong> {{ $contactForm->subject }}</p>
            <p><strong>Message:</strong></p>
            <p>{{ $contactForm->message }}</p>
            <p><strong>Submitted:</strong> {{ $timestamp->format('F j, Y \a\t g:i A T') }}</p>
        </div>

        <div class="reply-message">
            <h3>💬 Our Response:</h3>
            <p>{{ $replyMessage }}</p>
        </div>

        <p>If you have any follow-up questions or need further assistance, please don't hesitate to reach out to us again.</p>

        <div class="contact-info">
            <h3>📞 Need More Help?</h3>
            <p><strong>Phone:</strong> +358 2 123 456 789</p>
            <p><strong>Email:</strong> support@nordflex.store</p>
            <p><strong>Business Hours:</strong> Monday - Friday, 9:00 AM - 6:00 PM (EET)</p>
        </div>

        <p>Best regards,<br>
        The Nord Flex Support Team</p>
    </div>

    <div class="footer">
        <p>This is a response to your contact form submission.</p>
        <p>Nord Flex - Premium Leather Products</p>
        <p>© {{ date('Y') }} Nord Flex. All rights reserved.</p>
    </div>
</body>
</html>
