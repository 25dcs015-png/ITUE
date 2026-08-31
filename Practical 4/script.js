function initAll() {
    var themeToggleBtn = document.getElementById('theme-toggle');
    var themeStatus = document.getElementById('theme-status');
    var themeLink = document.getElementById('theme-style');

    function applyTheme(theme) {
        if (themeLink) {
            if (theme === 'dark') {
                themeLink.setAttribute('href', 'style2.css');
            } else {
                themeLink.setAttribute('href', 'style.css');
            }
        }
        if (themeStatus) {
            themeStatus.innerText = theme === 'dark' ? 'DARK' : 'LIGHT';
        }
    }

    var savedTheme = localStorage.getItem('studenthub_theme') || 'light';
    applyTheme(savedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.onclick = function(e) {
            e.preventDefault();
            var current = localStorage.getItem('studenthub_theme') || 'light';
            var nextTheme = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('studenthub_theme', nextTheme);
            applyTheme(nextTheme);
        };
    }

    var banner = document.getElementById('notification-banner');
    var closeBannerBtn = document.getElementById('close-notification');

    if (closeBannerBtn && banner) {
        closeBannerBtn.onclick = function() {
            banner.style.display = 'none';
        };
    }

    var hamburgerBtn = document.getElementById('hamburger-btn');
    var hamburgerMenu = document.getElementById('hamburger-menu');

    if (hamburgerBtn && hamburgerMenu) {
        hamburgerBtn.onclick = function(e) {
            e.stopPropagation();
            if (hamburgerMenu.style.display === 'flex' || hamburgerMenu.classList.contains('open')) {
                hamburgerMenu.style.display = 'none';
                hamburgerMenu.classList.remove('open');
                hamburgerBtn.classList.remove('active');
            } else {
                hamburgerMenu.style.display = 'flex';
                hamburgerMenu.classList.add('open');
                hamburgerBtn.classList.add('active');
            }
        };

        document.onclick = function(e) {
            if (!hamburgerBtn.contains(e.target) && !hamburgerMenu.contains(e.target)) {
                hamburgerMenu.style.display = 'none';
                hamburgerMenu.classList.remove('open');
                hamburgerBtn.classList.remove('active');
            }
        };
    }

    var slides = document.querySelectorAll('.slide');
    var dots = document.querySelectorAll('.slider-dot');
    var prevBtn = document.getElementById('prev-slide');
    var nextBtn = document.getElementById('next-slide');
    var currentSlide = 0;

    function showSlide(index) {
        if (slides.length === 0) return;
        if (index >= slides.length) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = slides.length - 1;
        } else {
            currentSlide = index;
        }

        for (var i = 0; i < slides.length; i++) {
            slides[i].style.display = 'none';
            slides[i].classList.remove('active');
            if (dots[i]) {
                dots[i].classList.remove('active');
            }
        }

        slides[currentSlide].style.display = 'block';
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) {
            dots[currentSlide].classList.add('active');
        }
    }

    if (prevBtn) {
        prevBtn.onclick = function(e) {
            e.preventDefault();
            showSlide(currentSlide - 1);
        };
    }

    if (nextBtn) {
        nextBtn.onclick = function(e) {
            e.preventDefault();
            showSlide(currentSlide + 1);
        };
    }

    for (var i = 0; i < dots.length; i++) {
        (function(index) {
            dots[index].onclick = function(e) {
                e.preventDefault();
                showSlide(index);
            };
        })(i);
    }

    if (slides.length > 0) {
        showSlide(0);
    }

    var openModalBtn = document.getElementById('open-modal');
    var noticeModal = document.getElementById('notice-modal');
    var closeModalBtn = document.getElementById('close-modal');
    var modalCloseFooterBtn = document.querySelector('.modal-close-btn');

    if (openModalBtn && noticeModal) {
        openModalBtn.onclick = function(e) {
            e.preventDefault();
            noticeModal.style.display = 'flex';
        };
    }

    if (closeModalBtn && noticeModal) {
        closeModalBtn.onclick = function() {
            noticeModal.style.display = 'none';
        };
    }

    if (modalCloseFooterBtn && noticeModal) {
        modalCloseFooterBtn.onclick = function() {
            noticeModal.style.display = 'none';
        };
    }

    if (noticeModal) {
        noticeModal.onclick = function(e) {
            if (e.target === noticeModal || e.target.classList.contains('modal-overlay')) {
                noticeModal.style.display = 'none';
            }
        };
    }

    document.onkeydown = function(e) {
        if (e.key === 'Escape' && noticeModal) {
            noticeModal.style.display = 'none';
        }
    };

    var faqQuestions = document.querySelectorAll('.faq-question');
    for (var j = 0; j < faqQuestions.length; j++) {
        faqQuestions[j].onclick = function() {
            var parent = this.parentElement;
            var answer = parent.querySelector('.faq-answer');
            var icon = this.querySelector('.faq-icon');

            if (parent.classList.contains('active')) {
                parent.classList.remove('active');
                if (answer) answer.style.display = 'none';
                if (icon) icon.innerText = '+';
            } else {
                parent.classList.add('active');
                if (answer) answer.style.display = 'block';
                if (icon) icon.innerText = '−';
            }
        };
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
} else {
    initAll();
}
