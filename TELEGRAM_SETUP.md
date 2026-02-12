# Telegram Order Notifications Setup

Get instant notifications on your phone/desktop when new orders arrive!

## Step 1: Create Your Bot (2 minutes)

1. Open **Telegram** app on your phone
2. Search for: `@BotFather`
3. Start chat and send: `/newbot`
4. Give it a name: `NordFlex Orders` (or anything you like)
5. Give it a username: `nordflex_orders_bot` (must end with `_bot`)
6. **BotFather will give you a TOKEN** - copy it!
   - Looks like: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`

## Step 2: Get Your Chat ID (1 minute)

1. Search for your bot in Telegram (the username you just created)
2. Start a chat and send any message (like "Hello")
3. Open this URL in your browser (replace `<YOUR_BOT_TOKEN>` with your actual token):
   ```
   https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates
   ```
4. Look for `"chat":{"id":123456789` - that number is your CHAT_ID
5. Copy the chat ID

## Step 3: Add to .env File

Open `nordic-fashion-store-backend/.env` and add:

```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
```

## Step 4: Test It!

1. Place a test order on your website
2. You should receive a notification on Telegram instantly!

## Notification Format

You'll receive messages like:

```
🛍️ New Order Received!

📦 Order #: 123
👤 Customer: John Doe
📧 Email: john@example.com
💰 Total: €299.99
📍 Status: pending
🕐 Time: 2026-02-13 15:30:00
```

## Troubleshooting

- **Not receiving notifications?**
  - Make sure you sent a message to your bot first
  - Check that TOKEN and CHAT_ID are correct in .env
  - Check Laravel logs: `storage/logs/laravel.log`

- **Want to disable notifications?**
  - Just remove or comment out the TELEGRAM_BOT_TOKEN in .env

## Notes

- Notifications are sent ONLY when orders are successfully created
- If Telegram is not configured, orders still work normally (no errors)
- Completely free, no limits!
