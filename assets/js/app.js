document.addEventListener('DOMContentLoaded', () => {
    const baseUrl = window.BASE_URL || '';
    const toast = document.getElementById('toast');

    function showToast(message) {
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('show');
        clearTimeout(showToast.timeout);
        showToast.timeout = setTimeout(() => toast.classList.remove('show'), 2400);
    }

    async function postJson(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            body: data
        });
        return response.json();
    }

    const searchForm = document.getElementById('property-search');
    const propertyList = document.getElementById('property-list');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const bookingForm = document.getElementById('booking-form');
    const contactForm = document.getElementById('contact-form');
    const logoutLink = document.getElementById('logout-link');

    if (searchForm && propertyList) {
        searchForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(searchForm);
            const params = new URLSearchParams(formData).toString();
            const response = await fetch(`${baseUrl}/api/properties.php?${params}`);
            const data = await response.json();

            if (!data.length) {
                propertyList.innerHTML = '<div class="panel card">No properties matched your search.</div>';
                return;
            }

            propertyList.innerHTML = data.map(property => `
                <article class="card property-card">
                    <img src="${property.image_url}" alt="${property.title}">
                    <div class="card-body">
                        <div class="meta-row">
                            <span>${property.location}</span>
                            <span>${property.bedrooms} bed</span>
                        </div>
                        <h3>${property.title}</h3>
                        <p>${property.description}</p>
                        <div class="card-footer">
                            <strong>$${Number(property.price_per_night).toFixed(2)}/night</strong>
                            <a class="btn btn-small" href="${baseUrl}/property.php?id=${property.id}">View</a>
                        </div>
                    </div>
                </article>
            `).join('');
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            formData.append('action', 'login');

            const result = await postJson(`${baseUrl}/api/auth.php`, formData);
            showToast(result.message);
            if (result.ok) window.location.reload();
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);
            formData.append('action', 'register');

            const result = await postJson(`${baseUrl}/api/auth.php`, formData);
            showToast(result.message);
            if (result.ok) window.location.reload();
        });
    }

    if (bookingForm) {
        bookingForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(bookingForm);
            const result = await postJson(`${baseUrl}/api/bookings.php`, formData);
            showToast(result.message);
            if (result.ok) window.location.href = `${baseUrl}/dashboard.php`;
        });
    }

    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(contactForm);
            const result = await postJson(`${baseUrl}/api/contact.php`, formData);
            showToast(result.message);
            if (result.ok) contactForm.reset();
        });
    }

    if (logoutLink) {
        logoutLink.addEventListener('click', async (e) => {
            e.preventDefault();
            const result = await postJson(`${baseUrl}/api/auth.php`, new URLSearchParams({ action: 'logout' }));
            showToast(result.message);
            if (result.ok) window.location.reload();
        });
    }
});
