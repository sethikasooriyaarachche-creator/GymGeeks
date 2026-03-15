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
    $stmt = $conn->prepare("DELETE FROM meal_templates WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: meals.php");
    exit();
  }

  // Add new meal
  $title = $_POST['title'];
  $description = $_POST['description'];
  $category = $_POST['category'];

  $stmt = $conn->prepare("INSERT INTO meal_templates (title, description, category, created_at) VALUES (?, ?, ?, NOW())");
  $stmt->bind_param("sss", $title, $description, $category);
  $stmt->execute();
  $stmt->close();
  header("Location: meals.php");
  exit();
}

// Fetch meals
$result = $conn->query("SELECT id, title, description, category FROM meal_templates ORDER BY created_at DESC");
$meals = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
  <title>Manage Meals - GYMgeekS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/site.js"></script>
</head>

<body>
  <div class="container py-5" data-reveal>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">🍽 Manage Meals</h2>
      <div class="d-flex gap-2 align-items-center">
        <a href="dashboard.php" class="btn btn-secondary">← Dashboard</a>
      </div>
    </div>

    <!-- Add Button -->
    <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addMealModal">
      ➕ Add Meal
    </button>

    <!-- Meals List -->
    <div class="card p-4 shadow">
      <h4>Existing Meals</h4>
      <?php if (empty($meals)): ?>
        <p class="text-muted">No meals added yet.</p>
      <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 g-4">
          <?php foreach ($meals as $meal): ?>
            <div class="col">
              <div class="card h-100">
                <div class="card-body d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <h5 class="card-title mb-1"><?= htmlspecialchars($meal['title']) ?></h5>
                      <span class="badge bg-info text-dark"><?= htmlspecialchars($meal['category']) ?></span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-meal-btn"
                      data-meal-id="<?= $meal['id'] ?>"
                      data-meal-title="<?= htmlspecialchars($meal['title'], ENT_QUOTES) ?>">
                      Delete
                    </button>
                  </div>
                  <p class="card-text mt-3 mb-0"><?= htmlspecialchars($meal['description']) ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Add Meal Modal -->
  <div class="modal fade" id="addMealModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Add New Meal</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="text" name="title" class="form-control mb-2" placeholder="Meal Title" required>
            <textarea name="description" class="form-control mb-2" placeholder="Meal Description" required></textarea>
            <select name="category" class="form-control mb-2" required>
              <option value="">Select Category</option>
              <option value="Weight Loss">Weight Loss</option>
              <option value="Muscle Gain">Muscle Gain</option>
              <option value="Balanced">Balanced</option>
            </select>
          </div>
          <div class="modal-footer">
            <button class="btn btn-success">Add Meal</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteMealModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <span id="mealTitlePlaceholder" class="fw-bold"></span>?</p>
          <input type="hidden" name="delete_id" id="deleteMealId">
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
  document.querySelectorAll('.delete-meal-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const mealId = btn.getAttribute('data-meal-id');
      const mealTitle = btn.getAttribute('data-meal-title');
      document.getElementById('deleteMealId').value = mealId;
      document.getElementById('mealTitlePlaceholder').innerText = mealTitle;
      const modal = new bootstrap.Modal(document.getElementById('deleteMealModal'));
      modal.show();
    });
  });
</script>

<script>
  document.querySelectorAll('.delete-meal-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const mealId = btn.getAttribute('data-meal-id');
      const mealTitle = btn.getAttribute('data-meal-title');
      document.getElementById('deleteMealId').value = mealId;
      document.getElementById('mealTitlePlaceholder').innerText = mealTitle;
      const modal = new bootstrap.Modal(document.getElementById('deleteMealModal'));
      modal.show();
    });
  });
</script>

</html>