document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('themeToggle');
    const body = document.body;
    const icon = toggleBtn ? toggleBtn.querySelector('i') : null;

    // 1. Check LocalStorage
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark-mode');
        if(icon) icon.className = 'fas fa-sun'; // Show sun icon when in dark mode
    }

    // 2. Toggle Logic
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
                if(icon) icon.className = 'fas fa-sun';
            } else {
                localStorage.setItem('theme', 'light');
                if(icon) icon.className = 'fas fa-moon';
            }
        });
    }
});
