<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance - I-TRAC</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #900b09;
            min-height: 100vh;
            overflow: hidden;
        }
        .maintenance-wrapper {
            min-height: 100vh;
            width: 100%;
            background-image: url("{{ asset('img/UNDER MAINTENANCE.svg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            padding: 20px;
            padding-bottom: 12vh;
        }
        .content-container {
            max-width: 800px;
            width: 100%;
            text-align: center;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
        }
        .headline {
            font-size: 2.3rem;
            font-weight: 400;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .subtext {
            font-size: 1.15rem;
            line-height: 1.6;
            color: #ffffff;
            margin: 0;
            font-weight: 300;
            letter-spacing: 0.3px;
        }
        .btn-go-back {
            display: inline-block;
            background-color: #900b09;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            padding: 10px 36px;
            border-radius: 6px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            margin-top: 15px;
            transition: background-color 0.2s ease-in-out, transform 0.1s ease-in-out;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
        }
        .btn-go-back:hover {
            background-color: #a81c1a;
            transform: scale(1.02);
        }
        .btn-go-back:active {
            transform: scale(0.98);
        }
        @media (max-width: 768px) {
            .maintenance-wrapper {
                padding-bottom: 8vh;
            }
            .headline {
                font-size: 1.75rem;
            }
            .subtext {
                font-size: 0.95rem;
                line-height: 1.5;
            }
            .btn-go-back {
                padding: 10px 28px;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-wrapper">
        <div class="content-container">
            <a href="javascript:void(0);" onclick="goBack()" class="btn-go-back">Go Back</a>
        </div>
    </div>

    <script>
        function goBack() {
            if (document.referrer && document.referrer.includes(window.location.hostname)) {
                history.back();
            } else {
                window.location.href = "{{ route('account.settings') }}";
            }
        }
    </script>
</body>
</html>
