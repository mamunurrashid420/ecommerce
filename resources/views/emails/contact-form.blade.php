<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Submission</title>
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
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .field {
            margin-bottom: 15px;
        }
        .field-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .field-value {
            color: #333;
            padding: 8px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        .message-box {
            padding: 15px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 3px;
            min-height: 100px;
            white-space: pre-wrap;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #777;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Contact Form Submission</h1>
    </div>
    
    <div class="content">
        <div class="field">
            <div class="field-label">Name:</div>
            <div class="field-value">{{ $contact->name }}</div>
        </div>
        
        <div class="field">
            <div class="field-label">Email:</div>
            <div class="field-value">{{ $contact->email }}</div>
        </div>
        
        @if($contact->phone)
        <div class="field">
            <div class="field-label">Phone:</div>
            <div class="field-value">{{ $contact->phone }}</div>
        </div>
        @endif
        
        @if($contact->subject)
        <div class="field">
            <div class="field-label">Subject:</div>
            <div class="field-value">{{ $contact->subject }}</div>
        </div>
        @endif
        
        <div class="field">
            <div class="field-label">Message:</div>
            <div class="message-box">{{ $contact->message }}</div>
        </div>
        
        <div class="field">
            <div class="field-label">Submitted At:</div>
            <div class="field-value">{{ $contact->created_at->format('F j, Y \a\t g:i A') }}</div>
        </div>
    </div>
    
    <div class="footer">
        <p>This is an automated email from your website contact form.</p>
        <p>You can view and manage this contact submission in your admin panel.</p>
    </div>
</body>
</html>

