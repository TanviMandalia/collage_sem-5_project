<?php include('include/header.php'); ?>

<!-- Banner -->
<section class="py-5 text-center text-white" style="background-image: url('img/correct_quality2.png'); background-size: cover; background-position: center;">
  <div class="container">
    <h2 class="display-4 fw-bold">Contact Us</h2>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb justify-content-center bg-transparent">
        <li class="breadcrumb-item"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
        <li class="breadcrumb-item active text-white" aria-current="page">Contact</li>
      </ol>
    </nav>
  </div>
</section>

<!-- Spacer -->
<div class="py-4"></div>

<!-- Contact Form & Info -->
<section class="py-5 bg-light">
  <div class="container">
    <div class="row g-4">

      <!-- Contact Form -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h4 class="card-title text-center mb-4">Send Us A Message</h4>

            <?php if (isset($_GET['success'])): ?>
              <div class="alert alert-success text-center">✅ Message sent successfully!</div>
            <?php elseif (isset($_GET['error'])): ?>
              <div class="alert alert-danger text-center">❌ Something went wrong. Please try again.</div>
            <?php endif; ?>

            <form action="contact_process.php" method="post">
              <div class="mb-3">
                <label for="c_name" class="form-label">Full Name</label>
                <input type="text" name="c_name" id="c_name" class="form-control" placeholder="Your Full Name" required>
              </div>
              <div class="mb-3">
                <label for="c_email" class="form-label">Email Address</label>
                <input type="email" name="c_email" id="c_email" class="form-control" placeholder="Your Email Address" required>
              </div>
              <div class="mb-3">
                <label for="c_mobileno" class="form-label">Mobile Number</label>
                <input type="text" name="c_mobileno" id="c_mobileno" class="form-control" placeholder="Your Mobile Number" required>
              </div>
              <div class="mb-3">
                <label for="c_subject" class="form-label">Subject</label>
                <input type="text" name="c_subject" id="c_subject" class="form-control" placeholder="Subject" required>
              </div>
              <div class="mb-3">
                <label for="c_message" class="form-label">Message</label>
                <textarea name="c_message" id="c_message" class="form-control" rows="4" placeholder="How Can We Help?" required></textarea>
              </div>
              <button type="submit" class="btn btn-primary w-100">Submit</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="col-lg-6">
        <div class="card border-0 mb-4 shadow-sm">
          <div class="card-body d-flex align-items-start">
            <i class="fas fa-map-marker-alt fa-2x text-primary me-3"></i>
            <div>
              <h5 class="mb-1">Let's Talk</h5>
              <p class="mb-0">Coza Store, Coza Inc, 78 Bhikhadan St, Ahmedabad</p>
            </div>
          </div>
        </div>
        <div class="card border-0 shadow-sm">
          <div class="card-body d-flex align-items-start">
            <i class="fas fa-envelope fa-2x text-danger me-3"></i>
            <div>
              <h5 class="mb-1">Sales Support</h5>
              <p class="mb-0">support@cozastore.com</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Spacer -->
<div class="py-4"></div>

<?php include('include/footer.php'); ?>
