/**
 * Tindlekit Toast Component
 * Accessible toast notifications with animation support
 */

export class TKToast {
  constructor() {
    this.container = this.createContainer();
    this.queue = [];
    this.activeToasts = new Set();
  }

  createContainer() {
    let container = document.getElementById('tk-toast-container');
    
    if (!container) {
      container = document.createElement('div');
      container.id = 'tk-toast-container';
      container.setAttribute('aria-live', 'polite');
      container.setAttribute('aria-atomic', 'true');
      container.style.cssText = `
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 9999;
        max-width: 400px;
        pointer-events: none;
      `;
      document.body.appendChild(container);
    }
    
    return container;
  }

  show(message, type = 'success', duration = 4000) {
    const toast = this.createToast(message, type);
    this.container.appendChild(toast);
    this.activeToasts.add(toast);

    // Animate in
    requestAnimationFrame(() => {
      toast.style.transform = 'translateX(0)';
      toast.style.opacity = '1';
    });

    // Auto dismiss
    setTimeout(() => {
      this.dismiss(toast);
    }, duration);

    return toast;
  }

  createToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `tk-toast tk-toast-${type}`;
    toast.setAttribute('role', 'status');
    toast.style.cssText = `
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.5rem;
      padding: 0.875rem 1rem;
      background: var(--tk-card);
      border: 1px solid var(--tk-border);
      border-radius: var(--tk-radius-sm);
      box-shadow: var(--tk-shadow-lg);
      transform: translateX(100%);
      opacity: 0;
      transition: all 0.3s var(--tk-ease-out);
      pointer-events: auto;
      max-width: 100%;
      word-wrap: break-word;
    `;

    // Icon based on type
    const icon = this.getIcon(type);
    const iconEl = document.createElement('div');
    iconEl.innerHTML = icon;
    iconEl.style.cssText = 'flex-shrink: 0; font-size: 1.125rem;';

    // Message
    const messageEl = document.createElement('div');
    messageEl.textContent = message;
    messageEl.style.cssText = `
      color: var(--tk-fg);
      font-size: 0.875rem;
      line-height: 1.25rem;
      flex: 1;
    `;

    // Dismiss button
    const dismissBtn = document.createElement('button');
    dismissBtn.innerHTML = '×';
    dismissBtn.setAttribute('aria-label', 'Dismiss notification');
    dismissBtn.style.cssText = `
      background: none;
      border: none;
      color: var(--tk-muted);
      cursor: pointer;
      font-size: 1.25rem;
      line-height: 1;
      padding: 0;
      margin: 0;
      width: 24px;
      height: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 0.25rem;
      flex-shrink: 0;
    `;

    dismissBtn.addEventListener('click', () => this.dismiss(toast));
    dismissBtn.addEventListener('mouseenter', () => {
      dismissBtn.style.backgroundColor = 'rgba(159, 179, 200, 0.1)';
    });
    dismissBtn.addEventListener('mouseleave', () => {
      dismissBtn.style.backgroundColor = 'transparent';
    });

    // Color based on type
    if (type === 'success') {
      toast.style.borderLeftColor = 'var(--tk-success)';
      toast.style.borderLeftWidth = '4px';
    } else if (type === 'error') {
      toast.style.borderLeftColor = '#ef4444';
      toast.style.borderLeftWidth = '4px';
    } else if (type === 'warning') {
      toast.style.borderLeftColor = '#f59e0b';
      toast.style.borderLeftWidth = '4px';
    }

    toast.appendChild(iconEl);
    toast.appendChild(messageEl);
    toast.appendChild(dismissBtn);

    return toast;
  }

  getIcon(type) {
    switch (type) {
      case 'success':
        return '✅';
      case 'error':
        return '❌';
      case 'warning':
        return '⚠️';
      case 'info':
        return 'ℹ️';
      default:
        return '📢';
    }
  }

  dismiss(toast) {
    if (!this.activeToasts.has(toast)) return;

    this.activeToasts.delete(toast);
    
    toast.style.transform = 'translateX(100%)';
    toast.style.opacity = '0';

    setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 300);
  }

  dismissAll() {
    this.activeToasts.forEach(toast => this.dismiss(toast));
  }

  // Convenience methods
  success(message, duration) {
    return this.show(message, 'success', duration);
  }

  error(message, duration) {
    return this.show(message, 'error', duration);
  }

  warning(message, duration) {
    return this.show(message, 'warning', duration);
  }

  info(message, duration) {
    return this.show(message, 'info', duration);
  }
}

// Create global toast instance
export const tkToast = new TKToast();

// Make it available globally for easy use
if (typeof window !== 'undefined') {
  window.tkToast = tkToast;
}