<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - FMIKOM Portal</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,600,800|inter:400,600,700,900&display=swap" rel="stylesheet" />



    <style>
        body {
            background-color: #121214;
            color: #ffffff;
            font-family: 'Instrument Sans', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            overflow-x: hidden;
        }
        .error-container {
            display: flex;
            flex-direction: column-reverse;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
            box-sizing: border-box;
            gap: 2rem;
        }
        @media (min-width: 768px) {
            .error-container {
                flex-direction: row;
                gap: 5rem;
                max-width: 1000px;
                margin: 0 auto;
            }
            .text-column {
                align-items: flex-start;
                text-align: left;
                width: 45%;
            }
            .graphic-column {
                width: 55%;
            }
        }
        .text-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .error-title {
            font-size: 1.875rem;
            font-weight: 900;
            margin: 0;
            letter-spacing: -0.025em;
            color: #ffffff;
        }
        .error-desc {
            font-size: 0.875rem;
            color: #a1a1aa;
            margin-top: 0.85rem;
            line-height: 1.6;
            font-weight: 500;
        }
        .btn-goback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.65rem;
            background-color: #00c853;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.75rem;
            border-radius: 9999px;
            text-decoration: none;
            margin-top: 1.75rem;
            box-shadow: 0 4px 14px rgba(0, 200, 83, 0.25);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            border: none;
            cursor: pointer;
            outline: none;
        }
        .btn-goback:hover {
            background-color: #00e676;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 200, 83, 0.35);
        }
        .btn-goback:active {
            transform: translateY(0);
        }
        .graphic-column {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .digits-wrapper {
            display: flex;
            align-items: center;
            font-weight: 900;
            color: #3f3f46;
            letter-spacing: -0.05em;
            user-select: none;
        }
        .digit-text {
            font-size: 6.5rem;
            line-height: 1;
            font-family: 'Inter', sans-serif;
            font-weight: 900;
        }
        @media (min-width: 640px) {
            .digit-text {
                font-size: 9.5rem;
            }
        }
        @media (min-width: 768px) {
            .digit-text {
                font-size: 13rem;
            }
        }
        .svg-container {
            width: 5.5rem;
            height: 6.5rem;
            margin: 0 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (min-width: 640px) {
            .svg-container {
                width: 8rem;
                height: 9.5rem;
                margin: 0 0.5rem;
            }
        }
        @media (min-width: 768px) {
            .svg-container {
                width: 11rem;
                height: 13rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <!-- Left Text Column -->
        <div class="text-column">
            <h1 class="error-title">@yield('title')</h1>
            <p class="error-desc">@yield('message')</p>
            <button id="btn-goback" class="btn-goback">
                Go Back
            </button>
        </div>
        
        <!-- Right Graphic Column -->
        <div class="graphic-column">
            <div class="digits-wrapper">
                <span class="digit-text">@yield('first_digit')</span>
                <div class="svg-container">
                    @yield('svg')
                </div>
                <span class="digit-text">@yield('last_digit')</span>
            </div>
        </div>
    </div>

    @if(isset($csp_nonce))
    <script nonce="{{ $csp_nonce }}">
    @else
    <script>
    @endif
        document.getElementById('btn-goback').addEventListener('click', function() {
            if (document.referrer && document.referrer.indexOf(window.location.host) !== -1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        });
    </script>
</body>
</html>
