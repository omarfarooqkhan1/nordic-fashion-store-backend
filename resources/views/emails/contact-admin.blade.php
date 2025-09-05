<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Contact Form Submission</title>
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
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .content {
            background: #fff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .field {
            margin-bottom: 15px;
        }
        .label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        .value {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 12px;
            color: #6c757d;
        }
        .reply-button {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>📧 New Contact Form Submission</h2>
        <p>A new message has been submitted through your website contact form.</p>
    </div>

    <div class="content">
        <div class="field">
            <div class="label">From:</div>
            <div class="value">{{ $contactData['firstName'] }} {{ $contactData['lastName'] }}</div>
        </div>

        <div class="field">
            <div class="label">Email:</div>
            <div class="value">{{ $contactData['email'] }}</div>
        </div>

        <div class="field">
            <div class="label">Subject:</div>
            <div class="value">{{ $contactData['subject'] }}</div>
        </div>

        <div class="field">
            <div class="label">Message:</div>
            <div class="value">{{ $contactData['message'] }}</div>
        </div>

        <div class="field">
            <div class="label">Submitted:</div>
            <div class="value">{{ $timestamp->format('F j, Y \a\t g:i A T') }}</div>
        </div>

        <div class="field">
            <div class="label">IP Address:</div>
            <div class="value">{{ $ip }}</div>
        </div>

        <a href="mailto:{{ $contactData['email'] }}?subject=Re: {{ $contactData['subject'] }}" class="reply-button">
            Reply to {{ $contactData['firstName'] }}
        </a>
    </div>

    <div class="footer">
        <p>This email was sent from your website contact form at {{ config('app.url') }}</p>
        <p>You can reply directly to this email to respond to the customer.</p>
    </div>
</body>
</html>
