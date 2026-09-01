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

    var regForm = document.getElementById('registration-form');
    if (regForm) {
        var nameInput = document.getElementById('name');
        var emailInput = document.getElementById('email');
        var mobileInput = document.getElementById('mobile');
        var passwordInput = document.getElementById('password');
        var confirmPasswordInput = document.getElementById('confirm_password');
        var courseSelect = document.getElementById('course');
        var yearSelect = document.getElementById('year');
        var termsCheckbox = document.getElementById('terms');

        function setFieldError(fieldId, message) {
            var errorEl = document.getElementById(fieldId + '-error');
            var inputEl = document.getElementById(fieldId);
            if (errorEl) {
                errorEl.innerText = message;
                errorEl.classList.add('visible');
            }
            if (inputEl) {
                inputEl.classList.add('input-invalid');
            }
        }

        function clearFieldError(fieldId) {
            var errorEl = document.getElementById(fieldId + '-error');
            var inputEl = document.getElementById(fieldId);
            if (errorEl) {
                errorEl.innerText = '';
                errorEl.classList.remove('visible');
            }
            if (inputEl) {
                inputEl.classList.remove('input-invalid');
            }
        }

        function getSelectedGender() {
            var radios = document.getElementsByName('gender');
            for (var k = 0; k < radios.length; k++) {
                if (radios[k].checked) {
                    return radios[k].value;
                }
            }
            return '';
        }

        function validateName() {
            var val = nameInput ? nameInput.value.trim() : '';
            var nameRegex = /^[A-Za-z\s]{2,50}$/;
            if (!val) {
                setFieldError('name', 'Name is required.');
                return false;
            }
            if (!/^[A-Za-z\s]+$/.test(val)) {
                setFieldError('name', 'Name can only contain alphabetic characters and spaces.');
                return false;
            }
            if (!nameRegex.test(val)) {
                setFieldError('name', 'Name must be between 2 and 50 characters.');
                return false;
            }
            clearFieldError('name');
            return true;
        }

        function validateEmail() {
            var val = emailInput ? emailInput.value.trim() : '';
            var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!val) {
                setFieldError('email', 'Email is required.');
                return false;
            }
            if (!emailRegex.test(val)) {
                setFieldError('email', 'Please enter a valid email address.');
                return false;
            }
            clearFieldError('email');
            return true;
        }

        function validateMobile() {
            var val = mobileInput ? mobileInput.value.trim() : '';
            var mobileRegex = /^[6-9]\d{9}$/;
            if (!val) {
                setFieldError('mobile', 'Mobile number is required.');
                return false;
            }
            if (!mobileRegex.test(val)) {
                setFieldError('mobile', 'Please enter a valid 10-digit mobile number.');
                return false;
            }
            clearFieldError('mobile');
            return true;
        }

        function validatePassword() {
            var val = passwordInput ? passwordInput.value : '';
            var passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{6,30}$/;
            if (!val) {
                setFieldError('password', 'Password is required.');
                return false;
            }
            if (val.length < 6) {
                setFieldError('password', 'Password must be at least 6 characters.');
                return false;
            }
            if (!passwordRegex.test(val)) {
                setFieldError('password', 'Password must contain at least one letter and one number.');
                return false;
            }
            clearFieldError('password');
            return true;
        }

        function validateConfirmPassword() {
            var pVal = passwordInput ? passwordInput.value : '';
            var cpVal = confirmPasswordInput ? confirmPasswordInput.value : '';
            if (!cpVal) {
                setFieldError('confirm_password', 'Please confirm your password.');
                return false;
            }
            if (cpVal !== pVal) {
                setFieldError('confirm_password', 'Passwords do not match.');
                return false;
            }
            clearFieldError('confirm_password');
            return true;
        }

        function validateCourse() {
            var val = courseSelect ? courseSelect.value : '';
            if (!val) {
                setFieldError('course', 'Please select a course.');
                return false;
            }
            clearFieldError('course');
            return true;
        }

        function validateYear() {
            var val = yearSelect ? yearSelect.value : '';
            if (!val) {
                setFieldError('year', 'Please select an academic year.');
                return false;
            }
            clearFieldError('year');
            return true;
        }

        function validateGender() {
            var val = getSelectedGender();
            if (!val) {
                setFieldError('gender', 'Please select a gender.');
                return false;
            }
            clearFieldError('gender');
            return true;
        }

        function validateTerms() {
            var isChecked = termsCheckbox ? termsCheckbox.checked : false;
            if (!isChecked) {
                setFieldError('terms', 'You must accept the Terms and Conditions.');
                return false;
            }
            clearFieldError('terms');
            return true;
        }

        if (nameInput) {
            nameInput.oninput = function() {
                if (nameInput.value.trim()) validateName();
            };
            nameInput.onblur = validateName;
        }
        if (emailInput) {
            emailInput.oninput = function() {
                if (emailInput.value.trim()) validateEmail();
            };
            emailInput.onblur = validateEmail;
        }
        if (mobileInput) {
            mobileInput.oninput = function() {
                if (mobileInput.value.trim()) validateMobile();
            };
            mobileInput.onblur = validateMobile;
        }
        if (passwordInput) {
            passwordInput.oninput = function() {
                if (passwordInput.value) validatePassword();
                if (confirmPasswordInput && confirmPasswordInput.value) validateConfirmPassword();
            };
            passwordInput.onblur = validatePassword;
        }
        if (confirmPasswordInput) {
            confirmPasswordInput.oninput = function() {
                if (confirmPasswordInput.value) validateConfirmPassword();
            };
            confirmPasswordInput.onblur = validateConfirmPassword;
        }
        if (courseSelect) {
            courseSelect.onchange = validateCourse;
        }
        if (yearSelect) {
            yearSelect.onchange = validateYear;
        }
        var genderRadios = document.getElementsByName('gender');
        for (var g = 0; g < genderRadios.length; g++) {
            genderRadios[g].onchange = validateGender;
        }
        if (termsCheckbox) {
            termsCheckbox.onchange = validateTerms;
        }

        regForm.onsubmit = function(e) {
            e.preventDefault();

            var isNameValid = validateName();
            var isEmailValid = validateEmail();
            var isMobileValid = validateMobile();
            var isPasswordValid = validatePassword();
            var isConfirmValid = validateConfirmPassword();
            var isCourseValid = validateCourse();
            var isYearValid = validateYear();
            var isGenderValid = validateGender();
            var isTermsValid = validateTerms();

            if (isNameValid && isEmailValid && isMobileValid && isPasswordValid && isConfirmValid && isCourseValid && isYearValid && isGenderValid && isTermsValid) {
                var studentData = {
                    name: nameInput.value.trim(),
                    email: emailInput.value.trim(),
                    mobile: mobileInput.value.trim(),
                    course: courseSelect.value,
                    year: yearSelect.value,
                    gender: getSelectedGender()
                };
                localStorage.setItem('studenthub_registered_student', JSON.stringify(studentData));
                window.location.href = 'profile.html';
            }
        };
    }

    var profileName = document.getElementById('profile-name');
    var profileEmail = document.getElementById('profile-email');
    var profileMobile = document.getElementById('profile-mobile');
    var profileCourse = document.getElementById('profile-course');
    var profileYear = document.getElementById('profile-year');
    var profileGender = document.getElementById('profile-gender');

    if (profileName || profileEmail || profileMobile || profileCourse || profileYear || profileGender) {
        var storedStudentStr = localStorage.getItem('studenthub_registered_student');
        if (storedStudentStr) {
            try {
                var storedStudent = JSON.parse(storedStudentStr);
                if (storedStudent) {
                    if (profileName && storedStudent.name) {
                        profileName.innerText = storedStudent.name;
                    }
                    if (profileEmail && storedStudent.email) {
                        profileEmail.innerText = storedStudent.email;
                    }
                    if (profileMobile && storedStudent.mobile) {
                        profileMobile.innerText = storedStudent.mobile;
                    }
                    if (profileCourse && storedStudent.course) {
                        profileCourse.innerText = storedStudent.course;
                    }
                    if (profileYear && storedStudent.year) {
                        profileYear.innerText = storedStudent.year;
                    }
                    if (profileGender && storedStudent.gender) {
                        profileGender.innerText = storedStudent.gender;
                    }
                }
            } catch (err) {}
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
} else {
    initAll();
}
