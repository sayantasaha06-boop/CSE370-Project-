// Authentication helper functions

function logout() {
    localStorage.removeItem('token');
    window.location.href = 'index.html';
}

function checkAuthToken() {
    const token = localStorage.getItem('token');
    return token !== null;
}

// Redirect to login if not authenticated (for protected pages)
function requireAuth() {
    if (!checkAuthToken()) {
        window.location.href = 'login.html';
    }
}

// Get token for API requests
function getAuthHeader() {
    const token = localStorage.getItem('token');
    return token ? { 'Authorization': token } : {};
}