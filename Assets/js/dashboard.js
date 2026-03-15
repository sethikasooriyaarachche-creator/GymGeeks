let bmiChartInstance;

// Show BMI chart
function showBMIChart(bmi) {
  if (bmiChartInstance) bmiChartInstance.destroy();
  const ctx = document.getElementById("bmiChart").getContext("2d");
  bmiChartInstance = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Your BMI"],
      datasets: [{
        label: "BMI Value",
        data: [bmi],
        backgroundColor: bmi < 18.5 ? "blue" : bmi < 25 ? "green" : "red"
      }]
    },
    options: {
      scales: {
        y: { beginAtZero: true }
      }
    }
  });
}

// Generate workout + meal plans based on BMI
function generatePlans(bmi) {
  const workoutList = document.getElementById("workoutList");
  const mealList = document.getElementById("mealList");
  workoutList.innerHTML = "";
  mealList.innerHTML = "";

  let workouts, meals;

  if (bmi < 18.5) {
    workouts = ["Push-ups", "Squats", "Bench Press"];
    meals = ["High-protein breakfast", "Chicken & rice", "Nuts & milk"];
  } else if (bmi < 25) {
    workouts = ["Jogging", "Plank", "Pull-ups"];
    meals = ["Balanced salad", "Grilled fish", "Fruits"];
  } else {
    workouts = ["Burpees", "Jumping Jacks", "Mountain Climbers"];
    meals = ["Low-carb veggies", "Lean chicken", "Green tea"];
  }

  workouts.forEach(w => {
    let li = document.createElement("li");
    li.textContent = w;
    workoutList.appendChild(li);
  });

  meals.forEach(m => {
    let li = document.createElement("li");
    li.textContent = m;
    mealList.appendChild(li);
  });
}





function addWorkoutItem(text) {
  const item = document.createElement("div");
  item.className = "list-group-item d-flex justify-content-between align-items-center";
  item.innerHTML = `${text} <span class="badge bg-primary rounded-pill">Workout</span>`;
  document.getElementById("workoutList").appendChild(item);
}

function addMealItem(text) {
  const item = document.createElement("div");
  item.className = "list-group-item d-flex justify-content-between align-items-center";
  item.innerHTML = `${text} <span class="badge bg-success rounded-pill">Meal</span>`;
  document.getElementById("mealList").appendChild(item);
}

// Expose globally so HTML button works
window.startWorkout = startWorkout;