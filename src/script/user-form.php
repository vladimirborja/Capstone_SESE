<?php
session_start();
require_once '../db_connect.php';

$id = $_GET['id'] ?? '';
$editing = false;
$user = ['name' => '', 'email' => '', 'role' => 'user'];

if ($id) {
  $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $editing = true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $role = $_POST['role'];
  $password = $_POST['password'];

  if ($editing) {
    if (!empty($password)) {
      $hashed = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?");
      $stmt->bind_param("ssssi", $name, $email, $role, $hashed, $id);
    } else {
      $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
      $stmt->bind_param("sssi", $name, $email, $role, $id);
    }
  } else {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, role, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $role, $hashed);
  }

  if ($stmt->execute()) {
    header("Location: dashboard.php");
    exit;
  } else {
    echo "Error: " . $conn->error;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $editing ? 'Edit' : 'Add' ?> User</title>
  <link rel="stylesheet" href="main.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #121826;
      color: #fff;
    }
    .container {
      margin-top: 80px;
      max-width: 500px;
    }
    .form-control, .form-select {
      background: #334155;
      color: #fff;
      border: none;
    }
  </style>
</head>
<body>
<div class="container">
  <h2 class="text-center mb-4" style="color:#00aaff;"><?= $editing ? 'Edit' : 'Add' ?> User</h2>
  <form method="post">
    <div class="mb-3">
      <label>Name</label>
      <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($user['name']) ?>">
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
    </div>
    <div class="mb-3">
      <label>Role</label>
      <select name="role" class="form-select">
        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
        <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
      </select>
    </div>
    <div class="mb-3">
      <label><?= $editing ? 'New Password (leave blank to keep current)' : 'Password' ?></label>
      <input type="password" name="password" class="form-control" <?= $editing ? '' : 'required' ?>>
    </div>
    <button type="submit" class="btn btn-primary"><?= $editing ? 'Update' : 'Create' ?></button>
    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
