<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Meal Packages - Catering Manager</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
  <nav class="top-nav">     
        <a class="btn" href="index.php">🍽️ Menu Manager</a>
        <a class="btn" href="event-booking.php">📅 Event Booking</a>
  </nav>
</head>
<body>
<header class="app-header">
  <div class="container">
    <h1>🍱 Meal Packages</h1>
    <p>Choose from our curated meal packages designed for bachelors, students, office goers, and families.</p>
  </div>
</header>

<main class="container">
  <!-- Filters Section -->
  <section class="panel">
    <div class="panel-header">
      <div class="filters">
        <button class="chip active" data-audience="All">All Packages</button>
        <button class="chip" data-audience="Bachelor">Bachelor</button>
        <button class="chip" data-audience="Student">Student</button>
        <button class="chip" data-audience="Office">Office</button>
        <button class="chip" data-audience="Family">Family</button>
        <button class="chip" data-audience="Premium">Premium</button>
      </div>
      <div class="actions">
        <input id="searchPackages" class="input" type="search" placeholder="Search packages..." />
        <select id="priceSort" class="input">
          <option value="">Sort by Price</option>
          <option value="asc">Price: Low to High</option>
          <option value="desc">Price: High to Low</option>
        </select>
      </div>
    </div>
  </section>

  <!-- Packages Grid -->
  <section class="packages-grid" id="packagesGrid">
    <!-- Packages will be loaded here dynamically -->
    <div id="packagesLoading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading packages...</p>
    </div>
  </section>

  <!-- No Results State -->
  <div id="noPackages" class="empty-state hidden">
    <div class="empty-icon">🍱</div>
    <h3>No packages found</h3>
    <p>Try adjusting your filters or search terms.</p>
  </div>
</main>

<!-- Package Details Modal -->
<div id="packageModal" class="modal hidden" aria-hidden="true">
  <div class="modal-backdrop" data-close="true"></div>
  <div class="modal-card large" role="dialog" aria-modal="true" aria-labelledby="packageModalTitle">
    <div class="modal-header">
      <h2 id="packageModalTitle">Package Details</h2>
      <button class="icon-btn" data-close="true" title="Close">✕</button>
    </div>
    
    <div class="package-details">
      <div class="package-hero">
        <img id="packageImage" src="" alt="" class="package-image">
        <div class="package-info">
          <h3 id="packageName"></h3>
          <div class="package-meta">
            <span class="badge" id="packageAudience"></span>
            <span class="price-badge">৳<span id="packagePrice"></span>/day</span>
          </div>
          <p id="packageDescription"></p>
        </div>
      </div>

      <div class="package-content">
        <!-- Features -->
        <div class="features-section">
          <h4>✨ Package Features</h4>
          <ul id="packageFeatures" class="features-list"></ul>
        </div>

        <!-- Meals Included -->
        <div class="meals-section">
          <h4>🍽️ Meals Included</h4>
          <div id="packageMeals" class="meals-list"></div>
        </div>

        <!-- Order Form -->
        <div class="order-section">
          <h4>📝 Place Order</h4>
          <form id="orderForm" class="order-form">
            <input type="hidden" id="orderPackageId" />
            
            <div class="form-row">
              <div class="field">
                <label>Start Date</label>
                <input type="date" id="startDate" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" />
              </div>
              <div class="field">
                <label>Duration (Days)</label>
                <select id="duration" required>
                  <option value="">Select duration</option>
                  <option value="1">1 Day (Trial)</option>
                  <option value="7">1 Week</option>
                  <option value="15">15 Days</option>
                  <option value="30">1 Month</option>
                  <option value="60">2 Months</option>
                  <option value="90">3 Months</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="field">
                <label>Customer Name</label>
                <input type="text" id="customerName" required maxlength="100" />
              </div>
              <div class="field">
                <label>Phone Number</label>
                <input type="tel" id="customerPhone" required />
              </div>
            </div>

            <div class="form-row">
              <div class="field full-width">
                <label>Email Address</label>
                <input type="email" id="customerEmail" required />
              </div>
            </div>

            <div class="field">
              <label>Delivery Address</label>
              <textarea id="deliveryAddress" required rows="3" placeholder="Full delivery address with area and landmarks"></textarea>
            </div>

            <div class="field">
              <label>Special Instructions (Optional)</label>
              <textarea id="specialInstructions" rows="2" placeholder="Any dietary preferences, allergies, or special requests..."></textarea>
            </div>

            <!-- Total Calculation -->
            <div class="order-summary">
              <div class="summary-row">
                <span>Daily Price:</span>
                <span>৳<span id="summaryDailyPrice">0</span></span>
              </div>
              <div class="summary-row">
                <span>Duration:</span>
                <span><span id="summaryDuration">0</span> days</span>
              </div>
              <div class="summary-row total">
                <span>Total Amount:</span>
                <span class="total-price">৳<span id="summaryTotal">0</span></span>
              </div>
            </div>

            <div class="modal-actions">
              <button type="button" class="btn" data-close="true">Cancel</button>
              <button type="submit" class="btn primary">💳 Place Order</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal hidden" aria-hidden="true">
  <div class="modal-backdrop" data-close="true"></div>
  <div class="modal-card" role="dialog" aria-modal="true">
    <div class="modal-header">
      <h2>✅ Order Placed Successfully!</h2>
      <button class="icon-btn" data-close="true" title="Close">✕</button>
    </div>
    <div class="content">
      <div class="success-content">
        <div class="success-icon">🎉</div>
        <h3>Thank you for your order!</h3>
        <p id="successMessage">Your meal package order has been placed successfully.</p>
        <div class="success-details">
          <p><strong>Order ID:</strong> <span id="successOrderId"></span></p>
          <p><strong>Total Amount:</strong> ৳<span id="successAmount"></span></p>
          <p><strong>Start Date:</strong> <span id="successStartDate"></span></p>
        </div>
        <p class="success-note">We'll contact you shortly to confirm the delivery details.</p>
      </div>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn primary" data-close="true">Continue</button>
    </div>
  </div>
</div>

<script>
window.PACKAGES_API = '/catering_menu_app/api/packages.php';
</script>
<script src="assets/packages.js"></script>
</body>
</html>