<?php
/**
 * index.php — Main portfolio page.
 * All photos, skill images, project thumbnails and the CV file
 * are managed from admin/media.php — no code changes needed.
 */
require_once 'db_connect.php';

// ── Defaults (shown if DB is unavailable) ─────────────────────────────────────
$s = [
  'portfolio_photo'      => 'images/wakoli-picc.jpeg',
  'about_photo'          => 'images/WAA.jpg',
  'skill_img_frontend'   => 'images/twwwo.jpg',
  'skill_img_javascript' => 'images/js.jpg',
  'skill_img_design'     => 'images/ui and ux.jpg',
  'skill_img_portfolio'  => 'images/ONE.jpg',
  'project_img_1'        => 'images/focus (1).jpg',
  'project_img_2'        => 'images/web.png',
  'project_img_3'        => 'images/dashbord.jpg',
  'cv_file'              => 'cv/Wenyaa-Brian-Wakoli-CV.pdf',
];

try {
    $conn = get_db_connection();
    $res  = $conn->query("SELECT setting_key, setting_value FROM site_settings");
    while ($row = $res->fetch_assoc()) {
        $s[$row['setting_key']] = $row['setting_value'];
    }
    $conn->close();
} catch (RuntimeException $e) { /* silently use defaults if DB is down */ }

// Sanitise every value for safe HTML output
foreach ($s as $k => $v) {
    $s[$k] = htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />

  <!-- ═══════════════════════════════════════════════════
       PRIMARY SEO
  ════════════════════════════════════════════════════ -->
  <title>Wenyaa Brian Wakoli | Web Developer & UI Designer – Nairobi, Kenya</title>
  <meta name="description"
    content="Wenyaa Brian Wakoli is a Nairobi-based web developer and UI designer specialising in responsive websites, landing pages, graphic design, and OS installation. Available for freelance projects." />
  <meta name="keywords"
    content="web developer Kenya, UI designer Nairobi, freelance web developer, HTML CSS JavaScript, portfolio website, graphic designer Kenya, computer maintenance Nairobi, Wenyaa Brian Wakoli, Marvels Empire Creations" />
  <meta name="author" content="Wenyaa Brian Wakoli" />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://wakolibrian.github.io/" />

  <!-- ═══════════════════════════════════════════════════
       OPEN GRAPH (Facebook, WhatsApp, LinkedIn previews)
  ════════════════════════════════════════════════════ -->
  <meta property="og:type"        content="website" />
  <meta property="og:url"         content="https://wakolibrian.github.io/" />
  <meta property="og:title"       content="Wenyaa Brian Wakoli | Web Developer & UI Designer" />
  <meta property="og:description" content="Responsive websites, landing pages, graphic design, and OS installation. Based in Nairobi, Kenya. Available for freelance projects." />
  <meta property="og:image"       content="https://wakolibrian.github.io/images/wakoli-picc.jpeg" />
  <meta property="og:image:width"  content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:locale"      content="en_KE" />
  <meta property="og:site_name"   content="Marvels Empire Creations" />

  <!-- ═══════════════════════════════════════════════════
       TWITTER / X CARD
  ════════════════════════════════════════════════════ -->
  <meta name="twitter:card"        content="summary_large_image" />
  <meta name="twitter:title"       content="Wenyaa Brian Wakoli | Web Developer & UI Designer" />
  <meta name="twitter:description" content="Responsive websites, landing pages, graphic design, and OS installation. Based in Nairobi, Kenya." />
  <meta name="twitter:image"       content="https://wakolibrian.github.io/images/wakoli-picc.jpeg" />

  <!-- ═══════════════════════════════════════════════════
       STRUCTURED DATA — Person (Google rich results)
  ════════════════════════════════════════════════════ -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Person",
    "name": "Wenyaa Brian Wakoli",
    "jobTitle": "Web Developer & UI Designer",
    "url": "https://wakolibrian.github.io/",
    "email": "wakolibrian1@gmail.com",
    "telephone": "+254706155601",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "Nairobi",
      "addressCountry": "KE"
    },
    "sameAs": ["https://github.com/dashboard"],
    "knowsAbout": ["HTML", "CSS", "JavaScript", "Graphic Design", "Computer Networking", "Database Management"]
  }
  </script>

  <!-- ═══════════════════════════════════════════════════
       FAVICONS
  ════════════════════════════════════════════════════ -->
  <link rel="icon" type="image/png" sizes="32x32"   href="images/logo rmove bg (1).png" />
  <link rel="icon" type="image/png" sizes="16x16"   href="images/logo rmove bg (1).png" />
  <link rel="apple-touch-icon" sizes="180x180"      href="images/logo rmove bg (1).png" />
  <link rel="icon" type="image/png" sizes="192x192" href="images/logo rmove bg (1).png" />
  <link rel="icon" type="image/png" sizes="512x512" href="images/logo rmove bg (1).png" />
  <meta name="theme-color" content="#0669f7" />
  <meta name="msapplication-TileColor" content="#0669f7" />

  <link rel="stylesheet" href="stylesheet.css" />
</head>

<body>
  <div class="onee">
    <header class="site-header" style="position:relative;">
      <div class="container header-inner">
        <div class="brand">
          <div class="brand-logo">
            <img src="images/logo rmove bg (1).png" alt="Marvels Empire Creations logo" />
          </div>
          <div>
            <p class="brand-name">Marvels Empire Creations</p>
            <p class="brand-tagline">Web Developer · UX-Focused Designer · Operating system installation</p>
          </div>
        </div>

        <button class="menu-toggle" aria-expanded="false" aria-controls="site-nav" aria-label="Toggle navigation">
          <span></span><span></span><span></span>
        </button>

        <nav class="site-nav" id="site-nav" aria-label="Primary navigation">
          <a href="#home">Home</a>
          <a href="#about">About</a>
          <a href="#skills">Skills</a>
          <a href="#projects">Projects</a>
          <a href="#contact">Contact</a>
        </nav>
      </div>
    </header>
  </div>

  <main id="main">

    <!-- ── Hero ─────────────────────────────────────────────── -->
    <section id="home" class="hero section hero-section">
      <div class="container hero-grid">
        <div class="hero-copy">
          <div class="two">
            <div class="hero-card-photo" aria-hidden="true">
              <!-- ★ Managed from admin → Media Manager ★ -->
              <img src="<?= $s['portfolio_photo'] ?>"
                   alt="Wenyaa Brian Wakoli — portfolio photo"
                   loading="eager"
                   width="600"
                   height="500">
            </div>
          </div>
          <div class="three">
            <p class="eyebrow">Hello, I'm Wenyaa</p>
            <h1>Building modern web experiences that feel fast, polished, and easy to use.</h1>
            <p class="hero-text">I create accessible, responsive websites and web apps using clean design and scalable
              front-end code. My work is tailored for professionals, startups, and creative businesses.</p>
          </div>
          <div class="buttons">
            <a class="button p" href="#projects">View Work</a>
            <a class="button s" href="#contact">Get in Touch</a>
            <a class="button cv" href="<?= $s['cv_file'] ?>" download aria-label="Download CV">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
              Download CV
            </a>
          </div>
        </div>

        <div class="hero-card">
          <div class="hero-card-content">
            <p class="card-title">Current Focus</p>
            <img src="images/focus.jpg" alt="Current focus — web development work" loading="lazy">
            <p>Frontend development, operating system installation, performance optimization, and accessibility.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ── About ─────────────────────────────────────────────── -->
    <section id="about" class="section about-section">
      <div class="container section-grid">
        <div>
          <p class="section-label">About Me</p>
          <h2>Creative problem solver with a passion for clean code.</h2>
          <p>I am a dedicated web developer with experience in building professional portfolio websites, landing pages,
            and custom front-end solutions. I focus on making every interaction intuitive, accessible, and visually
            appealing.</p>
          <h1>Personal Information</h1>
          <p>Name: Brian Wakoli Wenyaa<br>
            Place of Birth: Bungoma County, KENYA<br>
            Tel: <a href="tel:+254706155601">+254 706 155 601</a>
          </p>
          <h1>Educational Background</h1>
          <p>Primary School: Padmum Educational Centre.<br>
            Secondary School: St Mathias Kholera Boys.<br>
            Tertiary Education: National Youth Service Engineering Institute.
          </p>
          <h1>My Skills</h1>
          <p>
            Full Stack Web Developer<br>
            Operating System Installation<br>
            Graphic Design<br>
            Computer Networking<br>
            Computer Maintenance<br>
            Database Management
          </p>
          <ul class="about-list">
            <li><strong>Location:</strong> Nairobi, Kenya</li>
            <li><strong>Role:</strong> Web Developer & UI Designer, Graphic Designer, OS Installation</li>
            <li><strong>Experience:</strong> Modern HTML, CSS, JavaScript, responsive layout, graphic design</li>
          </ul>
        </div>
        <aside class="about-card">
          <p class="card-title">What I bring</p>
          <p>Fast-loading user interfaces, strong attention to detail, and polished portfolio-ready websites designed to
            impress potential clients and employers.</p>
          <img src="<?= $s['about_photo'] ?>" alt="Wenyaa Brian Wakoli at work" loading="lazy">
        </aside>
      </div>
    </section>

    <!-- ── Skills ─────────────────────────────────────────────── -->
    <section id="skills" class="section skills-section">
      <div class="container">
        <p class="section-label">Skills</p>
        <h2>Core strengths for modern web projects</h2>
        <div class="skills-grid">
          <article class="skill-card">
            <div class="skill-img-wrap">
              <img src="<?= $s['skill_img_frontend'] ?>" alt="Frontend Development — HTML and CSS code on a screen" loading="lazy" />
            </div>
            <div class="skill-body">
              <h3>Frontend Development</h3>
              <p>HTML, CSS, responsive layout, semantic markup, cross-browser compatibility.</p>
            </div>
          </article>
          <article class="skill-card">
            <div class="skill-img-wrap">
              <img src="<?= $s['skill_img_javascript'] ?>" alt="JavaScript programming language logo" loading="lazy" />
            </div>
            <div class="skill-body">
              <h3>JavaScript</h3>
              <p>Interactive elements, form validation, dynamic content, UX enhancements.</p>
            </div>
          </article>
          <article class="skill-card">
            <div class="skill-img-wrap">
              <img src="<?= $s['skill_img_design'] ?>" alt="UI and UX design wireframe illustration" loading="lazy" />
            </div>
            <div class="skill-body">
              <h3>Design & UX</h3>
              <p>Clear visual hierarchy, accessible color contrast, responsive navigation, polished UI.</p>
            </div>
          </article>
          <article class="skill-card">
            <div class="skill-img-wrap">
              <img src="<?= $s['skill_img_portfolio'] ?>" alt="Portfolio presentation on laptop and mobile" loading="lazy" />
            </div>
            <div class="skill-body">
              <h3>Portfolio Presentation</h3>
              <p>Showcase projects with strong storytelling, clean case studies, and easy navigation.</p>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- ── Projects ───────────────────────────────────────────── -->
    <section id="projects" class="section projects-section">
      <div class="container">
        <p class="section-label">Projects</p>
        <h2>Selected work</h2>
        <div class="projects-grid">
          <article class="project-card">
            <div class="project-thumb">
              <img src="<?= $s['project_img_1'] ?>" alt="Professional Portfolio Website screenshot" loading="lazy">
            </div>
            <div class="project-details">
              <h3>Professional Portfolio Website</h3>
              <p>A polished portfolio design for creatives and developers, focused on clarity, responsiveness, and a
                strong first impression.</p>
              <a class="project-link" href="https://github.com/dashboard" target="_blank"
                rel="noopener noreferrer">View on GitHub</a>
            </div>
          </article>
          <article class="project-card">
            <div class="project-thumb">
              <img src="<?= $s['project_img_2'] ?>" alt="Landing Page Redesign screenshot" loading="lazy">
            </div>
            <div class="project-details">
              <h3>Landing Page Redesign</h3>
              <p>A conversion-focused landing experience with clear calls to action, fast loading, and modern
                typography.</p>
              <a class="project-link" href="https://github.com/dashboard" target="_blank"
                rel="noopener noreferrer">View on GitHub</a>
            </div>
          </article>
          <article class="project-card">
            <div class="project-thumb">
              <img src="<?= $s['project_img_3'] ?>" alt="Responsive Web App Interface — admin dashboard screenshot" loading="lazy">
            </div>
            <div class="project-details">
              <h3>Responsive Web App Interface</h3>
              <p>A user-friendly dashboard layout built for easy navigation across desktop and mobile screens.</p>
              <a class="project-link" href="https://github.com/dashboard" target="_blank"
                rel="noopener noreferrer">View on GitHub</a>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- ── Contact ────────────────────────────────────────────── -->
    <section id="contact" class="section contact-section">
      <div class="container">
        <div id="form-status" class="form-status" role="alert" aria-live="polite" hidden></div>
        <div class="contact-grid">
          <div>
            <p class="section-label">Contact</p>
            <h2>Ready to work together?</h2>
            <p>Do you have a question, want a quote for your website, need to talk, or would like to work with me?
              Send a message to discuss web development, portfolio design, computer maintenance,
              or your next project — you are welcome!</p>
            <ul class="contact-list">
              <li><strong>Email:</strong> <a href="mailto:wakolibrian1@gmail.com">wakolibrian1@gmail.com</a></li>
              <li><strong>Phone:</strong> <a href="tel:+254706155601">+254 706 155 601</a></li>
              <li><strong>Location:</strong> Nairobi, Kenya</li>
            </ul>
          </div>

          <form class="contact-form" action="wenyaa.php" method="post">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" autocomplete="name" maxlength="254"
              placeholder="Your name" required />
            <label for="email">Email</label>
            <input type="email" id="email" name="email" maxlength="254" autocomplete="email"
              placeholder="Enter your email" required />
            <label for="subject">Subject</label>
            <select id="subject" name="subject" required>
              <option value="">Select a subject</option>
              <option value="Technical inquiry">Technical Inquiry</option>
              <option value="General Inquiry">General Inquiry</option>
              <option value="Business Partnership">Business Partnership</option>
              <option value="Feedback">Feedback</option>
              <option value="Other">Other</option>
            </select>
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="5"
              placeholder="Tell me about your project" required></textarea>
            <button class="button primary" type="submit">Send Message</button>
          </form>
        </div>
      </div>
    </section>

  </main>

  <!-- ── Footer ─────────────────────────────────────────────── -->
  <footer class="site-footer">
    <div class="container footer-inner">
      <p>© <?= date('Y') ?> Wenyaa Brian Wakoli. Built with modern HTML and CSS for a clean, professional portfolio experience.</p>
      <div class="footer-links">
        <a href="mailto:wakolibrian1@gmail.com" class="footer-link" aria-label="Send email to Wenyaa">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="2" y="4" width="20" height="16" rx="2"/>
            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
          </svg>
          wakolibrian1@gmail.com
        </a>
        <a href="tel:+254706155601" class="footer-link" aria-label="Call Wenyaa">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.36 2 2 0 0 1 3.6 1.18h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.72a16 16 0 0 0 6 6l.86-.86a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
          </svg>
          +254 706 155 601
        </a>
        <a href="https://github.com/wakolibrian" target="_blank" rel="noopener noreferrer"
          class="footer-link" aria-label="Wenyaa's GitHub profile">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
            fill="currentColor" aria-hidden="true">
            <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0 1 12 6.844a9.59 9.59 0 0 1 2.504.337c1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.02 10.02 0 0 0 22 12.017C22 6.484 17.522 2 12 2z"/>
          </svg>
          GitHub
        </a>
      </div>
      <!-- Discreet admin access link -->
      <a href="admin/login.php" class="admin-access-link" aria-label="Admin login">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        Admin
      </a>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
