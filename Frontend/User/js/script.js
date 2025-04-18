/* Navbar Fixed Top */
    window.addEventListener('scroll', function () {
        const navbar = document.getElementById('navbar');
        const topBarHeight = document.querySelector('.top-bar').offsetHeight;

        if (window.scrollY > topBarHeight) {
            navbar.classList.add('fixed-top', 'navbar-scrolled');
            document.body.classList.add('fixed-nav-padding');
        } else {
            navbar.classList.remove('fixed-top', 'navbar-scrolled');
            document.body.classList.remove('fixed-nav-padding');
        }
    });

/* Menu See More Btn */
document.addEventListener("DOMContentLoaded", function () {
    const menuItems = document.querySelectorAll(".menu-item");
    const seeMoreBtn = document.getElementById("see-more-btn");

    const initiallyVisible = 8;

    // Show the first 8 items
    menuItems.forEach((item, index) => {
        if (index < initiallyVisible) {
            item.classList.add("visible");
        }
    });

    seeMoreBtn.addEventListener("click", function () {
        menuItems.forEach(item => item.classList.add("visible"));
        seeMoreBtn.style.display = "none"; // Hide the button after clicked
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
    renderCart();
}

function renderCart() {
    const cartItemsDiv = document.getElementById('cart-items');
    cartItemsDiv.innerHTML = '';
    let total = 0;

    cart.forEach((item, index) => {
        const addOnsNames = item.addOns.map(a => a.name).join(', ');
        const addOnsTotal = item.addOns.reduce((sum, a) => sum + a.price, 0);
        const itemTotal = (item.basePrice + item.variantExtra + addOnsTotal) * item.quantity;
        total += itemTotal;

        const itemHTML = `
      <div class="cart-item">
        <p><strong>${item.name}</strong> (${item.variant})</p>
        <p>Add-ons: ${addOnsNames || 'None'}</p>
        <p>Note: ${item.specialRequest || 'None'}</p>
        <p>Qty: 
          <button onclick="changeQty(${index}, -1)">-</button>
          ${item.quantity}
          <button onclick="changeQty(${index}, 1)">+</button>
        </p>
        <p>Total: Rs.${itemTotal.toFixed(2)}</p>
      </div>
    `;
        cartItemsDiv.innerHTML += itemHTML;
    });

    document.getElementById('cart-total').innerText = `Total: Rs.${total.toFixed(2)}`;
}

function changeQty(index, delta) {
    cart[index].quantity += delta;
    if (cart[index].quantity <= 0) cart.splice(index, 1);
    renderCart();
}
function toggleCart() {
    const cartSection = document.getElementById('cart-section');
    // Toggle the 'show' class which will trigger the CSS transition
    cartSection.classList.toggle('show');
    // Toggle the display style for backward compatibility
    if (cartSection.style.display === 'none' || cartSection.style.display === '') {
        cartSection.style.display = 'block';
    } else {
        cartSection.style.display = 'none';
    }
}




