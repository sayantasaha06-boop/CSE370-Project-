<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Event Booking - Catering Manager</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
  <nav class="top-nav">
    <a class="btn" href="index.php">🍽️ Menu Manager</a>
    <a class="btn" href="meal-packages.php">🍱 Packages</a>
  </nav>
</head>
<body>
<header class="app-header">
  <div class="container">
    <h1>📅 Event Booking Manager</h1>
    <p>Manage catering bookings, check availability, and schedule events with conflict detection.</p>
  </div>
</header>

<main class="container">
  <!-- Booking Form Section -->
  <section class="panel">
    <div class="panel-header">
      <h2>📝 New Booking</h2>
    </div>
    
    <form id="bookingForm" class="form">
      <div class="grid-2">
        <!-- Left Column - Customer Info -->
        <div class="booking-section">
          <h3>Customer Information</h3>
          <div class="grid">
            <label class="field">
              <span>Customer Name *</span>
              <input type="text" id="customer_name" required maxlength="100" />
            </label>
            <label class="field">
              <span>Phone *</span>
              <input type="tel" id="phone" required />
            </label>
            <label class="field col-span-2">
              <span>Email *</span>
              <input type="email" id="email" required />
            </label>
          </div>
        </div>

        <!-- Right Column - Event Details -->
        <div class="booking-section">
          <h3>Event Details</h3>
          <div class="grid">
            <label class="field">
              <span>Event Type *</span>
              <select id="event_type" required>
                <option value="">Select event type</option>
                <option value="Wedding">Wedding</option>
                <option value="Birthday">Birthday Party</option>
                <option value="Corporate">Corporate Event</option>
                <option value="Anniversary">Anniversary</option>
                <option value="Conference">Conference</option>
                <option value="Other">Other</option>
              </select>
            </label>
            <label class="field">
              <span>Number of Guests *</span>
              <input type="number" id="guests" min="1" max="1000" required />
            </label>
          </div>
        </div>
      </div>

      <!-- Date & Time Section -->
      <div class="booking-section">
        <h3>📅 Date & Time</h3>
        <div class="calendar-hint">
          Select date & times first; conflicts will appear automatically.
        </div>
        <div class="grid">
          <label class="field">
            <span>Event Date *</span>
            <input type="date" id="event_date" required min="<?= date('Y-m-d') ?>" />
          </label>
          <label class="field">
            <span>Start Time *</span>
            <input type="time" id="start_time" required />
          </label>
          <label class="field">
            <span>End Time *</span>
            <input type="time" id="end_time" required />
          </label>
        </div>
        <div id="conflictNotice" class="alert neutral hidden">
          Time conflict detected with existing booking.
        </div>
      </div>

      <!-- Location & Menu -->
      <div class="booking-section">
        <h3>📍 Location & Menu</h3>
        <div class="grid">
          <label class="field col-span-2">
            <span>Event Location *</span>
            <input type="text" id="location" required maxlength="200" placeholder="Full address or venue name" />
          </label>
          <label class="field col-span-2">
            <span>Menu Preferences</span>
            <textarea id="menu_prefs" rows="3" placeholder="Dietary requirements, preferred dishes, special requests..."></textarea>
          </label>
        </div>
      </div>

      <div class="modal-actions">
        <button type="reset" class="btn">Clear Form</button>
        <button type="submit" class="btn primary">📅 Create Booking</button>
      </div>
    </form>
  </section>

  <!-- Bookings List Section -->
  <section class="panel">
    <div class="panel-header">
      <div class="filters">
        <button class="chip active" data-status="All">All Bookings</button>
        <button class="chip" data-status="Pending">Pending</button>
        <button class="chip" data-status="Confirmed">Confirmed</button>
        <button class="chip" data-status="Cancelled">Cancelled</button>
      </div>
      <div class="actions">
        <input id="searchBookings" class="input" type="search" placeholder="Search bookings..." />
        <select id="dateFilter" class="input">
          <option value="">All Dates</option>
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="upcoming">Upcoming</option>
        </select>
      </div>
    </div>

    <div class="table-wrap">
      <table class="table" id="bookingsTable">
        <thead>
          <tr>
            <th>Customer</th>
            <th>Event Type</th>
            <th>Date & Time</th>
            <th class="num">Guests</th>
            <th>Location</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="bookingsBody">
          <!-- Bookings will be loaded here -->
        </tbody>
      </table>
      <div id="bookingsEmpty" class="empty hidden">
        <p>No bookings found. Create your first booking above.</p>
      </div>
    </div>
  </section>
</main>

<!-- Edit Booking Modal -->
<div id="editModal" class="modal hidden" aria-hidden="true">
  <div class="modal-backdrop" data-close="true"></div>
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
    <div class="modal-header">
      <h2 id="editModalTitle">Edit Booking</h2>
      <button class="icon-btn" data-close="true" title="Close">✕</button>
    </div>
    <form id="editBookingForm" class="form">
      <input type="hidden" id="edit_booking_id" />
      <div class="grid">
        <label class="field">
          <span>Customer Name</span>
          <input type="text" id="edit_customer_name" required />
        </label>
        <label class="field">
          <span>Phone</span>
          <input type="tel" id="edit_phone" required />
        </label>
        <label class="field">
          <span>Email</span>
          <input type="email" id="edit_email" required />
        </label>
        <label class="field">
          <span>Event Type</span>
          <select id="edit_event_type" required>
            <option value="Wedding">Wedding</option>
            <option value="Birthday">Birthday Party</option>
            <option value="Corporate">Corporate Event</option>
            <option value="Anniversary">Anniversary</option>
            <option value="Conference">Conference</option>
            <option value="Other">Other</option>
          </select>
        </label>
        <label class="field">
          <span>Guests</span>
          <input type="number" id="edit_guests" min="1" required />
        </label>
        <label class="field">
          <span>Status</span>
          <select id="edit_status" required>
            <option value="Pending">Pending</option>
            <option value="Confirmed">Confirmed</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </label>
        <label class="field col-span-2">
          <span>Location</span>
          <input type="text" id="edit_location" required />
        </label>
        <label class="field col-span-2">
          <span>Menu Preferences</span>
          <textarea id="edit_menu_prefs" rows="2"></textarea>
        </label>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn" data-close="true">Cancel</button>
        <button type="submit" class="btn primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="confirmModal" class="modal hidden" aria-hidden="true">
  <div class="modal-backdrop" data-close="true"></div>
  <div class="modal-card" role="dialog" aria-modal="true">
    <div class="modal-header">
      <h2>Delete Booking</h2>
      <button class="icon-btn" data-close="true" title="Close">✕</button>
    </div>
    <div class="content">
      <p>Are you sure you want to delete this booking? This action cannot be undone.</p>
    </div>
    <div class="modal-actions">
      <button type="button" class="btn" data-close="true">Cancel</button>
      <button type="button" class="btn danger" id="confirmDeleteBtn">Delete</button>
    </div>
  </div>
</div>

<script>
window.BOOKINGS_API = '/catering_menu_app/api/bookings_api.php';
</script>
<script src="assets/booking.js"></script>
</body>
</html>