    </div> <!-- End max-w-7xl container -->
    
    <!-- Chatbot Button -->
    <div id="chatbot-button" class="fixed bottom-6 right-6 z-50">
        <button class="bg-green-500 hover:bg-green-600 text-white rounded-full w-14 h-14 flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110">
            <i class="fas fa-comments text-2xl"></i>
        </button>
    </div>

    <!-- Chatbot Window -->
    <div id="chatbot-window" class="fixed bottom-24 right-6 w-96 rounded-lg shadow-2xl z-50" style="display: none; max-height: 500px; background: #FFFFFF; border: 1px solid rgba(9, 63, 180, 0.2);">
        <div class="p-4 rounded-t-lg flex items-center justify-between" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
            <div class="flex items-center">
                <i class="fas fa-robot text-2xl mr-3 text-white"></i>
                <div>
                    <h3 class="font-bold text-white">Help Desk</h3>
                    <p class="text-xs text-blue-100" id="chatbot-mode">Checking AI status...</p>
                </div>
            </div>
            <button id="chatbot-close" class="text-white hover:text-blue-100">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="chatbot-messages" class="p-4 overflow-y-auto" style="height: 350px; background: #f8fafc;">
            <div class="bot-message mb-3">
                <div class="p-3 rounded-lg text-sm" style="background: #dbeafe; border: 1px solid rgba(59, 130, 246, 0.3); color: #1e293b;">
                    <p class="mb-2">👋 Hi! I'm your virtual assistant. How can I help you today?</p>
                    <p class="text-xs" style="color: #64748b;">Click on a question below or type your own:</p>
                </div>
            </div>
            
            <div class="space-y-2 mt-4" id="quick-questions">
                <button class="quick-question w-full text-left p-3 rounded-lg text-sm transition hover-lift" style="background: #FFFFFF; border: 1px solid rgba(9, 63, 180, 0.2); color: #1e293b;">
                    <i class="fas fa-chart-bar mr-2" style="color: #10b981;"></i>How do I view my grades?
                </button>
                <button class="quick-question w-full text-left p-3 rounded-lg text-sm transition hover-lift" style="background: #FFFFFF; border: 1px solid rgba(9, 63, 180, 0.2); color: #1e293b;">
                    <i class="fas fa-envelope mr-2" style="color: #3b82f6;"></i>How do I contact my teacher?
                </button>
                <button class="quick-question w-full text-left p-3 rounded-lg text-sm transition hover-lift" style="background: #FFFFFF; border: 1px solid rgba(9, 63, 180, 0.2); color: #1e293b;">
                    <i class="fas fa-file-pdf mr-2" style="color: #ef4444;"></i>How do I download my grade report?
                </button>
                <button class="quick-question w-full text-left p-3 rounded-lg text-sm transition hover-lift" style="background: #FFFFFF; border: 1px solid rgba(9, 63, 180, 0.2); color: #1e293b;">
                    <i class="fas fa-user-edit mr-2" style="color: #a855f7;"></i>How do I update my profile?
                </button>
                <button class="quick-question w-full text-left p-3 rounded-lg text-sm transition hover-lift" style="background: #FFFFFF; border: 1px solid rgba(9, 63, 180, 0.2); color: #1e293b;">
                    <i class="fas fa-key mr-2" style="color: #f59e0b;"></i>How do I reset my password?
                </button>
            </div>
        </div>
        
        <div class="p-3 border-t" style="border-color: rgba(9, 63, 180, 0.1); background: #FFFFFF;">
            <div class="flex space-x-2">
                <input type="text" id="chatbot-input" placeholder="Type your question..." class="flex-1 rounded-lg px-3 py-2 text-sm focus:outline-none" style="background: #f8fafc; border: 1px solid rgba(9, 63, 180, 0.2); color: #1e293b;">
                <button id="chatbot-send" class="rounded-lg px-4 py-2 transition text-white" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
            <div class="mt-2 text-xs text-center" style="color: #64748b;">
                Powered by Gemini AI
            </div>
        </div>
    </div>

    <!-- Include animations.js -->
    <script src="<?= site_url('public/js/animations.js') ?>"></script>

    <script>
    $(document).ready(function() {
        // Toastr configuration
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4000
        };

        // Notification Bell - Toggle Dropdown
        let notificationsLoaded = false;
        
        $('#notification-bell').on('click', function(e) {
            e.stopPropagation();
            const dropdown = $('#notification-dropdown');
            dropdown.toggleClass('show');
            
            if (dropdown.hasClass('show') && !notificationsLoaded) {
                loadNotifications();
                notificationsLoaded = true;
            }
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#notification-bell').length) {
                $('#notification-dropdown').removeClass('show');
            }
        });

        // Load Notifications via AJAX
        function loadNotifications() {
            $.ajax({
                url: '<?= site_url("student/notifications") ?>',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayNotifications(response.notifications);
                    } else {
                        $('#notification-list').html('<div class="text-center py-6 text-gray-400 text-sm">No notifications</div>');
                    }
                },
                error: function() {
                    $('#notification-list').html('<div class="text-center py-6 text-red-400 text-sm">Failed to load notifications</div>');
                }
            });
        }

        // Display Notifications
        function displayNotifications(notifications) {
            if (notifications.length === 0) {
                $('#notification-list').html('<div class="text-center py-6 text-gray-400 text-sm"><i class="fas fa-inbox mb-2"></i><p>No notifications yet</p></div>');
                return;
            }

            let html = '';
            notifications.forEach(function(notif) {
                const unreadClass = notif.is_read == 0 ? 'unread' : '';
                const timeAgo = formatTimeAgo(notif.created_at);
                
                html += `<div class="notification-item ${unreadClass}" data-id="${notif.id}">
                    <div class="flex items-start">
                        <i class="fas fa-circle text-xs mr-2 mt-1 ${notif.is_read == 0 ? 'text-green-400' : 'text-gray-600'}"></i>
                        <div class="flex-1">
                            <p class="text-sm text-gray-200 mb-1">${escapeHtml(notif.message)}</p>
                            <div class="flex items-center justify-between">
                                <span class="time">${timeAgo}</span>
                                <span class="text-xs text-gray-500">${escapeHtml(notif.sender_name)}</span>
                            </div>
                        </div>
                    </div>
                </div>`;
            });

            $('#notification-list').html(html);
        }

        // Mark Notification as Read
        $(document).on('click', '.notification-item.unread', function() {
            const notifId = $(this).data('id');
            const item = $(this);

            $.ajax({
                url: '<?= site_url("student/mark_notification_read/") ?>' + notifId,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        item.removeClass('unread');
                        item.find('.fa-circle').removeClass('text-green-400').addClass('text-gray-600');
                        updateBadge(response.unread_count);
                    }
                }
            });
        });

        // Mark All as Read
        $('#mark-all-read').on('click', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: '<?= site_url("student/mark_all_read") ?>',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('.notification-item').removeClass('unread');
                        $('.notification-item .fa-circle').removeClass('text-green-400').addClass('text-gray-600');
                        updateBadge(0);
                        toastr.success('All notifications marked as read');
                    }
                }
            });
        });

        // Update Badge Count
        function updateBadge(count) {
            if (count > 0) {
                if ($('#notification-badge').length) {
                    $('#notification-badge').text(count);
                } else {
                    $('#notification-bell').append(`<span class="notification-badge" id="notification-badge">${count}</span>`);
                }
                $('#notification-bell i').addClass('shake-bell');
                setTimeout(function() {
                    $('#notification-bell i').removeClass('shake-bell');
                }, 500);
            } else {
                $('#notification-badge').remove();
            }
        }

        // Chatbot Functionality
        $('#chatbot-button').on('click', function() {
            $('#chatbot-window').slideToggle(300);
            $(this).find('i').toggleClass('fa-comments fa-times');
            
            // Check AI status on first open
            if ($('#chatbot-mode').text() === 'Checking AI status...') {
                $('#chatbot-mode').html('<i class="fas fa-spinner fa-spin"></i> Testing...');
                $.ajax({
                    url: '<?= site_url("student/chatbot") ?>',
                    type: 'POST',
                    data: { question: 'test' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.mode === 'AI (Gemini)') {
                            $('#chatbot-mode').html('<i class="fas fa-check-circle"></i> AI Powered by Gemini');
                        } else {
                            $('#chatbot-mode').html('<i class="fas fa-code"></i> Rule-Based Mode');
                        }
                    },
                    error: function() {
                        $('#chatbot-mode').html('<i class="fas fa-exclamation-circle"></i> Offline Mode');
                    }
                });
            }
        });

        $('#chatbot-close').on('click', function() {
            $('#chatbot-window').slideUp(300);
            $('#chatbot-button').find('i').removeClass('fa-times').addClass('fa-comments');
        });

        // Quick Questions
        $('.quick-question').on('click', function() {
            const question = $(this).text().trim();
            askQuestion(question);
        });

        // Send Message
        $('#chatbot-send, #chatbot-input').on('keypress click', function(e) {
            if (e.type === 'keypress' && e.which !== 13) return;
            if (e.type === 'click' && $(this).attr('id') !== 'chatbot-send') return;
            
            const input = $('#chatbot-input');
            const question = input.val().trim();
            
            if (question) {
                askQuestion(question);
                input.val('');
            }
        });

        function askQuestion(question) {
            // Add user message
            $('#quick-questions').remove();
            $('#chatbot-messages').append(`
                <div class="user-message mb-3 text-right">
                    <div class="bg-green-600 inline-block p-3 rounded-lg text-sm max-w-xs">
                        ${escapeHtml(question)}
                    </div>
                </div>
            `);

            // Scroll to bottom
            $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);

            // Show typing indicator
            $('#chatbot-messages').append(`
                <div class="bot-message mb-3 typing-indicator">
                    <div class="bg-white/5 inline-block p-3 rounded-lg text-sm">
                        <i class="fas fa-circle text-xs text-gray-400 animate-pulse"></i>
                        <i class="fas fa-circle text-xs text-gray-400 animate-pulse" style="animation-delay: 0.2s"></i>
                        <i class="fas fa-circle text-xs text-gray-400 animate-pulse" style="animation-delay: 0.4s"></i>
                    </div>
                </div>
            `);

            $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);

            // Call AI API
            $.ajax({
                url: '<?= site_url("student/chatbot") ?>',
                type: 'POST',
                data: { question: question },
                dataType: 'json',
                success: function(response) {
                    $('.typing-indicator').remove();
                    
                    if (response.success) {
                        // Show mode badge (AI or Rule-Based)
                        const modeBadge = response.mode ? 
                            `<div class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-${response.mode === 'AI (Gemini)' ? 'robot' : 'code'}"></i> 
                                ${response.mode}
                            </div>` : '';
                        
                        $('#chatbot-messages').append(`
                            <div class="bot-message mb-3">
                                <div class="bg-green-500/20 border border-green-500/30 p-3 rounded-lg text-sm max-w-xs">
                                    ${escapeHtml(response.answer)}
                                    ${modeBadge}
                                </div>
                            </div>
                        `);
                    } else {
                        $('#chatbot-messages').append(`
                            <div class="bot-message mb-3">
                                <div class="bg-red-500/20 border border-red-500/30 p-3 rounded-lg text-sm max-w-xs">
                                    Sorry, I couldn't process your question. Please try again.
                                </div>
                            </div>
                        `);
                    }
                    
                    $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);
                },
                error: function() {
                    $('.typing-indicator').remove();
                    $('#chatbot-messages').append(`
                        <div class="bot-message mb-3">
                            <div class="bg-red-500/20 border border-red-500/30 p-3 rounded-lg text-sm max-w-xs">
                                ❌ Connection error. Please try again later.
                            </div>
                        </div>
                    `);
                    $('#chatbot-messages').scrollTop($('#chatbot-messages')[0].scrollHeight);
                }
            });
        }

        // Utility Functions
        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
            if (seconds < 60) return 'Just now';
            if (seconds < 3600) return Math.floor(seconds / 60) + ' min ago';
            if (seconds < 86400) return Math.floor(seconds / 3600) + ' hr ago';
            if (seconds < 604800) return Math.floor(seconds / 86400) + ' days ago';
            return date.toLocaleDateString();
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        // Add ripple effect to all buttons
        addRippleEffect();
        
        // Animate cards on page load
        $('.card').each(function(index) {
            $(this).css('opacity', '0').css('transform', 'translateY(20px)');
            setTimeout(() => {
                $(this).css('transition', 'all 0.5s ease');
                $(this).css('opacity', '1').css('transform', 'translateY(0)');
            }, index * 100);
        });
    });
    </script>
</body>
</html>
