<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
        }
        
        .logo-placeholder {
            width: 80px;
            height: 80px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            line-height: 1;
            position: relative;
        }
        
        .logo-placeholder::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0;
            height: 0;
        }
        
        .logo-placeholder span {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            margin: 0;
            padding: 0;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 600;
        }
        
        .main-text {
            font-size: 16px;
            color: #34495e;
            margin-bottom: 25px;
            line-height: 1.7;
        }
        
        .footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 30px;
            border-radius: 0 0 12px 12px;
        }
        
        .footer p {
            margin: 5px 0;
            opacity: 0.8;
        }
        
        .footer .company-name {
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            
            .header, .content, .footer {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="header">
            <div class="logo-placeholder"><span>SF</span></div>
            <h1>{{ $companyName }}</h1>
            <p>Mfumo wa Usimamizi wa Fedha</p>
        </div>
        
        <!-- Main Content -->
        <div class="content">
            <div class="greeting">
                Dear {{ $recipientName }},
            </div>
            
            <div class="main-text">
                {!! nl2br(e($content)) !!}
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="company-name">{{ $companyName }}</div>
            <p>Timu ya SmartFinance</p>
        </div>
    </div>
</body>
</html> 