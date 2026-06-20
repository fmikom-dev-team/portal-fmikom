<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        body {
            background: #f5f7fb;
            color: #0f172a;
            font-family: var(--font-family-body, "Times New Roman", serif);
            margin: 0;
            padding: 32px 20px;
        }

        .page {
            background: #fff;
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.08);
            margin: 0 auto;
            max-width: 820px;
            min-height: 1120px;
            padding: 56px 72px;
        }

        h1 {
            font-size: 24px;
            letter-spacing: 0.04em;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        p {
            font-size: 17px;
            line-height: 1.7;
            margin: 0 0 12px;
        }

        @media (max-width: 640px) {
            body {
                padding: 8px 6px;
            }

            .page {
                box-shadow: none;
                max-width: 100%;
                min-height: auto;
                padding: 16px 14px;
                font-size: 13px;
                line-height: 1.45;
            }

            h1 {
                font-size: 16px;
                line-height: 1.2;
            }

            p {
                font-size: 12px;
                line-height: 1.45;
                margin: 0 0 8px;
            }

            .page table {
                font-size: 12px;
            }

            .page td,
            .page th {
                padding: 0 3px;
            }

            .page img,
            .page video,
            .page svg {
                max-width: 100%;
                height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        {!! $html !!}
    </div>
</body>
</html>
