<?php
session_start();
include("includes/db.php");

// If user not logged in → show login/register
if (!isset($_SESSION['user_id'])) {
  ?>
  <!DOCTYPE html>
  <html>

  <head>
    <title>GYMgeekS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
  </head>

  <body>
    <div class="container py-5">
      <h1 class="text-center text-primary mb-4">🏋️ GYMgeekS Portal</h1>
      <div class="card p-4 shadow mx-auto" style="max-width:400px;">
        <form action="login.php" method="POST">
          <input type="text" name="username" class="form-control mb-3" placeholder="Username or Email" required>
          <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
          <button class="btn btn-success w-100">Login</button>
        </form>
        <hr>
        <p class="text-center">Don’t have a user account?</p>
        <a href="register.php" class="btn btn-primary w-100">Register</a>
      </div>
    </div>
  </body>

  </html>
  <?php
  exit();
}

// Logged in → fetch user data
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $weight = $_POST['weight'];
  $height = $_POST['height'];
  $chest = $_POST['chest'];
  $waist = $_POST['waist'];
  $arms = $_POST['arms'];
  $legs = $_POST['legs'];

  // BMI calculation (height in meters)
  $bmi = 0;
  if ($height > 0) {
    $bmi = $weight / ($height * $height);
  }

  $stmt = $conn->prepare("UPDATE users 
        SET weight=?, height=?, chest=?, waist=?, arms=?, legs=?, bmi=?, created_at=NOW()
        WHERE id=?");
  $stmt->bind_param("dddddddi", $weight, $height, $chest, $waist, $arms, $legs, $bmi, $user_id);
  $stmt->execute();
  $stmt->close();
}

// Fetch updated user data
$stmt = $conn->prepare("SELECT weight, height, chest, waist, arms, legs, bmi FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

// Fetch workouts dynamically filtered by BMI
$stmt = $conn->prepare("SELECT title, description, sets, reps, rest, category 
                        FROM workout_templates 
                        WHERE (bmi_min IS NULL OR bmi_min <= ?) 
                          AND (bmi_max IS NULL OR bmi_max >= ?)");
$stmt->bind_param("dd", $userData['bmi'], $userData['bmi']);
$stmt->execute();
$result = $stmt->get_result();
$workouts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch meals dynamically filtered by BMI
$stmt = $conn->prepare("SELECT title, description, category 
                        FROM meal_templates 
                        WHERE (bmi_min IS NULL OR bmi_min <= ?) 
                          AND (bmi_max IS NULL OR bmi_max >= ?)");
$stmt->bind_param("dd", $userData['bmi'], $userData['bmi']);
$stmt->execute();
$result = $stmt->get_result();
$meals = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Prepare BMI status and UI helpers
$bmi = floatval($userData['bmi'] ?? 0);
$bmiLabel = 'Unknown';
$bmiColor = 'secondary';
$bmiNote = '';
if ($bmi > 0) {
  if ($bmi < 18.5) {
    $bmiLabel = 'Underweight';
    $bmiColor = 'warning';
    $bmiNote = 'Try adding balanced calories and strength training.';
  } elseif ($bmi < 25) {
    $bmiLabel = 'Normal';
    $bmiColor = 'success';
    $bmiNote = 'Great job! Keep building consistent habits.';
  } elseif ($bmi < 30) {
    $bmiLabel = 'Overweight';
    $bmiColor = 'warning';
    $bmiNote = 'Focus on balanced nutrition and regular cardio.';
  } else {
    $bmiLabel = 'Obese';
    $bmiColor = 'danger';
    $bmiNote = 'Consider speaking with a coach or a doctor for a plan.';
  }
}

// Group workouts and meals by category for UI sections
$workoutsByCategory = [];
foreach ($workouts as $w) {
  $category = trim($w['category'] ?? '') ?: 'General';
  $workoutsByCategory[$category][] = $w;
}

$mealsByCategory = [];
foreach ($meals as $m) {
  $category = trim($m['category'] ?? '') ?: 'General';
  $mealsByCategory[$category][] = $m;
}

// Create a simple weekly workout schedule (workout / rest alternating days)
$weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$weeklySchedule = [];
$workoutIndex = 0;
foreach ($weekDays as $i => $day) {
  $isWorkoutDay = ($i % 2 === 0); // Mon, Wed, Fri, Sun
  if ($isWorkoutDay && !empty($workouts)) {
    $workout = $workouts[$workoutIndex % count($workouts)];
    $weeklySchedule[$day] = [
      'type' => 'workout',
      'workout' => $workout,
    ];
    $workoutIndex++;
  } else {
    $weeklySchedule[$day] = [
      'type' => 'rest',
    ];
  }
}

?>
<!DOCTYPE html>
<html>

<head>
  <title>GYMgeekS Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="assets/js/site.js"></script>
  <style>
    body {
      min-height: 100vh;
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .card-header {
      font-weight: bold;
    }
  </style>
</head>

<body>
  <div class="container py-5" data-reveal>
    <div class="mb-4">
      <h2 class="text-dark mb-3 mb-md-0">Welcome, <?= $_SESSION['user_name'] ?> 👋</h2>
    </div>

    <!-- Update sizes button + modal -->
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div>
        <h4 class="mb-1">Your Measurements</h4>
        <p class="small text-muted mb-0">Tip: update your measurements at least once a month to keep your plan accurate.
        </p>
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateSizesModal">Update sizes</button>
    </div>

    <!-- Update Sizes Modal -->
    <div class="modal fade" id="updateSizesModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST">
            <div class="modal-header">
              <h5 class="modal-title">Update Your Sizes</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Weight (kg)</label>
                <input type="number" step="0.1" name="weight" class="form-control"
                  value="<?= htmlspecialchars($userData['weight'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Height (m)</label>
                <input type="number" step="0.01" name="height" class="form-control"
                  value="<?= htmlspecialchars($userData['height'] ?? '') ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Chest (cm)</label>
                <input type="number" name="chest" class="form-control"
                  value="<?= htmlspecialchars($userData['chest'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Waist (cm)</label>
                <input type="number" name="waist" class="form-control"
                  value="<?= htmlspecialchars($userData['waist'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Arms (cm)</label>
                <input type="number" name="arms" class="form-control"
                  value="<?= htmlspecialchars($userData['arms'] ?? '') ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Legs (cm)</label>
                <input type="number" name="legs" class="form-control"
                  value="<?= htmlspecialchars($userData['legs'] ?? '') ?>">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success">Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <?php if (!empty($userData['weight']) && !empty($userData['height'])): ?>
      <!-- Sizes Display -->
      <div class="row text-center mb-4">
        <?php foreach (['weight' => 'kg', 'height' => 'm', 'chest' => 'cm', 'waist' => 'cm', 'arms' => 'cm', 'legs' => 'cm'] as $field => $unit): ?>
          <div class="col-md-4 mb-3">
            <div class="card shadow">
              <div class="card-body">
                <h6 class="card-title"><?= ucfirst($field) ?></h6>
                <p class="fw-bold text-primary"><span><?= htmlspecialchars($userData[$field]) ?></span> <span
                    class="text-muted"><?= $unit ?></span></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- BMI Status -->
      <div class="row text-center mb-4">
        <div class="col-md-4 mb-3">
          <div class="card shadow border-<?= $bmiColor ?>">
            <div class="card-header bg-<?= $bmiColor ?> text-white">BMI Status</div>
            <div class="card-body">
              <h3 class="fw-bold mb-1">
                <?= $bmi > 0 ? number_format($bmi, 1) : '—' ?>
              </h3>
              <p class="mb-1">You are <strong>
                  <?= htmlspecialchars($bmiLabel) ?>
                </strong>.</p>
              <p class="small text-muted mb-2">Healthy range: 18.5–24.9</p>
              <div class="position-relative" style="height:180px;">
                <canvas id="bmiChart"></canvas>
              </div>
              <?php if ($bmiNote): ?>
                <p class="small text-muted mt-2">
                  <?= htmlspecialchars($bmiNote) ?>
                </p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        Update your weight and height above to see personalized BMI insights and your weekly schedule.
      </div>
    <?php endif; ?>

    <!-- Weekly Workout Schedule -->
    <div class="card shadow mb-3">
      <div class="card-header bg-info text-white">📅 Weekly Workout Schedule</div>
      <div class="card-body">
        <div class="row row-cols-1 row-cols-md-3 row-cols-xl-7 g-3">
          <?php foreach ($weeklySchedule as $day => $plan): ?>
            <div class="col">
              <div class="card h-100 border-<?= $plan['type'] === 'rest' ? 'secondary' : 'primary' ?>">
                <div
                  class="card-header bg-<?= $plan['type'] === 'rest' ? 'light' : 'primary' ?> text-<?= $plan['type'] === 'rest' ? 'dark' : 'white' ?>">
                  <?= htmlspecialchars($day) ?>
                </div>
                <div class="card-body p-2 d-flex flex-column">
                  <?php if ($plan['type'] === 'rest'): ?>
                    <p class="mb-0 fw-bold">Rest Day</p>
                    <p class="small text-muted mb-2">Recovery & mobility</p>
                    <button type="button" class="btn btn-sm btn-outline-light mt-auto"
                      onclick="startWorkoutForDay('<?= addslashes($day) ?>')">Start Light Stretch</button>
                  <?php else: ?>
                    <p class="mb-1 fw-bold">
                      <?= htmlspecialchars($plan['workout']['title']) ?>
                    </p>
                    <p class="small text-muted mb-1">
                      <?= htmlspecialchars($plan['workout']['description']) ?>
                    </p>
                    <div class="small mb-2">
                      Sets:
                      <?= htmlspecialchars($plan['workout']['sets']) ?> •
                      Reps:
                      <?= htmlspecialchars($plan['workout']['reps']) ?> •
                      Rest:
                      <?= htmlspecialchars($plan['workout']['rest']) ?>s
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-light mt-auto"
                      onclick="startWorkoutForDay('<?= addslashes($day) ?>')">Start</button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Weekly Progress Chart -->
    <div class="card shadow mb-3">
      <div class="card-header bg-secondary text-white">📈 Weekly Progress</div>
      <div class="card-body">
        <canvas id="progressChart" height="120"></canvas>
      </div>
    </div>

    <!-- Workout Plan -->
    <div class="card shadow mb-3">
      <div class="card-header bg-primary text-white">🏋️ Workout Plan</div>
      <div class="card-body">
        <?php if (!empty($workoutsByCategory)): ?>
          <div class="accordion" id="workoutAccordion">
            <?php $idx = 0;
            foreach ($workoutsByCategory as $cat => $list):
              $idx++; ?>
              <div class="accordion-item">
                <h2 class="accordion-header" id="workoutHeading<?= $idx ?>">
                  <button class="accordion-button <?= $idx !== 1 ? 'collapsed' : '' ?>" type="button"
                    data-bs-toggle="collapse" data-bs-target="#workoutCollapse<?= $idx ?>"
                    aria-expanded="<?= $idx === 1 ? 'true' : 'false' ?>" aria-controls="workoutCollapse<?= $idx ?>">
                    <?= htmlspecialchars($cat) ?> (<?= count($list) ?>)
                  </button>
                </h2>
                <div id="workoutCollapse<?= $idx ?>" class="accordion-collapse collapse <?= $idx === 1 ? 'show' : '' ?>"
                  aria-labelledby="workoutHeading<?= $idx ?>" data-bs-parent="#workoutAccordion">
                  <div class="accordion-body p-0">
                    <div class="list-group list-group-flush">
                      <?php foreach ($list as $w): ?>
                        <div class="list-group-item">
                          <div class="d-flex justify-content-between align-items-start">
                            <div>
                              <div class="fw-bold"><?= htmlspecialchars($w['title']) ?></div>
                              <div class="text-muted small"><?= htmlspecialchars($w['description']) ?></div>
                            </div>
                            <div class="text-end small">
                              <div>Sets: <?= htmlspecialchars($w['sets']) ?></div>
                              <div>Reps: <?= htmlspecialchars($w['reps']) ?></div>
                              <div>Rest: <?= htmlspecialchars($w['rest']) ?>s</div>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted">No workouts available for your BMI.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Meal Plan -->
    <div class="card shadow mb-3">
      <div class="card-header bg-success text-white">🍽 Meal Plan</div>
      <div class="card-body">
        <?php if (!empty($mealsByCategory)): ?>
          <div class="row g-3">
            <?php foreach ($mealsByCategory as $cat => $list): ?>
              <div class="col-md-6">
                <div class="card border-secondary h-100">
                  <div class="card-header bg-light"><?= htmlspecialchars($cat) ?> (<?= count($list) ?>)</div>
                  <ul class="list-group list-group-flush">
                    <?php foreach ($list as $m): ?>
                      <li class="list-group-item">
                        <div class="fw-bold"><?= htmlspecialchars($m['title']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($m['description']) ?></div>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted">No meals available for your BMI.</p>
        <?php endif; ?>
      </div>
    </div>



    <a href="logout.php" class="btn btn-danger mt-4">Logout</a>
  </div>


  <!-- Workout Modal -->
  <div class="modal fade" id="workoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Workout Assistant</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="exerciseText"></p>
          <div class="progress mb-2">
            <div id="restBar" class="progress-bar bg-danger" role="progressbar" style="width:0%"></div>
          </div>
          <p id="restText" class="text-danger fw-bold"></p>
        </div>
        <div class="modal-footer">
          <button id="quitBtn" type="button" class="btn btn-secondary">Quit</button>
          <button id="nextBtn" class="btn btn-primary">Next</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const workouts = <?= json_encode($workouts) ?>;
    const weeklySchedule = <?= json_encode($weeklySchedule) ?>;

    let activeWorkouts = workouts;
    let currentExerciseIndex = 0;
    let currentSet = 1;
    let timerInterval = null;
    let timerRemaining = 0;
    let workoutModal = null;
    let progressChart = null;

    function formatTime(seconds) {
      const mins = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return mins > 0 ? `${mins}:${secs.toString().padStart(2, '0')}` : `${secs}s`;
    }

    const EXERCISE_SECONDS = 1 * 60; // 15 minutes per exercise
    const REST_SECONDS = 60; // 1 minute rest
    let currentPhase = 'exercise'; // 'exercise' | 'rest'

    function renderExercise() {
      const exerciseTextEl = document.getElementById('exerciseText');
      const restTextEl = document.getElementById('restText');
      const restBarEl = document.getElementById('restBar');
      const nextBtn = document.getElementById('nextBtn');

      if (currentExerciseIndex >= activeWorkouts.length) {
        exerciseTextEl.innerText = '🎉 Workout complete! Great job!';
        restTextEl.innerText = '';
        restBarEl.style.width = '0%';
        nextBtn.style.display = 'none';

        // Track progress and refresh chart
        recordWorkoutCompletion();
        renderProgressChart();
        if (window.gymgeeks) {
          gymgeeks.confetti();
          gymgeeks.showToast('Great job! Workout complete.', 'success');
        }
        return;
      }

      const workout = activeWorkouts[currentExerciseIndex];
      const totalSets = workout.sets || 1;
      exerciseTextEl.innerText = `${workout.title} — ${workout.description}\nSet ${currentSet} of ${totalSets} • ${workout.reps} reps`;

      // Reset UI
      restTextEl.innerText = `Get ready...`;
      restBarEl.style.width = '0%';
      nextBtn.style.display = 'inline-block';
      nextBtn.disabled = true;

      // Start with exercise timer, then automatically start rest timer
      currentPhase = 'exercise';
      startPhaseTimer(EXERCISE_SECONDS);
    }

    function startPhaseTimer(seconds) {
      const restTextEl = document.getElementById('restText');
      const restBarEl = document.getElementById('restBar');
      const nextBtn = document.getElementById('nextBtn');

      clearInterval(timerInterval);
      timerRemaining = seconds;

      updatePhaseUI();

      timerInterval = setInterval(() => {
        timerRemaining -= 1;
        if (timerRemaining < 0) {
          clearInterval(timerInterval);

          if (currentPhase === 'exercise') {
            currentPhase = 'rest';
            startPhaseTimer(REST_SECONDS);
            return;
          }

          // Rest finished
          nextBtn.disabled = false;
          restTextEl.innerText = '✅ Ready! Tap Next to continue.';
          restBarEl.style.width = '100%';
          return;
        }

        updatePhaseUI();
      }, 1000);

      function updatePhaseUI() {
        const total = currentPhase === 'exercise' ? EXERCISE_SECONDS : REST_SECONDS;
        const phaseLabel = currentPhase === 'exercise' ? 'Exercise' : 'Rest';
        restTextEl.innerText = `⏳ ${phaseLabel}: ${formatTime(timerRemaining)}`;
        const percent = ((total - timerRemaining) / total) * 100;
        restBarEl.style.width = `${percent}%`;
      }
    }

    function teardownWorkout() {
      clearInterval(timerInterval);
      timerInterval = null;
      currentExerciseIndex = 0;
      currentSet = 1;
    }

    function nextStep() {
      const workout = activeWorkouts[currentExerciseIndex];
      if (!workout) return;

      if (currentSet < (workout.sets || 1)) {
        currentSet += 1;
      } else {
        currentExerciseIndex += 1;
        currentSet = 1;
      }

      renderExercise();
    }

    function quitWorkout() {
      teardownWorkout();
      if (workoutModal) {
        workoutModal.hide();
      }
    }

    function startWorkoutSession(workoutList) {
      if (!Array.isArray(workoutList) || workoutList.length === 0) {
        alert('No workouts available for your BMI.');
        return;
      }

      activeWorkouts = workoutList;
      workoutModal = new bootstrap.Modal(document.getElementById('workoutModal'));
      workoutModal.show();
      currentExerciseIndex = 0;
      currentSet = 1;
      renderExercise();
    }

    function startServerWorkout() {
      startWorkoutSession(workouts);
    }

    function startWorkoutForDay(day) {
      const plan = weeklySchedule[day];
      if (!plan) {
        alert('No schedule found for that day.');
        return;
      }

      if (plan.type === 'rest') {
        startWorkoutSession([{
          title: 'Light Stretch & Mobility',
          description: 'Use this time to stretch, breathe, and reset.',
          sets: 1,
          reps: 1,
          rest: 120
        }]);
        return;
      }

      startWorkoutSession([plan.workout]);
    }

    // Progress tracking (stored locally per user/browser)
    function getProgressData() {
      try {
        const raw = localStorage.getItem('gymgeeks_progress');
        return raw ? JSON.parse(raw) : {};
      } catch {
        return {};
      }
    }

    function saveProgressData(data) {
      try {
        localStorage.setItem('gymgeeks_progress', JSON.stringify(data));
      } catch {
        // ignore storage errors
      }
    }

    function recordWorkoutCompletion() {
      const today = new Date().toISOString().slice(0, 10);
      const data = getProgressData();
      data[today] = (data[today] || 0) + 1;
      saveProgressData(data);
    }

    function getLast7Days() {
      const labels = [];
      const values = [];
      const data = getProgressData();
      for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        const key = d.toISOString().slice(0, 10);
        labels.push(d.toLocaleDateString(undefined, { weekday: 'short' }));
        values.push(data[key] || 0);
      }
      return { labels, values };
    }

    function renderProgressChart() {
      const ctx = document.getElementById('progressChart');
      if (!ctx) return;

      const { labels, values } = getLast7Days();

      const chartData = {
        labels,
        datasets: [{
          label: 'Workouts completed',
          data: values,
          backgroundColor: '#0d6efd',
          borderRadius: 8,
          maxBarThickness: 40
        }]
      };

      if (progressChart) {
        progressChart.data = chartData;
        progressChart.update();
        return;
      }

      progressChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1 }
            }
          },
          plugins: {
            legend: { display: false }
          }
        }
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      const nextBtn = document.getElementById('nextBtn');
      const quitBtn = document.getElementById('quitBtn');
      const modalEl = document.getElementById('workoutModal');

      if (nextBtn) nextBtn.addEventListener('click', nextStep);
      if (quitBtn) quitBtn.addEventListener('click', quitWorkout);

      if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', () => {
          teardownWorkout();
        });
      }

      // BMI Chart (renders a quick gauge-style donut)
      const userBMI = <?= json_encode($bmi) ?>;
      const bmiLabel = <?= json_encode($bmiLabel) ?>;
      const bmiColor = <?= json_encode($bmiColor) ?>;

      if (userBMI > 0) {
        const ctx = document.getElementById('bmiChart');
        if (ctx) {
          const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: ['BMI', 'Remaining'],
              datasets: [{
                data: [userBMI, Math.max(0, 40 - userBMI)],
                backgroundColor: [
                  bmiColor === 'success' ? '#198754' : bmiColor === 'warning' ? '#ffc107' : '#dc3545',
                  '#e9ecef'
                ],
                borderWidth: 0
              }]
            },
            options: {
              cutout: '75%',
              plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
              }
            }
          });

          const chartWrapper = ctx.parentElement;
          if (chartWrapper) {
            chartWrapper.style.position = 'relative';
            const labelEl = document.createElement('div');
            labelEl.className = 'position-absolute top-50 start-50 translate-middle text-center';
            labelEl.style.pointerEvents = 'none';
            labelEl.innerHTML = `<div class="fw-bold">${userBMI.toFixed(1)}</div><div class="small text-${bmiColor}">${bmiLabel}</div>`;
            chartWrapper.appendChild(labelEl);
          }
        }
      }

      // Render progress chart (workouts completed this week)
      renderProgressChart();
    });
  </script>
</body>

</html>