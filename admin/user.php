<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit();
}

include("../includes/db.php");

// Handle delete request inline
if (isset($_POST['delete_id'])) {
  $user_id = intval($_POST['delete_id']);
  $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->close();
}

// Fetch users
$result = $conn->query("SELECT id, username, email, bmi, created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>

<head>
  <title>Manage Users - GYMgeekS</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/site.js"></script>
</head>

<body>
  <div class="container py-5" data-reveal>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">👥 Manage Users</h2>
      <div class="d-flex gap-2 align-items-center">
        <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
      </div>
    </div>

    <div class="card p-4">
      <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>BMI</th>
              <th>Created At</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['bmi']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                  <button type="button" class="btn btn-danger btn-sm delete-user-btn" data-user-id="<?= $row['id'] ?>">
                    Delete
                  </button>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-muted">No users found.</p>
      <?php endif; ?>
    </div>
  </div>
  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete this user?</p>
            <input type="hidden" name="delete_id" id="deleteUserId">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    document.querySelectorAll('.delete-user-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const userId = btn.getAttribute('data-user-id');
        document.getElementById('deleteUserId').value = userId;
        const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
        modal.show();
      });
    });
  </script>
</body>

</html>