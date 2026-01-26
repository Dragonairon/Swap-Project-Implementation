<?php
require_once dirname(__DIR__, 3) . '/config/config.php';


$title = "TP AMC — Homepage";
require_once PROJECT_ROOT . '/pages/Irfan/includes/header.php';
?>

<section class="hero">
  <h1>Advanced Manufacturing Centre (AMC)</h1>
  <p>
    AMC supports applied learning, industry collaboration, and advanced manufacturing capabilities.
    This student project demonstrates secure login monitoring and role-based access aligned to the TP AMC context.
  </p>

  <?php if (isset($_GET['timeout'])): ?>
    <div class="alert error" style="margin-top:14px;">
      You were logged out due to inactivity. Please log in again.
    </div>
  <?php endif; ?>

  <div class="hero-actions">
    <a class="btn" href="<?= BASE_URL ?>pages/Irfan/login+logout+homepage/login.php">Login</a>
  </div>
</section>

<div class="grid">
  <div class="card col-6">
    <h3>Industry Collaboration</h3>
    <p>Support industry projects, prototyping, and solution development in advanced manufacturing workflows.</p>
  </div>

  <div class="card col-6">
    <h3>Applied Learning</h3>
    <p>Enable hands-on student learning aligned to real-world engineering and manufacturing operations.</p>
  </div>

  <div class="card col-12">
    <h3>Security Focus (This Project)</h3>
    <p>
      Implements authentication monitoring (A07), lockout after repeated failures, role-based access for Admin/HR,
      an admin-only unlock workflow, and a security logs interface for governance and incident review.
    </p>
  </div>

  <div class="card col-12">
    <h3>Session Security</h3>
    <p>
      Includes automatic logout after inactivity to reduce risk from unattended sessions (e.g., shared or public workstations).
    </p>
  </div>
</div>

<?php require_once PROJECT_ROOT . '/pages/Irfan/includes/footer.php'; ?>
