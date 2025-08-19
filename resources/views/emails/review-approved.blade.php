<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Review Approved</title>
  <style>
    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      background: #fff;
      color: #111;
      margin: 0;
      padding: 0;
      line-height: 1.6;
    }
    .container {
      max-width: 480px;
      margin: 0 auto;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      overflow: hidden;
      padding: 32px 24px;
    }
    .brand {
      font-size: 1rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: #111;
      margin-bottom: 18px;
      text-align: center;
    }
    .title {
      font-size: 1.3rem;
      font-weight: 700;
      margin-bottom: 18px;
      text-align: center;
    }
    .blockquote {
      background: #fafafa;
      padding: 14px 18px;
      border-left: 4px solid #111;
      margin: 18px 0;
      border-radius: 6px;
      color: #222;
    }
    .footer {
      color: #444;
      font-size: 0.98rem;
      text-align: center;
      margin-top: 32px;
    }
    @media only screen and (max-width: 600px) {
      .container {
        padding: 16px 8px;
        border-radius: 0;
        box-shadow: none;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="brand">Nord Flex</div>
    <div class="title">Your Review Has Been Approved!</div>
    <p>Hi {{ $customerName }},</p>
    <p>Thank you for sharing your feedback on <strong>{{ $productName }}</strong>.</p>
    <p>Your review has been approved and is now visible on our website.</p>
    <div class="blockquote">{{ $review->review }}</div>
    <p>We appreciate your support!</p>
    <div class="footer">Best regards,<br>Nord Flex Team</div>
  </div>
</body>
</html>
