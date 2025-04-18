 // Navbar scroll effect
 const navbar = document.getElementById('navbar');
 window.addEventListener('scroll', () => {
     if (window.scrollY > 50) {
         navbar.classList.add('scrolled');
     } else {
         navbar.classList.remove('scrolled');
     }
 });

 // Mobile menu toggle
 const mobileMenuToggle = document.getElementById('mobileMenuToggle');
 const navLinks = document.getElementById('navLinks');
 const overlay = document.getElementById('overlay');

 mobileMenuToggle.addEventListener('click', () => {
     navLinks.classList.toggle('active');
     overlay.classList.toggle('active');
     mobileMenuToggle.classList.toggle('active');
     
     // Toggle hamburger animation
     if (mobileMenuToggle.classList.contains('active')) {
         mobileMenuToggle.querySelectorAll('span')[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
         mobileMenuToggle.querySelectorAll('span')[1].style.opacity = '0';
         mobileMenuToggle.querySelectorAll('span')[2].style.transform = 'rotate(-45deg) translate(7px, -7px)';
     } else {
         mobileMenuToggle.querySelectorAll('span')[0].style.transform = 'none';
         mobileMenuToggle.querySelectorAll('span')[1].style.opacity = '1';
         mobileMenuToggle.querySelectorAll('span')[2].style.transform = 'none';
     }
 });

 // Close mobile menu when overlay is clicked
 overlay.addEventListener('click', () => {
     navLinks.classList.remove('active');
     overlay.classList.remove('active');
     mobileMenuToggle.classList.remove('active');
     
     // Reset hamburger animation
     mobileMenuToggle.querySelectorAll('span')[0].style.transform = 'none';
     mobileMenuToggle.querySelectorAll('span')[1].style.opacity = '1';
     mobileMenuToggle.querySelectorAll('span')[2].style.transform = 'none';
 });

 // Close mobile menu when a link is clicked
 navLinks.querySelectorAll('a').forEach(link => {
     link.addEventListener('click', () => {
         navLinks.classList.remove('active');
         overlay.classList.remove('active');
         mobileMenuToggle.classList.remove('active');
         
         // Reset hamburger animation
         mobileMenuToggle.querySelectorAll('span')[0].style.transform = 'none';
         mobileMenuToggle.querySelectorAll('span')[1].style.opacity = '1';
         mobileMenuToggle.querySelectorAll('span')[2].style.transform = 'none';
     });
 });

 // Smooth scroll for anchor links
 document.querySelectorAll('a[href^="#"]').forEach(anchor => {
     anchor.addEventListener('click', function (e) {
         e.preventDefault();
         const targetId = this.getAttribute('href');
         const targetElement = document.querySelector(targetId);
         
         if (targetElement) {
             window.scrollTo({
                 top: targetElement.offsetTop - 80,
                 behavior: 'smooth'
             });
         }
     });
 });