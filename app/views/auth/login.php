<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <style>
        body {
            font-family: "Inter", sans-serif;
            background: linear-gradient(135deg, #FFFCFB 0%, #f5f9fb 50%, #E8F9FF 100%);
            position: relative;
        }

        .glassmorphism {
            background: #FFFFFF;
            border: 1px solid rgba(9, 63, 180, 0.1);
            box-shadow: 0 2px 8px rgba(9, 63, 180, 0.08);
        }

        .form-input {
            background: #FFFFFF;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            color: #1e293b;
        }

        .form-input:focus {
            background: #FFFFFF;
            border-color: #093FB4;
            box-shadow: 0 0 0 3px rgba(9, 63, 180, 0.1);
            transform: translateY(-2px);
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .form-label {
            transition: all 0.3s ease;
            color: #64748b;
        }

        .form-input:focus + .form-label,
        .form-input:not(:placeholder-shown) + .form-label,
        .form-input.has-value + .form-label {
            transform: translateY(-28px) scale(0.85);
            color: #093FB4;
            font-weight: 600;
            background: #FFFFFF;
            padding: 0 8px;
            border-radius: 4px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #093FB4, #3b82f6);
            transition: all 0.25s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 20px rgba(9, 63, 180, 0.3);
        }

        .submit-btn:active {
            transform: scale(0.98);
        }

        .fade-in {
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(30px);
            }
            to { 
                opacity: 1; 
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from { 
                opacity: 0; 
                transform: translateX(-50px);
            }
            to { 
                opacity: 1; 
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from { 
                opacity: 0; 
                transform: translateX(50px);
            }
            to { 
                opacity: 1; 
                transform: translateX(0);
            }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .slide-in-left {
            animation: slideInLeft 0.6s ease;
        }

        .slide-in-right {
            animation: slideInRight 0.6s ease;
        }

        .icon-bounce {
            animation: bounce 2s infinite;
        }

        .form-input {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-input:focus {
            transform: translateY(-3px);
        }

        .submit-btn {
            position: relative;
            overflow: hidden;
        }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .submit-btn .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .submit-btn.loading .spinner {
            display: inline-block;
        }

        .submit-btn.loading .btn-text {
            opacity: 0;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            transform: scale(0);
            animation: ripple-effect 0.6s ease-out;
            pointer-events: none;
        }

        @keyframes ripple-effect {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #dc2626;
        }

        .success-message {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #16a34a;
        }

        .icon-container {
            background: linear-gradient(135deg, #093FB4, #3b82f6);
            box-shadow: 0 8px 25px rgba(9, 63, 180, 0.3);
        }

        /* Toastr Custom Styling */
        .toast-success { background-color: #10b981 !important; }
        .toast-error { background-color: #ef4444 !important; }
        .toast-info { background-color: #3b82f6 !important; }
        .toast-warning { background-color: #f59e0b !important; }
    </style>
</head>
<body class="min-h-screen">
    <!-- Back Button -->
    <div class="fixed top-6 left-6 z-50">
        <a href="<?= site_url('/') ?>" 
           class="flex items-center px-4 py-2 rounded-lg transition" style="background: #FFFFFF; border: 1px solid rgba(9, 63, 180, 0.2); color: #093FB4;">
            <i class="fas fa-arrow-left mr-2"></i>
            <span>Back to Home</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full shadow-lg z-40" style="background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);">
        <div class="max-w-6xl mx-auto px-6 py-3 flex justify-between items-center">
            <span class="text-xl font-bold tracking-wide" style="color: #093FB4;">🎓 Grading Management System</span>
            <div class="text-sm" style="color: #64748b;">Login Portal</div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen flex items-center justify-center pt-20 pb-12 px-4">
        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-8 slide-in-left">
                <div class="icon-container mx-auto w-20 h-20 rounded-full flex items-center justify-center mb-6 icon-bounce">
                    <i class="fas fa-graduation-cap text-white text-3xl"></i>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-wide mb-2" style="background: linear-gradient(135deg, #093FB4, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                    Welcome Back
                </h1>
                <p style="color: #64748b;">Sign in to your account</p>
            </div>

            <!-- Form Container -->
            <div class="glassmorphism rounded-2xl shadow-xl overflow-hidden fade-in">
                <div class="p-8">
                    <!-- Error Messages -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="error-message p-4 rounded-lg mb-6">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <span class="block sm:inline"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Success Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="success-message p-4 rounded-lg mb-6">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span class="block sm:inline"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form method="POST" action="<?= site_url('auth/login') ?>" class="space-y-6">
                        <div class="relative">
                            <input type="text"
                                   id="username"
                                   name="username"
                                   required
                                   class="form-input w-full px-4 py-3 rounded-lg placeholder-transparent focus:outline-none"
                                   placeholder="Enter your username"
                                   value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                            <label for="username" class="form-label absolute left-4 top-3 pointer-events-none">
                                <i class="fas fa-user mr-2"></i>Username
                            </label>
                        </div>

                        <div class="relative">
                            <input type="password"
                                   id="password"
                                   name="password"
                                   required
                                   class="form-input w-full px-4 py-3 rounded-lg placeholder-transparent focus:outline-none"
                                   placeholder="Enter your password">
                            <label for="password" class="form-label absolute left-4 top-3 pointer-events-none">
                                <i class="fas fa-lock mr-2"></i>Password
                            </label>
                        </div>

                        <button type="submit" id="login-btn" class="submit-btn w-full py-3 rounded-lg font-semibold text-white shadow-lg">
                            <span class="spinner"></span>
                            <span class="btn-text">
                                <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                            </span>
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <p style="color: #64748b;">
                            Don't have an account?
                            <a href="<?= site_url('auth/register') ?>" class="font-medium underline transition duration-200" style="color: #093FB4;">
                                Register here
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-sm" style="color: #64748b;">
                <p>© 2025 Grading Management System</p>
            </div>
        </div>
    </div>

    <script>
        // Toastr configuration
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Display flash messages with Toastr
        <?php if (isset($_SESSION['error'])): ?>
            toastr.error('<?= addslashes($_SESSION['error']); unset($_SESSION['error']); ?>');
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            toastr.success('<?= addslashes($_SESSION['success']); unset($_SESSION['success']); ?>');
        <?php endif; ?>

        // Ripple effect on buttons
        $('.submit-btn').on('click', function(e) {
            const button = $(this);
            const ripple = $('<span class="ripple"></span>');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.css({
                width: size,
                height: size,
                left: x,
                top: y
            });
            
            button.append(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });

        // Form submission with loading animation
        $('#verify-form, form').on('submit', function() {
            const btn = $('#login-btn');
            btn.addClass('loading').prop('disabled', true);
        });

        // Smooth input focus animations
        $('.form-input').on('focus', function() {
            $(this).parent().addClass('input-focused');
        }).on('blur', function() {
            $(this).parent().removeClass('input-focused');
        });

        // Add shake animation on error
        <?php if (isset($_SESSION['error'])): ?>
            $('.glassmorphism').css('animation', 'shake 0.5s');
            setTimeout(() => $('.glassmorphism').css('animation', ''), 500);
        <?php endif; ?>

        // Add success pulse on success message
        <?php if (isset($_SESSION['success'])): ?>
            $('.glassmorphism').css('animation', 'pulse 0.5s');
        <?php endif; ?>

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        // Enhanced floating label functionality
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-input');
            
            inputs.forEach(input => {
                // Handle autofill detection and pre-filled values
                setTimeout(() => {
                    if (input.value) {
                        input.classList.add('has-value');
                    }
                }, 100);
                
                input.addEventListener('input', function() {
                    if (this.value) {
                        this.classList.add('has-value');
                    } else {
                        this.classList.remove('has-value');
                    }
                });

                input.addEventListener('blur', function() {
                    if (this.value) {
                        this.classList.add('has-value');
                    } else {
                        this.classList.remove('has-value');
                    }
                });
            });
        });
    </script>
</body>
</html>