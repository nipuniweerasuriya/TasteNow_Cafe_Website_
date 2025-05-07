/* Navbar Fixed Top */
window.addEventListener('scroll', function () {
    const navbar = document.getElementById('navbar');
    const topBarHeight = document.querySelector('.top-bar')?.offsetHeight || 0;

    if (window.scrollY > topBarHeight) {
        navbar?.classList.add('fixed-top', 'navbar-scrolled');
        document.body.classList.add('fixed-nav-padding');
    } else {
        navbar?.classList.remove('fixed-top', 'navbar-scrolled');
        document.body.classList.remove('fixed-nav-padding');
    }
});

/* Menu See More Btn */
document.addEventListener("DOMContentLoaded", function () {
    const menuItems = document.querySelectorAll(".menu-item");
    const seeMoreBtn = document.getElementById("see-more-btn");

    const initiallyVisible = 8;

    menuItems.forEach((item, index) => {
        if (index < initiallyVisible) {
            item.classList.add("visible");
        }
    });

    seeMoreBtn?.addEventListener("click", function () {
        menuItems.forEach(item => item.classList.add("visible"));
        seeMoreBtn.style.display = "none";
    });
});

/* Menu Adds To Cart And Cart */
let cart = [];
let currentItem = null;

document.querySelectorAll('.add-to-cart-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        const itemEl = btn.parentElement;
        const name = itemEl.querySelector('h6').innerText;
        const price = parseFloat(itemEl.querySelector('p').innerText.replace('Rs.', ''));

        currentItem = { name, price };
        document.getElementById('modal-item-name').innerText = name;
        document.getElementById('modal-item-price').value = price;
        document.getElementById('modal-qty').value = 1;
        document.getElementById('modal-request').value = '';
        document.querySelectorAll('#customization-modal input[type="checkbox"]').forEach(cb => cb.checked = false);
        document.getElementById('customization-modal').style.display = 'flex';
    });
});

function closeModal() {
    document.getElementById('customization-modal').style.display = 'none';
}

function addToCart() {
    const variantSelect = document.getElementById('modal-variant');
    const variant = variantSelect.value;
    const variantExtra = parseFloat(variantSelect.selectedOptions[0].dataset.extra);

    const checkboxes = document.querySelectorAll('#customization-modal input[type="checkbox"]:checked');
    const addOns = Array.from(checkboxes).map(cb => ({
        name: cb.value,
        price: parseFloat(cb.dataset.price)
    }));

    const specialRequest = document.getElementById('modal-request').value;
    const quantity = parseInt(document.getElementById('modal-qty').value);
    const basePrice = parseFloat(document.getElementById('modal-item-price').value);

    const item = {
        ...currentItem,
        variant,
        variantExtra,
        addOns,
        specialRequest,
        quantity,
        basePrice
    };

    cart.push(item);
    closeModal();
}

/* SignIn and SignUp */
const signinBtn = document.getElementById('signing-btn');
const dropdown = document.getElementById('form-dropdown');
const signinForm = document.getElementById('signing-form');
const signupForm = document.getElementById('signup-form');
const switchToSignup = document.getElementById('switch-to-signup');
const switchToSignin = document.getElementById('switch-to-signin');

signinBtn?.addEventListener('click', () => {
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    signinForm.style.display = "block";
    signupForm.style.display = "none";
});

switchToSignup?.addEventListener('click', (e) => {
    e.preventDefault();
    signinForm.style.display = "none";
    signupForm.style.display = "block";
});

switchToSignin?.addEventListener('click', (e) => {
    e.preventDefault();
    signupForm.style.display = "none";
    signinForm.style.display = "block";
});

/* Dashboard Dropdowns */
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

document.addEventListener('click', function (event) {
    const isInside = event.target.closest('.dropdown-wrapper') || event.target.closest('#form-dropdown') || event.target.closest('#signing-btn');
    if (!isInside) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
        dropdown.style.display = 'none';
    }
});
