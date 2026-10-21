(function() {
    var form = document.getElementById('student-management-form');
    var filterForm = document.getElementById('student-management-filter');
    var searchInput = document.getElementById('student-management-search');
    var statusFilter = document.getElementById('student-management-status');
    var tableBody = document.getElementById('student-management-table-body');
    var listStatus = document.getElementById('student-management-list-status');
    var message = document.getElementById('student-management-message');
    var paginationInfo = document.getElementById('student-management-pagination-info');
    var pageInfo = document.getElementById('student-management-page-info');
    var pagination = document.getElementById('student-management-pagination');

    if (!form || !filterForm || !tableBody || !listStatus) return;

    var csrfToken = form.querySelector('[name="csrf_token"]').value;
    var currentPage = 1;
    var totalPages = 1;
    var studentsById = {};
    var searchTimer = null;
    var requestController = null;

    function showMessage(text, isError) {
        message.textContent = text;
        message.className = 'data-status-msg ' + (isError ? 'status-error' : 'status-success');
        message.style.display = 'block';
    }

    function setLoading(text) {
        listStatus.textContent = text;
        listStatus.className = 'data-status-msg status-loading';
        listStatus.style.display = 'block';
        tableBody.replaceChildren();
    }

    async function requestJson(url, options) {
        var response = await fetch(url, options);
        var payload;
        try {
            payload = await response.json();
        } catch (error) {
            throw new Error('The server returned an invalid response. Please try again.');
        }
        if (!response.ok || !payload || payload.ok === false) {
            throw new Error(payload && payload.error ? payload.error : 'The request could not be completed.');
        }
        return payload;
    }

    function apiUrl(page) {
        var params = new URLSearchParams({
            admin: '1',
            search: searchInput.value.trim(),
            status: statusFilter.value,
            page: String(page)
        });
        return 'students-api.php?' + params.toString();
    }

    function appendCell(row, value) {
        var cell = document.createElement('td');
        cell.textContent = value === null || value === undefined || value === '' ? '—' : String(value);
        row.appendChild(cell);
    }

    function appendActionButton(container, label, action, studentId, className) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = className || 'page-btn';
        button.textContent = label;
        button.dataset.action = action;
        button.dataset.studentId = String(studentId);
        container.appendChild(button);
    }

    function renderRows(students) {
        tableBody.replaceChildren();
        studentsById = {};
        if (!students.length) {
            listStatus.textContent = 'No students match this search and status filter.';
            listStatus.className = 'data-status-msg status-empty';
            listStatus.style.display = 'block';
            return;
        }
        listStatus.style.display = 'none';

        students.forEach(function(student) {
            studentsById[String(student.student_id)] = student;
            var row = document.createElement('tr');
            appendCell(row, student.student_code);
            appendCell(row, student.name);
            appendCell(row, student.email);
            appendCell(row, student.mobile);
            appendCell(row, student.course);
            appendCell(row, student.year);
            appendCell(row, student.gender);
            appendCell(row, student.gpa);
            appendCell(row, student.role);
            appendCell(row, student.status);

            var actions = document.createElement('td');
            if (student.status === 'active') {
                appendActionButton(actions, 'EDIT', 'edit', student.student_id, 'page-btn');
            }
            appendActionButton(
                actions,
                student.status === 'deleted' ? 'RESTORE' : 'DELETE',
                student.status === 'deleted' ? 'restore' : 'delete',
                student.student_id,
                'page-btn active'
            );
            row.appendChild(actions);
            tableBody.appendChild(row);
        });
    }

    function renderPagination(meta) {
        currentPage = meta.page;
        totalPages = meta.total_pages;
        paginationInfo.textContent = 'Showing ' + (meta.total ? ((currentPage - 1) * meta.page_size + 1) : 0) +
            '-' + Math.min(currentPage * meta.page_size, meta.total) + ' of ' + meta.total + ' matching records.';
        pageInfo.textContent = 'Page ' + currentPage + ' of ' + totalPages + '.';
        pagination.replaceChildren();

        if (currentPage > 1) {
            var previous = document.createElement('button');
            previous.type = 'button';
            previous.className = 'page-btn';
            previous.textContent = '« Previous';
            previous.dataset.page = String(currentPage - 1);
            pagination.appendChild(previous);
        }
        if (currentPage < totalPages) {
            var next = document.createElement('button');
            next.type = 'button';
            next.className = 'page-btn';
            next.textContent = 'Next »';
            next.dataset.page = String(currentPage + 1);
            pagination.appendChild(next);
        }
    }

    async function loadStudents(page) {
        if (requestController) requestController.abort();
        requestController = new AbortController();
        setLoading('Loading student records...');
        try {
            var payload = await requestJson(apiUrl(page), {
                headers: { 'Accept': 'application/json' },
                signal: requestController.signal
            });
            renderRows(payload.data);
            renderPagination(payload.meta);
        } catch (error) {
            if (error.name === 'AbortError') return;
            listStatus.textContent = error.message;
            listStatus.className = 'data-status-msg status-error';
            listStatus.style.display = 'block';
            paginationInfo.textContent = '';
            pageInfo.textContent = '';
            pagination.replaceChildren();
        }
    }

    function resetForm() {
        form.reset();
        form.querySelector('[name="student_id"]').value = '0';
        form.querySelector('[name="action"]').value = 'save';
        form.querySelector('button[type="submit"]').textContent = 'ADD STUDENT';
        var cancel = document.getElementById('student-management-cancel');
        if (cancel) cancel.remove();
    }

    function fillForm(student) {
        form.querySelector('[name="student_id"]').value = student.student_id;
        ['name', 'email', 'mobile', 'course', 'year', 'gender', 'gpa'].forEach(function(field) {
            form.querySelector('[name="' + field + '"]').value = student[field] === null ? '' : student[field];
        });
        form.querySelector('button[type="submit"]').textContent = 'UPDATE STUDENT';
        if (!document.getElementById('student-management-cancel')) {
            var cancel = document.createElement('button');
            cancel.type = 'button';
            cancel.id = 'student-management-cancel';
            cancel.className = 'page-btn';
            cancel.textContent = 'CANCEL';
            form.querySelector('button[type="submit"]').after(cancel);
            cancel.addEventListener('click', resetForm);
        }
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        if (!form.reportValidity()) return;

        var studentId = Number(form.querySelector('[name="student_id"]').value);
        var data = Object.fromEntries(new FormData(form).entries());
        delete data.csrf_token;
        delete data.action;
        showMessage(studentId ? 'Updating student record...' : 'Adding student record...', false);
        var submit = form.querySelector('button[type="submit"]');
        submit.disabled = true;
        try {
            var payload = await requestJson('students-api.php?admin=1', {
                method: studentId ? 'PUT' : 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify(data)
            });
            showMessage(payload.message, false);
            resetForm();
            currentPage = 1;
            await loadStudents(currentPage);
        } catch (error) {
            showMessage(error.message, true);
        } finally {
            submit.disabled = false;
        }
    });

    tableBody.addEventListener('click', async function(event) {
        var button = event.target.closest('button[data-action]');
        if (!button) return;
        var student = studentsById[button.dataset.studentId];
        if (!student) return;
        if (button.dataset.action === 'edit') {
            fillForm(student);
            return;
        }

        var deleting = button.dataset.action === 'delete';
        if (deleting && !window.confirm('Move this student to deleted records?')) return;
        button.disabled = true;
        showMessage(deleting ? 'Deleting student record...' : 'Restoring student record...', false);
        try {
            var payload = await requestJson('students-api.php?admin=1', {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ student_id: student.student_id, action: button.dataset.action })
            });
            showMessage(payload.message, false);
            await loadStudents(currentPage);
        } catch (error) {
            showMessage(error.message, true);
            button.disabled = false;
        }
    });

    pagination.addEventListener('click', function(event) {
        var button = event.target.closest('button[data-page]');
        if (button) loadStudents(Number(button.dataset.page));
    });

    filterForm.addEventListener('submit', function(event) {
        event.preventDefault();
        currentPage = 1;
        loadStudents(currentPage);
    });

    searchInput.addEventListener('input', function() {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(function() {
            currentPage = 1;
            loadStudents(currentPage);
        }, 300);
    });

    statusFilter.addEventListener('change', function() {
        currentPage = 1;
        loadStudents(currentPage);
    });

    var clearButton = filterForm.querySelector('a[href="student-management.php"]');
    if (clearButton) {
        clearButton.addEventListener('click', function(event) {
            event.preventDefault();
            searchInput.value = '';
            statusFilter.value = 'active';
            currentPage = 1;
            loadStudents(currentPage);
        });
    }

    loadStudents(1);
})();
