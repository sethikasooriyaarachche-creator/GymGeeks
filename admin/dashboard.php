<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit();
}

include("../includes/db.php");

// Handle Add / Delete Admin form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Delete admin
  if (isset($_POST['delete_admin_id'])) {
    $deleteId = (int) $_POST['delete_admin_id'];
    $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit();
  }

  // Add admin
  if (isset($_POST['add_admin'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admins (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
      $msg = "Admin added successfully!";
    } else {
      $msg = "Error: " . $stmt->error;
    }

    $stmt->close();
  }
}

// Load last 50 appointments
$appointments = [];
$stmt = $conn->prepare("SELECT a.*, u.username AS user_name FROM appointments a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 50");
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Load admins
$admins = [];
$stmt = $conn->prepare("SELECT id, username, email, created_at FROM admins ORDER BY created_at DESC LIMIT 50");
$stmt->execute();
$result = $stmt->get_result();
$admins = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html>

<head>
  <title>Admin Dashboard - GYMgeekS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/site.js"></script>
</head>

<body>
  <div class="container py-5" data-reveal>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Admin Dashboard</h2>
    </div>

    <!-- Feedback message -->
    <?php if (!empty($msg)): ?>
      <div class="alert alert-info text-center"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="row mt-4">
      <div class="col-md-3">
        <a href="users.php" class="btn btn-primary w-100 mb-3">Manage Users</a>
      </div>
      <div class="col-md-3">
        <a href="workouts.php" class="btn btn-success w-100 mb-3">Manage Workouts</a>
      </div>
      <div class="col-md-3">
        <a href="meals.php" class="btn btn-warning w-100 mb-3">Manage Meals</a>
      </div>
      <div class="col-md-3">
        <!-- Add Admin Button -->
        <button class="btn btn-info w-100 mb-3" data-bs-toggle="modal" data-bs-target="#addAdminModal">
          ➕ Add Admin
        </button>
      </div>
    </div>

    <!-- Admins -->
    <div class="card shadow mb-4">
      <div class="card-header bg-secondary text-white">👤 Admins</div>
      <div class="card-body">
        <?php if (empty($admins)): ?>
          <p class="text-muted mb-0">No admins available yet.</p>
        <?php else: ?>
          <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($admins as $admin): ?>
              <div class="col">
                <div class="card h-100">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-1"><?= htmlspecialchars($admin['username']) ?></h5>
                    <p class="card-text mb-1"><strong>Email:</strong> <?= htmlspecialchars($admin['email']) ?></p>
                    <p class="card-text text-muted mb-3"><small>Added: <?= htmlspecialchars($admin['created_at']) ?></small>
                    </p>
                    <button type="button" class="btn btn-sm btn-outline-danger w-100 delete-admin-btn"
                      data-admin-id="<?= $admin['id'] ?>"
                      data-admin-name="<?= htmlspecialchars($admin['username'], ENT_QUOTES) ?>">
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Appointments -->
    <div class="card shadow mb-4">
      <div class="card-header bg-secondary text-white">📅 Recent Appointments</div>
      <div class="card-body p-0">
        <?php if (!empty($appointments)): ?>
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead class="table-dark">
                <tr>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Booked by</th>
                  <th>Goal</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($appointments as $a): ?>
                  <tr>
                    <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                    <td><?= htmlspecialchars($a['appointment_time']) ?></td>
                    <td><?= htmlspecialchars($a['name']) ?></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
                    <td><?= htmlspecialchars($a['user_name'] ?? 'Guest') ?></td>
                    <td><?= nl2br(htmlspecialchars($a['goal'])) ?></td>
                    <td><?= htmlspecialchars($a['created_at']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="p-3">
            <p class="mb-0 text-muted">No appointments have been booked yet.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <a href="../logout.php" class="btn btn-danger mt-3">Logout</a>
  </div>

  <!-- Add Admin Modal -->
  <div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Create New Admin</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="add_admin" value="1">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Add Admin</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>

<!-- Delete Admin Confirmation Modal -->
<div class="modal fade" id="deleteAdminModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Remove Admin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to remove <span id="adminNamePlaceholder" class="fw-bold"></span> as an admin?</p>
          <input type="hidden" name="delete_admin_id" id="deleteAdminId">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Remove</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.querySelectorAll('.delete-admin-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const adminId = btn.getAttribute('data-admin-id');
      const adminName = btn.getAttribute('data-admin-name');
      document.getElementById('deleteAdminId').value = adminId;
      document.getElementById('adminNamePlaceholder').innerText = adminName;
      const modal = new bootstrap.Modal(document.getElementById('deleteAdminModal'));
      modal.show();
    });
  });
</script>

</html>