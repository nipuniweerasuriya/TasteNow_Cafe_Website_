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

/* Menu */
const categories = ['Breakfast', 'Lunch', 'Dinner', 'Drinks', 'Desserts'];

const menuItems = [
    { name: "Rice And Curry", description: "", price: 5.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-1.jpg", variants: ["White Rice", "Fried Rice", "Naan"], addons: ["Dhal Curry", "Chicken Curry", "Beetroot", "Potato Fry", "Fish Curry"] },
    { name: "Milk Rice", description: "", price: 5.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-2.jpg" },
    { name: "Hoppers", description: "", price: 5.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-3.jpg" },
    { name: "String Hoppers", description: "", price: 5.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-4.jpeg" },
    { name: "Avocado Toast", description: "", price: 4.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-5.jpg" },
    { name: "Pancake", description: "", price: 3.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-6.jpeg" },
    { name: "Yogurt parfait", description: "", price: 3.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-7.jpeg" },
    { name: "Boiled EgG With Avocado Toast", description: "", price: 3.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-8.jpeg" },
    { name: "Bugger With French Fries", description: "", price: 9.99, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-1.jpeg" },
    { name: "Naan", description: "", price: 9.99, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-2.jpeg" },
    { name: "Rice And Curry", description: "", price: 9.99, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-3.jpeg" },
    { name: "Biryani", description: "", price: 8.49, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-4.jpg" },
    { name: "Kottu", description: "", price: 7.99, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-5.jpeg" },
    { name: "pastry", description: "", price: 10.99, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-6.jpeg" },
    { name: "Kottu", description: "", price: 7.99, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-7.jpg" },
    { name: "Dumplings", description: "", price: 7.99, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-8.jpeg" },
    { name: "Kottu", description: "", price: 7.99, category: "Dinner", imageUrl: "../assets/images/Menu/Dinner-1.jpg" },
    { name: "Beef With Fry Vegetables", description: "", price: 7.99, category: "Dinner", imageUrl: "../assets/images/Menu/Dinner-2.jpeg" },
    { name: "Lawa Cake", description: "", price: 7.99, category: "Desserts", imageUrl: "../assets/images/Menu/Dessert-7.jpeg" },
    { name: "Yogurt", description: "", price: 7.99, category: "Desserts", imageUrl: "../assets/images/Menu/Dessert-5.webp" },
    { name: "Milk", description: "", price: 7.99, category: "Drinks", imageUrl: "../assets/images/Menu/Drinks-2.webp" },
    { name: "Bubble Tea", description: "", price: 7.99, category: "Drinks", imageUrl: "../assets/images/Menu/Drinks-1.webp" },
    { name: "Pizza", description: "", price: 7.99, category: "Fast Foods", imageUrl: "../assets/images/Menu/Fast food-4.jpeg" },
    { name: "Bugger", description: "", price: 7.99, category: "Fast Foods", imageUrl: "../assets/images/Menu/Fast food-4.jpeg" },
];

let currentCategory = null;
let showAll = false;
let selectedItem = null;
let cartItems = [];

document.addEventListener("DOMContentLoaded", () => {
    createCategoryButtons();
    showInitialMixedItems();
    setupCartToggle(); // handle showing/hiding cart
});

function createCategoryButtons() {
    const container = document.getElementById("category-container");
    container.innerHTML = "";
    categories.forEach(category => {
        const button = document.createElement("button");
        button.className = "category-button";
        button.textContent = category;
        button.onclick = () => {
            currentCategory = category;
            showAll = false;
            filterByCategory(category);
        };
        container.appendChild(button);
    });
}

function showInitialMixedItems() {
    const allItems = [...menuItems];
    const shuffledItems = allItems.sort(() => 0.5 - Math.random());
    const mixedItems = shuffledItems.slice(0, 6);
    displayMenuItems(mixedItems, false);
}

function filterByCategory(category) {
    const filtered = menuItems.filter(item => item.category === category);
    const toDisplay = showAll ? filtered : filtered.slice(0, 6);
    displayMenuItems(toDisplay, filtered.length > 6 && !showAll);
}

function displayMenuItems(items, showMoreButton) {
    const container = document.getElementById("menu-container");
    container.innerHTML = "";

    items.forEach(item => {
        const div = document.createElement("div");
        div.className = "menu-item";

        div.innerHTML = `
          <div class="item-info" onclick='showItemDetails(${JSON.stringify(item)})'>
            <img src="${item.imageUrl}" alt="${item.name}" class="menu-image" />
            <h6>${item.name}</h6>
            <p>Price: $${item.price}</p>
          </div>
          <button class="add-to-cart-btn" onclick='addToCart(${JSON.stringify(item)})'>Add to Cart</button>
        `;
        container.appendChild(div);
    });

    if (showMoreButton) {
        const seeMore = document.createElement("button");
        seeMore.textContent = "See More";
        seeMore.className = "see-more-btn";
        seeMore.onclick = () => {
            showAll = true;
            if (currentCategory) {
                filterByCategory(currentCategory);
            } else {
                displayMenuItems(menuItems, false);
            }
        };
        container.appendChild(seeMore);
    }
}

function showItemDetails(itemData) {
    const item = typeof itemData === "string" ? JSON.parse(itemData) : itemData;

    const modal = document.getElementById("modal");
    const modalContent = document.getElementById("modal-content");

    modalContent.innerHTML = `
        <span class="close" onclick="closeModal()">&times;</span>
        <img src="${item.imageUrl}" alt="${item.name}" class="menu-image" />
        <h5>${item.name}</h5>
        <p>${item.description}</p>
        <p>Price: $${item.price}</p>
      `;

    modal.style.display = "block";
}

function addToCart(itemData) {
    selectedItem = itemData;
    document.getElementById("quantity-input").value = 1;

    const optionsContainer = document.getElementById("item-options");
    optionsContainer.innerHTML = "";

    if (itemData.variants && itemData.variants.length > 0) {
        const variantLabel = document.createElement("label");
        variantLabel.textContent = "Choose a type:";
        optionsContainer.appendChild(variantLabel);

        itemData.variants.forEach(variant => {
            const radio = document.createElement("input");
            radio.type = "radio";
            radio.name = "item-variant";
            radio.value = variant;

            const span = document.createElement("span");
            span.textContent = variant;

            const div = document.createElement("div");
            div.appendChild(radio);
            div.appendChild(span);

            optionsContainer.appendChild(div);
        });
    }

    if (itemData.addons && itemData.addons.length > 0) {
        const addonLabel = document.createElement("label");
        addonLabel.textContent = "Add extras:";
        optionsContainer.appendChild(addonLabel);

        itemData.addons.forEach(addon => {
            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.value = addon;
            checkbox.name = "item-addon";

            const span = document.createElement("span");
            span.textContent = addon;

            const div = document.createElement("div");
            div.appendChild(checkbox);
            div.appendChild(span);

            optionsContainer.appendChild(div);
        });
    }

    document.getElementById("quantity-modal").style.display = "block";
}

function confirmAddToCart() {
    const quantity = parseInt(document.getElementById("quantity-input").value);
    if (isNaN(quantity) || quantity < 1) {
        alert("Please enter a valid quantity.");
        return;
    }

    let selectedVariant = "";
    const variantRadio = document.querySelector('input[name="item-variant"]:checked');
    if (variantRadio) {
        selectedVariant = variantRadio.value;
    }

    let selectedAddons = [];
    const addonCheckboxes = document.querySelectorAll('input[name="item-addon"]:checked');
    addonCheckboxes.forEach(cb => selectedAddons.push(cb.value));

    const itemToAdd = {
        ...selectedItem,
        quantity,
        variant: selectedVariant,
        addons: selectedAddons
    };

    cartItems.push(itemToAdd);
    updateCartDisplay();
    closeQuantityModal();
}

function updateCartDisplay() {
    const cartContainer = document.querySelector(".cart-items");
    cartContainer.innerHTML = "";

    cartItems.forEach((item, index) => {
        const addons = item.addons.length > 0 ? `<div>Add-ons: ${item.addons.join(", ")}</div>` : "";

        const cartItem = document.createElement("div");
        cartItem.className = "cart-item";
        cartItem.innerHTML = `
            <div class="item-details">
                <span>${item.name} ${item.variant ? `(${item.variant})` : ""}</span>
                ${addons}
                <div>
                    Quantity: 
                    <button onclick="changeQuantity(${index}, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="changeQuantity(${index}, 1)">+</button>
                </div>
                <div>Price: $${(item.price * item.quantity).toFixed(2)}</div>
            </div>
        `;
        cartContainer.appendChild(cartItem);
    });

    updateCartTotal();
}

function changeQuantity(index, delta) {
    cartItems[index].quantity += delta;
    if (cartItems[index].quantity < 1) {
        cartItems.splice(index, 1);
    }
    updateCartDisplay();
}

function updateCartTotal() {
    const total = cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.querySelector(".cart-total").textContent = `Total: $${total.toFixed(2)}`;
}

function closeModal() {
    document.getElementById("modal").style.display = "none";
}

function closeQuantityModal() {
    document.getElementById("quantity-modal").style.display = "none";
    selectedItem = null;
}

function setupCartToggle() {
    const cartIcon = document.querySelector(".icon-cart");
    const cartSidebar = document.querySelector(".cart-sidebar");

    cartIcon.addEventListener("click", () => {
        cartSidebar.classList.toggle("open");
    });
}
