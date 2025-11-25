<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grading Management System - Modern Academic Administration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <style>
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 30px rgba(59,130,246,0.5); }
            50% { box-shadow: 0 0 50px rgba(139,92,246,0.7); }
        }
        
        .landing-bg { 
            background: linear-gradient(-45deg, #0f172a, #1e293b, #334155, #475569);
            background-size: 400% 400%;
            animation: gradient 20s ease infinite;
            position: relative;
            overflow-x: hidden;
        }
        
        .landing-bg::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('<?= site_url('public/background.gif') ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            opacity: 0.25;
            z-index: 0;
        }
        
        .landing-bg::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(59,130,246,0.15) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(139,92,246,0.15) 0%, transparent 50%),
                        radial-gradient(circle at 50% 50%, rgba(236,72,153,0.08) 0%, transparent 70%);
            z-index: 0;
            pointer-events: none;
        }
        
        .card { 
            background: rgba(15,23,42,0.7); 
            border: 1px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(20px);
            transition: all 0.4s ease;
        }
        
        .card:hover {
            background: rgba(30,41,59,0.85);
            border-color: rgba(99,102,241,0.6);
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(99,102,241,0.25);
        }
        
        .nav-bg {
            background: rgba(15,23,42,0.85);
            backdrop-filter: blur(25px);
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(99,102,241,0.2);
        }
        
        .nav-scrolled {
            background: rgba(15,23,42,0.97);
            box-shadow: 0 10px 40px rgba(99,102,241,0.2);
            border-bottom: 1px solid rgba(99,102,241,0.3);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%);
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(99,102,241,0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(139,92,246,0.5);
            filter: brightness(1.1);
        }
        
        .btn-secondary {
            background: rgba(255,255,255,0.12);
            border: 2px solid rgba(255,255,255,0.25);
            transition: all 0.4s ease;
        }
        
        .btn-secondary:hover {
            background: rgba(255,255,255,0.25);
            border-color: rgba(255,255,255,0.4);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255,255,255,0.15);
        }
        
        .feature-icon {
            transition: all 0.3s ease;
        }
        
        .feature-icon:hover {
            transform: scale(1.2) rotate(5deg);
        }
        
        .stat-card {
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }
        
        .stat-card:hover::before {
            left: 100%;
        }
        
        .hero-text {
            text-shadow: 0 0 50px rgba(99,102,241,0.5), 0 0 80px rgba(139,92,246,0.3);
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        * {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="min-h-screen text-white landing-bg">
    <!-- Navigation -->
    <nav class="w-full nav-bg shadow-sm border-b border-white/10 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="text-xl font-bold tracking-wide flex items-center">
                    <i class="fas fa-graduation-cap mr-2" style="color: #6366f1;"></i>
                    <span class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">Grading Management System</span>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="<?= site_url('auth/login') ?>" 
                       class="px-6 py-2.5 rounded-lg font-semibold transition-all duration-300" style="background: rgba(99,102,241,0.15); border: 2px solid rgba(99,102,241,0.4); color: #a5b4fc;">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    <a href="<?= site_url('auth/register') ?>" 
                       class="px-6 py-2.5 rounded-lg font-semibold transition-all duration-300" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #ffffff; box-shadow: 0 4px 15px rgba(99,102,241,0.3);">
                        <i class="fas fa-user-plus mr-2"></i>Sign Up
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-32 relative">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center" data-aos="fade-up">
                <div class="inline-block mb-6 px-6 py-3 rounded-full text-sm font-bold" style="background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.2)); border: 2px solid rgba(167,139,250,0.4); color: #c4b5fd; box-shadow: 0 0 30px rgba(139,92,246,0.3);">
                    <i class="fas fa-star mr-2" style="color: #fbbf24;"></i>Modern Academic Management Platform
                </div>
                <h1 class="text-6xl md:text-7xl font-extrabold mb-6 hero-text">
                    <span class="bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                        Transform Your
                    </span>
                    <br>
                    <span class="bg-gradient-to-r from-pink-400 via-purple-400 to-blue-400 bg-clip-text text-transparent">
                        Grading Experience
                    </span>
                </h1>
                <p class="text-xl md:text-2xl text-gray-300 mb-12 max-w-3xl mx-auto leading-relaxed" data-aos="fade-up" data-aos-delay="100">
                    Streamline academic administration with our powerful, intuitive platform. 
                    <span class="text-blue-400 font-semibold">Track performance</span>, 
                    <span class="text-purple-400 font-semibold">manage grades</span>, and 
                    <span class="text-pink-400 font-semibold">empower learning</span>.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="fade-up" data-aos-delay="200">
                    <a href="<?= site_url('auth/register') ?>" 
                       class="px-10 py-4 rounded-xl btn-primary text-white text-lg font-bold shadow-2xl">
                        <i class="fas fa-rocket mr-2"></i>Get Started Free
                    </a>
                    <a href="#features" 
                       class="px-10 py-4 rounded-xl btn-secondary text-white text-lg font-bold">
                        <i class="fas fa-play-circle mr-2"></i>Discover Features
                    </a>
                </div>
                
                <!-- Floating Icons -->
                <div class="mt-20 grid grid-cols-3 gap-8 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="300">
                    <div class="floating" style="animation-delay: 0s;">
                        <div class="card rounded-2xl p-6 text-center">
                            <i class="fas fa-shield-alt text-4xl text-green-400 mb-2"></i>
                            <div class="text-sm text-gray-300">Secure & Reliable</div>
                        </div>
                    </div>
                    <div class="floating" style="animation-delay: 0.5s;">
                        <div class="card rounded-2xl p-6 text-center">
                            <i class="fas fa-bolt text-4xl text-yellow-400 mb-2"></i>
                            <div class="text-sm text-gray-300">Lightning Fast</div>
                        </div>
                    </div>
                    <div class="floating" style="animation-delay: 1s;">
                        <div class="card rounded-2xl p-6 text-center">
                            <i class="fas fa-mobile-alt text-4xl text-blue-400 mb-2"></i>
                            <div class="text-sm text-gray-300">Mobile Friendly</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20" style="background: linear-gradient(180deg, transparent 0%, rgba(99,102,241,0.08) 50%, transparent 100%);">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12" data-aos="fade-up">
                <h2 class="text-4xl font-bold mb-4 text-white">Trusted by Educators Nationwide</h2>
                <p class="text-gray-300 text-lg">Join thousands of schools using our platform</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="stat-card card rounded-2xl p-8 text-center group" data-aos="fade-up" data-aos-delay="0">
                    <div class="text-6xl mb-4 feature-icon">
                        <i class="fas fa-user-graduate text-blue-400"></i>
                    </div>
                    <div class="text-5xl font-extrabold mb-3 bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                        <?= $stats['total_students'] ?? 0 ?>+
                    </div>
                    <div class="text-gray-300 text-lg font-semibold">Active Students</div>
                    <div class="text-gray-500 text-sm mt-2">Enrolled and learning</div>
                </div>
                <div class="stat-card card rounded-2xl p-8 text-center group" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-6xl mb-4 feature-icon">
                        <i class="fas fa-chalkboard-teacher text-green-400"></i>
                    </div>
                    <div class="text-5xl font-extrabold mb-3 bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">
                        <?= $stats['total_teachers'] ?? 0 ?>+
                    </div>
                    <div class="text-gray-300 text-lg font-semibold">Expert Teachers</div>
                    <div class="text-gray-500 text-sm mt-2">Dedicated educators</div>
                </div>
                <div class="stat-card card rounded-2xl p-8 text-center group" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-6xl mb-4 feature-icon">
                        <i class="fas fa-book-open text-purple-400"></i>
                    </div>
                    <div class="text-5xl font-extrabold mb-3 bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                        <?= $stats['total_subjects'] ?? 0 ?>+
                    </div>
                    <div class="text-gray-300 text-lg font-semibold">Active Subjects</div>
                    <div class="text-gray-500 text-sm mt-2">Diverse curriculum</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16" data-aos="fade-up">
                <div class="inline-block mb-4 px-6 py-3 rounded-full text-sm font-bold" style="background: linear-gradient(135deg, rgba(139,92,246,0.2), rgba(236,72,153,0.2)); border: 2px solid rgba(192,132,252,0.4); color: #e9d5ff; box-shadow: 0 0 30px rgba(139,92,246,0.3);">
                    <i class="fas fa-rocket mr-2" style="color: #f472b6;"></i>Powerful Features
                </div>
                <h2 class="text-5xl font-bold mb-6 bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                    Everything You Need
                </h2>
                <p class="text-gray-300 text-xl max-w-2xl mx-auto">
                    Comprehensive tools designed to simplify academic management and enhance learning outcomes
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="card rounded-2xl p-8 group" data-aos="fade-up" data-aos-delay="0">
                    <div class="text-5xl mb-6 feature-icon">
                        <i class="fas fa-chart-line text-blue-400"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-white">Grade Tracking</h3>
                    <p class="text-gray-400 leading-relaxed">Real-time performance monitoring with detailed analytics, grade breakdowns, and trend visualization across all subjects and semesters.</p>
                    <div class="mt-6 flex items-center text-blue-400 text-sm font-semibold">
                        <span>Learn more</span>
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                    </div>
                </div>
                <div class="card rounded-2xl p-8 group" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-5xl mb-6 feature-icon">
                        <i class="fas fa-users-cog text-green-400"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-white">Student Management</h3>
                    <p class="text-gray-400 leading-relaxed">Centralized student records with enrollment tracking, section assignment, and comprehensive academic history at your fingertips.</p>
                    <div class="mt-6 flex items-center text-green-400 text-sm font-semibold">
                        <span>Learn more</span>
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                    </div>
                </div>
                <div class="card rounded-2xl p-8 group" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-5xl mb-6 feature-icon">
                        <i class="fas fa-clipboard-check text-purple-400"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-white">Grade Review System</h3>
                    <p class="text-gray-400 leading-relaxed">Multi-level approval workflow ensuring accuracy with draft, submission, and review stages for quality assurance.</p>
                    <div class="mt-6 flex items-center text-purple-400 text-sm font-semibold">
                        <span>Learn more</span>
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                    </div>
                </div>
                <div class="card rounded-2xl p-8 group" data-aos="fade-up" data-aos-delay="300">
                    <div class="text-5xl mb-6 feature-icon">
                        <i class="fas fa-file-excel text-yellow-400"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-white">Excel Import/Export</h3>
                    <p class="text-gray-400 leading-relaxed">Seamless bulk grade uploads and downloads with Excel templates, saving hours of manual data entry work.</p>
                    <div class="mt-6 flex items-center text-yellow-400 text-sm font-semibold">
                        <span>Learn more</span>
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                    </div>
                </div>
                <div class="card rounded-2xl p-8 group" data-aos="fade-up" data-aos-delay="400">
                    <div class="text-5xl mb-6 feature-icon">
                        <i class="fas fa-bullhorn text-red-400"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-white">Smart Announcements</h3>
                    <p class="text-gray-400 leading-relaxed">Role-based communication system to keep students, teachers, and admins informed with targeted messaging.</p>
                    <div class="mt-6 flex items-center text-red-400 text-sm font-semibold">
                        <span>Learn more</span>
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                    </div>
                </div>
                <div class="card rounded-2xl p-8 group" data-aos="fade-up" data-aos-delay="500">
                    <div class="text-5xl mb-6 feature-icon">
                        <i class="fas fa-shield-alt text-cyan-400"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-white">Audit & Security</h3>
                    <p class="text-gray-400 leading-relaxed">Comprehensive activity logging with role-based access control ensuring data integrity and accountability.</p>
                    <div class="mt-6 flex items-center text-cyan-400 text-sm font-semibold">
                        <span>Learn more</span>
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-2 transition-transform"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcements Section -->
    <?php if (!empty($announcements)): ?>
    <section class="py-20" style="background: linear-gradient(180deg, transparent 0%, rgba(30,41,59,0.5) 50%, transparent 100%);">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold mb-4">Latest Announcements</h2>
                <p class="text-gray-300 text-lg">Stay updated with the latest news and information</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($announcements as $announcement): ?>
                <div class="card rounded-xl p-6 hover:bg-white/5 transition">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-xl font-semibold text-white flex-1">
                            <?= htmlspecialchars($announcement['title']) ?>
                        </h3>
                        <i class="fas fa-bullhorn text-blue-400"></i>
                    </div>
                    <p class="text-gray-400 mb-4 line-clamp-3">
                        <?= htmlspecialchars(substr($announcement['content'], 0, 150)) ?>...
                    </p>
                    <div class="flex items-center justify-between text-sm text-gray-500">
                        <span>
                            <i class="fas fa-user mr-1"></i>
                            <?= htmlspecialchars(($announcement['first_name'] ?? 'Admin') . ' ' . ($announcement['last_name'] ?? '')) ?>
                        </span>
                        <span>
                            <i class="fas fa-calendar mr-1"></i>
                            <?= date('M d, Y', strtotime($announcement['created_at'])) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- About Section -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4">
            <div class="card rounded-xl p-12 text-center">
                <h2 class="text-4xl font-bold mb-6">About Our System</h2>
                <p class="text-gray-300 text-lg mb-8 max-w-3xl mx-auto">
                    Our Grading Management System is designed to streamline academic administration and provide 
                    educators with powerful tools to track student performance. With role-based access for administrators, 
                    teachers, and students, everyone has the right information at their fingertips.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                    <div>
                        <div class="text-5xl font-bold text-blue-400 mb-2">1</div>
                        <h3 class="text-xl font-semibold mb-2">Register Account</h3>
                        <p class="text-gray-400">Sign up as a student and wait for admin approval</p>
                    </div>
                    <div>
                        <div class="text-5xl font-bold text-green-400 mb-2">2</div>
                        <h3 class="text-xl font-semibold mb-2">Get Approved</h3>
                        <p class="text-gray-400">Admin reviews and approves your account</p>
                    </div>
                    <div>
                        <div class="text-5xl font-bold text-purple-400 mb-2">3</div>
                        <h3 class="text-xl font-semibold mb-2">Access Dashboard</h3>
                        <p class="text-gray-400">Login and access your personalized dashboard</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12" style="border-top: 2px solid rgba(99,102,241,0.2); background: rgba(15,23,42,0.5);">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-graduation-cap mr-2" style="color: #818cf8;"></i>
                        <span class="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">Grading System</span>
                    </h3>
                    <p class="text-gray-400">
                        A comprehensive platform for academic management and performance tracking.
                    </p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="<?= site_url('auth/login') ?>" class="text-gray-400 hover:text-blue-400 transition">Login</a></li>
                        <li><a href="<?= site_url('auth/register') ?>" class="text-gray-400 hover:text-blue-400 transition">Register</a></li>
                        <li><a href="#features" class="text-gray-400 hover:text-blue-400 transition">Features</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-envelope mr-2"></i>gradingsystem@gmail.com</li>
                        <li><i class="fas fa-phone mr-2"></i>+63 962 7875 334</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i>Bayanan 1, Calapan City, Oriental Mindoro</li>
                    </ul>
                </div>
            </div>
            <div class="text-center text-gray-400 pt-8 border-t border-white/10">
                © <?= date('Y') ?> Grading Management System. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- AOS Animation Library -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
        
        // Navbar scroll effect
        let lastScroll = 0;
        const navbar = document.querySelector('nav');
        
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                navbar.classList.add('nav-scrolled');
            } else {
                navbar.classList.remove('nav-scrolled');
            }
            
            lastScroll = currentScroll;
        });
        
        // Counter animation for stats
        const animateCounter = (element, target) => {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target + '+';
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current) + '+';
                }
            }, 30);
        };
        
        // Trigger counter animation when stats section is visible
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.text-5xl');
                    counters.forEach((counter, index) => {
                        const target = parseInt(counter.textContent);
                        if (!isNaN(target)) {
                            setTimeout(() => animateCounter(counter, target), index * 200);
                        }
                    });
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        const statsSection = document.querySelector('.stat-card').closest('section');
        if (statsSection) {
            statsObserver.observe(statsSection);
        }
    </script>
</body>
</html>
