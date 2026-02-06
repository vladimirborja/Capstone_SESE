<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - Pet Platform</title>

    <!-- Bootstrap 5 CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />

    <!-- Font Awesome -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />

    <!-- Google Fonts -->
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
      rel="stylesheet"
    />

    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: 'Inter', sans-serif;
        background-color: #e8eef3;
        min-height: 100vh;
      }

      /* Top Navigation Bar */
      .top-nav {
        background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        padding: 12px 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 30px;
      }

      .nav-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1400px;
        margin: 0 auto;
      }

      .paw-logo {
        width: 45px;
        height: 45px;
        background: rgba(255,255,255,0.25);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
      }

      .user-icon {
        width: 45px;
        height: 45px;
        background: rgba(255,255,255,0.9);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #666;
      }

      /* Main Container */
      .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 30px;
      }

      /* Top Section - Reports and Stats */
      .top-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-bottom: 30px;
      }

      /* Reports Panel */
      .reports-panel {
        background: #c5d5e0;
        border-radius: 15px;
        padding: 25px;
      }

      .reports-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
      }

      .reports-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 20px;
        font-weight: 700;
        color: #000;
      }

      .reports-icon {
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
      }

      .reports-count {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 28px;
        font-weight: 700;
      }

      .report-item {
        background: white;
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      }

      .report-user {
        display: flex;
        align-items: center;
        gap: 12px;
      }

      .report-avatar {
        width: 40px;
        height: 40px;
        background: #d0d0d0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #888;
        font-size: 20px;
      }

      .report-text {
        font-size: 15px;
        color: #333;
      }

      .delete-btn {
        background: none;
        border: none;
        font-size: 20px;
        color: #333;
        cursor: pointer;
        padding: 5px 10px;
      }

      .delete-btn:hover {
        color: #dc3545;
      }

      /* Stats Grid */
      .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
      }

      .stat-card {
        background: #c5d5e0;
        border-radius: 12px;
        padding: 25px 20px;
        text-align: left;
      }

      .stat-label {
        font-size: 13px;
        font-weight: 700;
        color: #000;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
      }

      .stat-value {
        font-size: 56px;
        font-weight: 900;
        line-height: 1;
        color: #000;
      }

      /* Messages Section */
      .messages-section {
        background: #c5d5e0;
        border-radius: 15px;
        padding: 25px;
      }

      .messages-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
      }

      .messages-icon {
        font-size: 28px;
        color: #000;
      }

      .messages-title {
        font-size: 20px;
        font-weight: 700;
        color: #000;
      }

      .messages-table {
        width: 100%;
      }

      .messages-table thead th {
        font-size: 16px;
        font-weight: 700;
        color: #000;
        padding-bottom: 15px;
        text-align: left;
        border: none;
      }

      .messages-table tbody tr {
        margin-bottom: 10px;
      }

      .messages-table tbody td {
        padding: 8px 0;
      }

      .message-input-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
      }

      .message-input {
        flex: 1;
        background: #d8e4ed;
        border: 1px solid #a0b5c5;
        border-radius: 6px;
        padding: 12px 15px;
        font-size: 14px;
        color: #333;
      }

      .message-input:focus {
        outline: none;
        border-color: #2196F3;
      }

      .message-delete-btn {
        background: none;
        border: none;
        font-size: 18px;
        color: #333;
        cursor: pointer;
        padding: 5px 10px;
      }

      .message-delete-btn:hover {
        color: #dc3545;
      }

      .messages-table tbody tr td:first-child .message-input {
        width: 280px;
      }

      .messages-table tbody tr td:nth-child(2) .message-input {
        width: 280px;
      }

      .messages-table tbody tr td:nth-child(3) {
        width: 100%;
      }
    </style>
  </head>
  <body>
    <!-- Top Navigation -->
    <div class="top-nav">
      <div class="nav-content">
        <div class="paw-logo">
          <i class="fas fa-paw"></i>
        </div>
        <div class="user-icon">
          <i class="fas fa-user"></i>
        </div>
      </div>
    </div>

    <!-- Main Dashboard -->
    <div class="dashboard-container">
      <!-- Top Section: Reports and Stats -->
      <div class="top-section">
        <!-- Reports Panel -->
        <div class="reports-panel">
          <div class="reports-header">
            <div class="reports-title">
              <div class="reports-icon">
                <i class="fas fa-clipboard-list"></i>
              </div>
              <span>REPORTS</span>
            </div>
            <div class="reports-count">
              <i class="fas fa-flag"></i>
              <span>10</span>
            </div>
          </div>

          <!-- Report Items -->
          <div class="report-item">
            <div class="report-user">
              <div class="report-avatar">
                <i class="fas fa-user"></i>
              </div>
              <span class="report-text">User Reports.....</span>
            </div>
            <button class="delete-btn">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>

          <div class="report-item">
            <div class="report-user">
              <div class="report-avatar">
                <i class="fas fa-user"></i>
              </div>
              <span class="report-text">User Reports.....</span>
            </div>
            <button class="delete-btn">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>

          <div class="report-item">
            <div class="report-user">
              <div class="report-avatar">
                <i class="fas fa-user"></i>
              </div>
              <span class="report-text">User Reports.....</span>
            </div>
            <button class="delete-btn">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>

          <div class="report-item">
            <div class="report-user">
              <div class="report-avatar">
                <i class="fas fa-user"></i>
              </div>
              <span class="report-text">User Reports.....</span>
            </div>
            <button class="delete-btn">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>

          <div class="report-item">
            <div class="report-user">
              <div class="report-avatar">
                <i class="fas fa-user"></i>
              </div>
              <span class="report-text">User Reports.....</span>
            </div>
            <button class="delete-btn">
              <i class="fas fa-trash-alt"></i>
            </button>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-label">ACTIVE USERS</div>
            <div class="stat-value">47</div>
          </div>

          <div class="stat-card">
            <div class="stat-label">INACTIVE USERS</div>
            <div class="stat-value">2</div>
          </div>

          <div class="stat-card">
            <div class="stat-label">PARTNERS</div>
            <div class="stat-value">16</div>
          </div>

          <div class="stat-card">
            <div class="stat-label">FOUND PETS</div>
            <div class="stat-value">9</div>
          </div>

          <div class="stat-card">
            <div class="stat-label">LOST PETS</div>
            <div class="stat-value">24</div>
          </div>

          <div class="stat-card">
            <div class="stat-label">MESSAGES</div>
            <div class="stat-value">7</div>
          </div>
        </div>
      </div>

      <!-- Messages Section -->
      <div class="messages-section">
        <div class="messages-header">
          <i class="fas fa-envelope messages-icon"></i>
          <span class="messages-title">MESSAGES</span>
        </div>

        <table class="messages-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Message</th>
              <th style="width: 50px;"></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <div class="message-input-wrapper">
                  <input type="text" class="message-input" />
                  <button class="message-delete-btn">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <div class="message-input-wrapper">
                  <input type="text" class="message-input" />
                  <button class="message-delete-btn">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <div class="message-input-wrapper">
                  <input type="text" class="message-input" />
                  <button class="message-delete-btn">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <div class="message-input-wrapper">
                  <input type="text" class="message-input" />
                  <button class="message-delete-btn">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <div class="message-input-wrapper">
                  <input type="text" class="message-input" />
                  <button class="message-delete-btn">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <div class="message-input-wrapper">
                  <input type="text" class="message-input" />
                  <button class="message-delete-btn">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <input type="text" class="message-input" />
              </td>
              <td>
                <div class="message-input-wrapper">
                  <input type="text" class="message-input" />
                  <button class="message-delete-btn">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>