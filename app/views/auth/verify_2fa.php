<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Verification - Grading Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <style>
        body {
            font-family: "Inter", sans-serif;
            background: linear-gradient(135deg, rgba(4,22,58,0.85) 0%, rgba(11,42,95,0.85) 50%, rgba(0,8,20,0.85) 100%);
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('<?= site_url('public/background1.gif') ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            opacity: 0.25;
            z-index: -1;
        }

        .glassmorphism {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(18px) saturate(180%);
            -webkit-backdrop-filter: blur(18px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .code-input {
            letter-spacing: 1rem;
            font-size: 2rem;
            font-weight: bold;
        }

        .code-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
        }

        .submit-btn {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            transition: all 0.25s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .resend-btn {
            color: #60a5fa;
            transition: all 0.2s;
        }

        .resend-btn:hover {
            color: #3b82f6;
            text-decoration: underline;
        }

        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Toastr Custom Styling */
        .toast-success { background-color: #10b981 !important; }
        .toast-error { background-color: #ef4444 !important; }
        .toast-info { background-color: #3b82f6 !important; }
        .toast-warning { background-color: #f59e0b !important; }
    </style>
</head>
<body class="min-h-screen text-white">
    <!-- Back Button -->
    <div class="fixed top-6 left-6 z-50">
        <a href="<?= site_url('auth/login') ?>" 
           class="flex items-center px-4 py-2 rounded-lg bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-white/20 transition">
            <i class="fas fa-arrow-left mr-2"></i>
            <span>Back to Login</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-black/50 backdrop-blur-md shadow-lg z-40">
        <div class="max-w-6xl mx-auto px-6 py-3 flex justify-between items-center">
            <span class="text-xl font-bold tracking-wide">🎓 Grading Management System</span>
            <div class="text-sm text-gray-300">2FA Verification</div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen flex items-center justify-center pt-20 pb-12 px-4">
        <div class="w-full max-w-md fade-in">
            <div class="glassmorphism rounded-2xl shadow-2xl p-8">
                <!-- Icon and Title -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 mb-4">
                        <i class="fas fa-shield-alt text-4xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold mb-2">Two-Factor Authentication</h2>
                    <p class="text-gray-400">Enter the 6-digit code sent to your email</p>
                    <?php if (isset($email)): ?>
                    <p class="text-sm text-blue-400 mt-2">
                        <i class="fas fa-envelope mr-1"></i><?= htmlspecialchars($email) ?>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Form -->
                <form method="POST" action="<?= site_url('auth/verify_2fa') ?>" id="verify-form">
                    <div class="mb-6">
                        <input type="text" 
                               name="code" 
                               id="code-input"
                               maxlength="6" 
                               pattern="\d{6}"
                               placeholder="000000"
                               class="code-input w-full text-center py-4 bg-white/5 border border-white/20 rounded-lg text-white transition"
                               required 
                               autofocus
                               autocomplete="off">
                        <p class="text-xs text-gray-500 text-center mt-2">Code expires in 10 minutes</p>
                    </div>
                    
                    <button type="submit" 
                            class="submit-btn w-full py-3 rounded-lg text-white font-semibold shadow-lg">
                        <i class="fas fa-check-circle mr-2"></i>Verify Code
                    </button>
                </form>

                <!-- Resend Code -->
                <div class="text-center mt-6">
                    <p class="text-gray-400 text-sm mb-2">Didn't receive the code?</p>
                    <button id="resend-btn" class="resend-btn font-semibold">
                        <i class="fas fa-redo mr-1"></i>Resend Code
                    </button>
                    <p id="timer" class="text-xs text-gray-500 mt-2"></p>
                </div>

                <!-- Help Text -->
                <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                    <p class="text-sm text-blue-300">
                        <i class="fas fa-info-circle mr-2"></i>
                        Check your spam folder if you don't see the email
                    </p>
                </div>
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

        // Auto-focus and format input
        $('#code-input').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Auto-submit when 6 digits entered
        $('#code-input').on('input', function() {
            if (this.value.length === 6) {
                $('#verify-form').submit();
            }
        });

        // Form submission with AJAX
        $('#verify-form').on('submit', function(e) {
            e.preventDefault();
            
            const code = $('#code-input').val();
            
            if (code.length !== 6) {
                toastr.error('Please enter a 6-digit code');
                return;
            }

            $.ajax({
                url: '<?= site_url('auth/verify_2fa') ?>',
                method: 'POST',
                data: { code: code },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Verification successful! Redirecting...');
                        setTimeout(function() {
                            window.location.href = response.redirect || '<?= site_url('admin') ?>';
                        }, 1500);
                    } else {
                        toastr.error(response.message || 'Invalid or expired code');
                        $('#code-input').val('').focus();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    try {
                        const response = JSON.parse(xhr.responseText);
                        toastr.error(response.message || 'An error occurred. Please try again.');
                    } catch (e) {
                        toastr.error('Server error. Please check your connection and try again.');
                    }
                    $('#code-input').val('').focus();
                }
            });
        });

        // Resend code functionality
        let canResend = false;
        let countdown = 60;
        
        // Calculate remaining time based on server timestamp
        const lastSentTime = <?= isset($last_sent) ? $last_sent : 'Date.now() / 1000' ?>;
        const currentTime = Date.now() / 1000;
        const elapsed = Math.floor(currentTime - lastSentTime);
        const remaining = Math.max(0, 60 - elapsed);
        
        countdown = remaining;

        function startTimer() {
            if (countdown <= 0) {
                canResend = true;
                $('#resend-btn').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                $('#timer').text('');
                return;
            }
            
            canResend = false;
            $('#resend-btn').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            
            const interval = setInterval(function() {
                countdown--;
                $('#timer').text(`Resend available in ${countdown}s`);
                
                if (countdown <= 0) {
                    clearInterval(interval);
                    canResend = true;
                    $('#resend-btn').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    $('#timer').text('');
                }
            }, 1000);
        }

        $('#resend-btn').on('click', function() {
            if (!canResend) return;

            $.ajax({
                url: '<?= site_url('auth/resend_2fa') ?>',
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'New code sent to your email');
                        countdown = 60;
                        startTimer();
                    } else {
                        toastr.error(response.message || 'Failed to resend code');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Resend Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    try {
                        const response = JSON.parse(xhr.responseText);
                        toastr.error(response.message || 'Failed to resend code');
                    } catch (e) {
                        toastr.error('An error occurred. Please try again.');
                    }
                }
            });
        });

        // Start timer on page load
        startTimer();

        // Display flash messages if any
        <?php if (isset($_SESSION['error'])): ?>
            toastr.error('<?= addslashes($_SESSION['error']); unset($_SESSION['error']); ?>');
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            toastr.success('<?= addslashes($_SESSION['success']); unset($_SESSION['success']); ?>');
        <?php endif; ?>
    </script>
</body>
</html>
