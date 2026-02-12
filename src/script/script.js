// Mock data for user reports
const userReportsData = [
    {
        id: 1,
        name: "John Smith",
        email: "john.smith@example.com",
        message: "Found a lost dog near Central Park",
        status: "resolved",
        date: "2023-10-15"
    },
    {
        id: 2,
        name: "Emma Johnson",
        email: "emma.j@example.com",
        message: "My cat hasn't returned home for 2 days",
        status: "pending",
        date: "2023-10-16"
    },
    {
        id: 3,
        name: "Michael Brown",
        email: "m.brown@example.com",
        message: "Saw an injured bird in my backyard",
        status: "urgent",
        date: "2023-10-17"
    },
    {
        id: 4,
        name: "Sarah Davis",
        email: "sarah.davis@example.com",
        message: "Looking for a home for 3 kittens",
        status: "resolved",
        date: "2023-10-18"
    },
    {
        id: 5,
        name: "Robert Wilson",
        email: "robert.w@example.com",
        message: "Lost pet poster found near downtown",
        status: "pending",
        date: "2023-10-19"
    },
    {
        id: 6,
        name: "Lisa Taylor",
        email: "lisa.t@example.com",
        message: "Found a pet tag with contact info",
        status: "resolved",
        date: "2023-10-20"
    },
    {
        id: 7,
        name: "David Miller",
        email: "d.miller@example.com",
        message: "Pet adoption inquiry for older dogs",
        status: "pending",
        date: "2023-10-21"
    },
    {
        id: 8,
        name: "Jennifer Lee",
        email: "j.lee@example.com",
        message: "Reporting a stray animal colony",
        status: "urgent",
        date: "2023-10-22"
    }
];

// Function to render user reports table
function renderReportsTable(reports) {
    const tableBody = document.getElementById('reportsTableBody');
    tableBody.innerHTML = '';
    
    reports.forEach(report => {
        const statusClass = getStatusClass(report.status);
        const statusText = report.status.charAt(0).toUpperCase() + report.status.slice(1);
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="ps-4 fw-semibold">${report.name}</td>
            <td>${report.email}</td>
            <td>
                <div class="d-flex align-items-center">
                    <span class="me-2">${report.message}</span>
                    <span class="badge ${statusClass}">${statusText}</span>
                </div>
            </td>
            <td class="text-end pe-4">
                <button class="btn btn-sm btn-outline-primary me-2 view-btn" data-id="${report.id}">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-outline-success me-2 resolve-btn" data-id="${report.id}">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-btn" data-id="${report.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
    
    // Update showing count
    document.getElementById('showingCount').textContent = Math.min(3, reports.length);
    document.getElementById('totalCount').textContent = reports.length;
}

// Helper function to get status badge class
function getStatusClass(status) {
    switch(status) {
        case 'resolved': return 'bg-success';
        case 'pending': return 'bg-warning';
        case 'urgent': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// Function to load more reports
let visibleReports = 3;

function loadMoreReports() {
    visibleReports = Math.min(visibleReports + 3, userReportsData.length);
    const reportsToShow = userReportsData.slice(0, visibleReports);
    renderReportsTable(reportsToShow);
    
    // Hide button if all reports are shown
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (visibleReports >= userReportsData.length) {
        loadMoreBtn.disabled = true;
        loadMoreBtn.innerHTML = '<i class="fas fa-check me-1"></i>All Reports Loaded';
    }
}

// Function to handle report actions
function handleReportActions() {
    document.addEventListener('click', function(e) {
        // View button
        if (e.target.closest('.view-btn')) {
            const btn = e.target.closest('.view-btn');
            const reportId = btn.getAttribute('data-id');
            const report = userReportsData.find(r => r.id == reportId);
            alert(`Viewing report #${reportId}\n\nName: ${report.name}\nEmail: ${report.email}\nMessage: ${report.message}\nStatus: ${report.status}\nDate: ${report.date}`);
        }
        
        // Resolve button
        if (e.target.closest('.resolve-btn')) {
            const btn = e.target.closest('.resolve-btn');
            const reportId = btn.getAttribute('data-id');
            const reportIndex = userReportsData.findIndex(r => r.id == reportId);
            
            if (reportIndex !== -1) {
                userReportsData[reportIndex].status = 'resolved';
                renderReportsTable(userReportsData.slice(0, visibleReports));
                alert(`Report #${reportId} marked as resolved`);
            }
        }
        
        // Delete button
        if (e.target.closest('.delete-btn')) {
            const btn = e.target.closest('.delete-btn');
            const reportId = btn.getAttribute('data-id');
            
            if (confirm('Are you sure you want to delete this report?')) {
                const reportIndex = userReportsData.findIndex(r => r.id == reportId);
                
                if (reportIndex !== -1) {
                    userReportsData.splice(reportIndex, 1);
                    visibleReports = Math.max(3, visibleReports - 1);
                    renderReportsTable(userReportsData.slice(0, visibleReports));
                    
                    // Update total count
                    document.getElementById('totalCount').textContent = userReportsData.length;
                    
                    alert(`Report #${reportId} has been deleted`);
                }
            }
        }
        
        // Load more button
        if (e.target.id === 'loadMoreBtn' || e.target.closest('#loadMoreBtn')) {
            loadMoreReports();
        }
    });
}

// Function to update statistics dynamically (simulating changes)
function updateStatistics() {
    const statCards = document.querySelectorAll('.report-card h2.card-title');
    
    // Simulate small random changes to statistics
    statCards.forEach(card => {
        const currentValue = parseInt(card.textContent);
        const change = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
        const newValue = Math.max(0, currentValue + change);
        
        // Animate the change
        card.style.transform = 'scale(1.1)';
        setTimeout(() => {
            card.textContent = newValue;
            card.style.transform = 'scale(1)';
        }, 200);
    });
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Render initial reports
    renderReportsTable(userReportsData.slice(0, visibleReports));
    
    // Set up event handlers
    handleReportActions();
    
    // Update statistics every 30 seconds
    setInterval(updateStatistics, 30000);
    
    // Add active class to nav links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Mobile menu toggle (for smaller screens)
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    // If screen is small, add toggle functionality
    if (window.innerWidth < 992) {
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'btn btn-primary d-lg-none mb-3';
        toggleBtn.innerHTML = '<i class="fas fa-bars"></i> Toggle Menu';
        toggleBtn.style.position = 'fixed';
        toggleBtn.style.top = '10px';
        toggleBtn.style.right = '10px';
        toggleBtn.style.zIndex = '1000';
        
        document.body.appendChild(toggleBtn);
        
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('d-none');
            mainContent.classList.toggle('col-12');
        });
    }
});