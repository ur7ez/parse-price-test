<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Failed Job Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <h1>Failed Job Notification</h1>
    <br/>
    <p><strong>Connection:</strong> {{ $connection }}</p>
    <p><strong>Queue:</strong> {{ $queue }}</p>
    <p><strong>Job Name:</strong> {{ $jobName }}</p>
    <p><strong>Error:</strong> {{ $exception }}</p>
    <br/>
    <footer>
        <p>{{config('app.name')}} Team</p>
    </footer>
</body>
</html>
