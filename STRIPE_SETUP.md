# Stripe Payment Gateway Setup

This guide explains how to set up Stripe payments for the Nordic Fashion Store.

## 1. Environment Variables

Add the following environment variables to your `.env` file:

```env
# Stripe Configuration
STRIPE_SECRET_KEY=sk_test_your_stripe_secret_key_here
STRIPE_PUBLISHABLE_KEY=pk_test_your_stripe_publishable_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here

# Frontend URL for return URLs
FRONTEND_URL=http://localhost:3000
```

## 2. Getting Stripe Keys

1. **Create a Stripe Account**: Sign up at [stripe.com](https://stripe.com)
2. **Get API Keys**: 
   - Go to Dashboard → Developers → API keys
   - Copy your **Publishable key** (starts with `pk_test_` or `pk_live_`)
   - Copy your **Secret key** (starts with `sk_test_` or `sk_live_`)

## 3. Webhook Setup

1. **Create Webhook Endpoint**:
   - Go to Dashboard → Developers → Webhooks
   - Click "Add endpoint"
   - Set URL to: `https://yourdomain.com/api/stripe/webhook`
   - Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`
   - Copy the **Webhook signing secret** (starts with `whsec_`)

2. **Test Webhook**:
   - Use Stripe CLI or dashboard to send test events
   - Verify webhook signature validation works

## 4. Testing

### Test Card Numbers:
- **Success**: `4242 4242 4242 4242`
- **Decline**: `4000 0000 0000 0002`
- **3D Secure**: `4000 0025 0000 3155`

### Test CVV: Any 3 digits (e.g., `123`)

### Test Expiry: Any future date (e.g., `12/25`)

## 5. Frontend Integration

The frontend will use the publishable key to initialize Stripe Elements and create payment intents through the backend API.

## 6. Security Notes

- **Never expose secret keys** in frontend code
- **Always validate webhook signatures** (already implemented)
- **Use HTTPS** in production for webhook endpoints
- **Test thoroughly** with Stripe test mode before going live

## 7. API Endpoints

- `POST /api/stripe/create-payment-intent` - Create payment intent
- `POST /api/stripe/confirm-payment` - Confirm payment
- `GET /api/stripe/payment-intent/{id}` - Get payment status
- `POST /api/stripe/webhook` - Handle Stripe events

## 8. Production Checklist

- [ ] Switch to live keys (`pk_live_`, `sk_live_`)
- [ ] Update webhook endpoint to production URL
- [ ] Test with real payment methods
- [ ] Monitor webhook delivery and errors
- [ ] Set up proper error handling and logging
- [ ] Implement idempotency for payment operations

