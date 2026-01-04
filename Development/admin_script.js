document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. Sidebar Toggle for Mobile ---
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('#menu-toggle'); 

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }

    // --- 2. Active Link Highlighting ---
    const currentLocation = location.href;
    const menuItem = document.querySelectorAll('.sidebar-menu li a');
    
    menuItem.forEach(item => {
        if (item.href === currentLocation) {
            item.classList.add("active");
        }
    });

    // --- 3. Delete Confirmation ---
    const deleteButtons = document.querySelectorAll('.btn-delete, .delete-badge');
    deleteButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                e.preventDefault(); 
            }
        });
    });

    // --- 4. Simple Status Update ---
    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const status = this.value;
            this.style.borderColor = ''; 
            
            if(status === 'confirmed') this.style.borderColor = '#2ecc71';
            else if(status === 'cancelled') this.style.borderColor = '#e74c3c';
            else this.style.borderColor = '#ccc';
        });
    });

    // ... existing code ...

    // --- 5. Manage Users: Scroll to Form on Edit ---
    // This checks if the URL has "?edit=" and scrolls smoothly to the form
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('edit')) {
        const userForm = document.querySelector('.col-form'); // Target the right column
        if (userForm) {
            // Small delay to ensure layout is ready
            setTimeout(() => {
                userForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Optional: Highlight the form visually
                const card = userForm.querySelector('.admin-card');
                if(card) {
                    card.style.border = "2px solid #3498db"; // Blue border
                    card.style.transition = "border 0.5s";
                }
            }, 100);
        }
    }

    // ==========================================
    // 6. DYNAMIC PRICE CALCULATION (Bookings)
    // ==========================================
    const paxInputs = document.querySelectorAll('.pax-input');
    const totalCostInput = document.getElementById('total_cost');
    const unitPriceInput = document.getElementById('unit_price'); // Get the real package price

    if (paxInputs.length > 0 && totalCostInput) {
        
        paxInputs.forEach(input => {
            input.addEventListener('input', calculateTotal);
        });

        function calculateTotal() {
            // Get base price from the hidden PHP field, or default to 50 if missing
            const basePrice = unitPriceInput ? parseFloat(unitPriceInput.value) : 50.00;
            const childDiscount = 0.6; // Example: Children pay 60% of price (Change as needed)

            const adults = parseInt(document.getElementById('pax_adults').value) || 0;
            const children = parseInt(document.getElementById('pax_children').value) || 0;

            // Adult = Full Price, Child = Full Price (or apply discount logic here)
            // If your DB has specific child price, you'd need another column. 
            // For now, let's assume Children are RM 20 cheaper than Adults:
            const adultTotal = adults * basePrice;
            const childTotal = children * (basePrice > 20 ? basePrice - 20 : basePrice * 0.5);

            const newTotal = adultTotal + childTotal;
            
            totalCostInput.value = newTotal.toFixed(2);
        }
    }
});

// =========================================================
// GLOBAL FUNCTIONS (Must be OUTSIDE 'DOMContentLoaded')
// =========================================================

// --- STATS EDIT FUNCTIONS ---
function editStat(data) {
    const idField = document.getElementById('stat_id');
    if (idField) idField.value = data.id;

    document.getElementById('stat_key').value = data.stat_key;
    document.getElementById('stat_value').value = data.stat_value;
    document.getElementById('stat_label').value = data.stat_label;
    
    const visCheckbox = document.getElementById('is_visible');
    if (visCheckbox) visCheckbox.checked = (data.is_visible == 1);
    
    const submitBtn = document.querySelector('button[name="save_stat"]');
    if (submitBtn) submitBtn.textContent = "Update Statistic";

    const formCard = document.querySelector('.admin-card');
    if (formCard) formCard.scrollIntoView({ behavior: 'smooth' });
}

function resetStatForm() {
    const idField = document.getElementById('stat_id');
    if (idField) idField.value = '';

    document.getElementById('stat_key').value = '';
    document.getElementById('stat_value').value = '';
    document.getElementById('stat_label').value = '';
    
    const visCheckbox = document.getElementById('is_visible');
    if (visCheckbox) visCheckbox.checked = true;

    const submitBtn = document.querySelector('button[name="save_stat"]');
    if (submitBtn) submitBtn.textContent = "Save Statistic";
}

// --- LIST EDITING FUNCTIONS ---
function editList(data) {
    document.getElementById('list_id').value = data.id;
    document.getElementById('list_category').value = data.category;
    document.getElementById('list_name').value = data.item_name;
    document.getElementById('list_order').value = data.order_num;
    document.getElementById('list_status').checked = (data.status == 1);
    document.getElementById('btn_save_list').textContent = "Update Item";
    document.getElementById('list_name').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function resetListForm() {
    document.getElementById('list_id').value = '';
    document.getElementById('list_category').value = 'activity';
    document.getElementById('list_name').value = '';
    document.getElementById('list_order').value = '';
    document.getElementById('list_status').checked = true;
    document.getElementById('btn_save_list').textContent = "Save Item";
}

// --- FEEDBACK EDIT FUNCTIONS ---
function editFeedback(data) {
    document.getElementById('fb_id').value = data.feedback_id;
    document.getElementById('fb_name').value = data.name;
    document.getElementById('fb_rating').value = data.rating;
    document.getElementById('fb_msg').value = data.message;
    document.getElementById('fb_status').checked = (data.status == 1);
    document.getElementById('btn_save_fb').textContent = "Update Feedback";
    document.getElementById('fb_name').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function resetFeedbackForm() {
    document.getElementById('fb_id').value = '';
    document.getElementById('fb_name').value = '';
    document.getElementById('fb_rating').value = '5';
    document.getElementById('fb_msg').value = '';
    document.getElementById('fb_status').checked = true;
    document.getElementById('btn_save_fb').textContent = "Save Feedback";
}

// --- MAIN STATS VALIDATION ---
function validateStats() {
    const pInput = document.getElementById("stat_participants");
    const dInput = document.getElementById("stat_donation");
    const vInput = document.getElementById("stat_vendors");

    if (!pInput || !dInput || !vInput) return true;

    const p = pInput.value.trim();
    const d = dInput.value.trim();
    const v = vInput.value.trim();

    if (p === "" || d === "" || v === "") {
        alert("Please fill the data");
        return false;
    }
    return true;
}

// --- GALLERY EDIT FUNCTIONS ---
function editGallery(data) {
    // 1. Populate Hidden ID
    document.getElementById('gallery_id').value = data.image_id;

    // 2. Populate Fields
    document.getElementById('gallery_caption').value = data.caption;
    document.getElementById('gallery_image').value = data.image_url;
    document.getElementById('gallery_order').value = data.order_number;

    // 3. Change UI to "Edit Mode"
    document.getElementById('galleryFormTitle').textContent = "Edit Image";
    
    // Change Button Name to trigger 'update_gallery'
    const saveBtn = document.getElementById('btn_save_gallery');
    saveBtn.textContent = "Update Image";
    saveBtn.name = "update_gallery"; 
    
    // Show Cancel Button
    document.getElementById('btn_cancel_gallery').style.display = "inline-block";

    // 4. Highlight the form container
    const container = document.getElementById('galleryFormContainer');
    container.style.borderColor = "#20621E";
    container.style.backgroundColor = "#f0fff4";
}

function resetGalleryForm() {
    document.getElementById('gallery_id').value = '';
    document.getElementById('gallery_caption').value = '';
    document.getElementById('gallery_image').value = '';
    document.getElementById('gallery_order').value = '1';

    document.getElementById('galleryFormTitle').textContent = "+ Add New Image";
    
    const saveBtn = document.getElementById('btn_save_gallery');
    saveBtn.textContent = "Save Image";
    saveBtn.name = "add_gallery"; 
    
    document.getElementById('btn_cancel_gallery').style.display = "none";
    
    const container = document.getElementById('galleryFormContainer');
    container.style.borderColor = "#ddd";
    container.style.backgroundColor = "#fff";
}

function validateGalleryOrder() {
    const orderInput = document.getElementById('gallery_order');
    const idInput = document.getElementById('gallery_id'); 
    
    if (!orderInput) return true;

    const newOrder = parseInt(orderInput.value);
    const currentId = idInput.value ? parseInt(idInput.value) : null; 

    // existingGalleryData is defined in manage_pages.php
    if (typeof existingGalleryData !== 'undefined') {
        for (let i = 0; i < existingGalleryData.length; i++) {
            const item = existingGalleryData[i];
            const itemOrder = parseInt(item.order_number);
            const itemId = parseInt(item.image_id);

            if (itemOrder === newOrder) {
                if (currentId !== itemId) {
                    alert(`Order number ${newOrder} is already used! Please choose a different number.`);
                    return false; 
                }
            }
        }
    }
    return true; 
}

// ==========================================
    // DISCOVER PAGE JS FUNCTIONS
    // ==========================================

    function editAttraction(data) {
        // 1. Fill the form with existing data
        document.getElementById('explore_id').value = data.explore_id;
        document.getElementById('attr_title').value = data.title;
        document.getElementById('attr_content').value = data.content;
        document.getElementById('attr_image').value = data.image_url;
        document.getElementById('attr_order').value = data.display_order;

        // 2. Change Title to "Edit" (Visual feedback)
        const title = document.getElementById('attractionFormTitle');
        title.textContent = "Edit Attraction";
        title.style.color = "#3498db"; // Blue

        // 3. Change Button to "Update"
        const btn = document.getElementById('btn_save_attr');
        btn.textContent = "Update Attraction";
        btn.name = "update_attraction"; // Switch PHP logic to UPDATE
        btn.style.backgroundColor = "#3498db"; // Blue button

        // 4. Show Cancel Button
        document.getElementById('btn_cancel_attr').style.display = "inline-block";

        // 5. Scroll to Form and Highlight
        const container = document.getElementById('attractionFormContainer');
        container.style.borderColor = "#3498db";
        container.style.backgroundColor = "#f0f8ff"; // Light blue bg
        container.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function resetAttractionForm() {
        // 1. Clear Form
        document.getElementById('explore_id').value = '';
        document.getElementById('attr_title').value = '';
        document.getElementById('attr_content').value = '';
        document.getElementById('attr_image').value = '';
        document.getElementById('attr_order').value = '1';

        // 2. Reset Title
        const title = document.getElementById('attractionFormTitle');
        title.textContent = "+ Add New Attraction";
        title.style.color = "#20621E";

        // 3. Reset Button
        const btn = document.getElementById('btn_save_attr');
        btn.textContent = "Save Attraction";
        btn.name = "add_attraction";
        btn.style.backgroundColor = "#20621E";

        // 4. Hide Cancel Button
        document.getElementById('btn_cancel_attr').style.display = "none";

        // 5. Reset Styles
        const container = document.getElementById('attractionFormContainer');
        container.style.borderColor = "#ddd";
        container.style.backgroundColor = "#fff";
    }

// ==========================================
        // CONTACT PAGE FUNCTIONS
        // ==========================================
        function editContact(data) {
            document.getElementById('contact_id').value = data.contact_id;
            document.getElementById('contact_email').value = data.email;
            document.getElementById('contact_phone').value = data.phone;
            document.getElementById('contact_address').value = data.address;

            const title = document.getElementById('contactFormTitle');
            if(title) {
                title.textContent = "Edit Contact Info";
                title.style.color = "#3498db";
            }

            const btn = document.getElementById('btn_save_contact');
            if(btn) {
                btn.textContent = "Update Details";
                btn.name = "update_contact"; 
                btn.style.backgroundColor = "#3498db"; 
            }

            document.getElementById('btn_cancel_contact').style.display = "inline-block";
            
            const container = document.getElementById('contactFormContainer');
            if(container) {
                container.style.borderColor = "#3498db";
                container.style.backgroundColor = "#f0fff4";
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function resetContactForm() {
            document.getElementById('contact_id').value = '';
            document.getElementById('contact_email').value = '';
            document.getElementById('contact_phone').value = '';
            document.getElementById('contact_address').value = '';

            const title = document.getElementById('contactFormTitle');
            if(title) {
                title.textContent = "+ Add New Contact Info";
                title.style.color = "#20621E";
            }

            const btn = document.getElementById('btn_save_contact');
            if(btn) {
                btn.textContent = "Save Details";
                btn.name = "add_contact";
                btn.style.backgroundColor = "#20621E";
            }

            document.getElementById('btn_cancel_contact').style.display = "none";

            const container = document.getElementById('contactFormContainer');
            if(container) {
                container.style.borderColor = "#ddd";
                container.style.backgroundColor = "#fff";
            }
        }

// ==========================================
        // LEARN PAGE FUNCTIONS
        // ==========================================
        function editLearn(data) {
            document.getElementById('learn_id').value = data.sections_id;
            document.getElementById('learn_title').value = data.title;
            document.getElementById('learn_content').value = data.content;
            document.getElementById('learn_image').value = data.image_url;
            document.getElementById('learn_order').value = data.order_number;

            const title = document.getElementById('learnFormTitle');
            if(title) {
                title.textContent = "Edit Learn Section";
                title.style.color = "#3498db";
            }

            const btn = document.getElementById('btn_save_learn');
            if(btn) {
                btn.textContent = "Update Section";
                btn.name = "update_learn"; 
                btn.style.backgroundColor = "#3498db"; 
            }

            document.getElementById('btn_cancel_learn').style.display = "inline-block";
            
            const container = document.getElementById('learnFormContainer');
            if(container) {
                container.style.borderColor = "#3498db";
                container.style.backgroundColor = "#f0fff4";
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function resetLearnForm() {
            document.getElementById('learn_id').value = '';
            document.getElementById('learn_title').value = '';
            document.getElementById('learn_content').value = '';
            document.getElementById('learn_image').value = '';
            document.getElementById('learn_order').value = '1';

            const title = document.getElementById('learnFormTitle');
            if(title) {
                title.textContent = "+ Add Learn Section";
                title.style.color = "#20621E";
            }

            const btn = document.getElementById('btn_save_learn');
            if(btn) {
                btn.textContent = "Save Section";
                btn.name = "add_learn";
                btn.style.backgroundColor = "#20621E";
            }

            document.getElementById('btn_cancel_learn').style.display = "none";

            const container = document.getElementById('learnFormContainer');
            if(container) {
                container.style.borderColor = "#ddd";
                container.style.backgroundColor = "#fff";
            }
        }

// ==========================================
// 7. DYNAMIC DATES (Packages)
// ==========================================

// Add a single empty input
function addDateInput(value = '') {
    const wrapper = document.getElementById('dates-wrapper');
    const div = document.createElement('div');
    div.className = 'date-row';
    div.innerHTML = `
        <input type="date" name="dates[]" value="${value}">
        <button type="button" class="btn-remove" onclick="removeDate(this)"><i class="fas fa-times"></i></button>
    `;
    wrapper.appendChild(div);
}

// Remove a row
function removeDate(btn) {
    const row = btn.parentNode;
    row.remove();
}

// BULK GENERATOR: Adds all dates between Start and End
function generateDateRange() {
    const startInput = document.getElementById('bulk_start').value;
    const endInput = document.getElementById('bulk_end').value;

    if (!startInput || !endInput) {
        alert("Please select both Start Date and End Date.");
        return;
    }

    const startDate = new Date(startInput);
    const endDate = new Date(endInput);

    if (startDate > endDate) {
        alert("End Date cannot be before Start Date.");
        return;
    }

    // Loop through dates
    let currentDate = startDate;
    let count = 0;
    
    // Clear existing empty inputs if there are any
    const inputs = document.querySelectorAll('#dates-wrapper input');
    inputs.forEach(input => {
        if(input.value === '') input.parentElement.remove();
    });

    while (currentDate <= endDate) {
        const dateStr = currentDate.toISOString().split('T')[0];
        addDateInput(dateStr);
        currentDate.setDate(currentDate.getDate() + 1); // Add 1 day
        count++;
    }
    
    // Reset bulk inputs
    document.getElementById('bulk_start').value = '';
    document.getElementById('bulk_end').value = '';
    
    // Smooth scroll to bottom of list
    const wrapper = document.getElementById('dates-wrapper');
    wrapper.scrollTop = wrapper.scrollHeight;
}

// ==========================================
// 8. PROGRAM FORM VALIDATION & DURATION
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    
    const startInput = document.getElementById('prog_start');
    const endInput = document.getElementById('prog_end');
    const durationInput = document.getElementById('prog_duration');
    const programForm = document.getElementById('programForm');

    // --- A. Auto-Calculate Duration ---
    if (startInput && endInput && durationInput) {
        function calculateDuration() {
            const start = startInput.value; 
            const end = endInput.value;     

            if (start && end) {
                const sDate = new Date(`2000-01-01T${start}:00`);
                const eDate = new Date(`2000-01-01T${end}:00`);
                let diff = eDate - sDate;

                if (diff < 0) { diff += 24 * 60 * 60 * 1000; } // Handle overnight

                const hours = diff / (1000 * 60 * 60);
                durationInput.value = Math.round(hours * 10) / 10;
            }
        }
        startInput.addEventListener('change', calculateDuration);
        endInput.addEventListener('change', calculateDuration);
    }

    // --- B. Form Validation ---
    if (programForm) {
        programForm.addEventListener('submit', function(e) {
            const name = document.getElementById('p_name').value.trim();
            const price = document.getElementById('p_price').value.trim();
            const start = document.getElementById('prog_start').value;
            const end = document.getElementById('prog_end').value;
            const desc = document.getElementById('p_desc').value.trim();

            if (!name || !price || !start || !end || !desc) {
                e.preventDefault(); // Stop form submission
                alert("Please fill up all program details");
            }
        });
    }
});

// ==========================================
// MESSAGE MODAL FUNCTIONS (Add this to fix the error)
// ==========================================

function viewMessage(data) {
    // 1. Populate Data
    document.getElementById('modalSubject').textContent = data.subject || 'No Subject';
    document.getElementById('modalName').textContent = data.name || 'Unknown';
    document.getElementById('modalEmail').textContent = data.email || 'No Email';
    document.getElementById('modalContent').textContent = data.message || '';
    
    // Format Date (Simple JS formatting)
    if (data.submitted_at) {
        const dateObj = new Date(data.submitted_at);
        document.getElementById('modalDate').textContent = dateObj.toLocaleString();
    }

    // 2. Show Modal
    const modal = document.getElementById('msgModal');
    if (modal) {
        modal.style.display = "flex";
    } else {
        console.error("Error: Modal with ID 'msgModal' not found.");
    }
}

function closeMsgModal() {
    const modal = document.getElementById('msgModal');
    if (modal) {
        modal.style.display = "none";
    }
}

// Close modal if clicking outside the white box
window.addEventListener('click', function(e) {
    const modal = document.getElementById('msgModal');
    if (e.target === modal) {
        modal.style.display = "none";
    }
});