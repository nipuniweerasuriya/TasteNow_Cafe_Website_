/* Menu */
const categories = ['Breakfast', 'Lunch', 'Dinner', 'Drinks', 'Desserts'];

const menuItems = [
    { name: "Rice And Curry", description: "Sri Lankan Food", price: 5.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-1.jpg", variants: ["White Rice", "Fried Rice", "Naan"],addons: ["Dhal Curry", "Chicken Curry", "Beetroot", "Potato Fry", "Fish Curry"] },
    { name: "Milk Rice", description: "Sri Lankan Food", price: 5.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-2.jpg" },
    { name: "Hoppers", description: "Sri Lankan Food", price: 5.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-3.jpg" },
    { name: "String Hoppers", description: "Sri Lankan Food", price: 5.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-4.jpeg" },
    { name: "Avocado Toast", description: "Creamy & Fresh", price: 4.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-5.jpg" },
    { name: "Pancake", description: "Fluffy & Sweet", price: 3.99, category: "Breakfast", imageUrl: "../assets/images/Menu/Breakfast-6.jpeg" },
    { name: "Bugger With French Fries", description: "Cheesy beef burger", price: 9.99, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-1.jpeg" },
    { name: "Naan", description: "Soft & Warm", price: 9.99, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-2.jpeg" },
    { name: "Rice And Curry", description: "Sri Lankan Food", price: 9.99, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-3.jpeg" },
    { name: "Biryani", description: "Chicken Biryani", price: 8.49, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-4.jpg" },
    { name: "Kottu", description: "Tasty & Hearty", price: 7.99, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-5.jpeg" },
    { name: "pastry", description: "Flaky & Buttery", price: 10.99, category: "Lunch", imageUrl: "../assets/images/Menu/Lunch-6.jpeg" },
    // Add more items for other categories
];

let currentCategory = null;
let showAll = false;
let selectedItem = null;

document.addEventListener("DOMContentLoaded", () => {
    createCategoryButtons();
    showInitialMixedItems();
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
    const mixedItems = [];
    categories.forEach(cat => {
        const itemsInCategory = menuItems.filter(item => item.category === cat);
        if (itemsInCategory.length > 0) {
            mixedItems.push(itemsInCategory[0]);
        }
    });
    displayMenuItems(mixedItems, false);
}

function filterByCategory(category) {
    const filtered = menuItems.filter(item => item.category === category);
    const toDisplay = showAll ? filtered : filtered.slice(0, 4);
    displayMenuItems(toDisplay, filtered.length > 4 && !showAll);
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

    // Variants (radio buttons)
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

    // Add-ons (checkboxes)
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

    // Selected Variant (radio)
    let selectedVariant = "";
    const variantRadio = document.querySelector('input[name="item-variant"]:checked');
    if (variantRadio) {
        selectedVariant = variantRadio.value;
    }

    // Selected Addons (checkboxes)
    let selectedAddons = [];
    const addonCheckboxes = document.querySelectorAll('input[name="item-addon"]:checked');
    addonCheckboxes.forEach(cb => selectedAddons.push(cb.value));

    const total = (selectedItem.price * quantity).toFixed(2);

    console.log(`Added to cart: ${selectedItem.name} (${selectedVariant}) x${quantity} - Total: $${total}`);
    if (selectedAddons.length > 0) {
        console.log(`Add-ons: ${selectedAddons.join(", ")}`);
    }

    // Optional: Add to cart array or localStorage here

    closeQuantityModal();
}



function closeModal() {
    document.getElementById("modal").style.display = "none";
}

function closeQuantityModal() {
    document.getElementById("quantity-modal").style.display = "none";
    selectedItem = null;
}