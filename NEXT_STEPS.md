# Next Steps - WhatsApp Chatbot Setup & Testing

## 🎉 Implementation Complete!

All core functionality for the WhatsApp Events & Courses Scheduling Chatbot has been implemented. Follow the steps below to configure, test, and deploy your chatbot.

---

## 📋 Step 1: Environment Configuration

### Update Your `.env` File

Copy the following variables from `.env.example` and update them with your actual credentials:

```env
# WhatsApp Business API Configuration
GRAPH_API_TOKEN=your_whatsapp_access_token_here
BUSINESS_PHONE_NUMBER_ID=your_phone_number_id_here
WEBHOOK_VERIFY_TOKEN=your_webhook_verify_token_here
GRAPH_API_VERSION=v22.0

# WhatsApp Configuration (Optional - defaults are fine)
WHATSAPP_CONVERSATION_TIMEOUT=30
WHATSAPP_RATE_LIMIT_PER_SECOND=20
WHATSAPP_BURST_LIMIT=100
WHATSAPP_DEFAULT_LANGUAGE=en

# PesePay Payment Gateway Configuration
PESEPAY_INT_KEY=your_pesepay_integration_key_here
PESEPAY_ENCY_KEY=your_pesepay_encryption_key_here
PESEPAY_CURRENCY=USD
PESEPAY_MERCHANT_CODE=PZW211

# Queue Configuration (Important!)
QUEUE_CONNECTION=database
```

### Where to Get Your Credentials:

**WhatsApp Business API:**
- Go to [Meta for Developers](https://developers.facebook.com/)
- Navigate to your WhatsApp Business app
- Find your Access Token and Phone Number ID

**PesePay:**
- Contact PesePay support to get your Integration Key and Encryption Key
- Or use your existing credentials from the reference project

---

## 📋 Step 2: Database Setup (If Not Done)

Make sure your database is set up and migrated:

```bash
# Run migrations
php artisan migrate

# (Optional) Seed sample data
php artisan db:seed
```

---

## 📋 Step 3: Queue Worker Setup

The chatbot uses background jobs for payment processing and reminders. You **must** run a queue worker:

### Development:
```bash
php artisan queue:work
```

### Production (Using Supervisor):

Create a supervisor config file `/etc/supervisor/conf.d/hustleprenure-worker.conf`:

```ini
[program:hustleprenure-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

Then reload supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hustleprenure-worker:*
```

---

## 📋 Step 4: Scheduler Setup (Cron Job)

The chatbot needs the Laravel scheduler to run for reminders. Add this to your crontab:

```bash
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

This will run:
- `reminders:send` - Every minute
- `flows:cleanup` - Daily at 2 AM

---

## 📋 Step 5: Create Sample Data (Optional)

Before testing, create some sample events/courses:

### Option 1: Manual via Admin Panel
1. Log in to your admin panel
2. Create categories (e.g., "Business", "Technology", "Marketing")
3. Create instructors
4. Create events and courses

### Option 2: Create Seeders (Recommended)

```bash
php artisan make:seeder CategorySeeder
php artisan make:seeder InstructorSeeder
php artisan make:seeder EventSeeder
```

Then populate the seeders with sample data and run:
```bash
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=InstructorSeeder
php artisan db:seed --class=EventSeeder
```

---

## 📋 Step 6: Testing the Chatbot

### Test 1: Connection Test
```bash
php artisan chatbot:test YOUR_PHONE_NUMBER
```

Select "Test WhatsApp Connection" to verify API connectivity.

### Test 2: Welcome Message
```bash
php artisan chatbot:test YOUR_PHONE_NUMBER
```

Select "Send Welcome Message" to test the welcome flow.

### Test 3: Full Flow Test

Send a WhatsApp message to your business number:
1. Type "hi" or "menu"
2. Click "Browse Events"
3. Select a category
4. View event details
5. Click "Enroll & Pay"
6. Complete payment flow

### Test 4: Verify Jobs Processing

```bash
# Check queue jobs
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed

# Check logs
tail -f storage/logs/laravel.log
```

---

## 📋 Step 7: Webhook Configuration

### For Development (Using Ngrok):

1. Install ngrok: https://ngrok.com/
2. Run your Laravel app: `php artisan serve`
3. Start ngrok: `ngrok http 8000`
4. Copy the HTTPS URL (e.g., `https://abc123.ngrok.io`)
5. Go to Meta for Developers → WhatsApp → Configuration
6. Set Webhook URL: `https://abc123.ngrok.io/webhook`
7. Set Verify Token: (same as `WEBHOOK_VERIFY_TOKEN` in .env)
8. Subscribe to `messages` webhook field

### For Production:

Set webhook URL to: `https://yourdomain.com/webhook`

---

## 📋 Step 8: Payment Webhook Configuration

Configure PesePay to send callbacks to:
- **Return URL**: `https://yourdomain.com/webhooks/pesepay/return`
- **Result URL**: `https://yourdomain.com/webhooks/pesepay/result`

These URLs are auto-configured in `config/services.php`.

---

## 🧪 Testing Checklist

### Basic Functionality:
- [ ] WhatsApp connection test passes
- [ ] Welcome message sends successfully
- [ ] Main menu displays with buttons
- [ ] Categories list shows when browsing
- [ ] Events display in selected category
- [ ] Event details show correctly

### Enrollment Flow:
- [ ] Can click "Enroll & Pay" button
- [ ] Payment method selection works
- [ ] Phone number input accepted
- [ ] Payment initiated with PesePay
- [ ] Payment status polling works
- [ ] Enrollment created on successful payment
- [ ] Confirmation message sent

### Reminders:
- [ ] Enrollment confirmation sent immediately
- [ ] Event reminders scheduled correctly
- [ ] Course session reminders created
- [ ] `reminders:send` command processes due reminders
- [ ] Reminder messages delivered via WhatsApp

### Background Jobs:
- [ ] Queue worker processing jobs
- [ ] Payment status checks running
- [ ] No failed jobs in queue
- [ ] Logs show successful processing

---

## 🐛 Troubleshooting

### Issue: Messages not sending
**Solution**:
- Check WhatsApp API credentials in `.env`
- Verify access token is valid
- Check logs: `tail -f storage/logs/laravel.log`

### Issue: Payments not processing
**Solution**:
- Verify PesePay credentials
- Check queue worker is running
- Look for `CheckPaymentStatus` job in queue
- Check PesePay logs in `storage/logs/`

### Issue: Reminders not sending
**Solution**:
- Verify cron job is set up correctly
- Run manually: `php artisan reminders:send`
- Check scheduled reminders: `SELECT * FROM reminders WHERE status='pending'`
- Ensure queue worker is running

### Issue: Webhook not receiving messages
**Solution**:
- Verify webhook URL is correct in Meta for Developers
- Check webhook verify token matches `.env`
- Test webhook: `curl -X GET 'https://yourdomain.com/webhook?hub.verify_token=YOUR_TOKEN&hub.challenge=test'`
- Check logs for incoming webhook requests

---

## 📊 Monitoring & Maintenance

### Check Queue Status:
```bash
php artisan queue:monitor
```

### View Failed Jobs:
```bash
php artisan queue:failed
php artisan queue:retry all  # Retry all failed jobs
```

### Clean Up Expired Flows:
```bash
php artisan flows:cleanup
```

### Check Reminder Status:
```sql
SELECT reminder_type, status, COUNT(*) as count
FROM reminders
GROUP BY reminder_type, status;
```

### Monitor Payment Status:
```sql
SELECT status, COUNT(*) as count, SUM(amount) as total
FROM payments
GROUP BY status;
```

---

## 🚀 Deployment Checklist

Before going to production:

- [ ] All environment variables configured
- [ ] Database migrated
- [ ] Queue worker running (via Supervisor)
- [ ] Cron job configured for scheduler
- [ ] Webhook configured in Meta for Developers
- [ ] PesePay webhook URLs configured
- [ ] Sample events/courses created
- [ ] Full flow tested end-to-end
- [ ] Error logging configured
- [ ] Backup strategy in place

---

## 📚 Available Commands

```bash
# Test chatbot
php artisan chatbot:test {phone}

# Send scheduled reminders
php artisan reminders:send

# Clean up expired conversation flows
php artisan flows:cleanup

# Queue management
php artisan queue:work
php artisan queue:monitor
php artisan queue:failed
php artisan queue:retry {id}

# Check schedule
php artisan schedule:list
```

---

## 🎓 How It Works

### User Journey:
1. User sends message to WhatsApp Business number
2. Webhook receives message → dispatches to queue
3. ChatbotService routes message based on state
4. EventBrowserService shows categories/events
5. User selects event → EnrollmentService initiates enrollment
6. Payment requested via PesePayService
7. CheckPaymentStatus job polls payment (every 3s, max 10 times)
8. On success: Enrollment created, reminders scheduled
9. ReminderService creates all reminders
10. Scheduler sends reminders at appropriate times

### Tech Stack:
- **Laravel 12**: Backend framework
- **WhatsApp Business API**: Messaging platform
- **PesePay**: Payment gateway
- **Queue Jobs**: Background processing
- **Observers**: Auto-trigger actions
- **Scheduler**: Automated tasks

---

## 🆘 Need Help?

If you encounter issues:

1. Check logs: `storage/logs/laravel.log`
2. Review queue failures: `php artisan queue:failed`
3. Verify environment variables
4. Test API connections individually
5. Review implementation status: `IMPLEMENTATION_STATUS.md`
6. Consult plan document: `.claude/plans/curried-giggling-candle.md`

---

## 🎉 You're All Set!

Your WhatsApp Events & Courses Scheduling Chatbot is ready to go!

Start testing with a simple "hi" message and explore the full functionality. Good luck! 🚀
