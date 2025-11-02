// ===== NOTIFICATION SYSTEM =====

class NotificationManager {
    constructor() {
        this.cache = new Map();
        this.pollInterval = null;
        this.pollFrequency = 30000; // 30 seconds
        this.isPolling = false;
        this.eventsInitialized = false; // Track if events are already bound
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    /**
     * Initialize notification system
     */
    async init() {
        try {
            console.log('🔔 Initializing notification system...');
            await this.loadNotifications();
            this.bindEvents();
            this.startPolling();
            console.log('✅ Notification system initialized successfully');
        } catch (error) {
            console.error('❌ Failed to initialize notification system:', error);
        }
    }

    /**
     * Load notifications from server
     */
    async loadNotifications(unreadOnly = false) {
        try {
            console.log('📡 Loading notifications from server...');
            const url = new URL('/api/notifications', window.location.origin);
            if (unreadOnly) {
                url.searchParams.set('unread_only', 'true');
            }

            const response = await fetch(url, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('📊 Received notification data:', data);
            
            if (data.success) {
                this.cache.set('notifications', data.notifications);
                this.cache.set('unread_count', data.unread_count);
                this.updateUI(data.notifications, data.unread_count);
                return data;
            } else {
                throw new Error(data.message || 'Failed to load notifications');
            }
        } catch (error) {
            console.error('❌ Error loading notifications:', error);
            // Show fallback empty state
            this.updateUI([], 0);
            return { notifications: [], unread_count: 0 };
        }
    }

    /**
     * Get unread count only
     */
    async getUnreadCount() {
        try {
            const response = await fetch('/api/notifications/unread-count', {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            if (data.success) {
                this.cache.set('unread_count', data.unread_count);
                this.updateUnreadCount(data.unread_count);
                return data.unread_count;
            }
        } catch (error) {
            console.error('Error getting unread count:', error);
        }
        return 0;
    }

    /**
     * Mark notification as read
     */
    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/api/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                // Update local cache
                const notifications = this.cache.get('notifications') || [];
                const notification = notifications.find(n => n.id === notificationId);
                if (notification) {
                    notification.is_read = true;
                }
                
                // Decrement unread count
                const currentCount = this.cache.get('unread_count') || 0;
                const newCount = Math.max(0, currentCount - 1);
                this.cache.set('unread_count', newCount);
                this.updateUnreadCount(newCount);
                
                return true;
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
        return false;
    }

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        try {
            const response = await fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                // Update local cache
                const notifications = this.cache.get('notifications') || [];
                notifications.forEach(n => n.is_read = true);
                this.cache.set('unread_count', 0);
                this.updateUI(notifications, 0);
                return true;
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
        return false;
    }

    /**
     * Update the notification UI
     */
    updateUI(notifications, unreadCount) {
        this.updateUnreadCount(unreadCount);
        this.updateNotificationList(notifications);
    }

    /**
     * Update unread count display
     */
    updateUnreadCount(count) {
        // Update all notification count elements
        const countElements = document.querySelectorAll('.notification-count, #notification-count, #notif-badge');
        countElements.forEach(element => {
            if (count > 0) {
                element.textContent = count > 99 ? '99+' : count.toString();
                element.style.display = 'inline-block';
            } else {
                element.style.display = 'none';
            }
        });
    }

    /**
     * Update notification list display
     */
    updateNotificationList(notifications) {
        const listElements = document.querySelectorAll('.notification-list, #notification-list');
        console.log('🔄 Updating notification list. Found elements:', listElements.length);
        console.log('📝 Notifications to display:', notifications.length);
        
        listElements.forEach(listElement => {
            if (notifications.length === 0) {
                listElement.innerHTML = `
                    <div class="notification-empty">
                        <i class="fas fa-inbox"></i> 
                        No new notifications
                    </div>
                `;
                return;
            }

            const notificationHTML = notifications.map(notification => `
                <div class="notification-item ${notification.is_read ? 'read' : 'unread'}" 
                     data-id="${notification.id}"
                     style="padding: 12px 14px; border-bottom: 1px solid #f1f5f9; cursor: pointer; ${!notification.is_read ? 'background: #f8fafc;' : ''}">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <div style="color: ${this.getPriorityColor(notification.priority)}; margin-top: 2px;">
                            <i class="${notification.icon}"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: ${!notification.is_read ? '600' : '500'}; font-size: 13px; color: #1f2937; margin-bottom: 2px;">
                                ${notification.title}
                            </div>
                            <div style="font-size: 12px; color: #6b7280; line-height: 1.4; margin-bottom: 4px;">
                                ${notification.message}
                            </div>
                            <div style="font-size: 11px; color: #9ca3af;">
                                ${notification.created_at_human}
                            </div>
                        </div>
                        ${!notification.is_read ? `<div style="width: 8px; height: 8px; background: #3b82f6; border-radius: 50%; margin-top: 4px;"></div>` : ''}
                    </div>
                </div>
            `).join('');

            listElement.innerHTML = notificationHTML;

            // Add click handlers
            listElement.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    const notificationId = parseInt(item.dataset.id);
                    const isUnread = item.classList.contains('unread');
                    
                    if (isUnread) {
                        this.markAsRead(notificationId);
                        item.classList.remove('unread');
                        item.classList.add('read');
                        item.style.background = '';
                        // Remove unread indicator
                        const indicator = item.querySelector('div[style*="background: #3b82f6"]');
                        if (indicator) indicator.remove();
                    }
                });
            });
        });
    }

    /**
     * Get color for notification priority
     */
    getPriorityColor(priority) {
        switch (priority) {
            case 'urgent': return '#dc2626'; // red-600
            case 'high': return '#ea580c';   // orange-600
            case 'normal': return '#2563eb'; // blue-600
            case 'low': return '#059669';    // emerald-600
            default: return '#6b7280';       // gray-500
        }
    }

    /**
     * Bind notification events
     */
    bindEvents() {
        // Prevent double binding
        if (this.eventsInitialized) {
            console.log('⚠️ Events already initialized, skipping...');
            return;
        }
        
        console.log('🔗 Binding notification events...');
        
        // Store reference to this for use in event handlers
        const self = this;
        
        // Handle notification bell clicks
        document.querySelectorAll('.notification-bell, #notif-bell').forEach(bell => {
            bell.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('🔔 Notification bell clicked');
                
                const wrapper = bell.closest('.notification-wrapper') || bell.parentElement;
                if (!wrapper) {
                    console.warn('⚠️ No notification wrapper found');
                    return;
                }
                
                const isCurrentlyOpen = wrapper.classList.contains('open');
                console.log('📊 Current state:', { isCurrentlyOpen });
                console.log('🎯 Wrapper classes:', wrapper.className);
                
                // Close all OTHER notification dropdowns first (but not the current one)
                document.querySelectorAll('.notification-wrapper').forEach(w => {
                    if (w !== wrapper) {
                        w.classList.remove('open');
                    }
                });
                
                // Toggle current dropdown
                if (!isCurrentlyOpen) {
                    wrapper.classList.add('open');
                    console.log('✅ Dropdown opened');
                    console.log('🎯 Wrapper classes after open:', wrapper.className);
                    
                    // Load fresh notifications when opening
                    self.loadNotifications();
                } else {
                    wrapper.classList.remove('open');
                    console.log('❌ Dropdown closed');
                    console.log('🎯 Wrapper classes after close:', wrapper.className);
                }
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const clickedInsideNotification = e.target.closest('.notification-wrapper');
            
            if (!clickedInsideNotification) {
                document.querySelectorAll('.notification-wrapper').forEach(wrapper => {
                    if (wrapper.classList.contains('open')) {
                        wrapper.classList.remove('open');
                        console.log('🚪 Dropdown closed by outside click');
                    }
                });
            }
        });

        // Prevent dropdown from closing when clicking inside it
        document.querySelectorAll('.notification-dropdown').forEach(dropdown => {
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        });

        // Add mark all as read button if not exists
        this.addMarkAllAsReadButton();
        
        // Mark events as initialized
        this.eventsInitialized = true;
        
        console.log('✅ Notification events bound successfully');
    }

    /**
     * Add "Mark all as read" button to dropdowns
     */
    addMarkAllAsReadButton() {
        document.querySelectorAll('.notification-dropdown, #notif-dropdown').forEach(dropdown => {
            if (dropdown.querySelector('.mark-all-read-btn')) return; // Already exists

            const header = dropdown.querySelector('div[style*="border-bottom"]') || 
                          dropdown.querySelector('div').parentNode.insertBefore(
                              document.createElement('div'), 
                              dropdown.querySelector('div')
                          );
            
            if (header && header.textContent.includes('Notifications')) {
                header.style.display = 'flex';
                header.style.justifyContent = 'space-between';
                header.style.alignItems = 'center';
                
                const button = document.createElement('button');
                button.className = 'mark-all-read-btn';
                button.textContent = 'Mark all read';
                button.style.cssText = `
                    background: none; 
                    border: none; 
                    color: #3b82f6; 
                    font-size: 11px; 
                    cursor: pointer; 
                    padding: 2px 4px;
                    border-radius: 4px;
                `;
                button.addEventListener('mouseover', () => button.style.background = '#f3f4f6');
                button.addEventListener('mouseout', () => button.style.background = 'none');
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.markAllAsRead();
                });
                
                header.appendChild(button);
            }
        });
    }

    /**
     * Start polling for new notifications
     */
    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.pollInterval = setInterval(() => {
            this.getUnreadCount(); // Just check count, don't reload full list
        }, this.pollFrequency);
    }

    /**
     * Stop polling for notifications
     */
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
            this.isPolling = false;
        }
    }

    /**
     * Show a toast notification
     */
    showToast(message, type = 'info', duration = 5000) {
        const toast = document.createElement('div');
        const iconClass = {
            'success': 'fas fa-check-circle',
            'error': 'fas fa-exclamation-circle',
            'warning': 'fas fa-exclamation-triangle',
            'info': 'fas fa-info-circle'
        }[type] || 'fas fa-info-circle';

        const bgColor = {
            'success': '#10b981',
            'error': '#ef4444',
            'warning': '#f59e0b',
            'info': '#3b82f6'
        }[type] || '#3b82f6';

        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${bgColor};
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            max-width: 300px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;

        toast.innerHTML = `<i class="${iconClass}"></i> ${message}`;
        document.body.appendChild(toast);

        // Slide in
        setTimeout(() => toast.style.transform = 'translateX(0)', 10);

        // Slide out and remove
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => document.body.removeChild(toast), 300);
        }, duration);
    }

    /**
     * Destroy notification manager
     */
    destroy() {
        this.stopPolling();
        this.cache.clear();
    }
}

// Create global notification manager instance
window.notificationManager = new NotificationManager();

// Note: Manual initialization required
// Call window.notificationManager.init() when needed