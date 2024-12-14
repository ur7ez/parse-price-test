<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLX Price Change Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 16px;
        }
        table th, table td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        table th {
            background-color: #f4f4f4;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>OLX Price Change Notification</h1>
    <br/>
    <p>Dear {{ $subscriberLogin }},</p>
    <p>The following OLX ads have experienced price changes:</p>
    <table>
        <thead>
            <tr>
                <th>URL</th>
                <th>Previous price</th>
                <th>Current price</th>
                <th>Currency</th>
                <th>Reviewed at</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($priceChanges as $change)
                <tr>
                    <td><a href="{{ $change['url'] }}" title="Click to open OLX advert">.../{{ basename($change['url']) }}</a></td>
                    <td>{{ $change['previous_price'] ?? 'Unknown' }}</td>
                    <td>{{ $change['current_price'] ?? 'Removed' }}</td>
                    <td>{{ $change['price_currency'] ?? 'N/A' }}</td>
                    <td>{{ $change['parsed_at']->format('d-m-Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <br/>
    <p>Thank you for using our service!</p>
    <br/>
    <footer>
        <p>Best regards,</p>
        <p>{{config('app.name')}} Team</p>
    </footer>
</body>
</html>
