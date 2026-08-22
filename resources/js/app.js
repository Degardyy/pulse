import './bootstrap';

import Alpine from 'alpinejs';

// Global UI state for the PULSE shell. The theme class itself is applied by an
// inline script in the layout <head> (before first paint); this store only
// mutates it afterwards.
Alpine.store('pulse', {
    sidebarCollapsed: localStorage.getItem('pulse.sidebar') === 'collapsed',
    paletteOpen: false,
    aiOpen: false,

    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('pulse.sidebar', this.sidebarCollapsed ? 'collapsed' : 'expanded');
    },

    get theme() {
        return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    },

    setTheme(theme) {
        document.documentElement.classList.toggle('dark', theme === 'dark');
        localStorage.setItem('pulse.theme', theme);
    },

    toggleTheme() {
        this.setTheme(this.theme === 'dark' ? 'light' : 'dark');
    },
});

window.Alpine = Alpine;
Alpine.start();
