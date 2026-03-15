<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit();
}
include("../includes/db.php");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Deletion request
  if (isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM workout_templates WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: workouts.php");
    exit();
  }

  // Add new workout
  $title = $_POST['title'];
  $description = $_POST['description'];
  $category = $_POST['category'];

  $stmt = $conn->prepare("INSERT INTO workout_templates (title, description, category, created_at) VALUES (?, ?, ?, NOW())");
  $stmt->bind_param("sss", $title, $description, $category);
  $stmt->execute();
  $stmt->close();
  header("Location: workouts.php");
  exit();
}

// Fetch workouts
$result = $conn->query("SELECT id, title, description, category FROM workout_templates ORDER BY created_at DESC");
$workouts = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
  <title>Manage Workouts - GYMgeekS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/site.js"></script>
</head>

<body>
  <div class="container py-5" data-reveal>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">🏋️ Manage Workouts</h2>
      <div class="d-flex gap-2 align-items-center">
        <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
      </div>
    </div>

    <!-- Add Button -->
    <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addWorkoutModal">
      ➕ Add Workout
    </button>

    <!-- Workouts List -->
    <div class="card p-4 shadow">
      <h4>Existing Workouts</h4>
      <?php if (empty($workouts)): ?>
        <p class="text-muted">No workouts added yet.</p>
      <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 g-4">
          <?php foreach ($workouts as $workout): ?>
            <div class="col">
              <div class="card h-100">
                <div class="card-body d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <h5 class="card-title mb-1"><?= htmlspecialchars($workout['title']) ?></h5>
                      <span class="badge bg-info text-dark"><?= htmlspecialchars($workout['category']) ?></span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-workout-btn"
                      data-workout-id="<?= $workout['id'] ?>"
                      data-workout-title="<?= htmlspecialchars($workout['title'], ENT_QUOTES) ?>">
                      Delete
                    </button>
                  </div>
                  <p class="card-text mt-3 mb-0"><?= htmlspecialchars($workout['description']) ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Add Workout Modal -->
  <div class="modal fade" id="addWorkoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Add New Workout</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="text" name="title" class="form-control mb-2" placeholder="Workout Title" required>
            <textarea name="description" class="form-control mb-2" placeholder="Workout Description"
              required></textarea>
            <select name="category" class="form-control mb-2" required>
              <option value="">Select Category</option>
              <option value="Strength">Strength</option>
              <option value="Cardio">Cardio</option>
              <option value="Flexibility">Flexibility</option>
              <option value="Weight Loss">Weight Loss</option>
            </select>
          </div>
          <div class="modal-footer">
            <button class="btn btn-success">Add Workout</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteWorkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <span id="workoutTitlePlaceholder" class="fw-bold"></span>?</p>
          <input type="hidden" name="delete_id" id="deleteWorkoutId">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.querySelectorAll('.delete-workout-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const workoutId = btn.getAttribute('data-workout-id');
      const workoutTitle = btn.getAttribute('data-workout-title');
      document.getElementById('deleteWorkoutId').value = workoutId;
      document.getElementById('workoutTitlePlaceholder').innerText = workoutTitle;
      const modal = new bootstrap.Modal(document.getElementById('deleteWorkoutModal'));
      modal.show();
    });
  });
</script>

<script>
  document.querySelectorAll('.delete-workout-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const workoutId = btn.getAttribute('data-workout-id');
      const workoutTitle = btn.getAttribute('data-workout-title');
      document.getElementById('deleteWorkoutId').value = workoutId;
      document.getElementById('workoutTitlePlaceholder').innerText = workoutTitle;
      const modal = new bootstrap.Modal(document.getElementById('deleteWorkoutModal'));
      modal.show();
    });
  });
</script>

</html>