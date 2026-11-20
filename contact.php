<form class="contact-form" action="contact.php" method="POST">
    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION["csrf_token"], ENT_QUOTES, "UTF-8") ?>"
    >

    <input type="text" name="name" placeholder="Your name" required>
    <input type="email" name="email" placeholder="Your email" required>
    <input type="text" name="subject" placeholder="Subject" required>
    <textarea name="message" placeholder="Your message" rows="6" required></textarea>
    <button type="submit" class="button">Send Message ↗</button>
</form>