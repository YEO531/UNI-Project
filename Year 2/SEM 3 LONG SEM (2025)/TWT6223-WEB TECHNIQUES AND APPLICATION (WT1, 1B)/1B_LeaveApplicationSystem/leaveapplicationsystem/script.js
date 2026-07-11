// --- Global State ---
let leaveHistory = [];
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();

const leaveBalances = {
    'Annual Leave': { total: 14, used: 0 },
    'Emergency Leave': { total: 3, used: 0 },
    'Maternity Leave': { total: 90, used: 0 },
    'Paternity Leave': { total: 7, used: 0 },
    'Sick Leave': { total: 14, used: 0 },
    'Unpaid Leave': { total: 30, used: 0 }
};

const API_BASE = 'api';
const ENDPOINTS = {
    leaveRequest: `${API_BASE}/leave_request.php`,
    auth: `${API_BASE}/auth.php`
};

// --- Utility Functions ---
function showNotification(type, message) {
    const notification = document.getElementById(`${type}Notification`);
    if (!notification) return;
    const messageElement = notification.querySelector('p');
    if (messageElement) messageElement.textContent = message;
    notification.classList.add('show');
    setTimeout(() => notification.classList.remove('show'), 3000);
}

function validateDates(startDate, endDate) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const start = new Date(startDate);
    const end = new Date(endDate);
    if (isNaN(start) || isNaN(end)) return { valid: false, message: 'Invalid date format' };
    if (start < today) return { valid: false, message: 'Start date cannot be in the past' };
    if (end < start) return { valid: false, message: 'End date cannot be before start date' };
    const diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
    if (diffDays > 30) return { valid: false, message: 'Leave duration cannot exceed 30 days' };
    return { valid: true, days: diffDays };
}

// --- Calendar ---
function generateCalendar(month, year) {
    const calendar = document.getElementById('calendar');
    if (!calendar) return;
    calendar.innerHTML = '';
    const monthNames = ["January", "February", "March", "April", "May", "June", 
                      "July", "August", "September", "October", "November", "December"];
    document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    days.forEach(day => {
        const el = document.createElement('div');
        el.className = 'calendar-day header';
        el.textContent = day;
        calendar.appendChild(el);
    });
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    for (let i = 0; i < firstDay; i++) {
        const empty = document.createElement('div');
        empty.className = 'calendar-day';
        calendar.appendChild(empty);
    }
    for (let i = 1; i <= daysInMonth; i++) {
        const dayEl = document.createElement('div');
        dayEl.className = 'calendar-day';
        dayEl.textContent = i;
        const currentDate = new Date(year, month, i);
        currentDate.setHours(0, 0, 0, 0);
        if ([0, 6].includes(currentDate.getDay())) dayEl.classList.add('weekend');
        const today = new Date(); today.setHours(0, 0, 0, 0);
        if (currentDate.getTime() === today.getTime()) dayEl.classList.add('active');
        leaveHistory.forEach(leave => {
            const start = new Date(leave.startDate), end = new Date(leave.endDate);
            start.setHours(0,0,0,0); end.setHours(0,0,0,0);
            if (currentDate >= start && currentDate <= end) {
                dayEl.classList.add('leave');
                if (leave.status === 'pending') dayEl.classList.add('pending');
                dayEl.title = `${leave.title} (${leave.status})`;
            }
        });
        dayEl.addEventListener('click', () => {
            const startInput = document.getElementById('startDate');
            const endInput = document.getElementById('endDate');
            if (!startInput.value) {
                startInput.value = currentDate.toISOString().split('T')[0];
                calculateDays();
            } else if (!endInput.value) {
                const startDate = new Date(startInput.value);
                if (currentDate >= startDate) {
                    endInput.value = currentDate.toISOString().split('T')[0];
                    calculateDays();
                }
            } else {
                startInput.value = currentDate.toISOString().split('T')[0];
                endInput.value = '';
                calculateDays();
            }
        });
        calendar.appendChild(dayEl);
    }
}

// --- Leave History ---
function renderLeaveHistory() {
    const historyList = document.getElementById('historyList');
    if (!historyList) return;
    historyList.innerHTML = '';
    if (leaveHistory.length === 0) {
        historyList.innerHTML = `<div class="empty-state"><i class="fas fa-calendar-times"></i><p>No leave history found</p></div>`;
        return;
    }
    leaveHistory.forEach(leave => {
        const item = document.createElement('div');
        item.className = `history-item ${leave.type.toLowerCase().replace(' ', '-')}`;
        
        // Set appropriate icon based on leave type
        let iconClass = 'fas fa-user-clock';
        if (leave.type === 'Annual Leave') iconClass = 'fas fa-umbrella-beach';
        else if (leave.type === 'Sick Leave') iconClass = 'fas fa-heartbeat';
        else if (leave.type === 'Maternity Leave') iconClass = 'fas fa-baby';
        else if (leave.type === 'Paternity Leave') iconClass = 'fas fa-user-friends';
        else if (leave.type === 'Emergency Leave') iconClass = 'fas fa-exclamation-triangle';
        else if (leave.type === 'Unpaid Leave') iconClass = 'fas fa-calendar-times';
        
        const start = new Date(leave.startDate), end = new Date(leave.endDate);
        const startStr = start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        const endStr = end.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        const submittedAt = new Date(leave.submittedAt || leave.startDate);
        const submittedStr = submittedAt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        item.innerHTML = `
            <div class="history-icon"><i class="${iconClass}"></i></div>
            <div class="history-info">
                <h4>${leave.title}</h4>
                <div class="history-meta">
                    <span><i class="fas fa-calendar"></i> ${startStr} - ${endStr}</span>
                    <span><i class="fas fa-clock"></i> ${leave.days} days</span>
                    <span class="status ${leave.status}">${leave.status.charAt(0).toUpperCase() + leave.status.slice(1)}</span>
                </div>
                <div class="history-details">
                    <p><i class="fas fa-comment"></i> ${leave.reason}</p>
                    <p><i class="fas fa-paper-plane"></i> Submitted: ${submittedStr}</p>
                </div>
                ${leave.status === 'pending' ? `<button class="btn btn-danger btn-sm" onclick="cancelLeaveRequest(${leave.id})"><i class="fas fa-times"></i> Cancel</button>` : ''}
            </div>
        `;
        historyList.appendChild(item);
    });
}

// --- Leave Balances ---
function calculateLeaveBalances() {
    Object.keys(leaveBalances).forEach(type => leaveBalances[type].used = 0);
    leaveHistory.forEach(leave => {
        if (leave.status === 'approved' && leaveBalances[leave.type]) {
            leaveBalances[leave.type].used += leave.days;
        }
    });
    updateLeaveBalanceUI();
}

function updateLeaveBalanceUI() {
    Object.keys(leaveBalances).forEach(type => {
        const balance = leaveBalances[type];
        const remaining = balance.total - balance.used;
        const cssClass = type.toLowerCase().replace(' ', '-');
        const statCard = document.querySelector(`.stat-card.${cssClass}`);
        if (statCard) {
            const countElement = statCard.querySelector('h3');
            if (countElement) countElement.textContent = remaining;
        }
    });
}

// --- Days Calculation ---
function calculateDays() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const daysCount = document.getElementById('daysCount');
    if (!startDate || !endDate) {
        if (daysCount) daysCount.textContent = '0';
        return;
    }
    const validation = validateDates(startDate, endDate);
    if (!validation.valid) {
        showNotification('error', validation.message);
        if (daysCount) daysCount.textContent = '0';
        return;
    }
    if (daysCount) daysCount.textContent = validation.days;
}

// --- API Helpers ---
async function apiRequest(endpoint, method = 'GET', data = null) {
    const options = { method, headers: { 'Content-Type': 'application/json' } };
    if (data) options.body = JSON.stringify(data);
    const response = await fetch(endpoint, options);
    const result = await response.json();
    if (!response.ok || result.status === 'error') {
        showNotification('error', result.message || 'An error occurred');
        throw new Error(result.message || 'An error occurred');
    }
    return result;
}

// --- Leave Form Submission ---
async function handleSubmit(e) {
    e.preventDefault();
    const leaveType = document.getElementById('leaveType').value;
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const reason = document.getElementById('reason').value;
    if (!leaveType || !startDate || !endDate || !reason) {
        showNotification('error', 'Please fill all required fields');
        return;
    }
    const validation = validateDates(startDate, endDate);
    if (!validation.valid) {
        showNotification('error', validation.message);
        return;
    }

    // Check leave balance before submitting
    const typeKey = Object.keys(leaveBalances).find(
        key => key.toLowerCase() === leaveType.toLowerCase()
    );
    if (typeKey) {
        const balance = leaveBalances[typeKey];
        const remaining = balance.total - balance.used;
        if (validation.days > remaining) {
            showNotification('error', 'Insufficient leave balance');
            return;
        }
    }

    try {
        const data = {
            user_id: 1, // Replace with actual user_id from session/auth
            type_id: leaveType,
            start_date: startDate,
            end_date: endDate,
            total_days: validation.days,
            reason: reason
        };
        const result = await apiRequest(ENDPOINTS.leaveRequest, 'POST', data);
        document.getElementById('leaveForm').reset();
        document.getElementById('daysCount').textContent = '0';
        await loadLeaveHistory();
        generateCalendar(currentMonth, currentYear);
        showNotification('success', result.message);
    } catch (error) {
        // Error handled in apiRequest
    }
}

// --- Load Leave History ---
async function loadLeaveHistory() {
    try {
        const response = await fetch(`${ENDPOINTS.leaveRequest}?user_id=1`); // Replace with actual user_id
        const data = await response.json();
        if (Array.isArray(data)) {
            leaveHistory = data.map(leave => ({
                id: leave.request_id,
                type: leave.leave_type.toLowerCase(),
                title: leave.leave_type + ' Request',
                startDate: leave.start_date,
                endDate: leave.end_date,
                days: leave.total_days,
                status: leave.status,
                reason: leave.reason,
                submittedAt: leave.created_at
            }));
            renderLeaveHistory();
            calculateLeaveBalances();
        }
    } catch (error) {
        showNotification('error', 'Failed to load leave history');
    }
}

// --- Cancel Leave Request ---
async function cancelLeaveRequest(leaveId) {
    if (!confirm('Are you sure you want to cancel this leave request?')) return;
    try {
        const response = await fetch(`${ENDPOINTS.leaveRequest}/${leaveId}`, { method: 'DELETE' });
        const result = await response.json();
        if (result.status === 'success') {
            await loadLeaveHistory();
            generateCalendar(currentMonth, currentYear);
            showNotification('success', result.message);
        } else {
            showNotification('error', result.message);
        }
    } catch (error) {
        showNotification('error', 'Failed to cancel leave request');
    }
}

// --- Login/Register ---
async function handleLogin(e) {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    try {
        const result = await apiRequest(ENDPOINTS.auth, 'POST', { action: 'login', email, password });
        showNotification('success', 'Login successful');
        window.location.href = 'index.html';
    } catch (error) {
        // Error handled in apiRequest
    }
}

async function handleRegister(e) {
    e.preventDefault();
    const formData = {
        action: 'register',
        employee_id: document.getElementById('employee_id').value,
        first_name: document.getElementById('first_name').value,
        last_name: document.getElementById('last_name').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        department: document.getElementById('department').value,
        position: document.getElementById('position').value
    };
    try {
        const result = await apiRequest(ENDPOINTS.auth, 'POST', formData);
        showNotification('success', 'Registration successful');
        window.location.href = 'login.html';
    } catch (error) {
        // Error handled in apiRequest
    }
}

// --- Initialization ---
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('leaveForm')) {
        loadLeaveHistory();
        generateCalendar(currentMonth, currentYear);
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('startDate').min = today;
    document.getElementById('endDate').min = today;
    document.getElementById('startDate').addEventListener('change', calculateDays);
    document.getElementById('endDate').addEventListener('change', calculateDays);
    document.getElementById('leaveForm').addEventListener('submit', handleSubmit);
        if (document.getElementById('clearBtn')) {
    document.getElementById('clearBtn').addEventListener('click', () => {
        document.getElementById('leaveForm').reset();
        document.getElementById('daysCount').textContent = '0';
    });
        }
        if (document.getElementById('prevMonth')) {
    document.getElementById('prevMonth').addEventListener('click', () => {
        currentMonth--;
                if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        generateCalendar(currentMonth, currentYear);
    });
        }
        if (document.getElementById('nextMonth')) {
    document.getElementById('nextMonth').addEventListener('click', () => {
        currentMonth++;
                if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        generateCalendar(currentMonth, currentYear);
    });
        }
        if (document.getElementById('refreshHistory')) {
            document.getElementById('refreshHistory').addEventListener('click', loadLeaveHistory);
        }
    }
    if (document.getElementById('loginForm')) {
        document.getElementById('loginForm').addEventListener('submit', handleLogin);
    }
    if (document.getElementById('registerForm')) {
        document.getElementById('registerForm').addEventListener('submit', handleRegister);
    }
});

eventClick: function(info) {
    // Only allow editing/deleting for notes
    if (info.event.title === 'Note') {
        const currentText = info.event.extendedProps.description || '';
        const newText = prompt('Edit note text:', currentText);

        if (newText === null) {
            // User cancelled
            return;
        }

        if (newText === '') {
            // If empty, ask to delete
            if (confirm('Delete this note?')) {
                fetch('api/add_note.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'delete',
                        note_id: info.event.id
                    })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) location.reload();
                });
            }
        } else if (newText !== currentText) {
            // Edit note
            fetch('api/add_note.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'edit',
                    note_id: info.event.id,
                    note: newText
                })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            });
        }
    }
}

var calendar = new FullCalendar.Calendar(calendarEl, {
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'refreshBtn dayGridMonth,timeGridWeek,timeGridDay'
  },
  customButtons: {
    refreshBtn: {
      text: 'Refresh',
      click: function() {
        location.reload();
      }
    }
  },
  buttonText: {
    today:    'Today',
    month:    'Monthly',
    week:     'Weekly',
    day:      'Daily'
  }
});