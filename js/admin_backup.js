// ==========================================
// ADMIN DASHBOARD - UI/UX CONTROLLER
// ==========================================

// 1. Sidebar Toggle Logic
const menuIconButton = document.querySelector(".menu-icon-btn");
const sidebar = document.querySelector(".sidebar");

if (menuIconButton && sidebar) {
    menuIconButton.addEventListener("click", () => {
        sidebar.classList.toggle("open");
    });
}

// 2. Modal (Popup) Close Logic
const closeModalBtns = document.querySelectorAll('.modal-close');

closeModalBtns.forEach(btn => {
    btn.addEventListener('click', function(e) {
        // Prevent default form submission if it's a button inside a form
        e.preventDefault(); 
        const modal = this.closest('.modal');
        if (modal) {
            modal.classList.remove('open');
        }
    });
});

// 3. Image Preview Logic (For Add/Edit Product forms)
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('imagePreview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// 4. Reset Add Product Form (Clear data when closing)
function setDefaultValue() {
    const preview = document.querySelector(".upload-image-preview");
    if (preview) preview.src = "./image/";
    
    const nameInput = document.getElementById("book-name");
    const priceInput = document.getElementById("import-price");
    const descInput = document.getElementById("description");
    
    if (nameInput) nameInput.value = "";
    if (priceInput) priceInput.value = "";
    if (descInput) descInput.value = "";
}

// Reset form when clicking close button on Add Product modal
const addProductCloseBtn = document.querySelector(".modal-close.product-form");
if (addProductCloseBtn) {
    addProductCloseBtn.addEventListener("click", () => {
        setDefaultValue();
    });
}

// 5. Open Add Product Modal (If triggered by JS)
const btnAddProduct = document.getElementById("btn-add-product");
const addProductModal = document.querySelector(".add-product");

if (btnAddProduct && addProductModal) {
    btnAddProduct.addEventListener("click", () => {
        addProductModal.classList.add("open");
    });
}