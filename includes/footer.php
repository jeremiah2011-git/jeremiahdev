<footer class="site-footer">
  <div class="container footer-inner">
    <div>
      <a class="logo" href="<?= htmlspecialchars($homePrefix ?? "", ENT_QUOTES, "UTF-8") ?>index.php">Jeremiah<span>.dev</span></a>
      <p>Designed with curiosity. Built with code.</p>
    </div>
    <div class="footer-socials" aria-label="Social links">
      <!-- Replace these # destinations with your actual profile URLs. -->
      <a href="#contact" aria-label="LinkedIn profile (add your URL)">LinkedIn ↗</a>
      <a href="#contact" aria-label="GitHub profile (add your URL)">GitHub ↗</a>
      <a href="index.php#contact">Contact ↗</a>
    </div>
    <p>© <?= date("Y") ?> Jeremiah. All rights reserved.</p>
  </div>
</footer>
</body>
</html>
