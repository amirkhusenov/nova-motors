document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            const offset = 70;
            const targetPosition = target.offsetTop - offset;
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function (entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

document.querySelectorAll('.fade-in, .fade-in-left, .fade-in-right, .scale-in').forEach(element => {
    observer.observe(element);
});

document.querySelectorAll('.car-card').forEach((card, index) => {
    card.style.transitionDelay = `${index * 0.1}s`;
});

const bookButton = document.querySelector('.btn-cta-secondary');
if (bookButton) {
    bookButton.addEventListener('click', function (e) {
        e.preventDefault();
        handleRentOrBooking();
    });
}

function handleRentOrBooking() {
    if (typeof isUserLoggedIn !== 'undefined' && isUserLoggedIn) {
        window.location.href = './profile.php';
    } else {
        window.location.href = './register.php';
    }
}

