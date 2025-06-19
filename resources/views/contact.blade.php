<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact</title>
    <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
</head>
<body>
    @include('navbar')
   
    <section class="contact-section">
    <h1>Contact Us</h1>
    <form>
        <div>
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required />
        </div>

        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required />
        </div>

        <div>
            <label for="message">Message</label>
            <textarea id="message" name="message" required></textarea>
        </div>

        <button type="submit">Send</button>
    </form>
</section>
</body>
</html>
