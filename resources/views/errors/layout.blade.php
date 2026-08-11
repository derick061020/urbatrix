<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    <style>
        :root {
            --ink-950: #10201c;
            --ink-600: #5b6b66;
            --ink-400: #93a29d;
            --line: #e4e9e7;
            --brand: #074540;
            --sand: #faf8f4;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: var(--sand);
            color: var(--ink-950);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .card {
            width: 100%;
            max-width: 460px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 36px 32px;
            text-align: center;
            box-shadow: 0 12px 40px rgba(16, 32, 28, .06);
        }
        .code {
            font-size: 12px;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--ink-400);
        }
        h1 {
            margin: 10px 0 8px;
            font-size: 22px;
            line-height: 1.25;
            font-weight: 700;
        }
        p {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
            color: var(--ink-600);
        }
        .actions {
            margin-top: 26px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        a.btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid var(--line);
            color: var(--ink-950);
            background: #fff;
        }
        a.btn-primary {
            background: var(--brand);
            border-color: var(--brand);
            color: #fff;
        }
        .brand {
            margin-top: 28px;
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--ink-400);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">@yield('code')</div>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            <a class="btn btn-primary" href="{{ url('/') }}">{{ __('Ir al inicio') }}</a>
            <a class="btn" href="javascript:history.back()">{{ __('Volver') }}</a>
        </div>
        <div class="brand">{{ config('app.name') }}</div>
    </div>
</body>
</html>
