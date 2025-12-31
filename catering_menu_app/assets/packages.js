// assets/packages.js
const $ = (s, root = document) => root.querySelector(s);
const $$ = (s, root = document) => Array.from(root.querySelectorAll(s));

const state = {
  packages: [],
  filteredPackages: [],
  audienceFilter: "All",
  searchQuery: "",
  priceSort: "",
  selectedPackage: null,
  isLoading: false
};

// Utility functions
function formatPrice(price) {
  return Number(price).toLocaleString('en-BD', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

// Safe element access functions
function safeShow(elementId) {
  const element = $(elementId);
  if (element) {
    element.style.display = 'block';
    element.classList.remove('hidden');
  } else {
    console.warn(`Element ${elementId} not found`);
  }
}

function safeHide(elementId) {
  const element = $(elementId);
  if (element) {
    element.style.display = 'none';
    element.classList.add('hidden');
  } else {
    console.warn(`Element ${elementId} not found`);
  }
}

// API functions
const api = {
  async getPackages(params = {}) {
    try {
      const url = new URL(window.PACKAGES_API, window.location.origin);
      url.searchParams.set('action', 'list');
      Object.entries(params).forEach(([k, v]) => {
        if (v) url.searchParams.set(k, v);
      });
      
      const response = await fetch(url);
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('API Error:', error);
      return { ok: false, error: error.message };
    }
  },

  async getPackage(id) {
    try {
      const url = new URL(window.PACKAGES_API, window.location.origin);
      url.searchParams.set('action', 'get');
      url.searchParams.set('id', id);
      
      const response = await fetch(url);
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('API Error:', error);
      return { ok: false, error: error.message };
    }
  },

  async calculateTotal(packageId, duration) {
    try {
      const url = new URL(window.PACKAGES_API, window.location.origin);
      url.searchParams.set('action', 'calculate');
      url.searchParams.set('package_id', packageId);
      url.searchParams.set('duration', duration);
      
      const response = await fetch(url);
      const data = await response.json();
      return data;
    } catch (error) {
      console.error('API Error:', error);
      return { ok: false, error: error.message };
    }
  },

  async placeOrder(orderData) {
    try {
      const response = await fetch(window.PACKAGES_API, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(orderData)
      });
      
      // Get the raw response text first
      const responseText = await response.text();
      console.log('Raw API response:', responseText);
      console.log('Response status:', response.status);
      
      // Check if response is ok
      if (!response.ok) {
        console.error('HTTP Error:', response.status, response.statusText);
        return { ok: false, error: `HTTP ${response.status}: ${response.statusText}` };
      }
      
      // Try to parse JSON
      let data;
      try {
        data = JSON.parse(responseText);
      } catch (jsonError) {
        console.error('JSON Parse Error:', jsonError);
        console.error('Response was:', responseText.substring(0, 500));
        return { ok: false, error: `Server returned invalid JSON: ${responseText.substring(0, 100)}...` };
      }
      
      console.log('Parsed JSON response:', data);
      return data;
    } catch (error) {
      console.error('Network Error in placeOrder:', error);
      return { ok: false, error: error.message };
    }
  }
};

// Loading and filtering
async function loadPackages() {
  state.isLoading = true;
  showLoading();
  
  const params = {};
  if (state.audienceFilter !== 'All') {
    params.audience = state.audienceFilter;
  }
  if (state.searchQuery) {
    params.q = state.searchQuery;
  }
  if (state.priceSort) {
    params.sort = state.priceSort;
  }
  
  const result = await api.getPackages(params);
  
  if (result.ok) {
    state.packages = result.packages || [];
    state.filteredPackages = [...state.packages];
  } else {
    console.error('Failed to load packages:', result.error);
    state.packages = [];
    state.filteredPackages = [];
  }
  
  state.isLoading = false;
  renderPackages();
}

function showLoading() {
  safeShow('#packagesLoading');
  safeHide('#noPackages');
}

function hideLoading() {
  safeHide('#packagesLoading');
}

// Rendering functions
function renderPackages() {
  const grid = $('#packagesGrid');
  const noResults = $('#noPackages');
  
  hideLoading();
  
  if (!grid) {
    console.error('Packages grid element not found');
    return;
  }
  
  if (!state.filteredPackages.length) {
    grid.innerHTML = '';
    if (noResults) noResults.classList.remove('hidden');
    return;
  }
  
  if (noResults) noResults.classList.add('hidden');
  
  grid.innerHTML = state.filteredPackages.map(pkg => createPackageCard(pkg)).join('');
  
  // Bind click events to cards
  $$('.package-card').forEach(card => {
    card.addEventListener('click', () => {
      const packageId = parseInt(card.dataset.packageId);
      openPackageModal(packageId);
    });
  });
}

function createPackageCard(pkg) {
  const audienceColors = {
    'Bachelor': 'bachelor',
    'Student': 'student', 
    'Office': 'office',
    'Family': 'family',
    'Premium': 'premium'
  };

  const mealIcons = {
    'breakfast': '🌅',
    'lunch': '🍽️',
    'dinner': '🌙'
  };
  
  const mealsDisplay = pkg.meals_included.map(meal => 
    `<span class="meal-badge" title="${meal}">${mealIcons[meal] || '🍽️'}</span>`
  ).join('');
  
  return `
    <div class="package-card" data-package-id="${pkg.id}">
      <div class="package-image-container">
        <img src="${pkg.image_url || 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=400'}" 
             alt="${pkg.name}" class="package-card-image" loading="lazy">
        <div class="package-overlay">
          <span class="audience-badge ${audienceColors[pkg.target_audience] || ''}">${pkg.target_audience}</span>
        </div>
      </div>
      
      <div class="package-card-content">
        <h3 class="package-card-title">${pkg.name}</h3>
        <p class="package-card-description">${pkg.description}</p>
        
        <div class="package-card-meals">
          <span class="meals-label">Includes:</span>
          <div class="meals-display">${mealsDisplay}</div>
        </div>
        
        <div class="package-card-features">
          ${pkg.features.slice(0, 2).map(feature => 
            `<span class="feature-tag">✓ ${feature}</span>`
          ).join('')}
          ${pkg.features.length > 2 ? `<span class="feature-more">+${pkg.features.length - 2} more</span>` : ''}
        </div>
        
        <div class="package-card-footer">
          <div class="package-price">
            <span class="price-amount">৳${formatPrice(pkg.daily_price)}</span>
            <span class="price-period">/day</span>
          </div>
          <button class="btn primary package-btn" onclick="event.stopPropagation(); openPackageModal(${pkg.id})">
            Order Now
          </button>
        </div>
      </div>
    </div>
  `;
}

// Modal functions
async function openPackageModal(packageId) {
  const result = await api.getPackage(packageId);
  
  if (!result.ok) {
    alert('Failed to load package details');
    return;
  }
  
  state.selectedPackage = result.package;
  displayPackageModal(result.package);
  const modal = $('#packageModal');
  if (modal) modal.classList.remove('hidden');
}

function displayPackageModal(pkg) {
  const setTextContent = (id, text) => {
    const element = $(id);
    if (element) element.textContent = text;
  };
  
  const setSrc = (id, src) => {
    const element = $(id);
    if (element) element.src = src;
  };
  
  const setInnerHTML = (id, html) => {
    const element = $(id);
    if (element) element.innerHTML = html;
  };
  
  const setValue = (id, value) => {
    const element = $(id);
    if (element) element.value = value;
  };
  
  setTextContent('#packageModalTitle', pkg.name);
  setSrc('#packageImage', pkg.image_url || 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=400');
  const packageImage = $('#packageImage');
  if (packageImage) packageImage.alt = pkg.name;
  
  setTextContent('#packageName', pkg.name);
  
  const audienceBadge = $('#packageAudience');
  if (audienceBadge) {
    audienceBadge.textContent = pkg.target_audience;
    audienceBadge.className = `badge ${pkg.target_audience.toLowerCase()}`;
  }
  
  setTextContent('#packagePrice', formatPrice(pkg.daily_price));
  setTextContent('#packageDescription', pkg.description);
  
  // Features
  const featuresList = $('#packageFeatures');
  if (featuresList) {
    featuresList.innerHTML = pkg.features.map(feature => `<li>${feature}</li>`).join('');
  }
  
  // Meals
  const mealsList = $('#packageMeals');
  const mealNames = {
    'breakfast': 'Breakfast',
    'lunch': 'Lunch', 
    'dinner': 'Dinner'
  };
  const mealIcons = {
    'breakfast': '🌅',
    'lunch': '🍽️',
    'dinner': '🌙'
  };
  
  if (mealsList) {
    mealsList.innerHTML = pkg.meals_included.map(meal => 
      `<div class="meal-item">
        <span class="meal-icon">${mealIcons[meal] || '🍽️'}</span>
        <span class="meal-name">${mealNames[meal] || meal}</span>
      </div>`
    ).join('');
  }
  
  // Set form data
  setValue('#orderPackageId', pkg.id);
  setTextContent('#summaryDailyPrice', formatPrice(pkg.daily_price));
  
  // Reset form
  const orderForm = $('#orderForm');
  if (orderForm) {
    orderForm.reset();
    setValue('#orderPackageId', pkg.id);
  }
  
  updateOrderSummary();
}

function updateOrderSummary() {
  const durationElement = $('#duration');
  const duration = durationElement ? parseInt(durationElement.value) || 0 : 0;
  const dailyPrice = state.selectedPackage ? state.selectedPackage.daily_price : 0;
  const total = duration * dailyPrice;
  
  const summaryDuration = $('#summaryDuration');
  const summaryTotal = $('#summaryTotal');
  
  if (summaryDuration) summaryDuration.textContent = duration;
  if (summaryTotal) summaryTotal.textContent = formatPrice(total);
}

function closeModal() {
  const packageModal = $('#packageModal');
  const successModal = $('#successModal');
  
  if (packageModal) packageModal.classList.add('hidden');
  if (successModal) successModal.classList.add('hidden');
}

// Form handling
async function handleOrderSubmit(e) {
  e.preventDefault();
  
  const getValue = (id) => {
    const element = $(id);
    return element ? element.value.trim() : '';
  };
  
  const formData = {
    package_id: parseInt(getValue('#orderPackageId')),
    customer_name: getValue('#customerName'),
    phone: getValue('#customerPhone'),
    email: getValue('#customerEmail'),
    delivery_address: getValue('#deliveryAddress'),
    start_date: getValue('#startDate'),
    duration_days: parseInt(getValue('#duration')),
    special_instructions: getValue('#specialInstructions')
  };
  
  console.log('Submitting order data:', formData);
  
  // Validate required fields
  const requiredFields = {
    'Package': formData.package_id,
    'Customer Name': formData.customer_name,
    'Phone': formData.phone,
    'Email': formData.email,
    'Delivery Address': formData.delivery_address,
    'Start Date': formData.start_date,
    'Duration': formData.duration_days
  };
  
  for (const [field, value] of Object.entries(requiredFields)) {
    if (!value) {
      alert(`Please fill in the ${field} field`);
      return;
    }
  }
  
  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(formData.email)) {
    alert('Please enter a valid email address');
    return;
  }
  
  // Validate phone (basic check for digits)
  if (!/^\d{10,15}$/.test(formData.phone.replace(/\D/g, ''))) {
    alert('Please enter a valid phone number');
    return;
  }
  
  // Show loading state
  const submitBtn = e.target.querySelector('button[type="submit"]');
  if (!submitBtn) return;
  
  const originalText = submitBtn.textContent;
  submitBtn.textContent = '⏳ Processing...';
  submitBtn.disabled = true;
  
  try {
    console.log('Making API call to:', window.PACKAGES_API);
    const result = await api.placeOrder(formData);
    console.log('API result:', result);
    
    if (result.ok) {
      // Show success modal
      const setSuccessText = (id, text) => {
        const element = $(id);
        if (element) element.textContent = text;
      };
      
      setSuccessText('#successOrderId', `#${result.order_id}`);
      setSuccessText('#successAmount', formatPrice(result.total_amount));
      setSuccessText('#successStartDate', formatDate(formData.start_date));
      
      const packageModal = $('#packageModal');
      const successModal = $('#successModal');
      
      if (packageModal) packageModal.classList.add('hidden');
      if (successModal) successModal.classList.remove('hidden');
      
      // Clear form
      const orderForm = $('#orderForm');
      if (orderForm) orderForm.reset();
    } else {
      console.error('Order failed with error:', result.error);
      alert(`Order failed: ${result.error || 'Unknown error occurred'}`);
    }
  } catch (error) {
    console.error('Caught error in handleOrderSubmit:', error);
    alert(`Order failed: ${error.message || 'Network error occurred'}`);
  } finally {
    // Restore button state
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
  }
}

// Event bindings
function bindEvents() {
  // Filter controls
  $$('.chip[data-audience]').forEach(chip => {
    chip.addEventListener('click', () => {
      $$('.chip[data-audience]').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      state.audienceFilter = chip.dataset.audience;
      loadPackages();
    });
  });
  
  // Search
  const searchInput = $('#searchPackages');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      state.searchQuery = e.target.value.trim();
      loadPackages();
    });
  }
  
  // Price sorting
  const priceSort = $('#priceSort');
  if (priceSort) {
    priceSort.addEventListener('change', (e) => {
      state.priceSort = e.target.value;
      loadPackages();
    });
  }
  
  // Modal controls
  $$('[data-close]').forEach(btn => {
    btn.addEventListener('click', closeModal);
  });
  
  // Form submission
  const orderForm = $('#orderForm');
  if (orderForm) {
    orderForm.addEventListener('submit', handleOrderSubmit);
  }
  
  // Duration change - update summary
  const durationSelect = $('#duration');
  if (durationSelect) {
    durationSelect.addEventListener('change', updateOrderSummary);
  }
  
  // Close modal on backdrop click
  const packageModal = $('#packageModal');
  if (packageModal) {
    packageModal.addEventListener('click', (e) => {
      if (e.target.classList.contains('modal-backdrop')) {
        closeModal();
      }
    });
  }
  
  const successModal = $('#successModal');
  if (successModal) {
    successModal.addEventListener('click', (e) => {
      if (e.target.classList.contains('modal-backdrop')) {
        closeModal();
      }
    });
  }
  
  // ESC key support
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeModal();
    }
  });
}

// Initialize
async function init() {
  console.log('Initializing packages app...');
  bindEvents();
  await loadPackages();
}

// Start when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

// Global function for inline onclick handlers
window.openPackageModal = openPackageModal;