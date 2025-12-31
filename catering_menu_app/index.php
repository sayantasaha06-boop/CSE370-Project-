<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Catering Menu Manager</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
  <nav class="top-nav">
  <a class="btn" href="event-booking.php">📅 Event Booking</a>
  <a class="btn" href="meal-packages.php">🍱 Packages</a>
  </nav>
</head>
<body>
<header class="app-header">
  <div class="container">
    <h1>🍽️ Catering Menu Manager</h1>
    <p>Add, update, categorize items (Veg, Non-Veg, Drinks, Desserts), set prices & delivery time.</p>
  </div>
</header>

<main class="container">
  <section class="panel">
    <div class="panel-header">
      <div class="filters">
        <button class="chip active" data-category="All">All</button>
        <button class="chip" data-category="Veg">Veg</button>
        <button class="chip" data-category="Non-Veg">Non-Veg</button>
        <button class="chip" data-category="Drinks">Drinks</button>
        <button class="chip" data-category="Desserts">Desserts</button>
      </div>
      <div class="actions">
        <input id="searchInput" class="input" type="search" placeholder="Search items..." />
        <button id="addBtn" class="btn primary">+ Add Item</button>
      </div>
    </div>

    <div class="table-wrap">
      <table class="table" id="itemsTable">
        <thead>
          <tr>
            <th>Name</th>
            <th>Category</th>
            <th class="num">Price</th>
            <th class="num">ETA (min)</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="itemsBody">
          
        </tbody>
      </table>
      <div id="emptyState" class="empty hidden">
        <p>No items found. Click <strong>+ Add Item</strong> to create your first entry.</p>
      </div>
    </div>
  </section>
</main>


<div id="modal" class="modal hidden" aria-hidden="true">
  <div class="modal-backdrop" data-close="true"></div>
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-header">
      <h2 id="modalTitle">Add Menu Item</h2>
      <button class="icon-btn" data-close="true" title="Close">✕</button>
    </div>
    <form id="itemForm" class="form">
      <input type="hidden" id="itemId" />
      <div class="grid">
        <label class="field">
          <span>Name</span>
          <input type="text" id="name" required maxlength="100" />
        </label>
        <label class="field">
          <span>Category</span>
          <select id="category" required>
            <option value="">Select category</option>
            <option>Veg</option>
            <option>Non-Veg</option>
            <option>Drinks</option>
            <option>Desserts</option>
          </select>
        </label>
        <label class="field">
          <span>Price</span>
          <input type="number" id="price" min="0" step="0.01" required />
        </label>
        <label class="field">
          <span>ETA (minutes)</span>
          <input type="number" id="eta" min="1" step="1" required />
        </label>
        <label class="field col-span-2">
          <span>Description</span>
          <textarea id="description" rows="3" placeholder="Optional notes..."></textarea>
        </label>
        <label class="switch">
          <input type="checkbox" id="is_available" checked />
          <span>Available</span>
        </label>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn" data-close="true">Cancel</button>
        <button type="submit" class="btn primary" id="saveBtn">Save</button>
      </div>
    </form>
  </div>
</div>

<script src="assets/app.js"></script>
</body>
</html>
