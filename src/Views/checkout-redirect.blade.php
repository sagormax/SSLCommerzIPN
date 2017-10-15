<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Redirecting | SSL Commerz Checkout</title>
</head>
<body>
    <center>
        <img src="{{ asset('vendor/SSLWIRELESS/SSLCommerzIPN/images/loader.gif') }}" alt="loading..." />
    </center>
    <script type="text/javascript">
        window.location.href = "{!! $redirect_url !!}";
    </script>
</body>
</html>