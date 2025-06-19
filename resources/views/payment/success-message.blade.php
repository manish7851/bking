<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        setTimeout(function() {
            window.location.href = "{{ $redirectUrl }}";
        }, 2500);
    </script>
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height:100vh;">
    <div class="card shadow p-4 text-center">
        <div class="mb-3">
            <svg width="64" height="64" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="32" cy="32" r="32" fill="#28a745"/><path d="M44 26l-12 12-4-4" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h2 class="text-success mb-3">Success!</h2>
        <p class="mb-0">{{ $message }}</p>
        <small class="text-muted">You will be redirected shortly...</small>
    </div>
</body>
</html>
