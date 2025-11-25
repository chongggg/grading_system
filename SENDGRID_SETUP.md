# SendGrid Setup Instructions

If port 587 doesn't work, use SendGrid SMTP Relay (free 100 emails/day):

## Step 1: Get SendGrid API Key
1. Sign up at https://sendgrid.com (free account)
2. Go to Settings > API Keys
3. Create new API key with "Mail Send" permissions
4. Copy the API key (starts with `SG.`)

## Step 2: Update mail_helper.php

Replace the SMTP config section with:

```php
// SendGrid SMTP config
$mail->isSMTP();
$mail->Host       = 'smtp.sendgrid.net';
$mail->SMTPAuth   = true;
$mail->Username   = 'apikey';  // literally the word "apikey"
$mail->Password   = 'YOUR_SENDGRID_API_KEY_HERE';
$mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

## Step 3: Update env.php (recommended)

Add to env.php:
```php
$sendgrid_api_key = "YOUR_SENDGRID_API_KEY_HERE";
```

Then in mail_helper.php:
```php
$mail->Password   = $GLOBALS['sendgrid_api_key'] ?? 'ylhe ufic nuff vmtw';
```

## Benefits:
- ✅ No code changes in controllers
- ✅ Works with PHPMailer (drop-in replacement)
- ✅ No firewall/port blocking issues
- ✅ 100 emails/day free
- ✅ Better deliverability
