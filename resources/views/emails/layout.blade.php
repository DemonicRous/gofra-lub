<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'GofraLub' }}</title>
    <style>
        /* Reset styles */
        body, table, td, a {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            background-color: #f3f4f6;
            line-height: 1.5;
        }

        /* Container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Header */
        .email-header {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            padding: 48px 24px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Декоративные элементы */
        .email-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            pointer-events: none;
        }

        .email-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Логотип */
        .logo {
            display: inline-block;
            text-decoration: none;
            position: relative;
            z-index: 1;
        }

        .logo-text {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff;
        }

        .logo-highlight {
            background: #ffffff;
            color: #2563eb;
            padding: 4px 12px;
            border-radius: 12px;
            display: inline-block;
            margin-left: 4px;
            font-weight: 800;
        }

        /* Slogan */
        .slogan {
            margin-top: 16px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }

        /* Content */
        .email-content {
            padding: 40px 32px;
        }

        /* Button */
        .email-button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 600;
            margin: 24px 0;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .email-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        /* Footer */
        .email-footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
        }

        /* Typography */
        h1 {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 16px;
        }

        h2 {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
        }

        p {
            color: #4b5563;
            margin-bottom: 16px;
            line-height: 1.6;
        }

        .text-center {
            text-align: center;
        }

        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 24px 0;
        }

        /* Info box */
        .info-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .info-box p {
            margin: 0;
            color: #1e40af;
        }

        /* Badge */
        .badge {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-content {
                padding: 24px 20px;
            }

            .email-header {
                padding: 32px 20px;
            }

            h1 {
                font-size: 20px;
            }

            .logo-text {
                font-size: 28px;
            }

            .logo-highlight {
                padding: 3px 10px;
                font-size: 28px;
            }

            .slogan {
                font-size: 12px;
            }
        }
    </style>
</head>
<body style="background-color: #f3f4f6; padding: 40px 20px;">
<div class="email-container">
    <!-- Header -->
    <div class="email-header">
        <div class="logo">
                <span class="logo-text">
                    Gofra<span class="logo-highlight">LUB</span>
                </span>
        </div>
        @if(isset($subtitle))
            <div class="slogan">{{ $subtitle }}</div>
        @else
            <div class="slogan">Автоматизация отдела развития</div>
        @endif
    </div>

    <!-- Content -->
    <div class="email-content">
        @yield('content')
    </div>

    <!-- Footer -->
    <div class="email-footer">
        <p style="margin-bottom: 8px;">© {{ date('Y') }} GofraLub. Все права защищены.</p>
        <p style="margin-bottom: 8px;">Система для автоматизации работы отдела развития фабрики ЮжУралКартон</p>
        <p style="font-size: 11px; color: #9ca3af;">
            Это автоматическое сообщение, пожалуйста, не отвечайте на него.
        </p>
    </div>
</div>
</body>
</html>
