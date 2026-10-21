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
            if (val.length > 254 || !emailRegex.test(val)) {
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
                setFieldError('password', 'Use 6 to 30 letters, numbers, or @$!%*#?&, including a letter and a number.');
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
            var isNameValid = validateName();
            var isEmailValid = validateEmail();
            var isMobileValid = validateMobile();
            var isPasswordValid = validatePassword();
            var isConfirmValid = validateConfirmPassword();
            var isCourseValid = validateCourse();
            var isYearValid = validateYear();
            var isGenderValid = validateGender();
            var isTermsValid = validateTerms();

            if (!isNameValid || !isEmailValid || !isMobileValid || !isPasswordValid || !isConfirmValid || !isCourseValid || !isYearValid || !isGenderValid || !isTermsValid) {
                e.preventDefault();
                return false;
            }

            return true;
        };
    }

    initEventsModule();
    initFaqsModule();
    initStudentsModule();
}

function initEventsModule() {
    var container = document.getElementById('events-container');
    var statusEl = document.getElementById('event-status');
    if (!container || !statusEl) return;

    var searchInput = document.getElementById('event-search');
    var categoryFilter = document.getElementById('event-category-filter');
    var formatFilter = document.getElementById('event-format-filter');
    var sortSelect = document.getElementById('event-sort');
    var paginationContainer = document.getElementById('events-pagination');
    var paginationInfo = document.getElementById('events-pagination-info');

    var allEvents = [];
    var filteredEvents = [];
    var currentPage = 1;
    var itemsPerPage = 6;

    function renderBadgeClass(category) {
        var cat = (category || '').toLowerCase();
        if (cat === 'technical') return 'tag-technical';
        if (cat === 'cultural') return 'tag-cultural';
        if (cat === 'sports') return 'tag-sports';
        return 'tag-general';
    }

    function renderEvents() {
        var startIndex = (currentPage - 1) * itemsPerPage;
        var endIndex = startIndex + itemsPerPage;
        var paginatedItems = filteredEvents.slice(startIndex, endIndex);

        container.innerHTML = '';

        if (filteredEvents.length === 0) {
            statusEl.innerText = 'No events match the selected criteria.';
            statusEl.className = 'data-status-msg status-empty';
            statusEl.style.display = 'block';
            if (paginationContainer) paginationContainer.innerHTML = '';
            if (paginationInfo) paginationInfo.innerText = 'Showing 0 of 0 events';
            return;
        }

        statusEl.style.display = 'none';

        for (var i = 0; i < paginatedItems.length; i++) {
            var ev = paginatedItems[i];
            var card = document.createElement('div');
            card.className = 'card-item';

            var badgeClass = renderBadgeClass(ev.category);
            var dateObj = new Date(ev.date);
            var formattedDate = !isNaN(dateObj.getTime()) ? dateObj.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : ev.date;

            card.innerHTML =
                '<div>' +
                    (ev.poster && /^uploads\/event-posters\/[a-f0-9]{32}\.(jpg|png|webp)$/.test(ev.poster) ? '<img src="' + ev.poster + '" alt="Event poster" style="width: 100%; max-height: 220px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">' : '') +
                    '<span class="tag-badge ' + badgeClass + '">' + (ev.category || 'General') + '</span>' +
                    '<span class="event-format-pill">' + (ev.format || 'Offline') + '</span>' +
                    '<h3>' + ev.title + '</h3>' +
                    '<p style="margin-top: 6px;"><b>DATE &amp; TIME:</b> ' + formattedDate + ' at ' + (ev.time || 'TBD') + '</p>' +
                    '<p><b>VENUE:</b> ' + (ev.venue || 'Campus') + '</p>' +
                    '<p style="font-size: 13px; color: #555; margin-top: 8px;">' + (ev.description || '') + '</p>' +
                '</div>';

            container.appendChild(card);
        }

        renderPagination();
    }

    function renderPagination() {
        if (!paginationContainer) return;
        paginationContainer.innerHTML = '';

        var totalPages = Math.ceil(filteredEvents.length / itemsPerPage);
        if (totalPages <= 1) {
            if (paginationInfo) {
                paginationInfo.innerText = 'Showing all ' + filteredEvents.length + ' events';
            }
            return;
        }

        var startIdx = (currentPage - 1) * itemsPerPage + 1;
        var endIdx = Math.min(currentPage * itemsPerPage, filteredEvents.length);
        if (paginationInfo) {
            paginationInfo.innerText = 'Showing ' + startIdx + '-' + endIdx + ' of ' + filteredEvents.length + ' events';
        }

        var prevBtn = document.createElement('button');
        prevBtn.className = 'page-btn';
        prevBtn.innerText = '« Prev';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = function() {
            if (currentPage > 1) {
                currentPage--;
                renderEvents();
                container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        };
        paginationContainer.appendChild(prevBtn);

        for (var p = 1; p <= totalPages; p++) {
            (function(pageNum) {
                var btn = document.createElement('button');
                btn.className = 'page-btn' + (pageNum === currentPage ? ' active' : '');
                btn.innerText = pageNum;
                btn.onclick = function() {
                    currentPage = pageNum;
                    renderEvents();
                    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                };
                paginationContainer.appendChild(btn);
            })(p);
        }

        var nextBtn = document.createElement('button');
        nextBtn.className = 'page-btn';
        nextBtn.innerText = 'Next »';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = function() {
            if (currentPage < totalPages) {
                currentPage++;
                renderEvents();
                container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        };
        paginationContainer.appendChild(nextBtn);
    }

    function applyFilters() {
        var query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var selectedCat = categoryFilter ? categoryFilter.value : 'all';
        var selectedFormat = formatFilter ? formatFilter.value : 'all';
        var sortValue = sortSelect ? sortSelect.value : 'date-asc';

        filteredEvents = allEvents.filter(function(item) {
            var matchSearch = !query ||
                (item.title && item.title.toLowerCase().indexOf(query) !== -1) ||
                (item.description && item.description.toLowerCase().indexOf(query) !== -1) ||
                (item.venue && item.venue.toLowerCase().indexOf(query) !== -1);

            var matchCat = selectedCat === 'all' || (item.category && item.category.toLowerCase() === selectedCat.toLowerCase());
            var matchFormat = selectedFormat === 'all' || (item.format && item.format.toLowerCase() === selectedFormat.toLowerCase());

            return matchSearch && matchCat && matchFormat;
        });

        filteredEvents.sort(function(a, b) {
            if (sortValue === 'date-asc') {
                return new Date(a.date) - new Date(b.date);
            } else if (sortValue === 'date-desc') {
                return new Date(b.date) - new Date(a.date);
            } else if (sortValue === 'title-asc') {
                return a.title.localeCompare(b.title);
            } else if (sortValue === 'title-desc') {
                return b.title.localeCompare(a.title);
            }
            return 0;
        });

        currentPage = 1;
        renderEvents();
    }

    if (searchInput) searchInput.oninput = applyFilters;
    if (categoryFilter) categoryFilter.onchange = applyFilters;
    if (formatFilter) formatFilter.onchange = applyFilters;
    if (sortSelect) sortSelect.onchange = applyFilters;

    statusEl.className = 'data-status-msg status-loading';
    statusEl.innerText = 'Loading events from the database...';
    statusEl.style.display = 'block';

    fetch('./events-api.php')
        .then(function(res) {
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            return res.json();
        })
        .then(function(data) {
            allEvents = Array.isArray(data) ? data : [];
            localStorage.setItem('cached_studenthub_events', JSON.stringify(allEvents));
            applyFilters();
        })
        .catch(function(err) {
            console.warn('Events fetch failed:', err);
            var cached = localStorage.getItem('cached_studenthub_events');
            if (cached) {
                try {
                    allEvents = JSON.parse(cached);
                    applyFilters();
                    return;
                } catch (e) {}
            }
            statusEl.className = 'data-status-msg status-error';
            statusEl.innerText = 'Failed to load events. Check the database connection and try again.';
            statusEl.style.display = 'block';
        });
}

function initFaqsModule() {
    var container = document.getElementById('faq-dynamic-container');
    var statusEl = document.getElementById('faq-status');
    if (!container || !statusEl) return;

    var searchInput = document.getElementById('faq-search');
    var categoryFilter = document.getElementById('faq-category-filter');

    var allFaqs = [];
    var filteredFaqs = [];

    function renderFaqs() {
        container.innerHTML = '';

        if (filteredFaqs.length === 0) {
            statusEl.innerText = 'No FAQs match your search.';
            statusEl.className = 'data-status-msg status-empty';
            statusEl.style.display = 'block';
            return;
        }

        statusEl.style.display = 'none';

        for (var i = 0; i < filteredFaqs.length; i++) {
            var faq = filteredFaqs[i];
            var item = document.createElement('div');
            item.className = 'faq-item';

            var btn = document.createElement('button');
            btn.className = 'faq-question';
            btn.type = 'button';
            btn.setAttribute('aria-expanded', 'false');
            btn.innerHTML = '<span><b>[' + (faq.category || 'General') + ']</b> ' + faq.question + '</span><span class="faq-icon">+</span>';

            var answerDiv = document.createElement('div');
            answerDiv.className = 'faq-answer';
            answerDiv.innerHTML = '<p>' + faq.answer + '</p>';

            btn.onclick = (function(parentEl, ansEl, iconEl) {
                return function() {
                    var isActive = parentEl.classList.contains('active');
                    if (isActive) {
                        parentEl.classList.remove('active');
                        ansEl.style.display = 'none';
                        iconEl.innerText = '+';
                    } else {
                        parentEl.classList.add('active');
                        ansEl.style.display = 'block';
                        iconEl.innerText = '−';
                    }
                };
            })(item, answerDiv, btn.querySelector('.faq-icon'));

            item.appendChild(btn);
            item.appendChild(answerDiv);
            container.appendChild(item);
        }
    }

    function applyFaqFilters() {
        var query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var selectedCat = categoryFilter ? categoryFilter.value : 'all';

        filteredFaqs = allFaqs.filter(function(item) {
            var matchSearch = !query ||
                (item.question && item.question.toLowerCase().indexOf(query) !== -1) ||
                (item.answer && item.answer.toLowerCase().indexOf(query) !== -1);

            var matchCat = selectedCat === 'all' || (item.category && item.category.toLowerCase() === selectedCat.toLowerCase());

            return matchSearch && matchCat;
        });

        renderFaqs();
    }

    if (searchInput) searchInput.oninput = applyFaqFilters;
    if (categoryFilter) categoryFilter.onchange = applyFaqFilters;

    statusEl.className = 'data-status-msg status-loading';
    statusEl.innerText = 'Loading FAQs from faqs.json...';
    statusEl.style.display = 'block';

    fetch('./faqs.json')
        .then(function(res) {
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            return res.json();
        })
        .then(function(data) {
            allFaqs = Array.isArray(data) ? data : [];
            localStorage.setItem('cached_studenthub_faqs', JSON.stringify(allFaqs));
            applyFaqFilters();
        })
        .catch(function(err) {
            console.warn('FAQs fetch failed:', err);
            var cached = localStorage.getItem('cached_studenthub_faqs');
            if (cached) {
                try {
                    allFaqs = JSON.parse(cached);
                    applyFaqFilters();
                    return;
                } catch (e) {}
            }
            statusEl.className = 'data-status-msg status-error';
            statusEl.innerText = 'Failed to load FAQs via Fetch API. If running locally via file:///, please open using VS Code Live Server or a local HTTP server.';
            statusEl.style.display = 'block';
        });
}

function initStudentsModule() {
    var container = document.getElementById('students-container');
    var statusEl = document.getElementById('student-status');
    if (!container || !statusEl) return;

    var searchInput = document.getElementById('student-search');
    var courseFilter = document.getElementById('student-course-filter');
    var yearFilter = document.getElementById('student-year-filter');
    var sortSelect = document.getElementById('student-sort');
    var paginationContainer = document.getElementById('students-pagination');
    var paginationInfo = document.getElementById('students-pagination-info');

    var allStudents = [];
    var filteredStudents = [];
    var currentPage = 1;
    var itemsPerPage = 6;

    function renderStudents() {
        var query = searchInput ? searchInput.value.trim() : '';

        if (!query) {
            container.innerHTML = '';
            statusEl.innerText = 'Type in the search box above to find student profiles by Name, ID, or Email.';
            statusEl.className = 'data-status-msg status-empty';
            statusEl.style.display = 'block';
            if (paginationContainer) paginationContainer.innerHTML = '';
            if (paginationInfo) paginationInfo.innerText = '';
            return;
        }

        var startIndex = (currentPage - 1) * itemsPerPage;
        var endIndex = startIndex + itemsPerPage;
        var paginatedItems = filteredStudents.slice(startIndex, endIndex);

        container.innerHTML = '';

        if (filteredStudents.length === 0) {
            statusEl.innerText = 'No student profiles found matching "' + query + '".';
            statusEl.className = 'data-status-msg status-empty';
            statusEl.style.display = 'block';
            if (paginationContainer) paginationContainer.innerHTML = '';
            if (paginationInfo) paginationInfo.innerText = 'Showing 0 of 0 students';
            return;
        }

        statusEl.style.display = 'none';

        for (var i = 0; i < paginatedItems.length; i++) {
            var stu = paginatedItems[i];
            var card = document.createElement('div');
            card.className = 'card-item';

            card.innerHTML =
                '<div>' +
                    '<span class="tag-badge tag-technical">' + (stu.id || 'ID') + '</span>' +
                    '<h3>' + stu.name + '</h3>' +
                    '<div class="student-card-meta"><b>COURSE:</b> ' + (stu.course || 'N/A') + '</div>' +
                    '<div class="student-card-meta"><b>YEAR:</b> ' + (stu.year || 'N/A') + ' | <b>GENDER:</b> ' + (stu.gender || 'N/A') + '</div>' +
                    '<div class="student-card-meta"><b>EMAIL:</b> ' + (stu.email || 'N/A') + '</div>' +
                    '<div class="student-card-meta"><b>MOBILE:</b> ' + (stu.mobile || 'N/A') + '</div>' +
                    '<div class="student-gpa-badge">CGPA: ' + (stu.gpa ? (typeof stu.gpa === 'number' ? stu.gpa.toFixed(2) : stu.gpa) : 'N/A') + '</div>' +
                '</div>';

            container.appendChild(card);
        }

        renderStudentPagination();
    }

    function renderStudentPagination() {
        if (!paginationContainer) return;
        paginationContainer.innerHTML = '';

        var totalPages = Math.ceil(filteredStudents.length / itemsPerPage);
        if (totalPages <= 1) {
            if (paginationInfo) {
                paginationInfo.innerText = 'Showing all ' + filteredStudents.length + ' result(s)';
            }
            return;
        }

        var startIdx = (currentPage - 1) * itemsPerPage + 1;
        var endIdx = Math.min(currentPage * itemsPerPage, filteredStudents.length);
        if (paginationInfo) {
            paginationInfo.innerText = 'Showing ' + startIdx + '-' + endIdx + ' of ' + filteredStudents.length + ' result(s)';
        }

        var prevBtn = document.createElement('button');
        prevBtn.className = 'page-btn';
        prevBtn.innerText = '« Prev';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = function() {
            if (currentPage > 1) {
                currentPage--;
                renderStudents();
                container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        };
        paginationContainer.appendChild(prevBtn);

        for (var p = 1; p <= totalPages; p++) {
            (function(pageNum) {
                var btn = document.createElement('button');
                btn.className = 'page-btn' + (pageNum === currentPage ? ' active' : '');
                btn.innerText = pageNum;
                btn.onclick = function() {
                    currentPage = pageNum;
                    renderStudents();
                    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                };
                paginationContainer.appendChild(btn);
            })(p);
        }

        var nextBtn = document.createElement('button');
        nextBtn.className = 'page-btn';
        nextBtn.innerText = 'Next »';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = function() {
            if (currentPage < totalPages) {
                currentPage++;
                renderStudents();
                container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        };
        paginationContainer.appendChild(nextBtn);
    }

    function applyStudentFilters() {
        var query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        var selectedCourse = courseFilter ? courseFilter.value : 'all';
        var selectedYear = yearFilter ? yearFilter.value : 'all';
        var sortValue = sortSelect ? sortSelect.value : 'name-asc';

        if (!query) {
            filteredStudents = [];
            renderStudents();
            return;
        }

        filteredStudents = allStudents.filter(function(item) {
            var matchSearch = (item.name && item.name.toLowerCase().indexOf(query) !== -1) ||
                (item.id && item.id.toLowerCase().indexOf(query) !== -1) ||
                (item.email && item.email.toLowerCase().indexOf(query) !== -1);

            var matchCourse = selectedCourse === 'all' || (item.course && item.course === selectedCourse);
            var matchYear = selectedYear === 'all' || (item.year && item.year === selectedYear);

            return matchSearch && matchCourse && matchYear;
        });

        filteredStudents.sort(function(a, b) {
            if (sortValue === 'name-asc') {
                return a.name.localeCompare(b.name);
            } else if (sortValue === 'name-desc') {
                return b.name.localeCompare(a.name);
            } else if (sortValue === 'gpa-desc') {
                return (Number(b.gpa) || 0) - (Number(a.gpa) || 0);
            } else if (sortValue === 'gpa-asc') {
                return (Number(a.gpa) || 0) - (Number(b.gpa) || 0);
            }
            return 0;
        });

        currentPage = 1;
        renderStudents();
    }

    if (searchInput) searchInput.oninput = applyStudentFilters;
    if (courseFilter) courseFilter.onchange = applyStudentFilters;
    if (yearFilter) yearFilter.onchange = applyStudentFilters;
    if (sortSelect) sortSelect.onchange = applyStudentFilters;

    fetch('./students-api.php')
        .then(function(res) {
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            return res.json();
        })
        .then(function(data) {
            allStudents = Array.isArray(data) ? data : [];
            localStorage.setItem('cached_studenthub_students', JSON.stringify(allStudents));
            applyStudentFilters();
        })
        .catch(function(err) {
            console.warn('Students fetch failed:', err);
            var cached = localStorage.getItem('cached_studenthub_students');
            if (cached) {
                try {
                    allStudents = JSON.parse(cached);
                    applyStudentFilters();
                    return;
                } catch (e) {}
            }
            statusEl.className = 'data-status-msg status-error';
            statusEl.innerText = 'Failed to load students. Check the database connection and try again.';
            statusEl.style.display = 'block';
        });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
} else {
    initAll();
}
