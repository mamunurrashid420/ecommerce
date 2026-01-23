#!/bin/bash

# SMS Test Script - Test all SMS endpoints
# Usage: ./test-all-sms-endpoints.sh [phone_number]

PHONE="${1:-01672164422}"
BASE_URL="http://localhost:8000"

echo "=========================================="
echo "SMS Test Suite - e3shopbd"
echo "=========================================="
echo ""
echo "Phone Number: $PHONE"
echo "Base URL: $BASE_URL"
echo ""

# Test 1: SMS Status
echo "=========================================="
echo "Test 1: Checking SMS Service Status"
echo "=========================================="
echo "Endpoint: GET /api/sms-status"
echo ""
curl -s "$BASE_URL/api/sms-status" | python3 -m json.tool
echo ""
echo ""

# Test 2: Send Test SMS
echo "=========================================="
echo "Test 2: Sending Test SMS"
echo "=========================================="
echo "Endpoint: GET /api/test-sms-send?phone=$PHONE"
echo ""
curl -s "$BASE_URL/api/test-sms-send?phone=$PHONE" | python3 -m json.tool
echo ""
echo ""

# Test 3: Check Laravel Logs
echo "=========================================="
echo "Test 3: Recent SMS Logs"
echo "=========================================="
echo "Last 10 SMS-related log entries:"
echo ""
grep -i "sms" storage/logs/laravel.log | tail -n 10
echo ""
echo ""

# Summary
echo "=========================================="
echo "Test Summary"
echo "=========================================="
echo ""
echo "✓ SMS Status API tested"
echo "✓ SMS Send API tested"
echo "✓ Logs checked"
echo ""
echo "Next Steps:"
echo "1. Check if you received the SMS on $PHONE"
echo "2. If not received, check BD Bulk SMS dashboard"
echo "3. Verify account balance and sender ID"
echo ""
echo "Web Interface: $BASE_URL/sms-test.html"
echo ""

