<?php
session_start();
include "includes/db.php";

// Simple landing page for GYMgeekS
// Appointment form posts back to this page and shows a confirmation message.
$appointmentSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_submit'])) {
    $username = trim($_POST['username'] ?? ($_SESSION['user_name'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $goal = trim($_POST['goal'] ?? '');

    if ($username && $email && $date && $time) {
        // Save appointment to database
        $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, name, email, appointment_date, appointment_time, goal) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $userId, $username, $email, $date, $time, $goal);
        if ($stmt->execute()) {
            $appointmentSuccess = "Thanks, " . htmlspecialchars($username) . ". Your session request for " . htmlspecialchars($date) . " at " . htmlspecialchars($time) . " has been received!";
        } else {
            $appointmentSuccess = "We could not save your appointment right now. Please try again.";
        }
        $stmt->close();
    } else {
        $appointmentSuccess = "Please fill in all required fields to book your appointment.";
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GYMgeekS | Campus Fitness Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(4, 43, 103, 0.85), rgba(9, 15, 35, 0.7)),
                url('https://images.unsplash.com/photo-1554284126-aa88f22d8f2d?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            min-height: 85vh;
            min-height: calc(85vh + 50px);
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.12) 0%, rgba(0, 0, 0, 0) 60%);
            mix-blend-mode: screen;
            animation: shimmer 12s linear infinite;
            pointer-events: none;
        }

        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(45deg, rgba(255, 255, 255, 0.05) 0 2px, transparent 2px 20px);
            opacity: 0.55;
            pointer-events: none;
        }

        @keyframes shimmer {
            from {
                transform: translateX(-100%);
            }

            to {
                transform: translateX(100%);
            }
        }

        .hero h1 {
            text-shadow: 0 2px 14px rgba(0, 0, 0, 0.65);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: #0d6efd;
        }

        .gallery img {
            object-fit: cover;
            height: 220px;
            width: 100%;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="images/logo/logo.jpeg" alt="GYMgeekS logo"
                    style="height: 32px; width: auto; margin-right: 8px;">
                <span>GYMgeekS</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#benefits">Benefits</a></li>
                    <li class="nav-item"><a class="nav-link" href="#gallery">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-light btn-sm" href="members.php">Login</a>
                    </li>
                    <li class="nav-item ms-2 d-flex align-items-center">
                        <button type="button" class="theme-toggle btn btn-light btn-sm" aria-label="Toggle theme">
                            <i class="bi bi-moon-stars-fill"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero d-flex align-items-center" data-reveal>
        <div class="container text-white">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="display-4 fw-bold">Your Campus. <span
                            data-typewriter="Your Gym.|Your Goals.|Your Community."></span></h1>
                    <p class="lead">Train smarter with campus coaches, modern equipment, and a community that keeps you
                        motivated.</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal"
                            data-bs-target="#appointmentModal">Book a Free Session</button>
                        <a href="register.php" class="btn btn-outline-light btn-lg">Join Today</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section id="about" class="py-5" data-reveal>
            <div class="container">
                <div class="row gx-5 align-items-center">
                    <div class="col-lg-6">
                        <h2>What Makes GYMgeekS Unique?</h2>
                        <p class="lead">GYMgeekS is designed for students, staff, and faculty. Whether you’re focused on
                            strength, endurance, or recovery—our facility has you covered.</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>24/7 Access</strong> (with campus ID) so you can train on your
                                schedule.</li>
                            <li class="mb-2"><strong>Certified trainers</strong> who tailor workouts to your goals and
                                experience level.</li>
                            <li class="mb-2"><strong>Group classes</strong> including HIIT, yoga, cycling, and sports
                                conditioning.</li>
                            <li><strong>Healthy community</strong> with student clubs, challenges, and friendly
                                competition.</li>
                        </ul>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#appointmentModal">Book
                            an Appointment</button>
                    </div>
                    <div class="col-lg-6 mt-4 mt-lg-0">
                        <div class="ratio ratio-16x9 rounded shadow-sm overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=1200&q=80"
                                alt="Gym workout" class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="benefits" class="bg-light py-5" data-reveal>
            <div class="container">
                <h2 class="text-center mb-5">Why Work Out With Us?</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="feature-icon mb-3">
                                    <i class="bi bi-heart-pulse"></i>
                                </div>
                                <h5 class="card-title">Boost Your Health</h5>
                                <p class="card-text">Regular strength and cardio training improves mood, energy, focus,
                                    and overall wellness.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="feature-icon mb-3">
                                    <i class="bi bi-people"></i>
                                </div>
                                <h5 class="card-title">Build Community</h5>
                                <p class="card-text">Make friends in classes, join fitness challenges, and find workout
                                    partners who keep you accountable.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="feature-icon mb-3">
                                    <i class="bi bi-bar-chart-line"></i>
                                </div>
                                <h5 class="card-title">Track Progress</h5>
                                <p class="card-text">Set goals, track improvements, and celebrate milestones with our
                                    friendly coaching staff.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="trainers" class="py-5 bg-light" data-reveal>
            <div class="container">
                <h2 class="text-center mb-5">Meet Our Trainers</h2>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <img src="images/coach/b1.jpg" class="card-img-top" alt="Trainer 1">
                            <div class="card-body">
                                <h5 class="card-title">Alex Rivera</h5>
                                <p class="card-text mb-1"><strong>Certified Strength Coach</strong></p>
                                <p class="card-text text-muted small mb-2">M.S. Exercise Science • NASM CPT</p>
                                <p class="card-text">Specializes in functional strength training and hypertrophy.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <img src="images/coach/b2.jpg" class="card-img-top" alt="Trainer 2">
                            <div class="card-body">
                                <h5 class="card-title">Maya Chen</h5>
                                <p class="card-text mb-1"><strong>Certified Nutrition Coach</strong></p>
                                <p class="card-text text-muted small mb-2">B.S. Nutrition • Precision Nutrition Level 1
                                </p>
                                <p class="card-text">Helps athletes build sustainable meal plans for performance.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <img src="images/coach/g1.jpg" class="card-img-top" alt="Trainer 3">
                            <div class="card-body">
                                <h5 class="card-title">Jordan Hayes</h5>
                                <p class="card-text mb-1"><strong>Performance Specialist</strong></p>
                                <p class="card-text text-muted small mb-2">B.A. Kinesiology • ISSA Certified</p>
                                <p class="card-text">Focuses on mobility, speed training, and injury prevention.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 shadow-sm">
                            <img src="images/coach/g2.jpg" class="card-img-top"
                                alt="Athletic woman in a sleeveless maroon workout outfit standing with arms crossed in a modern gym, surrounded by exercise equipment and geometric wall designs, conveying confidence and focus">
                            <div class="card-body">
                                <h5 class="card-title">Sofia Patel</h5>
                                <p class="card-text mb-1"><strong>Yoga + Recovery Coach</strong></p>
                                <p class="card-text text-muted small mb-2">RYT-200 • Certified Pilates Instructor</p>
                                <p class="card-text">Leads mobility sessions and recovery routines for busy schedules.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="gallery" class="py-5" data-reveal>
            <div class="container">
                <h2 class="text-center mb-5">Our Space & Equipment</h2>
                <div class="row g-3 gallery">
                    <div class="col-md-4">
                        <img src="images/instrument/ins3.jpg" alt="Weight machines" class="rounded shadow-sm">
                    </div>
                    <div class="col-md-4">
                        <img src="images/instrument/ins2.jpg" alt="Cardio equipment" class="rounded shadow-sm">
                    </div>
                    <div class="col-md-4">
                        <img src="images/instrument/ins1.jpg" alt="Group workout" class="rounded shadow-sm">
                    </div>
                </div>
            </div>
        </section>

        <section id="hours" class="py-5 bg-light" data-reveal>
            <div class="container">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-6">
                        <div class="card shadow-sm p-4 h-100">
                            <h3 class="mb-3">Gym Hours</h3>
                            <p class="lead mb-2">We’re open every day to fit your busy schedule.</p>
                            <ul class="list-unstyled">
                                <li><strong>Opens:</strong> 8:00 AM</li>
                                <li><strong>Closes:</strong> 8:00 PM</li>
                                <li class="mt-3">Plan your visit — the gym stays lively throughout the day.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card shadow-sm p-4 h-100">
                            <h3 class="mb-3">Crowded Times</h3>
                            <p class="lead mb-3">Want a quieter workout? Avoid the busiest hours.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-danger">10:00 AM – 11:00 AM</span>
                                <span class="badge bg-danger">4:00 PM – 7:00 PM</span>
                            </div>
                            <p class="mt-3 text-muted">Tip: Train early or later for more space and faster machine
                                access.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="bg-dark text-white py-5" data-reveal>
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2>Ready to start?</h2>
                        <p class="lead">Book your free welcome session and get a personalized plan from our fitness
                            staff.</p>
                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal"
                            data-bs-target="#appointmentModal">Book Now</button>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <li><strong>Location:</strong> Campus Recreation Center, Main Campus</li>
                            <li><strong>Hours:</strong> 8:00AM – 8:00PM daily</li>
                            <li><strong>Phone:</strong> (555) 123-4567</li>
                            <li><strong>Email:</strong> <a href="mailto:fit@gymgeeks.edu"
                                    class="text-white">fit@gymgeeks.edu</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="py-4 bg-black text-white">
        <div class="container text-center">
            <p class="mb-1">© <?= date('Y'); ?> GYMgeekS. All rights reserved.</p>
            <small>Designed to support student wellness and active campus life.</small>
        </div>
    </footer>

    <!-- Appointment Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalLabel">Book an Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="#" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <?php if ($appointmentSuccess): ?>
                            <div class="alert <?= strpos($appointmentSuccess, 'Thanks') === 0 ? 'alert-success' : 'alert-warning'; ?>"
                                role="alert">
                                <?= htmlspecialchars($appointmentSuccess); ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required
                                value="<?= htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
                            <div class="invalid-feedback">Please enter your username.</div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                value="<?= htmlspecialchars($_SESSION['user_email'] ?? ''); ?>">
                            <div class="invalid-feedback">Please enter a valid email.</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="date" class="form-label">Preferred Date</label>
                                <input type="date" class="form-control" id="date" name="date" required>
                                <div class="invalid-feedback">Please pick a date.</div>
                            </div>
                            <div class="col-sm-6">
                                <label for="time" class="form-label">Preferred Time</label>
                                <input type="time" class="form-control" id="time" name="time" required>
                                <div class="invalid-feedback">Please pick a time.</div>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label for="goal" class="form-label">What are your goals?</label>
                            <textarea class="form-control" id="goal" name="goal" rows="3"
                                placeholder="e.g., build strength, lose weight, improve athletic performance"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="appointment_submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/site.js"></script>
    <script>
        // Bootstrap form validation
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })();

        // If the appointment was successful, reopen modal to show the message
        <?php if ($appointmentSuccess): ?>
            const appointmentModal = new bootstrap.Modal(document.getElementById('appointmentModal'));
            appointmentModal.show();
            if (window.gymgeeks) {
                gymgeeks.showToast('Appointment request saved! Expect an email from our team soon.', 'success');
            }
        <?php endif; ?>
    </script>
</body>

</html>