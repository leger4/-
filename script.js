// ========================================
// NAVBAR SCROLL EFFECT
// ========================================

const navbar = document.getElementById('navbar');
let lastScrollTop = 0;

window.addEventListener('scroll', () => {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    if (scrollTop > 100) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
    
    lastScrollTop = scrollTop;
});

// ========================================
// MOBILE MENU TOGGLE
// ========================================

const burger = document.querySelector('.burger');
const navMenu = document.querySelector('.nav-menu');
const navLinks = document.querySelectorAll('.nav-menu a');

burger.addEventListener('click', () => {
    burger.classList.toggle('active');
    navMenu.classList.toggle('active');
    document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
});

// Close menu when clicking on a link
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        burger.classList.remove('active');
        navMenu.classList.remove('active');
        document.body.style.overflow = '';
    });
});

// Close menu when clicking outside
document.addEventListener('click', (e) => {
    if (!burger.contains(e.target) && !navMenu.contains(e.target)) {
        burger.classList.remove('active');
        navMenu.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// ========================================
// SMOOTH SCROLL
// ========================================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        
        if (target) {
            const offsetTop = target.offsetTop - 80;
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
    });
});

// ========================================
// INTERSECTION OBSERVER FOR ANIMATIONS
// ========================================

const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe all cards and sections
const animateElements = document.querySelectorAll(
    '.movie-card, .advantage-card, .review-card, .section-title'
);

animateElements.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});

// Add animation class
const style = document.createElement('style');
style.textContent = `
    .animate-in {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
`;
document.head.appendChild(style);

// ========================================
// MOVIE CARDS INTERACTION
// ========================================

const movieCards = document.querySelectorAll('.movie-card');

movieCards.forEach(card => {
    const btn = card.querySelector('.movie-btn');
    
    if (btn) {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            
            // Add ripple effect
            const ripple = document.createElement('div');
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                width: 20px;
                height: 20px;
                pointer-events: none;
                animation: ripple-animation 0.6s ease-out;
            `;
            
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - 10;
            const y = e.clientY - rect.top - 10;
            
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            
            btn.style.position = 'relative';
            btn.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    }
});

// Add ripple animation
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
    @keyframes ripple-animation {
        to {
            width: 200px;
            height: 200px;
            opacity: 0;
            transform: translate(-50%, -50%);
        }
    }
`;
document.head.appendChild(rippleStyle);

// ========================================
// PARALLAX EFFECT FOR HERO
// ========================================

const hero = document.querySelector('.hero');
const heroContent = document.querySelector('.hero-content');

window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const parallaxSpeed = 0.5;
    
    if (hero && scrolled < hero.offsetHeight) {
        heroContent.style.transform = `translateY(${scrolled * parallaxSpeed}px)`;
        heroContent.style.opacity = 1 - (scrolled / hero.offsetHeight) * 1.5;
    }
});

// ========================================
// HERO BUTTON GLOW EFFECT
// ========================================

const heroBtn = document.querySelector('.hero-btn');

if (heroBtn) {
    heroBtn.addEventListener('mouseenter', function(e) {
        const glow = this.querySelector('.btn-glow');
        if (glow) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            glow.style.left = x + 'px';
            glow.style.top = y + 'px';
        }
    });
}

// ========================================
// LAZY LOADING IMAGES
// ========================================

const images = document.querySelectorAll('img[src]');

const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            
            // Проверяем, загружено ли изображение
            if (img.complete && img.naturalHeight !== 0) {
                // Изображение уже загружено (из кеша)
                img.style.opacity = '1';
            } else {
                // Изображение ещё не загружено
                img.style.transition = 'opacity 0.5s ease';
                img.style.opacity = '0';
                
                img.addEventListener('load', () => {
                    img.style.opacity = '1';
                });
                
                // На случай ошибки загрузки
                img.addEventListener('error', () => {
                    img.style.opacity = '1';
                });
            }
            
            imageObserver.unobserve(img);
        }
    });
}, {
    rootMargin: '50px'
});

images.forEach(img => {
    // Если изображение уже загружено, показываем его сразу
    if (img.complete && img.naturalHeight !== 0) {
        img.style.opacity = '1';
    }
    imageObserver.observe(img);
});

// ========================================
// PERFORMANCE OPTIMIZATIONS
// ========================================

// Debounce function for scroll events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function for frequent events
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// ========================================
// CONSOLE EASTER EGG
// ========================================

console.log('%c🎬 LUMIÈRE Cinema', 'font-size: 24px; font-weight: bold; color: #6366f1;');
console.log('%cИскусство большого экрана', 'font-size: 14px; color: #a0a0a0;');
console.log('%cWebsite crafted with ❤️', 'font-size: 12px; color: #6366f1; font-style: italic;');

// ========================================
// LOADING COMPLETE
// ========================================

window.addEventListener('load', () => {
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.3s ease';
    
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
    
    console.log('%c✓ All assets loaded', 'color: #10b981; font-weight: bold;');
});
