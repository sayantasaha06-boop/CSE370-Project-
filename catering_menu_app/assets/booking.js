// assets/booking.js - Complete implementation
const $ = (s, root = document) => root.querySelector(s);
const $$ = (s, root = document) => Array.from(root.querySelectorAll(s));

const state = {
  bookings: [],
  statusFilter: "All",
  dateFilter: "",
  search: "",
  editingId: null
};

// API helper functions
const api = {
  async create(data) {
    try {
      const res = await fetch('./' + window.BOOKINGS_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      return await res.json();
    } catch (e) {
      return { ok: false, error: e.message };
    }
  },

  async list(params = {}) {
    try {
      const url = new URL(window.BOOKINGS_API, window.location.origin);
      Object.entries(params).forEach(([k, v]) => {
        if (v) url.searchParams.set(k, v);
      });
      const res = await fetch(url);
      return await res.json();
    } catch (e) {
      return { ok: false, error: e.message };
    }
  },

  async update(id, data) {
    try {
      const res = await fetch(`${window.BOOKINGS_API}?id=${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      return await res.json();
    } catch (e) {
      return { ok: false, error: e.message };
    }
  },

  async delete(id) {
    try {
      const res = await fetch(`${window.BOOKINGS_API}?id=${id}`, {
        method: 'DELETE'
      });
      return await res.json();
    } catch (e) {
      return { ok: false, error: e.message };
    }
  },

  async checkConflict(date, start, end, excludeId = null) {
    try {
      const url = new URL(window.BOOKINGS_API, window.location.origin);
      url.searchParams.set('action', 'check_conflict');
      url.searchParams.set('date', date);
      url.searchParams.set('start', start);
      url.searchParams.set('end', end);
      if (excludeId) url.searchParams.set('exclude', excludeId);
      
      const res = await fetch(url);
      return await res.json();
    } catch (e) {
      return { ok: true }; // If check fails, allow booking
    }
  }
};

// Validation helpers
function validateTimes(date, start, end) {
  if (!date || !start || !end) {
    return { ok: false, error: 'Please fill all date/time fields' };
  }
  
  const eventDate = new Date(`${date}T${start}`);
  const endDate = new Date(`${date}T${end}`);
  const now = new Date();
  
  if (eventDate < now) {
    return { ok: false, error: 'Event cannot be scheduled in the past' };
  }
  
  if (endDate <= eventDate) {
    return { ok: false, error: 'End time must be after start time' };
  }
  
  return { ok: true };
}

// Format helpers
function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short', 
    day: 'numeric'
  });
}

function formatTime(timeStr) {
  return new Date(`2000-01-01T${timeStr}`).toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  });
}

// Conflict checking
async function checkConflict() {
  const notice = $('#conflictNotice');
  const date = $('#event_date').value;
  const start = $('#start_time').value;
  const end = $('#end_time').value;

  notice.classList.add('hidden');

  if (!date || !start || !end) return;

  const validation = validateTimes(date, start, end);
  if (!validation.ok) {
    notice.textContent = validation.error;
    notice.classList.remove('hidden');
    return;
  }

  const conflict = await api.checkConflict(date, start, end);
  if (!conflict.ok) {
    notice.textContent = conflict.error || 'Time conflict detected';
    notice.classList.remove('hidden');
  }
}

// Load and render bookings
async function loadBookings() {
  const params = {};
  if (state.statusFilter && state.statusFilter !== 'All') {
    params.status = state.statusFilter;
  }
  if (state.dateFilter) {
    params.date_filter = state.dateFilter;
  }
  if (state.search) {
    params.q = state.search;
  }

  const result = await api.list(params);
  if (result.ok) {
    state.bookings = result.bookings || [];
  } else {
    console.error('Failed to load bookings:', result.error);
    state.bookings = [];
  }
  
  renderBookingsTable();
}

function renderBookingsTable() {
  const tbody = $('#bookingsBody');
  const empty = $('#bookingsEmpty');
  
  tbody.innerHTML = '';
  
  if (!state.bookings.length) {
    empty.classList.remove('hidden');
    return;
  }
  
  empty.classList.add('hidden');
  
  state.bookings.forEach(booking => {
    const tr = document.createElement('tr');
    
    const statusClass = {
      'Pending': 'pending',
      'Confirmed': 'available', 
      'Cancelled': 'unavailable'
    }[booking.status] || 'pending';
    
    tr.innerHTML = `
      <td>
        <strong>${booking.customer_name}</strong><br>
        <small>${booking.phone} • ${booking.email}</small>
      </td>
      <td>${booking.event_type}</td>
      <td>
        <strong>${formatDate(booking.event_date)}</strong><br>
        <small>${formatTime(booking.start_time)} - ${formatTime(booking.end_time)}</small>
      </td>
      <td class="num">${booking.guests}</td>
      <td>
        <div class="location-cell">${booking.location}</div>
        ${booking.menu_prefs ? `<small>${booking.menu_prefs.substring(0, 50)}${booking.menu_prefs.length > 50 ? '...' : ''}</small>` : ''}
      </td>
      <td>
        <span class="badge ${statusClass}">${booking.status}</span>
      </td>
      <td>
        <div class="row-actions">
          <button class="icon-btn" data-edit="${booking.id}">✎ Edit</button>
          <button class="icon-btn danger" data-delete="${booking.id}">🗑 Delete</button>
        </div>
      </td>
    `;
    
    tbody.appendChild(tr);
  });
}

// Modal management
function openEditModal(booking) {
  const modal = $('#editModal');
  modal.classList.remove('hidden');
  
  state.editingId = booking.id;
  $('#edit_booking_id').value = booking.id;
  $('#edit_customer_name').value = booking.customer_name;
  $('#edit_phone').value = booking.phone;
  $('#edit_email').value = booking.email;
  $('#edit_event_type').value = booking.event_type;
  $('#edit_guests').value = booking.guests;
  $('#edit_status').value = booking.status;
  $('#edit_location').value = booking.location;
  $('#edit_menu_prefs').value = booking.menu_prefs || '';
}

function closeModals() {
  $('#editModal').classList.add('hidden');
  $('#confirmModal').classList.add('hidden');
  state.editingId = null;
}

// Form submission handlers
async function handleCreateBooking(e) {
  e.preventDefault();
  
  const formData = {
    customer_name: $('#customer_name').value.trim(),
    phone: $('#phone').value.trim(),
    email: $('#email').value.trim(),
    event_type: $('#event_type').value,
    event_date: $('#event_date').value,
    start_time: $('#start_time').value,
    end_time: $('#end_time').value,
    guests: parseInt($('#guests').value, 10),
    location: $('#location').value.trim(),
    menu_prefs: $('#menu_prefs').value.trim()
  };
  
  // Validate required fields
  if (!formData.customer_name || !formData.phone || !formData.email || 
      !formData.event_type || !formData.location || !formData.guests) {
    alert('Please fill all required fields');
    return;
  }
  
  // Validate times
  const validation = validateTimes(formData.event_date, formData.start_time, formData.end_time);
  if (!validation.ok) {
    alert(validation.error);
    return;
  }
  
  const result = await api.create(formData);
  if (result.ok) {
    $('#bookingForm').reset();
    $('#conflictNotice').classList.add('hidden');
    await loadBookings();
    alert('Booking created successfully!');
  } else {
    alert(result.error || 'Failed to create booking');
  }
}

async function handleEditBooking(e) {
  e.preventDefault();
  
  const formData = {
    customer_name: $('#edit_customer_name').value.trim(),
    phone: $('#edit_phone').value.trim(),
    email: $('#edit_email').value.trim(),
    event_type: $('#edit_event_type').value,
    guests: parseInt($('#edit_guests').value, 10),
    status: $('#edit_status').value,
    location: $('#edit_location').value.trim(),
    menu_prefs: $('#edit_menu_prefs').value.trim()
  };
  
  const result = await api.update(state.editingId, formData);
  if (result.ok) {
    closeModals();
    await loadBookings();
    alert('Booking updated successfully!');
  } else {
    alert(result.error || 'Failed to update booking');
  }
}

async function handleDeleteBooking(bookingId) {
  state.deleteId = bookingId;
  $('#confirmModal').classList.remove('hidden');
}

async function confirmDelete() {
  if (!state.deleteId) return;
  
  const result = await api.delete(state.deleteId);
  if (result.ok) {
    closeModals();
    await loadBookings();
    alert('Booking deleted successfully');
  } else {
    alert(result.error || 'Failed to delete booking');
  }
  
  state.deleteId = null;
}

// Event bindings
function bindEvents() {
  // Form submissions
  $('#bookingForm').addEventListener('submit', handleCreateBooking);
  $('#editBookingForm').addEventListener('submit', handleEditBooking);
  
  // Time conflict checking
  ['#event_date', '#start_time', '#end_time'].forEach(selector => {
    $(selector).addEventListener('change', checkConflict);
  });
  
  // Filter controls
  $$('.chip[data-status]').forEach(chip => {
    chip.addEventListener('click', () => {
      $$('.chip[data-status]').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      state.statusFilter = chip.dataset.status;
      loadBookings();
    });
  });
  
  $('#dateFilter').addEventListener('change', (e) => {
    state.dateFilter = e.target.value;
    loadBookings();
  });
  
  $('#searchBookings').addEventListener('input', (e) => {
    state.search = e.target.value.trim();
    loadBookings();
  });
  
  // Table actions
  $('#bookingsBody').addEventListener('click', (e) => {
    const editId = e.target.getAttribute('data-edit');
    const deleteId = e.target.getAttribute('data-delete');
    
    if (editId) {
      const booking = state.bookings.find(b => b.id === editId);
      if (booking) openEditModal(booking);
    }
    
    if (deleteId) {
      handleDeleteBooking(deleteId);
    }
  });
  
  // Modal controls
  $$('[data-close]').forEach(btn => {
    btn.addEventListener('click', closeModals);
  });
  
  $('#confirmDeleteBtn').addEventListener('click', confirmDelete);
  
  // Close modal on backdrop click
  ['#editModal', '#confirmModal'].forEach(selector => {
    $(selector).addEventListener('click', (e) => {
      if (e.target.dataset.close || e.target.classList.contains('modal-backdrop')) {
        closeModals();
      }
    });
  });
  
  // ESC key support
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModals();
  });
}

// Initialize
async function init() {
  bindEvents();
  await loadBookings();
}

// Start when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}