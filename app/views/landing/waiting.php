<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Grading Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-bg {
            background: linear-gradient(135deg, #FFFCFB 0%, #f5f9fb 50%, #E8F9FF 100%);
            min-height: 100vh;
        }
        .card {
            background: #FFFFFF;
            border: 1px solid rgba(9, 63, 180, 0.1);
            box-shadow: 0 2px 8px rgba(9, 63, 180, 0.08);
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .animate-pulse-slow {
            animation: pulse-slow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="admin-bg flex items-center justify-center min-h-screen">
    
    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Logo -->
        <div class="text-center mb-8">
            <i class="fas fa-graduation-cap text-6xl mb-4" style="color: #093FB4;"></i>
            <h1 class="text-3xl font-bold" style="color: #093FB4;">Grading Management System</h1>
        </div>

        <!-- Main Card -->
        <div class="card p-8 rounded-lg shadow-2xl text-center">
            <!-- Animated Icon -->
            <div class="mb-6">
                <i class="fas fa-clock text-6xl text-yellow-400 animate-pulse-slow"></i>
            </div>

            <!-- Title -->
            <h2 class="text-3xl font-bold mb-4" style="color: #093FB4;">Registration Pending Approval</h2>
            
            <!-- Message -->
            <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-6 mb-6">
                <p class="text-lg mb-4" style="color: #1e293b;">
                    Hello, <span class="font-bold" style="color: #093FB4;"><?= $user_name ?></span>!
                </p>
                <p class="mb-4" style="color: #1e293b;">
                    Thank you for registering with our Grading Management System.
                </p>
                <p style="color: #1e293b;">
                    Your account is currently <span class="font-bold" style="color: #f59e0b;">pending approval</span> by the system administrator.
                </p>
            </div>

            <!-- Info Box -->
            <div class="card p-6 rounded-lg mb-6 text-left">
                <h3 class="font-bold text-xl mb-4 text-center" style="color: #093FB4;">
                    <i class="fas fa-info-circle mr-2" style="color: #3b82f6;"></i>What happens next?
                </h3>
                <ul class="space-y-3" style="color: #1e293b;">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mr-3 mt-1" style="color: #10b981;"></i>
                        <span>Our administrator will review your registration details</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-envelope mr-3 mt-1" style="color: #3b82f6;"></i>
                        <span>You will receive an email notification once your account is approved</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-sign-in-alt mr-3 mt-1" style="color: #8b5cf6;"></i>
                        <span>After approval, you can login and access your student dashboard</span>
                    </li>
                </ul>
            </div>

            <!-- Expected Time -->
            <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 mb-6">
                <p class="text-sm" style="color: #1e293b;">
                    <i class="fas fa-hourglass-half mr-2" style="color: #3b82f6;"></i>
                    Approval typically takes <span class="font-bold" style="color: #093FB4;">24-48 hours</span>
                </p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?= site_url('landing') ?>" class="px-6 py-3 rounded-lg transition" style="background: #FFFFFF; border: 1px solid rgba(9, 63, 180, 0.2); color: #093FB4;">
                    <i class="fas fa-home mr-2"></i>Back to Home
                </a>
                <a href="<?= site_url('auth/logout') ?>" class="px-6 py-3 rounded-lg transition" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444;">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>

            <!-- Contact Info -->
            <div class="mt-8 pt-6" style="border-top: 1px solid rgba(9, 63, 180, 0.1);">
                <p class="text-sm" style="color: #64748b;">
                    <i class="fas fa-question-circle mr-2" style="color: #3b82f6;"></i>
                    Have questions? Contact us at <a href="mailto:gradingsystem@gmail.com" class="hover:underline" style="color: #093FB4;">gradingsystem@gmail.com</a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-sm" style="color: #64748b;">
            <p>&copy; <?= date('Y') ?> Grading Management System. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
