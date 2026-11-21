<?php
session_start();
require __DIR__ . "/config/database.php";
$pageTitle = "Jeremiah.dev | Developer Portfolio";
$projects = [];
try {
    $projects = $pdo->query("SELECT id, title, description, tech_stack, image_path, project_url FROM projects WHERE is_published = 1 ORDER BY sort_order ASC, created_at DESC")->fetchAll();
} catch (PDOException $e) {
    // The V3 database upgrade must be imported before project management is available.
}
require __DIR__ . "/includes/header.php";
$flash = $_SESSION["flash"] ?? "";
$flashType = $_SESSION["flash_type"] ?? "success";
unset($_SESSION["flash"], $_SESSION["flash_type"]);
?>
<main>
  <section class="hero container" id="home">
    <div class="hero-copy">
      <h1>Building useful web experiences with <span>clean, practical code.</span></h1>

<p class="lead">
  I’m an aspiring full-stack developer creating responsive websites
  and practical web applications with HTML, CSS, JavaScript, PHP,
  and MySQL. I focus on clear design, accessible interfaces,
  and reliable functionality.
</p>
      <div class="hero-actions">
        <a class="button" href="#projects">Explore my work <span aria-hidden="true">↗</span></a>
        <a class="button button-outline" href="#contact">Let’s talk</a>
      </div>
      <div class="hero-meta">
        <span><i class="status-dot"></i> Open to learning & opportunities</span>
        <span class="meta-divider">•</span>
        <span>HTML · CSS · JavaScript · PHP · MySQL</span>
      </div>
      <div class="social-links" aria-label="Social profiles">
        <!-- Replace # with your real profile URLs when ready. -->
        <a href="#contact" aria-label="LinkedIn profile (add your URL)">LinkedIn <span>↗</span></a>
        <a href="#contact" aria-label="GitHub profile (add your URL)">GitHub <span>↗</span></a>
      </div>
    </div>
    <div class="hero-visual">
      <div class="portrait-frame">
        <div class="portrait-glow"></div>
     <img src="img/profile.png" alt="Portrait of Jeremiah" class="profile-photo">
        <div class="portrait-caption">
          <span class="caption-mark">J.</span>
          <span><strong>Jeremiah</strong><small>Developer in progress</small></span>
        </div>
      </div>
      <div class="floating-code">
        <span class="code-label"><i></i> currently building</span>
        <code>const <b>ideas</b> = "into reality";</code>
      </div>
      <div class="orbit orbit-one"></div><div class="orbit orbit-two"></div>
    </div>
  </section>

  <div class="tech-strip">
    <div class="container tech-strip-inner">
      <span>MY TOOLKIT</span><b>HTML5</b><b>CSS3</b><b>JavaScript</b><b>PHP</b><b>MySQL</b><b>Git</b>
    </div>
  </div>

  <section class="section container reveal" id="about">
    <div class="section-heading">
      <p class="eyebrow">A LITTLE ABOUT ME</p>
      <h2>Curiosity turned into <span>code.</span></h2>
    </div>
    <div class="about-grid">
      <div class="about-intro"><span class="big-quote">“</span><p>I’m Jeremiah, an aspiring full-stack developer interested in building practical, accessible, and reliable digital products.</p></div>
      <div class="about-details">
        <p>I enjoy learning how frontend interfaces, backend logic, and databases work together. My approach is simple: stay curious, build consistently, and improve with every project.</p>
        <a class="text-link" href="#contact">Get in touch <span aria-hidden="true">→</span></a>
      </div>
    </div>
  </section>

  <section class="section section-muted reveal" id="skills">
    <div class="container">
      <div class="section-heading section-heading-row">
        <div><p class="eyebrow">WHAT I WORK WITH</p><h2>Skills & <span>technologies.</span></h2></div>
        <p class="section-aside">The tools I’m using to design, develop, and bring web ideas to life.</p>
      </div>
      <div class="skills-grid">
        <article class="skill-card reveal-item"><span class="skill-icon">⌘</span><span class="skill-index">01</span><h3>HTML</h3><p>Semantic structure and accessible web content.</p></article>
        <article class="skill-card reveal-item"><span class="skill-icon">✳</span><span class="skill-index">02</span><h3>CSS</h3><p>Responsive layouts, visual systems, and styling.</p></article>
        <article class="skill-card reveal-item"><span class="skill-icon">JS</span><span class="skill-index">03</span><h3>JavaScript</h3><p>Interactive interfaces and browser behavior.</p></article>
        <article class="skill-card reveal-item"><span class="skill-icon">₱</span><span class="skill-index">04</span><h3>PHP</h3><p>Server-side logic and form processing.</p></article>
        <article class="skill-card reveal-item"><span class="skill-icon">▤</span><span class="skill-index">05</span><h3>MySQL</h3><p>Structured data storage and database queries.</p></article>
        <article class="skill-card reveal-item"><span class="skill-icon">⑂</span><span class="skill-index">06</span><h3>Git</h3><p>Version control and project collaboration.</p></article>
      </div>
    </div>
  </section>

  <section class="section container reveal" id="projects">
    <div class="section-heading section-heading-row">
      <div><p class="eyebrow">A FEW THINGS I’M BUILDING</p><h2>Selected <span>projects.</span></h2></div>
      <p class="section-aside">A growing collection of practical ideas and hands-on development work.</p>
    </div>
    <?php if ($projects): ?>
      <div class="projects-grid">
        <?php foreach ($projects as $project): ?>
          <article class="project-card reveal-item">
            <?php if (!empty($project["image_path"])): ?>
              <div class="project-image"><img src="<?= htmlspecialchars($project["image_path"], ENT_QUOTES, "UTF-8") ?>" alt="<?= htmlspecialchars($project["title"], ENT_QUOTES, "UTF-8") ?>"></div>
            <?php else: ?>
              <div class="project-art project-art-portfolio"><div class="portfolio-ui"><div class="portfolio-nav"></div><div class="portfolio-hero"><b></b><i></i><i></i><span></span></div><div class="portfolio-cards"><i></i><i></i><i></i></div></div><span class="art-caption">FEATURED PROJECT</span></div>
            <?php endif; ?>
            <div class="project-content">
              <div class="project-topline"><span>FEATURED PROJECT</span><?php if (!empty($project["project_url"])): ?><a class="project-arrow" href="<?= htmlspecialchars($project["project_url"], ENT_QUOTES, "UTF-8") ?>" target="_blank" rel="noopener noreferrer" aria-label="Open <?= htmlspecialchars($project["title"], ENT_QUOTES, "UTF-8") ?>">↗</a><?php endif; ?></div>
              <h3><?= htmlspecialchars($project["title"], ENT_QUOTES, "UTF-8") ?></h3>
              <p><?= nl2br(htmlspecialchars($project["description"], ENT_QUOTES, "UTF-8")) ?></p>
              <?php if (!empty($project["tech_stack"])): ?><div class="tags"><?php foreach (array_filter(array_map("trim", explode(",", $project["tech_stack"]))) as $tag): ?><span><?= htmlspecialchars($tag, ENT_QUOTES, "UTF-8") ?></span><?php endforeach; ?></div><?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-projects"><span class="empty-project-icon">✳</span><h3>Projects are coming soon.</h3><p>I'm preparing a few things to share here. Please check back soon.</p></div>
    <?php endif; ?>
  </section>
  <section class="section container reveal" id="contact">
  <div class="section-heading">
    <p class="eyebrow">GET IN TOUCH</p>
    <h2>Let's build something <span>together.</span></h2>
    <p class="section-aside">Have a project or opportunity? Send me a message.</p>
  </div>

  <form class="contact-form" action="contact.php" method="POST">
    <input type="text" name="name" placeholder="Your name" required>
    <input type="email" name="email" placeholder="Your email" required>
    <input type="text" name="subject" placeholder="Subject" required>
    <textarea name="message" placeholder="Your message" rows="6" required></textarea>
    <button type="submit" class="button">Send Message ↗</button>
  </form>
</section>


