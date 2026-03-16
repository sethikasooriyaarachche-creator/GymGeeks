-- Schema for UniGym application (all queries in one file)
-- This file creates the database schema used by the PHP pages in the app.
-- Run this file once to initialize the database (e.g. via mysql < schema.sql).

-- WARNING: The statements below will drop existing database/tables and replace them.
DROP DATABASE IF EXISTS uni_gym;
CREATE DATABASE uni_gym;
USE uni_gym;

-- Admins table (used for admin login and dashboard management)
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users table (used for user login and storing measurements)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  weight FLOAT DEFAULT NULL,
  height FLOAT DEFAULT NULL,
  chest FLOAT DEFAULT NULL,
  waist FLOAT DEFAULT NULL,
  arms FLOAT DEFAULT NULL,
  legs FLOAT DEFAULT NULL,
  bmi FLOAT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Workout templates (used to generate workout plans for users)
CREATE TABLE IF NOT EXISTS workout_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(50) NOT NULL,
  sets INT DEFAULT NULL,
  reps INT DEFAULT NULL,
  rest INT DEFAULT NULL,
  bmi_min DECIMAL(5,2) DEFAULT NULL,
  bmi_max DECIMAL(5,2) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Meal templates (used to show meal suggestions to users)
CREATE TABLE IF NOT EXISTS meal_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(50) NOT NULL,
  bmi_min DECIMAL(5,2) DEFAULT NULL,
  bmi_max DECIMAL(5,2) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Appointments (booked via the landing page)
CREATE TABLE IF NOT EXISTS appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL,
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  goal TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Example seed data (optional):
-- Note: Any password stored here must be a password_hash() output.
--       Use PHP to generate hashes, e.g. password_hash('yourpass', PASSWORD_DEFAULT).

INSERT INTO admins (username, email, password) VALUES
  ('admin', 'admin@unigym.local', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');

INSERT INTO workout_templates (title, description, sets, reps, rest, category, bmi_min, bmi_max) VALUES
  ('Push-Ups','Classic bodyweight chest and triceps exercise.',3,12,60,'Strength',18.50,29.99),
  ('Pull-Ups','Upper body back and biceps exercise.',3,8,90,'Strength',20.00,29.99),
  ('Squats','Lower body strength for quads and glutes.',4,15,90,'Strength',18.50,35.00),
  ('Bench Press','Chest and triceps barbell exercise.',4,10,120,'Strength',20.00,29.99),
  ('Deadlift','Full-body compound lift.',4,6,150,'Strength',20.00,29.99),
  ('Jump Rope','High-intensity cardio skipping.',5,60,30,'Cardio',18.50,24.99),
  ('Burpees','Explosive full-body cardio.',3,15,60,'Cardio',18.50,29.99),
  ('Mountain Climbers','Core and cardio exercise.',4,30,45,'Cardio',18.50,29.99),
  ('Running','Steady-state cardio.',1,20,0,'Cardio',18.50,35.00),
  ('Cycling','Low-impact endurance cardio.',1,30,0,'Cardio',18.50,35.00),
  ('Plank','Isometric core stability.',3,60,45,'Core',18.50,35.00),
  ('Russian Twists','Rotational core with weight.',3,20,60,'Core',18.50,29.99),
  ('Leg Raises','Lower abdominal exercise.',3,15,60,'Core',18.50,29.99),
  ('Bicycle Crunches','Dynamic core for obliques.',3,20,60,'Core',18.50,29.99),
  ('Side Plank','Oblique stability exercise.',3,45,45,'Core',18.50,35.00),
  ('Yoga Sun Salutation','Dynamic yoga flow.',3,10,30,'Flexibility',18.50,35.00),
  ('Dynamic Stretching','Warm-up mobility routine.',1,10,0,'Flexibility',18.50,35.00),
  ('Foam Rolling','Self-myofascial release.',1,10,0,'Flexibility',18.50,35.00),
  ('Pilates Roll-Up','Core + flexibility.',3,12,45,'Flexibility',18.50,29.99),
  ('Cat-Cow Stretch','Spinal mobility yoga pose.',3,10,30,'Flexibility',18.50,35.00);

INSERT INTO meal_templates (title, description, category, bmi_min, bmi_max) VALUES
  ('Oatmeal with Berries','High-fiber oats topped with berries.','Breakfast',18.50,24.99),
  ('Greek Yogurt Parfait','Protein-rich yogurt with granola and fruit.','Breakfast',18.50,29.99),
  ('Egg White Omelette','Low-calorie omelette with spinach.','Breakfast',25.00,35.00),
  ('Avocado Toast','Whole grain bread with avocado.','Breakfast',18.50,29.99),
  ('Smoothie Bowl','Blended fruits with chia seeds.','Breakfast',18.50,24.99),
  ('Grilled Chicken Salad','Lean chicken with greens.','Lunch',18.50,29.99),
  ('Quinoa and Veggie Bowl','Protein-packed quinoa with veggies.','Lunch',18.50,24.99),
  ('Salmon with Brown Rice','Omega-3 salmon with rice.','Lunch',25.00,29.99),
  ('Turkey Wrap','Whole wheat wrap with turkey.','Lunch',18.50,29.99),
  ('Lentil Soup','Hearty lentil and vegetable soup.','Lunch',18.50,35.00),
  ('Grilled Fish with Veggies','Light fish dinner with vegetables.','Dinner',18.50,24.99),
  ('Chicken Stir-Fry','Chicken with colorful vegetables.','Dinner',25.00,29.99),
  ('Tofu Curry','Plant-based curry with tofu.','Dinner',18.50,29.99),
  ('Beef and Broccoli','Lean beef sautéed with broccoli.','Dinner',25.00,29.99),
  ('Vegetable Pasta','Whole grain pasta with tomato sauce.','Dinner',18.50,35.00),
  ('Mixed Nuts','Healthy fats and protein.','Snack',18.50,29.99),
  ('Fruit Salad','Seasonal fruits with lime.','Snack',18.50,24.99),
  ('Protein Shake','Whey protein blended with milk.','Snack',25.00,35.00),
  ('Rice Cakes with Peanut Butter','Light snack with carbs and fats.','Snack',18.50,29.99),
  ('Hummus with Veggies','Chickpea dip with carrot sticks.','Snack',18.50,35.00);

-- Underweight (BMI < 18.5) workout and meal plan additions
-- These templates are meant for users classified as underweight.
-- They will be selected when BMI is below 18.5.

INSERT INTO workout_templates (title, description, sets, reps, rest, category, bmi_min, bmi_max) VALUES
  ('Bodyweight Squats','Build lower-body strength with controlled reps.',3,12,60,'Strength',NULL,18.49),
  ('Resistance Band Rows','Upper back strengthening using bands.',3,15,60,'Strength',NULL,18.49),
  ('Glute Bridges','Glute and posterior chain activation.',3,15,60,'Strength',NULL,18.49),
  ('Farmer\'s Carry','Grip and core strength with loaded carries.',3,40,60,'Strength',NULL,18.49);

INSERT INTO meal_templates (title, description, category, bmi_min, bmi_max) VALUES
  ('Peanut Butter Banana Smoothie','Calorie-dense smoothie with nut butter and oats.','Breakfast',NULL,18.49),
  ('Protein Pancakes','High-protein pancakes topped with fruit.','Breakfast',NULL,18.49),
  ('Avocado Chicken Wrap','Calories and protein in a convenient wrap.','Lunch',NULL,18.49),
  ('Trail Mix','Nuts, seeds, and dried fruit for healthy calories.','Snack',NULL,18.49),
  ('Greek Yogurt with Granola','Creamy yogurt with crunchy granola and honey.','Snack',NULL,18.49);

-- Quick reference query (run to inspect underweight plans):
-- SELECT * FROM workout_templates WHERE bmi_max < 18.5;
-- SELECT * FROM meal_templates WHERE bmi_max < 18.5;
