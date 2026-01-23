# SMS Testing Documentation

## Overview
This document provides information about testing SMS functionality on the e3shopbd server.

## Test Results Summary

✅ **Server SMS Integration**: Working correctly
✅ **API Connection**: Successfully connected to BD Bulk SMS API
✅ **API Response**: Returns "SMS Sent Successfully"
⚠️ **SMS Delivery**: Not received on phone (requires investigation)

## Available Test Endpoints

### 1. Test SMS Send API
**Endpoint:** `GET /api/test-sms-send`

**Parameters:**
- `phone` (required): Phone number to send SMS to

**Example:**
```bash
curl "http://your-domain.com/api/test-sms-send?phone=01672164422"
```

**Response:**
```json
{
  "success": true,
  "message": "SMS sent successfully! Check your phone.",
  "data": {
    "original_phone": "01672164422",
    "formatted_phone": "+8801672164422",
    "timestamp": "2026-01-23 04:34:15",
    "http_code": 200,
    "api_response": [
      {
        "to": "+8801672164422",
        "message": "Test SMS from e3shopbd server...",
        "status": "SENT",
        "statusmsg": "SMS Sent Successfully To +8801672164422"
      }
    ]
  },
  "debug": {
    "api_url": "https://api.bdbulksms.net/api.php?json",
    "token_configured": "Yes",
    "token_length": 52
  }
}
```

### 2. SMS Status API
**Endpoint:** `GET /api/sms-status`

**Example:**
```bash
curl "http://your-domain.com/api/sms-status"
```

**Response:**
```json
{
  "success": true,
  "message": "SMS service configuration",
  "data": {
    "api_url": "https://api.bdbulksms.net/api.php?json",
    "token_configured": true,
    "token_length": 52
  },
  "status": "Configured"
}
```

### 3. Web-based Test Interface
**URL:** `http://your-domain.com/sms-test.html`

A user-friendly web interface for testing SMS sending.

## Why SMS is Not Being Received

The server is successfully sending SMS requests to BD Bulk SMS API, and the API is responding with "SENT" status. However, the SMS is not being delivered to the phone. Here are the most likely reasons:

### 1. **Account Balance** (Most Likely)
- Your BD Bulk SMS account may have insufficient balance
- **Action:** Login to https://bdbulksms.net and check your account balance

### 2. **Sender ID Not Approved**
- In Bangladesh, promotional/transactional SMS requires BTRC-approved Sender ID
- Messages without approved Sender ID may be blocked by carriers
- **Action:** Check your BD Bulk SMS dashboard for Sender ID configuration

### 3. **Account Status**
- Your BD Bulk SMS account might be inactive or suspended
- **Action:** Verify account status in the dashboard

### 4. **Phone Number Issues**
- Number might be on DND (Do Not Disturb) list
- Carrier might be blocking promotional SMS
- **Action:** Try sending to a different phone number

### 5. **Network Delays**
- SMS can sometimes be delayed by hours due to network congestion
- **Action:** Wait and check again later

## Recommended Actions

### Priority 1: Check BD Bulk SMS Dashboard
1. Login to https://bdbulksms.net
2. Check account balance
3. Check SMS delivery reports
4. Verify account status
5. Check Sender ID configuration

### Priority 2: Contact BD Bulk SMS Support
- Provide them with:
  - Phone number: +8801672164422
  - Timestamp: Check the API response
  - API response showing "SENT" status
- Ask why messages with "SENT" status are not being delivered

### Priority 3: Test with Different Numbers
- Try sending to different phone numbers
- Try different carriers (GP, Robi, Banglalink, etc.)

## Configuration Details

**SMS Gateway:** BD Bulk SMS (https://bdbulksms.net)
**API URL:** https://api.bdbulksms.net/api.php?json
**Token:** Configured (52 characters)

## Files Created

1. **Controller:** `app/Http/Controllers/Api/SmsTestController.php`
2. **Routes:** Added to `routes/api.php`
3. **Test Page:** `public/sms-test.html`
4. **CLI Test Scripts:**
   - `test-sms.php` - Command line SMS test
   - `check-sms-account.php` - Account status check

## Testing from Command Line

```bash
# Test SMS sending
php test-sms.php 01672164422

# Check account status
php check-sms-account.php
```

## Logs

SMS attempts are logged in `storage/logs/laravel.log`

View SMS logs:
```bash
tail -f storage/logs/laravel.log | grep -i sms
```

## Support

If issues persist after checking the above:
1. Contact BD Bulk SMS support
2. Verify with them that your account can send SMS
3. Ask for delivery reports for the test messages
4. Request they check if there are any restrictions on your account

## Conclusion

The server-side SMS integration is working correctly. The issue is likely with:
- BD Bulk SMS account balance/status
- Sender ID approval
- Carrier-level blocking

Next step: **Check your BD Bulk SMS account dashboard immediately.**

