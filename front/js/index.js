//all event listeners for the index.html page
//frontend
//burger
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("menu-burger-button").addEventListener("click", function() {
        document.querySelector(".header-links").classList.toggle("open")
    })
});

//Close burger by Esc
window.addEventListener('keydown', (e) => {
    if (e.key === "Escape") {
        document.querySelector(".header-links").classList.remove("open")
    }
});

// Close burger by outer click
document.getElementById("open-menu-burger").addEventListener('click', event => {
    if (event._isClickWithInMenu) return;
    document.querySelector(".header-links").classList.remove("open")
});
document.getElementById("menu-burger-button").addEventListener('click', event => {
    event._isClickWithInMenu = true;
});
document.body.addEventListener('click', event => {
    if (event._isClickWithInMenu) return;
    document.querySelector(".header-links").classList.remove("open") 
});


//Open user-menu
document.addEventListener("DOMContentLoaded", function() {
     document.getElementById("button-user").addEventListener("click", function() {
         document.getElementById("user-menu").classList.toggle("open")
     })
 });

//Open user-menu after registration
document.addEventListener("DOMContentLoaded", function() {
   document.getElementById("button-user-active").addEventListener("click", function() {
       document.getElementById("dropmenu").classList.toggle("open")
   })
});

//Close user-menu by Esc
window.addEventListener('keydown', (e) => {
    if (e.key === "Escape") {
        document.getElementById("user-menu").classList.remove("open");
    }
});

//Close user-menu by outer click
document.getElementById("button-user").addEventListener('click', event => {
    event._isClickWithInUserMenu = true;
 });

document.getElementById("user-inactive").addEventListener('click', event => {
    if (event._isClickWithInUserMenu) return;
    document.getElementById("user-menu").classList.remove("open")
});

document.body.addEventListener('click', event => {
    if (event._isClickWithInUserMenu) return;
    document.getElementById("user-menu").classList.remove("open") 
});

// Close dropmenu by outer click
document.getElementById("button-user-active").addEventListener('click', event => {
   event._isClickWithInUserMenu = true;
});

document.getElementById("user-active").addEventListener('click', event => {
   if (event._isClickWithInUserMenu) return;
   document.getElementById("dropmenu").classList.remove("open")
});

document.body.addEventListener('click', event => {
   if (event._isClickWithInUserMenu) return;
   document.getElementById("dropmenu").classList.remove("open") 
});


/*Open modale window Login in user-menu*/
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("user-menu-link-login").addEventListener("click", function() {
        document.getElementById("overlay").classList.toggle("overlay-open");
        let login = document.getElementById("modal-login");
        login.style.display = 'flex';
    })
});

/* from Login to Register*/
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("no-account-button").addEventListener("click", function() {
        let login = document.getElementById("modal-login");
        login.style.display = 'none';
        let register = document.getElementById("modal-register");
        register.style.display = 'flex';
    })
});

/*Open modale window Register in user-menu*/
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("user-menu-link-register").addEventListener("click", function() {
        document.getElementById("overlay").classList.toggle("overlay-open");
        let register = document.getElementById("modal-register");
        register.style.display = 'flex';
    })
});

/*from Register to Login*/
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("has-account-button").addEventListener("click", function() {
        let register = document.getElementById("modal-register");
        register.style.display = 'none';
        let login = document.getElementById("modal-login");
        login.style.display = 'flex';
    })
});

//Close modal window Buy a book and overlay by x
document.getElementById("modal-buy-book-close-button").addEventListener('click', event => {
    document.getElementById("overlay-buy").classList.remove("overlay-open");
    let modalBuyCard = document.getElementById("modal-buy-book");
    modalBuyCard.style.display = 'none';
});

/*Open profile modal window*/
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".profile").forEach(el => el.addEventListener("click", function() {
        document.getElementById("overlay").classList.toggle("overlay-open");
        let register = document.getElementById("modal-profile");
        register.style.display = 'flex';
    }))
});

/*Close modal windows by x */
document.querySelectorAll(".close-button").forEach(el => el.addEventListener('click', event => {
    document.getElementById("overlay").classList.remove("overlay-open");
    let div = document.querySelectorAll(".modal");
    div.forEach(el => el.style.display = 'none');
}));

// Slider About
let slideIndex = 1;
let slideTimer;

function plusSlides(n) {
    slideIndex = slideIndex + n;
    showSlides(slideIndex);
    resetSlideShow();
}

function currentSlide(n) {
    showSlides(slideIndex = n);
    resetSlideShow();
}

function showSlides(n) {
    const slides = document.getElementsByClassName("img");
    const dots = document.getElementsByClassName("circle-button");
    if (n > slides.length) {
        slideIndex = 1;
    }
    if (n < 1) {
        slideIndex = slides.length;
    }
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    slides[slideIndex - 1].style.display = "block";
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    dots[slideIndex - 1].className += " active";

    const prev = document.getElementById("leftCarret");
    const next = document.getElementById("rightCarret");
    if (prev) prev.disabled = false;
    if (next) next.disabled = false;
}

function resetSlideShow() {
    clearInterval(slideTimer);
    slideTimer = setInterval(() => {
        slideIndex += 1;
        showSlides(slideIndex);
    }, 5000);
}

showSlides(slideIndex);
resetSlideShow();

let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => showSlides(slideIndex), 100);
});

// Header video: restart 5s before the end
document.addEventListener("DOMContentLoaded", function () {
    const video = document.querySelector(".header-bg-video");
    if (!video) return;

    const skipTailSeconds = 5;

    video.addEventListener("timeupdate", function () {
        if (!Number.isFinite(video.duration) || video.duration <= skipTailSeconds) return;
        if (video.currentTime >= video.duration - skipTailSeconds) {
            video.currentTime = 0;
            video.play().catch(() => {});
        }
    });
});
