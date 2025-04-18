const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
    if (window.scrollY > 20) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const navLinks = document.getElementById('navLinks');
const overlay = document.getElementById('overlay');

mobileMenuToggle.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    overlay.classList.toggle('active');
    mobileMenuToggle.classList.toggle('active');

    if (mobileMenuToggle.classList.contains('active')) {
        mobileMenuToggle.querySelectorAll('span')[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
        mobileMenuToggle.querySelectorAll('span')[1].style.opacity = '0';
        mobileMenuToggle.querySelectorAll('span')[2].style.transform = 'rotate(-45deg) translate(5px, -5px)';
    } else {
        mobileMenuToggle.querySelectorAll('span')[0].style.transform = 'none';
        mobileMenuToggle.querySelectorAll('span')[1].style.opacity = '1';
        mobileMenuToggle.querySelectorAll('span')[2].style.transform = 'none';
    }
});

overlay.addEventListener('click', () => {
    navLinks.classList.remove('active');
    overlay.classList.remove('active');
    mobileMenuToggle.classList.remove('active');
    document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('active'));

    mobileMenuToggle.querySelectorAll('span')[0].style.transform = 'none';
    mobileMenuToggle.querySelectorAll('span')[1].style.opacity = '1';
    mobileMenuToggle.querySelectorAll('span')[2].style.transform = 'none';
});

navLinks.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
        navLinks.classList.remove('active');
        overlay.classList.remove('active');
        mobileMenuToggle.classList.remove('active');
        document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('active'));

        mobileMenuToggle.querySelectorAll('span')[0].style.transform = 'none';
        mobileMenuToggle.querySelectorAll('span')[1].style.opacity = '1';
        mobileMenuToggle.querySelectorAll('span')[2].style.transform = 'none';
    });
});

document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            e.preventDefault();
            const dropdown = toggle.closest('.dropdown');
            dropdown.classList.toggle('active');
        }
    });
});