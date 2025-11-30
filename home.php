<?php
session_start();

$email = $_SESSION['email'] ?? "$email";


// Redirect if not logged in
if (!isset($_SESSION["username"])) {
    header("Location: index.html");
    exit();
}

$username = $_SESSION["username"];

$firstLetter = strtoupper(substr($username, 0, 1));
$user_id = $_SESSION["user_id"];

// === Database ===
$db_host = "localhost";
$db_user = "root";
$db_pass = "Jagan@143";
$db_name = "snapsite";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("DB_ERROR");
}

// Fetch project history
$stmt = $conn->prepare("
    SELECT id, project_name, category, primary_color, secondary_color, published_url, created_at 
    FROM projects 
    WHERE user_id=? 
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$projects = $res->fetch_all(MYSQLI_ASSOC);

//feedback sessions
if (
    isset($_POST["submit_feedback"]) &&
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    !empty($_POST["fb_message"])
) {

    $name    = $_SESSION["username"];
    $email   = $_SESSION["email"];
    $rating  = $_POST["fb_rating"];
    $topic   = $_POST["fb_topic"];
    $message = $_POST["fb_message"];
    $uid     = $_SESSION["user_id"];

    // INSERT INTO DB
    $stmt = $conn->prepare("
        INSERT INTO feedback (user_id, name, email, rating, topic, message)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ississ", $uid, $name, $email, $rating, $topic, $message);
    $stmt->execute();

    // Redirect to avoid resubmission popup + prevent repeat save
    header("Location: home.php?#top");
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="images/logo.png">
  <title>Snapsite</title>
  <link rel="stylesheet" href="home.css" />
</head>

<body>

<!-- NAVBAR -->
<header class="nav-wrapper">
  <div class="navbar">

    <div class="nav-left">
      <img src="images/logo.png" class="nav-logo">
      <span class="nav-title">Snapsite</span>
    </div>

    <nav class="nav-center">
      <a href="#guide" class="nav-item">Guide</a>
      <a href="#project" class="nav-item">Project</a>
      <a href="#feature" class="nav-item">Features</a>
      <a href="#feedback" class="nav-item">Feedback</a>
    </nav>
      <div class="nav-right" id="navUser">
        <span class="username-text"><?php echo htmlspecialchars($username); ?></span>
        <div class="user-icon" id="navbarProfilePic">
            <?php
                $profilePath = "uploads/" . $username . ".jpg";
                if (file_exists($profilePath)) {
                    echo "<img src='$profilePath' class='nav-profile-img'>";
                } else {
                    echo $firstLetter;  // default letter
                }
            ?>
        </div>
      </div>


  </div>
</header>


<!-- =====================
     ACCOUNT MODAL POPUP
===================== -->
<div id="accountModal" class="account-modal">

  <div class="account-modal-inner">

    <button id="closeAccountModal" class="account-close">✕</button>

    <!-- Profile Image -->
    <div class="account-avatar-wrap">
      <div class="account-avatar">
        <img id="profileImagePreview"
             src="<?='uploads/'.$username.'.jpg'?>"
             onerror="this.src='images/default-user.png'" />
        <span class="avatar-icon">📷</span>
        <input type="file" id="profileImageInput" accept="image/*" hidden>
      </div>
    </div>

    <!-- Username -->
    <div class="field-group">
        <label><b>Username<b></label>
        <input type="text" value="<?=$username?>" readonly class="readonly-input">
    </div>

    <!-- Email -->
    <div class="field-group">
        <label><b>Email</b></label>
        <input type="text" value="<?=$email?>" readonly class="readonly-input">
    </div>

    <div class="account-actions">
      <button class="account-btn outline" id="logoutBtn">Sign Out</button>
      <button class="account-btn danger" id="deleteAccountBtn">Delete Account</button>
    </div>

  </div>
</div>




<!-- HOME HERO SECTION -->
<section class="home">
  <div class="home-left">
    <!-- <h1 class="typing-text"></h1> -->
    <p class="typing-text"></p>
    <p class="home-sub">Make it, Snap it, Launch it .!</p>
      <!-- 🔥 NEW AI PROMPT SECTION (Framer Style) -->
<section class="ai-section">
    <div class="ai-box">
        <input 
            type="text" 
            id="aiPrompt" 
            placeholder="Describe your website... (Ex: Create a porfolio website)"
        />

        <button id="generateBtn">Generate</button>
    </div>
</section>
  </div>




  <div class="home-right">
    <img src="images/homeimg.png" class="home-img" alt="hero image" />
  </div>
</section>

<!-- ========================
      GUIDE SECTION
======================== -->
<section id="guide" class="guide-wrapper">

  <div class="guide-card">

    <div class="guide-left">
      <h2 class="guide-title">Snap Your Idea Into a Website in<br>3 Steps</h2>

      <div class="guide-step">
        <span class="step-title">1. Describe Your Website Idea</span>
        <p class="step-desc">Tell to Snapsite AI what kind of website you need — business, portfolio, personal brand, e-commerce, anything.
Our AI transforms your idea into a complete website structure in a minutes using modern layouts and clean code.</p>
      </div>

      <div class="guide-step">
        <span class="step-title">2. Personalize and Edit with Snapsite AI</span>
        <p class="step-desc">Use Snapsite AI to modify text, colors, sections, images, and layout using simple prompts.
No coding needed — just tell the AI what you want changed, and the website updates instantly in real time.</p>
      </div>

      <div class="guide-step">
        <span class="step-title">3. Publish & Download Your Website</span>
        <p class="step-desc">Once you're happy with your design, publish it immediately or download the full source code.
Export includes HTML, CSS, JS, images, and assets — ready to deploy anywhere.</p>
      </div>
    </div>

    <div class="guide-right">
      <img src="images/guide_img.png" class="guide-image">
    </div>

  </div>

</section>

<!-- ===========================
        USER PROJECTS
=========================== -->
<section id="project" class="projects-wrapper">

  <h2 class="projects-title">Your Projects</h2>

  <?php
      $uid = $_SESSION['user_id'];
      $projects = $conn->query("SELECT * FROM projects WHERE user_id='$uid' ORDER BY id DESC");

      if ($projects->num_rows == 0) {
  ?>

    <div class="no-project-box">
        <p>You have not created any projects yet.</p>
        <button class="create-project-btn" onclick="window.location.href='#top'">
            Create a Project
        </button>
    </div>

  <?php
      } else {
  ?>

  <div class="projects-grid">

    <?php
        // Local preview images
        $previewImages = [
            "images/previews/site1.jpg",
            "images/previews/site2.jpg",
            "images/previews/site3.jpg",
            "images/previews/site4.jpg",
            "images/previews/site5.jpg",
            "images/previews/site6.jpg"
        ];
        $thumbIndex = 0;

        while ($p = $projects->fetch_assoc()) {
            $pname = $p['project_name'];
            $clean = strtolower(str_replace(' ', '-', $pname));

            // Assign preview image
            $imgUrl = $previewImages[$thumbIndex % count($previewImages)];
            $thumbIndex++;
    ?>

    <div class="project-card">
        <img src="<?=$imgUrl?>" class="project-img">

        <div class="project-info">
            <h3 class="project-name"><?=$pname?></h3>

            <div class="project-buttons">
                <button class="project-edit-btn"
                    onclick="window.location.href='builder/editor.php?project=<?=$clean?>'">
                    Edit
                </button>

                <button class="project-delete-btn"
                    onclick="deleteProject('<?=$clean?>')">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <?php } // end while ?>

  </div> <!-- end projects-grid -->

  <?php } // end else ?>

</section>


<!-- ===========================
        FEATURES SECTION
=========================== -->
<section id="feature" class="features-wrapper">
  <div class="features-card">
    <h2 class="features-title">
        Everything you need to build <br><span class="highlight">amazing websites</span>
    </h2>
    <p class="features-subtitle">
        Powerful features that make website creation effortless
    </p>

    <div class="features-grid">

        <!-- Feature 1 -->
        <div class="feature-card">
            <div class="feature-icon">⚡</div>
            <h3 class="feature-title">Lightning Fast</h3>
            <p class="feature-text">
                Go from idea to deployed website in minutes. Our snapsite AI builds your site instantly.
            </p>
        </div>

        <!-- Feature 2 -->
        <div class="feature-card">
            <div class="feature-icon">🎨</div>
            <h3 class="feature-title">Beautiful Designs</h3>
            <p class="feature-text">
                Every website is pixel-perfect, responsive, and visually stunning right out of the box.
            </p>
        </div>

        <!-- Feature 3 -->
        <div class="feature-card">
            <div class="feature-icon">🧩</div>
            <h3 class="feature-title">No Coding Needed</h3>
            <p class="feature-text">
                Describe what you want. Snapsite AI handles all the code and complexity.
            </p>
        </div>

        <!-- Feature 4 -->
        <div class="feature-card">
            <div class="feature-icon">🤖</div>
            <h3 class="feature-title">Powered by AI</h3>
            <p class="feature-text">
                Our advanced Snapsite AI turns your ideas into real websites with intelligent automation.
            </p>
        </div>

    </div>

</section>

<!-- ============================
        FEEDBACK SECTION
============================= -->
<section id="feedback" class="feedback-wrapper">

    <h2 class="feedback-title">We’d Love Your Feedback ❤️</h2>
    <p class="feedback-subtitle">Tell us what you think about Snapsite — your thoughts help us improve!</p>

    
    <form method="POST" class="feedback-form">

    <input type="hidden" name="submit_feedback" value="1">

        <div class="fb-row">
            <div class="fb-field">
                <label>Name</label>
                <input type="text" value="<?=htmlspecialchars($_SESSION['username'])?>" disabled>
            </div>

            <div class="fb-field">
                <label>Email</label>
                <input type="text" value="<?=htmlspecialchars($_SESSION['email'])?>" disabled>
            </div>
        </div>

        <div class="fb-row">
            <div class="fb-field">
                <label>Rating</label>
                <select name="fb_rating">
                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                    <option value="4">⭐⭐⭐⭐ Good</option>
                    <option value="3">⭐⭐⭐ Average</option>
                    <option value="2">⭐⭐ Needs Improvement</option>
                    <option value="1">⭐ Poor</option>
                </select>
            </div>

            <div class="fb-field">
                <label>Topic</label>
                <select name="fb_topic">
                    <option value="AI Generation">AI Generation</option>
                    <option value="Website Editor">Website Editor</option>
                    <option value="Design / UI">Design & UI</option>
                    <option value="Publish / Download">Publish & Download</option>
                    <option value="Bug">Bug / Issue</option>
                    <option value="Feature Request">Feature Request</option>
                </select>
            </div>
        </div>

        <div class="fb-field">
            <label>Your Feedback</label>
            <textarea name="fb_message" rows="5" required></textarea>
        </div>

        <button type="submit" class="fb-submit">Submit Feedback</button>

    </form>

</section>





<!-- WELCOME TOAST -->
<div id="welcomeToast" class="welcome-toast">
    Welcome to SnapSite, <strong><?php echo htmlspecialchars($username); ?></strong> .!🎉
</div>








<!-- LOADING OVERLAY -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loader-circle"></div>
    <div class="loader-text">Creating your website…</div>
</div>
<!-- ============================
             FOOTER
============================= -->
<footer class="snap-footer">

    <div class="footer-top">
        <div class="footer-left">
            <img src="images/logo.png" class="footer-logo">
            <h3 class="footer-title">Snapsite</h3>
            <p class="footer-text">
                Create beautiful AI-powered websites in minutes.  
                Snapsite helps you design, edit & publish effortlessly.
            </p>
        </div>

        <div class="footer-links">
            <h4 style>Quick Links</h4>
            <a href="#top">Home</a>
            <a href="#guide">Guide</a>
            <a href="#project">Projects</a>
            <a href="#feature">Feature</a>
            <a href="#feedback">Feedback</a>
        </div>

        <div class="footer-links">
            <h4>Support</h4>
        
            <a href="mailto:jagank0512@gmail.com">Contact Support</a>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© <?=date("Y")?> Snapsite. All rights reserved.</p>
    </div>

</footer>

<script src="home.js"></script>
</body>
</html>
