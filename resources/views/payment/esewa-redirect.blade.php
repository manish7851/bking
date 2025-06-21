<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to eSewa...</title>
</head>
<body>
    <div style="display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column;">
        <h3>Redirecting to eSewa payment...</h3>
        <p>Please do not close this window.</p>
        <form action="{{ $url }}" method="POST" id="esewa-form">
            @foreach($data as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
        </form>
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('esewa-form').submit();
        });
    </script>
</body>
</html>
