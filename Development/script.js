// ============================================
// GLOBAL VARIABLES
// ============================================
let packagesData = [];
let dbActivitiesData = [];
let currentSearch = {
    arrival: "", 
    depart: "",
    adults: 1,
    children: 0
};

// Sticky Bar Variables
let customPax = 1;   
let standardPax = 1; 
let activeStandardPackage = null; 

// ============================================
// 1. MAIN INITIALIZATION
// ============================================
document.addEventListener("DOMContentLoaded", () => {
    // 1. UI & Auth Setup
    setupAuthButtons();
    setupHeroSlider();
    setupFadeInAnimations();
    setupModalClosers();

    // 2. Page Specific Logic

    // A. ITINERARY PAGE
    if (document.getElementById('available-packages-list')) {
        initItineraryPage(); 
        setTimeout(checkPersistentCart, 500); 
        setupStandardBarControls(); 
    }

    // B. DETAILS PAGE
    if (document.getElementById('details-container')) {
        initDetailsPage();
        checkPersistentCart(); 
        setupStandardBarControls(); 
    }

    // C. CUSTOM BUILDER
    if (document.getElementById('custom-activities-list')) {
        initCustomBuilder();
        setupCustomPaxControls(); 
    }

    // D. CHECKOUT PAGE
    if (document.getElementById('checkout-content')) {
        initCheckoutPage();
    }

    // --- NEW: CONTACT PAGE ---
    if (document.getElementById('messageForm')) {
        initContactPage();
    }

    if (document.getElementById('visitorsChart')) {
        initHomePage();
    }

    if (document.getElementById('main-participants')) {
        console.log("Loading Main Dashboard Stats...");
        loadMainStats();
    }

    if (document.getElementById('visitorsChart')) {
        initHomePage();
    }

    if (document.getElementById('learn-content-container')) {
        loadLearnContent();
    }

    if (document.querySelector('body.user-page')) {
    initUserPage();
    }

    loadHeroSlides(); 
    loadGallery();
    initExploreSection();
    setupChatBot();
});

// ============================================
// 2. UI HELPERS (Auth, Slider, Image Fix)
// ============================================

// --- SMART IMAGE PATH FIX (CONSOLIDATED) ---
function resolveImagePath(rawPath) {
    // 1. Check for empty/null values -> Return "No Image" placeholder
    if (!rawPath || rawPath === "null" || (typeof rawPath === 'string' && rawPath.trim() === "")) {
        return 'https://placehold.co/400x250?text=No+Image';
    }

    let cleanPath = (typeof rawPath === 'string') ? rawPath.trim() : String(rawPath);

    // 2. If it's a URL (starts with http), return as is
    if (cleanPath.startsWith('http://') || cleanPath.startsWith('https://')) {
        return cleanPath;
    }

    // 3. Remove leading slash if present (e.g. "/img/photo.jpg" -> "img/photo.jpg")
    if (cleanPath.startsWith('/')) {
        cleanPath = cleanPath.substring(1);
    }

    // 4. If it already has a folder (uploads/, images/, img/, assets/, etc.), use as-is
    //    But if it's explicitly starting with 'img/' keep it unchanged.
    if (cleanPath.includes('/')) {
        // If someone accidentally stored "img//photo.jpg" reduce double slashes
        cleanPath = cleanPath.replace(/\/{2,}/g, '/');
        console.log(`Loading Image (kept as-is): ${cleanPath}`);
        return cleanPath;
    }

    // 5. Otherwise, add the 'img/' prefix (assume bare filename)
    const finalPath = `img/${cleanPath}`;
    
    // Debugging: prints the path to your browser Console (F12)
    console.log(`Loading Image (prefixed): ${finalPath}`);
    
    return finalPath;
}

function setupAuthButtons() {
    // 1. Select all the buttons
    const signupBtn = document.getElementById('signupBtn');
    const loginBtn = document.getElementById('loginBtn');
    const logoutBtn = document.getElementById('logoutBtn');
    const userLink = document.querySelector('.nav-user-link'); 

    // 2. Check Login Status
    // We check if "true" exists in local storage
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';

    // 3. Toggle Visibility based on status
    if (isLoggedIn) {
        // --- USER IS LOGGED IN ---
        // Hide Guest Buttons
        if (signupBtn) signupBtn.style.display = 'none';
        if (loginBtn) loginBtn.style.display = 'none';
        
        // Show User Elements
        if (userLink) userLink.style.display = 'flex';   
        if (logoutBtn) logoutBtn.style.display = 'block'; 

        // Attach Logout Logic
        if (logoutBtn) {
            // Clone node to remove old listeners to be safe
            const newLogoutBtn = logoutBtn.cloneNode(true);
            logoutBtn.parentNode.replaceChild(newLogoutBtn, logoutBtn);
            
            newLogoutBtn.addEventListener('click', (e) => {
                e.preventDefault(); 
                if (confirm("Are you sure you want to log out?")) { 
                    localStorage.removeItem('isLoggedIn'); 
                    localStorage.removeItem('username');
                    window.location.href = "index.html"; 
                }
            });
        }
    } else {
        // --- USER IS GUEST (Logged Out) ---
        // Show Guest Buttons
        if (signupBtn) signupBtn.style.display = 'block'; 
        if (loginBtn) loginBtn.style.display = 'block';   
        
        // Hide User Elements
        if (userLink) userLink.style.display = 'none';    
        if (logoutBtn) logoutBtn.style.display = 'none';  
        
        // Ensure click events work for guests
        if(signupBtn) signupBtn.onclick = () => window.location.href = 'signup.html';
        if(loginBtn) loginBtn.onclick = () => window.location.href = 'login.html';
    }
}

function setupHeroSlider() {
    const heroSlides = document.querySelectorAll('.hero-slide');
    const prevBtn = document.querySelector('.hero-btn.prev'); 
    const nextBtn = document.querySelector('.hero-btn.next');
    let currentSlideIndex = 0;

    if (heroSlides.length > 0) {
        heroSlides[0].classList.add('active');
        const showSlide = (index) => {
            heroSlides[currentSlideIndex].classList.remove('active');
            if (index >= heroSlides.length) currentSlideIndex = 0;
            else if (index < 0) currentSlideIndex = heroSlides.length - 1;
            else currentSlideIndex = index;
            heroSlides[currentSlideIndex].classList.add('active');
        };
        if (nextBtn) nextBtn.onclick = (e) => { e.preventDefault(); showSlide(currentSlideIndex + 1); };
        if (prevBtn) prevBtn.onclick = (e) => { e.preventDefault(); showSlide(currentSlideIndex - 1); };
        setInterval(() => showSlide(currentSlideIndex + 1), 5000);
    }
}

function setupFadeInAnimations() {
    const faders = document.querySelectorAll('.fade-in');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('visible'); });
    });
    faders.forEach(el => observer.observe(el));
}

function setupModalClosers() {
    document.querySelectorAll('.close-btn').forEach(btn => btn.onclick = (e) => e.target.closest('.modal').style.display = 'none');
    window.onclick = (e) => { if (e.target.classList.contains('modal')) e.target.style.display = 'none'; };
}

function isUserLoggedIn() { return localStorage.getItem('isLoggedIn') === 'true'; }

function dateDiffInDays(date1, date2) {
    if (!date1 || !date2) return 1;
    const diff = Math.abs(new Date(date2) - new Date(date1));
    const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
    return days > 0 ? days : 1;
}

// ============================================
// 3. PACKAGE & ITINERARY LOGIC
// ============================================

async function fetchPackagesFromDB() {
    // Primary: get_packages.php (you confirmed this endpoint exists)
    try {
        const response = await fetch('get_packages.php');
        if (!response.ok) throw new Error("Server Error (get_packages.php)");
        const json = await response.json();
        if (!Array.isArray(json)) {
            console.warn("get_packages.php returned non-array JSON:", json);
            return [];
        }
        // Debug: log to console for easier troubleshooting
        console.log("fetchPackagesFromDB: loaded", json.length, "packages");
        return json;
    } catch (err) {
        console.error("fetchPackagesFromDB error:", err);
        return [];
    }
}

async function fetchProgramsFromDB() {
    try {
        const response = await fetch('get_programs.php');
        if (!response.ok) throw new Error("Server Error (get_programs.php)");
        
        // Parse the JSON and Log the result (Just like Packages)
        const json = await response.json();
        console.log("fetchProgramsFromDB: loaded", json.length, "programs");
        return json;

    } catch (err) { 
        console.error("fetchProgramsFromDB error:", err);
        return []; 
    }
}

function initItineraryPage() {
    const listContainer = document.getElementById('available-packages-list');
    const loadingMsg = document.getElementById('loading-message');
    
    // 1. FETCH PACKAGES (Initial Load)
    fetchPackagesFromDB().then(data => {
        packagesData = data;
        if(loadingMsg) loadingMsg.style.display = 'none';
        if (packagesData.length > 0) {
            renderPackageList(packagesData);
        } else {
            if(listContainer) listContainer.innerHTML = '<p style="text-align:center; color:#555;">No packages found in database.</p>';
        }
    });

    // 2. CHECK AVAILABILITY BUTTON LOGIC
    const checkBtn = document.getElementById('check-availability-btn');
    if(checkBtn) {
        checkBtn.onclick = () => {
            const arrival = document.getElementById('arrival-date').value;
            const depart = document.getElementById('departure-date').value;
            const listContainer = document.getElementById('available-packages-list');
            const loadingMsg = document.getElementById('loading-message');

            if (!arrival || !depart) { 
                alert("Please select both Arrival and Departure dates."); 
                return; 
            }
            
            // Save dates for booking
            currentSearch.arrival = arrival;
            currentSearch.depart = depart;

            // UI: Show loading state
            if(listContainer) listContainer.innerHTML = '';
            if(loadingMsg) {
                loadingMsg.style.display = 'block';
                loadingMsg.textContent = "Checking availability with database...";
            }

            // CALL THE NEW PHP FILE
            fetch(`check_availability.php?date=${arrival}`)
                .then(response => response.json())
                .then(data => {
                    if(loadingMsg) loadingMsg.style.display = 'none';

                    if (data.length > 0) {
                        renderPackageList(data, true);
                        
                        // Optional: Update UI to show slots left
                        // Since renderPackageList wipes HTML, we can add slot badges after rendering
                        data.forEach(pkg => {
                            const card = document.querySelector(`.package-card[data-id="${pkg.id}"]`);
                            if(card) {
                                // Find where to insert the slot count
                                const typeEl = card.querySelector('p[style*="color:#20621E"]');
                                if(typeEl) {
                                    const badge = document.createElement('span');
                                    badge.style.cssText = "background:#ff9800; color:white; padding:2px 6px; border-radius:4px; font-size:0.8em; margin-left:10px;";
                                    badge.textContent = `${pkg.slots_left} slots left`;
                                    typeEl.appendChild(badge);
                                }
                            }
                        });

                    } else {
                        if(listContainer) {
                            listContainer.innerHTML = `
                                <div style="text-align:center; padding:30px; color:#666;">
                                    <h3>No packages available.</h3>
                                    <p>Sorry, either the dates are fully booked or no packages run on this date.</p>
                                </div>`;
                        }
                    }
                })
                .catch(err => {
                    console.error("Availability Check Error:", err);
                    if(loadingMsg) loadingMsg.textContent = "Error checking availability.";
                });
        }; // Close onclick
    } // Close if(checkBtn)
} // Close initItineraryPage

function renderPackageList(packages, datesSelected = false) {
    const listContainer = document.getElementById('available-packages-list');
    if (!listContainer) return;
    listContainer.innerHTML = '';

    packages.forEach(pkg => {
        const card = document.createElement('div');
        card.className = 'package-card fade-in visible'; 
        card.dataset.id = pkg.id; 

        // Support multiple possible field names from server: imageUrl, image_url, image
        const rawImageField = pkg.imageUrl ?? pkg.image_url ?? pkg.image ?? '';
        const imgSrc = resolveImagePath(rawImageField);
        
        // 2. Calculate duration safely
        let days = pkg.durationDays || 1;
        if (datesSelected && typeof dateDiffInDays === 'function' && typeof currentSearch !== 'undefined') {
            days = dateDiffInDays(currentSearch.arrival, currentSearch.depart);
        }

        const detailsLink = `details.html?id=${pkg.id}`;

        card.innerHTML = `
            <img src="${imgSrc}" class="package-image" alt="${pkg.name}" 
                 style="width:100%; height:200px; object-fit:cover; display:block; background:#eee;"
                 onerror="this.onerror=null; console.error('Image not found:', this.src); this.src='https://placehold.co/400x250?text=Image+Error'">
            
            <div class="package-details" style="padding: 15px; display: flex; flex-direction: column; flex-grow: 1;">
                <div style="display:flex; justify-content:space-between; align-items:start;">
                    <h3 style="margin: 0 0 5px 0; color: #333; font-size: 1.2rem; font-family: 'Poppins',sans-serif; width:85%;">${pkg.name}</h3>
                    <button class="add-btn-small pkg-select-btn" type="button" title="Select Package" 
                            style="background:#f0f0f0; border:1px solid #20621E; color:#20621E; width:32px; height:32px; border-radius:50%; font-weight:bold; cursor:pointer; font-size:18px;">
                        +
                    </button>
                </div>
                
                <p style="color:#666; margin-bottom: 5px; font-size: 0.9rem;">Duration: ${days} Days</p>
                <p style="font-size:0.8rem; color:#20621E; font-weight:bold;">${pkg.type || 'Standard'}</p>
                
                <div style="margin-top: auto; padding-top: 15px;">
                    <div style="font-size: 1.2rem; font-weight: bold; color: #20621E; margin-bottom: 10px;">
                        RM ${parseFloat(pkg.pricePerPax || 0).toFixed(2)} <small style="font-size:0.8rem; color:#555;">/pax</small>
                    </div>
                    <div class="package-buttons" style="display: flex; gap: 10px;">
                        <button class="see-details-btn" onclick="window.location.href='${detailsLink}'" style="flex: 1; padding: 8px; cursor: pointer; border:1px solid #ccc; border-radius:4px; background:#f9f9f9; color:#333; font-weight:600;">See Details</button>
                    </div>
                </div>
            </div>`;

        const selectBtn = card.querySelector('.pkg-select-btn');
        if (selectBtn) {
            selectBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (typeof togglePackageSelection === 'function') {
                    togglePackageSelection(card, pkg, selectBtn);
                } else {
                    console.error("togglePackageSelection function is missing!");
                }
            });
        }

        listContainer.appendChild(card);
    });
}

function togglePackageSelection(card, pkg, btn) {
    document.querySelectorAll('.package-card.selected').forEach(c => {
        if (c !== card) {
            c.classList.remove('selected');
            const b = c.querySelector('.pkg-select-btn');
            if (b) { b.textContent = "+"; b.style.backgroundColor = "#f0f0f0"; b.style.color = "#20621E"; }
        }
    });

    if (card.classList.contains('selected')) {
        card.classList.remove('selected');
        btn.textContent = "+";
        btn.style.backgroundColor = "#f0f0f0";
        btn.style.color = "#20621E";
        document.getElementById('package-sticky-bar').classList.remove('visible');
        activeStandardPackage = null;
        sessionStorage.removeItem('standard_cart');
    } else {
        card.classList.add('selected');
        btn.textContent = "✓";
        btn.style.backgroundColor = "#20621E";
        btn.style.color = "white";
        addToCart(pkg);
    }
}

// ============================================
// 4. DETAILS PAGE LOGIC
// ============================================
function initDetailsPage() {
    const urlParams = new URLSearchParams(window.location.search);
    const pkgId = parseInt(urlParams.get('id'));

    Promise.all([fetchPackagesFromDB(), fetchProgramsFromDB()]).then(([pkgs, allPrograms]) => {
        packagesData = pkgs;
        // Use loose equality (==) to match string "101" with number 101
        const pkg = pkgs.find(p => p.id == pkgId);
        
        if (!pkg) {
            document.getElementById('loading-details').textContent = "Package not found.";
            return;
        }

        document.getElementById('loading-details').style.display = 'none';
        document.getElementById('details-container').style.display = 'block';

        document.getElementById('detail-title').textContent = pkg.name;
        document.getElementById('detail-type').textContent = pkg.type;
        document.getElementById('detail-price').textContent = `RM ${parseFloat(pkg.pricePerPax).toFixed(2)} /pax`;
       // --- START FIX: Auto-format Description to Bullet Points ---
        const descContainer = document.getElementById('detail-desc');
        const rawDesc = pkg.description || "";

        // Check if the text contains dashes "-"
        if (rawDesc.includes('-')) {
            // 1. Split by dash
            // 2. Filter out empty lines
            // 3. Trim whitespace
            const items = rawDesc.split('-')
                .map(s => s.trim())
                .filter(s => s.length > 0);

            // 4. Build HTML List
            if (items.length > 0) {
                const listHtml = items
                    .map(item => `<li style="position: relative; padding-left: 25px; margin-bottom: 10px; list-style: none;">
                                    <span style="position: absolute; left: 0; color: #20621E; font-weight: bold;">•</span>
                                    ${item}
                                  </li>`)
                    .join('');
                
                // Render as a clean UL list
                descContainer.innerHTML = `<ul style="padding: 0; margin: 0;">${listHtml}</ul>`;
            } else {
                descContainer.textContent = rawDesc;
            }
        } else {
            // If no dashes, just handle line breaks normally
            descContainer.innerHTML = rawDesc.replace(/\n/g, "<br>");
        }
        // --- END FIX ---

        const durationEl = document.getElementById('detail-duration');
        if (pkg.durationDays && pkg.durationDays > 0) {
            durationEl.textContent = `${pkg.durationDays} Days / ${pkg.durationDays - 1} Nights`;
        }

        const imgEl = document.getElementById('detail-img');
        imgEl.src = resolveImagePath(pkg.imageUrl ?? pkg.image_url ?? pkg.image ?? '');
        imgEl.onerror = function() {
            this.src = 'https://placehold.co/600x400?text=Image+Not+Found';
        };

        const actList = document.getElementById('detail-activities');
        if(actList) {
            actList.innerHTML = '';
            let actIds = pkg.activitiesIds; 
            if (typeof actIds === 'string') {
                try { actIds = JSON.parse(actIds); } catch(e) { actIds = []; }
            }
            if (actIds && actIds.length > 0 && Array.isArray(actIds)) {
                actIds.forEach(id => {
                    const actName = allPrograms.find(a => a.id == id)?.name || `Activity ID ${id}`;
                    actList.innerHTML += `<li>${actName}</li>`;
                });
            } else {
                actList.innerHTML = '<li>Includes all main package highlights.</li>';
            }
        }

        const bookBtn = document.getElementById('detail-book-btn');
        bookBtn.onclick = () => {
            if (!isUserLoggedIn()) {
                alert("Please login to book.");
                window.location.href = "login.html";
                return;
            }
            addToCart(pkg);
        };
    });
}

// ============================================
// 5. PACKAGE STICKY BAR LOGIC (Standard)
// ============================================

function checkPersistentCart() {
    const existingCart = sessionStorage.getItem('standard_cart');
    if (existingCart) {
        try {
            const item = JSON.parse(existingCart);
            standardPax = item.pax || 1;
            if(item.arrival) currentSearch.arrival = item.arrival;
            if(item.depart) currentSearch.depart = item.depart;
            
            renderPackageStickyBar(item.pkg);

            // Highlight card if on itinerary page
            setTimeout(() => {
                const card = document.querySelector(`.package-card[data-id="${item.pkg.id}"]`);
                if (card) {
                    card.classList.add('selected');
                    const btn = card.querySelector('.pkg-select-btn');
                    if (btn) { btn.textContent = "✓"; btn.style.backgroundColor = "#20621E"; btn.style.color = "white"; }
                }
            }, 500);
        } catch (e) { console.error(e); }
    }
}

function addToCart(pkg) {
    standardPax = 1; // Reset to 1 on new add
    activeStandardPackage = pkg;
    saveStandardCart(pkg);
    renderPackageStickyBar(pkg);
}

function saveStandardCart(pkg) {
    const arrInput = document.getElementById('pkg-arrival-input');
    const depInput = document.getElementById('pkg-depart-input');
    
    const arr = (arrInput && arrInput.value) ? arrInput.value : currentSearch.arrival;
    const dep = (depInput && depInput.value) ? depInput.value : currentSearch.depart;
    
    let validDep = dep;
    if(!validDep && arr) {
        let d = new Date(arr);
        d.setDate(d.getDate() + (parseInt(pkg.durationDays) || 1));
        validDep = d.toISOString().split('T')[0];
    }

    const total = (parseFloat(pkg.pricePerPax) || 0) * standardPax; // Fix: Price * Pax only
    
    const cartItem = { pkg: pkg, pax: standardPax, arrival: arr, depart: validDep, cost: total };
    sessionStorage.setItem('standard_cart', JSON.stringify(cartItem));
    
    currentSearch.arrival = arr;
    currentSearch.depart = validDep;
}

function setupStandardBarControls() {
    const minusBtn = document.getElementById('pkg-pax-minus');
    const plusBtn = document.getElementById('pkg-pax-plus');
    const display = document.getElementById('pkg-pax-display');
    const arrInput = document.getElementById('pkg-arrival-input');
    const depInput = document.getElementById('pkg-depart-input');

    if (minusBtn && plusBtn && display) {
        minusBtn.onclick = (e) => {
            e.preventDefault();
            if (standardPax > 1) {
                standardPax--;
                display.textContent = standardPax;
                if (activeStandardPackage) { saveStandardCart(activeStandardPackage); renderPackageStickyBar(activeStandardPackage); }
            }
        };

        plusBtn.onclick = (e) => {
            e.preventDefault();
            standardPax++;
            display.textContent = standardPax;
            if (activeStandardPackage) { saveStandardCart(activeStandardPackage); renderPackageStickyBar(activeStandardPackage); }
        };
    }

    if (arrInput && depInput) {
        const updateDatePrice = () => {
            if (activeStandardPackage) { saveStandardCart(activeStandardPackage); renderPackageStickyBar(activeStandardPackage); }
        };
        arrInput.addEventListener('change', updateDatePrice);
        depInput.addEventListener('change', updateDatePrice);
    }
}

function renderPackageStickyBar(pkg) {
    activeStandardPackage = pkg;
    
    const bar = document.getElementById('package-sticky-bar');
    const nameEl = document.getElementById('pkg-sticky-name');
    const priceEl = document.getElementById('pkg-sticky-total');
    const paxDisplay = document.getElementById('pkg-pax-display');
    const btn = document.getElementById('pkg-sticky-checkout-btn');
    const arrInput = document.getElementById('pkg-arrival-input');
    const depInput = document.getElementById('pkg-depart-input');

    if (bar && nameEl && priceEl) {
        const totalCost = (parseFloat(pkg.pricePerPax) || 0) * standardPax;

        nameEl.textContent = pkg.name;
        priceEl.textContent = `RM ${parseFloat(totalCost).toFixed(2)}`;
        
        if (paxDisplay) paxDisplay.textContent = standardPax;
        
        // Populate dates if available
        const arr = currentSearch.arrival;
        const dep = currentSearch.depart;
        if (arrInput) arrInput.value = arr || "";
        if (depInput) depInput.value = dep || "";

        const customBar = document.getElementById('custom-sticky-bar');
        if (customBar) customBar.classList.remove('visible');

        bar.classList.add('visible');

        btn.onclick = () => {
            if (!arrInput.value || !depInput.value) {
                alert("Please select Arrival and Departure dates.");
                return;
            }

            // 1. Resolve Image Path (Supports multiple DB column names)
            const rawImg = pkg.imageUrl || pkg.image_url || pkg.image || "";
            const finalImg = resolveImagePath(rawImg);

            // 2. Add 'pkg_image' to the session data
            const sessionData = {
                type: 'standard',
                pkg_id: pkg.id,
                pkg_name: pkg.name,
                pkg_image: finalImg, // <--- THIS LINE WAS MISSING
                date: arrInput.value,
                endDate: depInput.value,
                adults: standardPax,
                children: 0,
                totalPrice: totalCost
            };
            
            sessionStorage.setItem('checkout_session', JSON.stringify(sessionData));
            window.location.href = "checkout.html";
        };
    }
}

// ============================================
// 6. CUSTOM BUILDER LOGIC (Itinerary Page)
// ============================================

function setupCustomPaxControls() {
    const minusBtn = document.getElementById('pax-minus');
    const plusBtn = document.getElementById('pax-plus');
    const display = document.getElementById('pax-display');

    if (minusBtn && plusBtn && display) {
        minusBtn.onclick = (e) => { e.preventDefault(); if (customPax > 1) { customPax--; display.textContent = customPax; updateStickyBarState(); } };
        plusBtn.onclick = (e) => { e.preventDefault(); customPax++; display.textContent = customPax; updateStickyBarState(); };
    }
}

function initCustomBuilder() {
    const loading = document.getElementById('custom-programs-loading');
    
    // Use the shared helper function
    fetchProgramsFromDB().then(data => {
        dbActivitiesData = data; 
        
        if (loading) loading.style.display = 'none';
        
        if (!data || data.length === 0) {
            console.warn("initCustomBuilder: No programs found in database.");
        }

        renderNewCustomCards(data);
    });
}

function renderNewCustomCards(programs) {
    const container = document.getElementById('custom-activities-list');
    if (!container) return;
    container.innerHTML = '';

    programs.forEach(act => {
        // 1. Calculate Time Display
        let t = "Timing varies";
        if (act.startTime && act.endTime) {
            t = `${act.startTime.substring(0, 5)} - ${act.endTime.substring(0, 5)}`;
        }

        // 2. Resolve Image
        const imgSrc = resolveImagePath(act.imageUrl ?? act.image_url ?? act.image ?? '');

        // 3. Create Card Element
        const card = document.createElement('div');
        // Matches CSS: .program-card
        card.className = 'program-card fade-in visible';
        
        // Dataset attributes for logic
        card.dataset.id = act.id;
        card.dataset.price = act.price;
        card.dataset.start = act.startTime;
        card.dataset.end = act.endTime;
        card.dataset.name = act.name; 
        card.dataset.img = imgSrc;    

        // 4. Generate HTML (UPDATED TO MATCH STYLE.CSS)
        card.innerHTML = `
            <img src="${imgSrc}" alt="${act.name}" class="program-card-img"
                 onerror="this.src='https://placehold.co/400x250?text=No+Image'">
            
            <div class="program-card-body">
                <div class="program-card-title">${act.name}</div>
                
                <div class="program-card-info">
                    <span>⏰ ${t}</span>
                    <span class="program-card-price">RM ${parseFloat(act.price).toFixed(2)}</span>
                </div>

                <div style="display: flex; gap: 10px; align-items: center; margin-top: auto;">
                    <button class="btn-details" style="flex: 1;">See Details</button>
                    
                    <button class="add-btn-small">+</button>
                </div>
            </div>
        `;

        // 5. Add Event Listeners
        // A. Details Button (Updated class name)
        const detailsBtn = card.querySelector('.btn-details');
        if(detailsBtn) {
            detailsBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                openProgramDetailsModal(act, t);
            });
        }

        // B. Add (+) Button (Updated class name)
        const addBtn = card.querySelector('.add-btn-small');
        if(addBtn) {
            addBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleProgramSelection(card, act, t, addBtn);
            });
        }

        // 6. Append to Container
        container.appendChild(card);
    });
}

function toggleProgramSelection(card, act, t, btn) {
    if (card.classList.contains('selected')) {
        card.classList.remove('selected');
        btn.textContent = "+";
        btn.style.backgroundColor = "#f0f0f0";
        btn.style.color = "#20621E";
    } else {
        if (hasTimeConflict(act.startTime, act.endTime)) {
            // Build a safe conflict message from the activity times
            const conflictPeriod = (act.startTime && act.endTime) ? `${act.startTime} - ${act.endTime}` : 'the selected time';
            showToast(`Conflict! You already have an activity during ${conflictPeriod}`);
            return;
        }
        card.classList.add('selected');
        btn.textContent = "✓";
        btn.style.backgroundColor = "#20621E";
        btn.style.color = "white";
    }
    updateStickyBarState();
}

function updateStickyBarState() {
    const selectedCards = document.querySelectorAll('.program-card.selected');
    const stickyBar = document.getElementById('custom-sticky-bar');
    
    // --- FIX: UPDATED IDs TO MATCH HTML ---
    const countSpan = document.getElementById('sticky-count'); 
    const priceSpan = document.getElementById('sticky-total');
    // --------------------------------------

    const checkoutBtn = document.getElementById('sticky-checkout-btn');

    // 1. Calculate Totals
    let totalCount = 0;
    let grandTotal = 0;

    selectedCards.forEach(card => {
        totalCount++;
        const price = parseFloat(card.dataset.price || 0);
        grandTotal += price;
    });

    // 2. Multiply by Pax (customPax is a global variable)
    grandTotal = grandTotal * customPax;

    // 3. Update UI
    if (selectedCards.length > 0) {
        stickyBar.classList.add('visible');
        
        // --- FIX: Keep the word "Activities" in the display ---
        if(countSpan) countSpan.textContent = `${totalCount} Activities`;
        if(priceSpan) priceSpan.textContent = `RM ${grandTotal.toFixed(2)}`;

        checkoutBtn.onclick = () => {
            const dateInput = document.getElementById('sticky-date-input').value;
            if(!dateInput) {
                alert("Please select a date.");
                document.getElementById('sticky-date-input').focus();
                return;
            }
            const sessionData = {
                type: 'custom',
                date: dateInput,
                pax: customPax,
                programIds: Array.from(selectedCards).map(c => c.dataset.id),
                totalPrice: grandTotal,
                programNames: Array.from(selectedCards).map(c => c.dataset.name),
                programImages: Array.from(selectedCards).map(c => c.dataset.img)
            };
            sessionStorage.setItem('checkout_session', JSON.stringify(sessionData));
            window.location.href = "checkout.html";
        };
    } else {
        stickyBar.classList.remove('visible');
    }
}

// ============================================
// 7. SHARED HELPERS (Modals, Toast)
// ============================================

function openProgramDetailsModal(act, timeDisplay) {
    const modal = document.getElementById('program-details-modal');
    if(!modal) return;
    
    // 1. Setup Image
    const imgSrc = resolveImagePath(act.imageUrl ?? act.image_url ?? act.image ?? '');
    document.getElementById('prog-modal-img').src = imgSrc;
    
    // 2. Setup Basic Info
    document.getElementById('prog-modal-title').textContent = act.name;
    document.getElementById('prog-modal-time').textContent = `⏰ ${timeDisplay}`;
    document.getElementById('prog-modal-price').textContent = `RM ${parseFloat(act.price).toFixed(2)} /pax`;
    
    // 3. Setup Description (NEW LOGIC FOR BULLET POINTS)
    const descContainer = document.getElementById('prog-modal-desc');
    const rawDesc = act.description || "No description available.";

    // Check if the description contains hyphens indicating a list
    if (rawDesc.includes('-')) {
        // Split text by the hyphen, remove empty parts, and trim whitespace
        const points = rawDesc.split('-').filter(point => point.trim() !== "");

        // If we successfully found points, build a list
        if (points.length > 0) {
            let listHtml = '<ul style="padding-left: 20px; text-align: left; margin-top: 10px;">';
            points.forEach(point => {
                listHtml += `<li style="margin-bottom: 8px; color: #555; list-style-type: disc;">${point.trim()}</li>`;
            });
            listHtml += '</ul>';
            descContainer.innerHTML = listHtml;
        } else {
            // Fallback if split fails
            descContainer.textContent = rawDesc;
        }
    } else {
        // Standard paragraph text if no hyphens exist
        descContainer.style.whiteSpace = "pre-line"; // Respects enter keys
        descContainer.textContent = rawDesc;
    }
    
    // 4. Setup Action Button (Close)
    let actionBtn = document.getElementById('prog-modal-action-btn');
    if (!actionBtn) {
        actionBtn = document.createElement('button');
        actionBtn.id = 'prog-modal-action-btn';
        actionBtn.className = 'book-slot-btn'; 
        actionBtn.style.marginTop = '15px';
        actionBtn.style.width = '100%';
        modal.querySelector('.modal-content').appendChild(actionBtn);
    }
    actionBtn.textContent = "Close";
    actionBtn.onclick = () => modal.style.display = 'none';
    
    // 5. Show Modal
    modal.style.display = 'block';
}

function hasTimeConflict(newStart, newEnd) {
    if (!newStart || !newEnd) return false; 
    const toMinutes = (timeStr) => { const [h, m] = timeStr.split(':').map(Number); return h * 60 + m; };
    const newS = toMinutes(newStart);
    const newE = toMinutes(newEnd);
    let conflictFound = false;
    document.querySelectorAll('.program-card.selected').forEach(card => {
        const existStart = card.dataset.start;
        const existEnd = card.dataset.end;
        if (existStart && existEnd) {
            const existS = toMinutes(existStart);
            const existE = toMinutes(existEnd);
            if (newS < existE && newE > existS) conflictFound = true;
        }
    });
    return conflictFound;
}

function showToast(message) {
    const toast = document.getElementById("conflict-toast");
    if(!toast) return;
    toast.textContent = message;
    toast.className = "show";
    setTimeout(() => { toast.className = toast.className.replace("show", ""); }, 3000);
}


// ============================================
// 8. CHECKOUT PAGE LOGIC
// ============================================

// Add this line inside your main DOMContentLoaded event in script.js:
// if (document.getElementById('checkout-content')) { initCheckoutPage(); }

function initCheckoutPage() {
    console.log("Initializing Checkout Page...");

    // 1. Retrieve data from Session Storage
    const sessionData = sessionStorage.getItem('checkout_session');
    
    if (!sessionData) {
        console.warn("No session data found.");
        // Optional: Redirect if data is missing, or just let them know
        alert("No booking found. Redirecting to Itinerary.");
        window.location.href = "itinerary.html";
        return;
    }

    const booking = JSON.parse(sessionData);
    console.log("Booking Data Loaded:", booking);

    // 2. Map Elements
    const titleEl = document.getElementById('summary-title');
    const typeEl = document.getElementById('summary-type');
    const dateStartLabel = document.getElementById('label-date-start');
    const dateStartEl = document.getElementById('summary-date-start');
    const rowEnd = document.getElementById('row-date-end');
    const dateEndEl = document.getElementById('summary-date-end');
    const paxEl = document.getElementById('summary-pax');
    const totalEl = document.getElementById('summary-total-cost');
    const imgContainer = document.getElementById('summary-img-container'); 

    // --- NEW: Donation Logic Variables ---
    const donateCheckbox = document.getElementById('donate-checkbox');
    let currentTotal = parseFloat(booking.totalPrice); 
    let donationAmount = 0;

    // 3. Populate Data based on Booking Type
    if (booking.type === 'standard') {
        titleEl.textContent = booking.pkg_name;
        typeEl.textContent = "Standard Package";
        
        dateStartLabel.textContent = "Arrival";
        dateStartEl.textContent = booking.date;
        
        rowEnd.style.display = "flex";
        dateEndEl.textContent = booking.endDate;
        
        paxEl.textContent = `${booking.adults} Adults`;
        
        // --- ROBUST IMAGE LOGIC (The Fix) ---
        if (imgContainer) {
            // 1. Show whatever we have initially (placeholder or session image)
            let initialImg = booking.pkg_image || "https://placehold.co/400x250?text=Loading...";
            imgContainer.innerHTML = `<img src="${initialImg}" style="width:100%; height:100%; object-fit:cover; display:block;">`;

            // 2. FETCH FRESH DATA from Database to ensure image is correct
            // This fixes the issue where session data might be missing the image URL
            fetchPackagesFromDB().then(packages => {
                const freshPkg = packages.find(p => p.id == booking.pkg_id);
                if (freshPkg) {
                    // Resolve image path using your helper
                    const raw = freshPkg.imageUrl || freshPkg.image_url || freshPkg.image;
                    const freshSrc = resolveImagePath(raw);
                    
                    // Update the image src immediately
                    const imgEl = imgContainer.querySelector('img');
                    if (imgEl) {
                        imgEl.src = freshSrc;
                        console.log("Image auto-corrected to:", freshSrc);
                    }
                }
            });
        }

        // Hide Custom Toggle logic for standard packages
        const toggleBtn = document.getElementById('summary-details-toggle');
        const listEl = document.getElementById('summary-activities-list');
        if(toggleBtn) document.getElementById('summary-toggle-icon').style.display = 'none';
        if(listEl) listEl.style.display = 'none';

    } else if (booking.type === 'custom') {
        // ... (Existing Custom Logic remains unchanged) ...
        titleEl.textContent = "Custom Itinerary";
        dateStartLabel.textContent = "Activity Date";
        dateStartEl.textContent = booking.date;
        rowEnd.style.display = "none"; 
        
        paxEl.textContent = `${booking.pax} Pax`;

        if (imgContainer && booking.programImages && booking.programImages.length > 0) {
            imgContainer.innerHTML = '';
            const imagesToShow = booking.programImages.slice(0, 4);
            const grid = document.createElement('div');
            grid.className = `collage-grid cols-${imagesToShow.length}`; 
            imagesToShow.forEach(src => {
                const img = document.createElement('img'); img.src = src; grid.appendChild(img);
            });
            imgContainer.appendChild(grid);
        } else {
             imgContainer.innerHTML = '<img src="https://placehold.co/400x250?text=Custom+Activities" style="width:100%; height:100%; object-fit:cover;">';
        }

        const listEl = document.getElementById('summary-activities-list');
        const toggleBtn = document.getElementById('summary-details-toggle');
        const icon = document.getElementById('summary-toggle-icon');
        
        typeEl.textContent = `${booking.programIds.length} Activities Selected`;

        if (booking.programNames && booking.programNames.length > 0) {
            listEl.innerHTML = booking.programNames.map(name => `<li>${name}</li>`).join('');
            toggleBtn.onclick = () => {
                const isHidden = listEl.style.display === 'none' || listEl.style.display === '';
                listEl.style.display = isHidden ? 'block' : 'none';
                icon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
            };
            icon.style.display = 'inline-block';
            typeEl.style.cursor = 'pointer';
        }
    }

    // --- 4. RENDER INITIAL TOTAL ---
    totalEl.textContent = `RM ${currentTotal.toFixed(2)}`;

    // --- 5. DONATION CHECKBOX EVENT ---
    if (donateCheckbox) {
        donateCheckbox.addEventListener('change', function() {
            if (this.checked) {
                donationAmount = 1.00;
            } else {
                donationAmount = 0;
            }
            const displayedTotal = currentTotal + donationAmount;
            totalEl.textContent = `RM ${displayedTotal.toFixed(2)}`;
        });
    }

    window.addEventListener('pageshow', function(event) {
        // We re-select the button here to be safe
        const btn = document.getElementById('confirm-payment-btn');
        if (btn) {
            btn.disabled = false;
            btn.textContent = "Confirm & Pay";
        }
    });

    // 6. Handle "Confirm & Pay" Button Logic
    const confirmBtn = document.getElementById('confirm-payment-btn');
    if (confirmBtn) {
        confirmBtn.onclick = () => {
            const name = document.getElementById('checkout-name').value.trim();
            const phone = document.getElementById('checkout-phone').value.trim();
            const email = document.getElementById('checkout-email').value.trim();
            const paymentOption = document.querySelector('input[name="payment-method"]:checked');

            if (!name || !phone || !email) {
                alert("Please fill in all details.");
                return;
            }
            if (!paymentOption) {
                alert("Please choose a payment method.");
                return;
            }

            const finalTotalToSend = currentTotal + donationAmount;

            const payload = {
                ...booking, 
                customer_name: name,
                customer_phone: phone,
                customer_email: email,
                payment_method: paymentOption.value,
                totalPrice: finalTotalToSend, 
                donation_added: (donationAmount > 0) 
            };

            confirmBtn.textContent = "Processing...";
            confirmBtn.disabled = true;

            fetch('process_booking.php', { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload) 
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.payment_url) {
                        window.location.href = data.payment_url; 
                    } 
                    else {
                        alert("Booking Successful! Please pay at the counter.");
                        sessionStorage.removeItem('checkout_session');
                        sessionStorage.removeItem('standard_cart');
                        window.location.href = "index.html"; 
                    }
                } else {
                    alert("Booking Failed: " + data.message);
                    confirmBtn.textContent = "Confirm & Pay";
                    confirmBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error("Error:", err);
                alert("Network error occurred.");
                confirmBtn.textContent = "Confirm & Pay";
                confirmBtn.disabled = false;
            });
        };
    }
}

// ============================================
// 9. CONTACT PAGE LOGIC
// ============================================
function initContactPage() {
    console.log("Initializing Contact Page..."); 

    loadContactDetails();
    
    const contactForm = document.getElementById("messageForm");
    const successMsg = document.getElementById("successMessage");
    const sendBtn = document.getElementById("sendBtn");

    if (contactForm) {
        contactForm.addEventListener("submit", (e) => {
            e.preventDefault();

            // 1. Get Values
            const name = document.getElementById("name").value.trim();
            const email = document.getElementById("email").value.trim();
            const subject = document.getElementById("subject").value.trim();
            const message = document.getElementById("message").value.trim();

            // 2. Validation
            if (!name || !email || !message) {
                alert("Please fill in all required fields.");
                return;
            }

            // 3. Disable Button
            sendBtn.textContent = "Sending...";
            sendBtn.disabled = true;

            // 4. Send to PHP
            fetch("process_message.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ name, email, subject, message })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    successMsg.textContent = "✅ Message sent successfully!";
                    successMsg.style.color = "green";
                    contactForm.reset(); 
                } else {
                    successMsg.textContent = "❌ Error: " + result.message;
                    successMsg.style.color = "red";
                }
            })
            .catch(error => {
                console.error("Error:", error);
                successMsg.textContent = "❌ Network error. Please try again.";
                successMsg.style.color = "red";
            })
            .finally(() => {
                sendBtn.textContent = "Send Message";
                sendBtn.disabled = false;
            });
        });
    }

    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault(); 
            const textToCopy = btn.getAttribute('data-copy');
            
            if (!textToCopy) return;

            // Helper to show "Copied!" animation
            const showSuccess = () => {
                const originalText = btn.textContent;
                btn.textContent = "Copied!";
                setTimeout(() => btn.textContent = originalText, 2000);
            };

            // 1. Try Modern API (Requires HTTPS)
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy)
                    .then(showSuccess)
                    .catch(err => {
                        console.warn("Clipboard API failed, trying fallback...", err);
                        fallbackCopyText(textToCopy, showSuccess);
                    });
            } else {
                // 2. Fallback for HTTP (Legacy Method)
                fallbackCopyText(textToCopy, showSuccess);
            }
        });
    });

    // Fallback Function for HTTP environments
    function fallbackCopyText(text, callback) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        
        // Ensure textarea is not visible but part of DOM
        textArea.style.position = "fixed";
        textArea.style.left = "-9999px";
        textArea.style.top = "0";
        document.body.appendChild(textArea);
        
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (successful && callback) callback();
        } catch (err) {
            console.error('Fallback copy failed', err);
            alert("Unable to copy automatically. Please copy manually.");
        }
        
        document.body.removeChild(textArea);
    }
}

// ============================================
// 10. DISCOVER PAGE LOGIC (CRUD & DYNAMIC HERO)
// ============================================

document.addEventListener("DOMContentLoaded", () => {
    // Check if we are on the Discover Page
    if (document.getElementById('discover-container')) {
        initDiscoverPage();
    }
});

function initDiscoverPage() {
    console.log("Initializing Discover Page...");
    fetchDiscoverData();

    // Setup CRUD Form Submit Listener
    const crudForm = document.getElementById('crud-form');
    if (crudForm) {
        crudForm.addEventListener('submit', handleCrudSubmit);
    }
}

function fetchDiscoverData() {
    const container = document.getElementById('discover-container');
    
    // Call the PHP API
    fetch('get_discover.php')
        .then(res => res.json())
        .then(data => {
            container.innerHTML = ''; // Clear "Loading..." text
            
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<p style="text-align:center; padding:50px;">No attractions found in database.</p>';
                return;
            }

            // --- 1. SEPARATE HERO (Order #1) FROM LIST (Rest) ---
            const heroItem = data.find(item => item.display_order == 1);
            const listItems = data.filter(item => item.display_order != 1);

            // --- 2. UPDATE HERO SECTION (Header) ---
            if (heroItem) {
                // Resolve Hero Image Path
                let heroImg = "";
                let cleanHero = heroItem.image_url ? heroItem.image_url.trim() : "";
                
                if (cleanHero.startsWith('http') || cleanHero.startsWith('img/')) {
                    heroImg = cleanHero;
                } else {
                    heroImg = `img/${cleanHero}`;
                }

                // Update DOM Elements
                const heroTitle = document.querySelector('.discover-hero h1');
                const heroDesc = document.querySelector('.discover-hero p');
                const heroSection = document.querySelector('.discover-hero');

                if (heroTitle) heroTitle.textContent = heroItem.title;
                if (heroDesc) heroDesc.textContent = heroItem.content;
                if (heroSection) {
                    heroSection.style.backgroundImage = `linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('${heroImg}')`;
                }
            }

            // --- 3. RENDER THE REST (The List Below) ---
            listItems.forEach(item => {
                const section = document.createElement('section');
                section.className = 'attraction-section fade-in visible';
                
                // Image Path Logic
                let cleanFilename = item.image_url ? item.image_url.trim() : "";
                let bgImage = "";
                if (cleanFilename.startsWith('http') || cleanFilename.startsWith('img/')) {
                    bgImage = cleanFilename;
                } else {
                    bgImage = `img/${cleanFilename}`;
                }

                // REMOVED: const adminControls = ...
                // REMOVED: const isAdmin = ...

                // UPDATED HTML (No admin controls)
                section.innerHTML = `
                    <div class="overlay"></div>
                    <div class="attraction-content" style="position: relative; z-index: 2; max-width: 800px; padding: 20px;">
                        <h2>${item.title}</h2>
                        <p>${item.content}</p>
                    </div>
                `;

                // Set Background
                section.style.backgroundImage = `linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('${bgImage}')`;
                section.style.backgroundSize = "cover";
                section.style.backgroundPosition = "center";
                
                container.appendChild(section);
            });
        })
        .catch(err => {
            console.error("Error loading discover data:", err);
            container.innerHTML = '<p style="text-align:center; color:red;">Error loading data.</p>';
        });
}

// --- ADMIN HELPER FUNCTIONS ---

// 1. Open Modal (Create or Update)
window.openAdminModal = function(action, item = null) {
    const modal = document.getElementById('crud-modal');
    document.getElementById('crud-action').value = action;
    
    if (action === 'create') {
        document.getElementById('modal-title').textContent = "Add New Attraction";
        document.getElementById('crud-id').value = '';
        document.getElementById('crud-title').value = '';
        document.getElementById('crud-image').value = ''; 
        document.getElementById('crud-order').value = '0'; // Default order
        document.getElementById('crud-content').value = '';
    } else {
        document.getElementById('modal-title').textContent = "Edit Attraction";
        document.getElementById('crud-id').value = item.explore_id;
        document.getElementById('crud-title').value = item.title;
        document.getElementById('crud-image').value = item.image_url; 
        document.getElementById('crud-order').value = item.display_order;
        document.getElementById('crud-content').value = item.content;
    }
    modal.style.display = 'block';
};

// 2. Edit Hero Function (Called by the button in HTML)
window.editHero = function() {
    fetch('get_discover.php')
        .then(res => res.json())
        .then(data => {
            const hero = data.find(item => item.display_order == 1);
            if (hero) {
                openAdminModal('update', hero);
            } else {
                alert("Hero section (Order #1) not found in database!");
            }
        });
};

// 3. Handle Form Submit (Save Changes)
function handleCrudSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', document.getElementById('crud-action').value);
    formData.append('id', document.getElementById('crud-id').value);
    formData.append('title', document.getElementById('crud-title').value);
    formData.append('image_url', document.getElementById('crud-image').value);
    formData.append('display_order', document.getElementById('crud-order').value);
    formData.append('content', document.getElementById('crud-content').value);

    fetch('admin_discover_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert("Success!");
            document.getElementById('crud-modal').style.display = 'none';
            fetchDiscoverData(); // Refresh page data
        } else {
            alert("Error: " + data.error);
        }
    })
    .catch(err => console.error("Error submitting form:", err));
}

// 4. Handle Delete
window.deleteAttraction = function(id) {
    if(!confirm("Are you sure you want to delete this attraction?")) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('admin_discover_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            fetchDiscoverData(); // Refresh list
        } else {
            alert("Error deleting: " + data.error);
        }
    });
};

// 5. Enable Admin Mode (For testing)
window.enableAdmin = function() {
    localStorage.setItem('isAdmin', 'true');
    alert("Admin Mode Enabled! Refreshing page...");
    location.reload();
}


// ============================================
// 11. HOME PAGE LOGIC (CHART + DASHBOARD CONTENT)
// ============================================

function initHomePage() {
    console.log("Initializing Home Page...");

    // 1. Load Text Content (Stats, Lists, Feedback)
    if (typeof loadHomeDashboard === 'function') {
        loadHomeDashboard();
    } else {
        console.error("loadHomeDashboard function is not defined correctly.");
    }

    // 2. Load Chart (Visitors)
    loadVisitorsChart();

    loadFeaturedPackages();
}

// ============================================
// PART A: LOAD VISITOR CHART (Logic: Latest 6 Months)
// ============================================
function loadVisitorsChart() {
    const ctx = document.getElementById('visitorsChart');
    if (!ctx) return;

    fetch('get_visitors.php')
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                
                // 1. Month Mapping for Sorting
                const monthMap = {
                    "jan": 1, "january": 1, "feb": 2, "february": 2, "mar": 3, "march": 3,
                    "apr": 4, "april": 4, "may": 5, "jun": 6, "june": 6, "jul": 7, "july": 7,
                    "aug": 8, "august": 8, "sep": 9, "september": 9, "oct": 10, "october": 10,
                    "nov": 11, "november": 11, "dec": 12, "december": 12
                };

                // 2. Sort Data: Year ASC -> Month ASC
                data.sort((a, b) => {
                    const yearA = parseInt(a.year) || 0;
                    const yearB = parseInt(b.year) || 0;
                    if (yearA !== yearB) return yearA - yearB;
                    
                    const mA = (a.month || "").toLowerCase().substring(0, 3);
                    const mB = (b.month || "").toLowerCase().substring(0, 3);
                    return (monthMap[mA] || 0) - (monthMap[mB] || 0);
                });

                // 3. SLICE: Get only the last 6 items
                const recentData = data.slice(-6);

                // 4. Map to Labels and Counts
                const labels = recentData.map(d => d.month);
                const counts = recentData.map(d => d.visitors_count);

                renderChart(labels, counts);
            } else {
                console.warn("No visitor data found, rendering static chart.");
                renderStaticChart();
            }
        })
        .catch(err => {
            console.error("Fetch failed, loading static data.", err);
            renderStaticChart();
        });
}

// ============================================
// PART B: RENDER CHART (Style: White & Transparent)
// ============================================
function renderChart(labels, counts) {
    const ctx = document.getElementById('visitorsChart');
    if (!ctx) return;

    // 1. Remove White Box from Container (CSS Override)
    const container = document.querySelector('.visitors');
    if (container) {
        container.style.background = 'transparent';
        container.style.boxShadow = 'none';
        container.style.border = 'none';
        
        // Style Title to be White
        const title = container.querySelector('h3');
        if (title) {
            title.style.color = '#ffffff';
            title.style.textShadow = '2px 2px 5px rgba(0,0,0,0.7)';
            title.style.fontSize = '2rem';
            title.style.textAlign = 'center';
        }
    }

    // 2. Destroy Old Chart
    let chartStatus = Chart.getChart("visitorsChart");
    if (chartStatus != undefined) {
        chartStatus.destroy();
    }

    // 3. Create New Chart
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Visitors',
                data: counts,
                backgroundColor: 'rgba(255, 255, 255, 0.9)', // White Bars
                borderColor: 'rgba(255, 255, 255, 1)',
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: 'white', font: { size: 14, weight: 'bold' } }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: 'white', font: { size: 14 } },
                    grid: { color: 'rgba(255, 255, 255, 0.2)' },
                    border: { color: 'white' }
                },
                x: {
                    ticks: { color: 'white', font: { size: 14 } },
                    grid: { display: false },
                    border: { color: 'white' }
                }
            }
        }
    });
}

// Fallback Function
function renderStaticChart() {
    renderChart(["Jul", "Aug", "Sep", "Oct", "Nov", "Dec"], [0, 0, 0, 0, 0, 0]);
}


// ============================================
// 12. LOAD MAIN DASHBOARD STATS (TOP SECTION)
// ============================================
function loadMainStats() {
    fetch('get_main_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success || data.participants !== undefined) {
                const partEl = document.getElementById('main-participants');
                if (partEl) partEl.textContent = parseInt(data.participants).toLocaleString();

                const donEl = document.getElementById('main-donation');
                if (donEl) {
                    const money = parseFloat(data.donation).toLocaleString('en-MY', {
                        minimumFractionDigits: 2, maximumFractionDigits: 2
                    });
                    donEl.textContent = "RM " + money;
                }

                const vendEl = document.getElementById('main-vendors');
                if (vendEl) vendEl.textContent = parseInt(data.vendors).toLocaleString();
            }
        })
        .catch(err => console.error("Error loading main stats:", err));
}

// ============================================
// 13. LOAD HOME DASHBOARD CONTENT (STATS, LISTS, FEEDBACK)
// ============================================
function loadHomeDashboard() {
    console.log("Loading Dashboard Data...");

    fetch('get_home_content.php')
        .then(response => response.json())
        .then(data => {
            
            // 1. RENDER STATS (Balanced Size, White Text, No Box)
            const statsContainer = document.getElementById('dynamic-stats');
            if (statsContainer && data.stats) {
                statsContainer.innerHTML = '';
                
                // Layout
                statsContainer.style.display = 'flex';
                statsContainer.style.justifyContent = 'center';
                statsContainer.style.flexWrap = 'wrap';
                statsContainer.style.gap = '50px'; 
                statsContainer.style.marginTop = '25px';

                data.stats.forEach(stat => {
                    let icon = '📊'; 
                    const k = (stat.stat_key || "").toLowerCase();
                    if(k.includes('co2')) icon = '☁️';
                    else if(k.includes('tourist')) icon = '👥';
                    else if(k.includes('tree')) icon = '🌳';

                    // CHANGED: font-size reduced from 4.5rem to 3rem
                    const html = `
                        <div style="text-align: center; min-width: 160px;">
                            <h3 style="font-size: 3rem; line-height: 1.2; color: #ffffff; margin: 0; font-weight: 800; text-shadow: 2px 2px 8px rgba(0,0,0,0.6);">
                                ${stat.stat_value} ${icon}
                            </h3>
                            <p style="color: #ffffff; margin-top: 8px; font-size: 1rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px; text-shadow: 1px 1px 4px rgba(0,0,0,0.6);">
                                ${stat.stat_label}
                            </p>
                        </div>
                    `;
                    statsContainer.innerHTML += html;
                });
            }

            // 2. RENDER FEEDBACK
            const feedbackContainer = document.getElementById('dynamic-feedback');
            if (feedbackContainer && data.feedback) {
                let html = '<h3>Rating & Feedback</h3>';
                data.feedback.forEach(fb => {
                    const stars = '⭐'.repeat(parseInt(fb.rating));
                    html += `
                        <div style="margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:10px;">
                            <p style="margin-bottom:5px;"><strong>${fb.name}</strong> <span style="color:gold;">${stars}</span></p>
                            <p style="font-style:italic; color:#555;">“${fb.message}”</p>
                        </div>
                    `;
                });
                feedbackContainer.innerHTML = html;
            }

            // 3. RENDER ACTIVITIES LIST
            const actList = document.getElementById('dynamic-activities');
            if (actList && data.activities) {
                actList.innerHTML = '';
                data.activities.forEach(item => {
                    actList.innerHTML += `<li>${item}</li>`;
                });
            }

            // 4. RENDER DESTINATIONS LIST
            const destList = document.getElementById('dynamic-destinations');
            if (destList && data.destinations) {
                destList.innerHTML = '';
                data.destinations.forEach(item => {
                    destList.innerHTML += `<li>${item}</li>`;
                });
            }

        })
        .catch(err => console.error("Error loading dashboard data:", err));
}

function initExploreSection() {
    const section = document.querySelector('.explore-deeper');
    const titleEl = document.querySelector('.explore-box h2');
    const contentEl = document.querySelector('.explore-box p');

    if (!section || !titleEl || !contentEl) return;

    fetch('get_explore.php')
        .then(response => {
            if (!response.ok) throw new Error("Server Error");
            return response.json();
        })
        .then(data => {
            // Success: Update Text
            if (data && (data.status == 1 || data.status == "1")) {
                titleEl.textContent = data.title;
                contentEl.innerHTML = data.content.replace(/\n/g, '<br>');
                section.style.display = 'flex';
            } else {
                // Hidden: Remove the section entirely
                section.style.display = 'none';
            }
        })
        .catch(err => {
            console.error("Explore Load Error:", err);
            // Show error on screen so you know why it failed
            titleEl.textContent = "Unable to Load";
            contentEl.textContent = "Check console (F12) for details. Ensure database is connected.";
        });
}

// ============================================
// 15. LOAD HERO SLIDES (Dynamic)
// ============================================
function loadHeroSlides() {
    const sliderContainer = document.querySelector('.hero-slider');
    if (!sliderContainer) return;

    fetch('get_hero_slides.php')
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                sliderContainer.innerHTML = ''; // Clear container
                
                data.forEach((slide, index) => {
                    const activeClass = index === 0 ? 'active' : '';
                    
                    // Handle image path
                    let imgPath = slide.image_url;
                    // If it's not a web URL and doesn't start with img/, add img/
                    if (!imgPath.startsWith('http') && !imgPath.startsWith('img/')) {
                        imgPath = 'img/' + imgPath;
                    }

                    const html = `
                        <div class="hero-slide ${activeClass}" style="background-image: url('${imgPath}');">
                          <div class="hero-overlay"></div>
                          <div class="slide-caption">
                            <h2>${slide.title}</h2>
                            <p>${slide.description}</p>
                          </div>
                        </div>
                    `;
                    sliderContainer.innerHTML += html;
                });

                // IMPORTANT: Re-initialize the slider animation/buttons logic 
                // AFTER the HTML has been added.
                setupHeroSlider(); 
            }
        })
        .catch(err => console.error("Error loading hero slides:", err));
}

// ============================================
// 16. LOAD GALLERY (Dynamic) - FIXED
// ============================================
function loadGallery() {
    const galleryContainer = document.querySelector('.image-gallery');
    if (!galleryContainer) return;

    fetch('get_gallery.php') 
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`); 
            }
            return res.json();
        })
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                galleryContainer.innerHTML = ''; // Clear placeholder images
                
                data.forEach(item => {
                    // 1. Resolve Image Path
                    let imgPath = item.image_url;
                    if (imgPath && !imgPath.startsWith('http') && !imgPath.startsWith('img/')) {
                        imgPath = 'img/' + imgPath;
                    }

                    // 2. Create the Wrapper (The Fix)
                    const card = document.createElement('div');
                    card.classList.add('gallery-item'); // Matches CSS .gallery-item

                    // 3. Create the Image
                    const img = document.createElement('img');
                    img.src = imgPath;
                    img.alt = item.caption || 'Gallery Image';

                    // 4. Create the Caption (for hover effect)
                    const caption = document.createElement('p');
                    caption.textContent = item.caption || '';

                    // 5. Assemble and Append
                    card.appendChild(img);
                    card.appendChild(caption);
                    galleryContainer.appendChild(card);
                });
            }
        })
        .catch(err => console.error("Error loading gallery:", err));
}

// ============================================
// 17. LOAD LEARN CONTENT (Dynamic) - UPDATED FOR VIDEO
// ============================================
function loadLearnContent() {
    const container = document.getElementById('learn-content-container');
    if (!container) return;

    fetch('get_learn_content.php')
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                container.innerHTML = ''; // Clear loading message

                data.forEach(item => {
                    let html = '';

                    // --- 1. HERO SECTION (Order #1) ---
                    if (item.order_number == 1) {
                        html = `
                          <section class="learn-more-hero">
                            <div class="learn-hero-text">
                              <h1>${item.title}</h1>
                              <p>${item.content}</p>
                            </div>
                          </section>`;
                    }
                    // --- 2. VIDEO SECTION (New Check!) ---
                    else if (item.video_url) {
                        html = `
                        <section class="learn-section fade-in">
                            <h3>${item.title}</h3>
                            <div class="video-wrapper">
                                <iframe 
                                    src="${item.video_url}" 
                                    title="${item.title}" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                                </iframe>
                            </div>
                            <p>${item.content}</p>
                        </section>`;
                    }
                    // --- 3. PHOTO SECTION (Has Image, Empty Content) ---
                    else if (item.image_url && (!item.content || item.content.trim() === '')) {
                        // Ensure resolveImagePath is defined elsewhere in your code, or simply use item.image_url directly if not needed.
                        const imgPath = (typeof resolveImagePath === 'function') ? resolveImagePath(item.image_url) : item.image_url;
                        
                        html = `
                          <section class="learn-photo-section fade-in">
                            <img src="${imgPath}" alt="${item.title}" class="learn-photo">
                          </section>`;
                    }
                    // --- 4. STANDARD TEXT SECTION ---
                    else {
                        let contentHtml = item.content;
                        if (!contentHtml.includes('<p>') && !contentHtml.includes('<ul') && !contentHtml.includes('<div')) {
                            contentHtml = `<p>${contentHtml}</p>`;
                        }

                        html = `
                          <section class="learn-section fade-in">
                            <h2>${item.title}</h2>
                            ${contentHtml}
                          </section>`;
                    }

                    container.innerHTML += html;
                });

                if (typeof setupFadeInAnimations === 'function') {
                    setupFadeInAnimations();
                }
            } else {
                container.innerHTML = '<p style="text-align:center; padding:50px;">No content found.</p>';
            }
        })
        .catch(err => {
            console.error("Error loading learn content:", err);
            container.innerHTML = '<p style="text-align:center; padding:50px; color:red;">Error loading content.</p>';
        });
}


// ============================================
// 18. LOAD CONTACT DETAILS (Dynamic)
// ============================================
function loadContactDetails() {
    fetch('get_contact.php')
        .then(res => res.json())
        .then(data => {
            // Check if we got data
            if (!data.email) return;

            // --- 1. Update Email ---
            const emailLink = document.getElementById('emailLink');
            if (emailLink) {
                emailLink.href = "mailto:" + data.email;
                emailLink.textContent = data.email;

                // Update the Copy Button next to it
                const btn = emailLink.closest('.info-box')?.querySelector('.copy-btn');
                if (btn) btn.setAttribute('data-copy', data.email);
            }

            // --- 2. Update Phone ---
            const phoneLink = document.getElementById('phoneLink');
            if (phoneLink) {
                phoneLink.textContent = data.phone;
                // Remove spaces/dashes for the actual link (e.g., tel:+60123456789)
                const cleanPhone = data.phone.replace(/[^0-9+]/g, '');
                phoneLink.href = "tel:" + cleanPhone;

                const btn = phoneLink.closest('.info-box')?.querySelector('.copy-btn');
                if (btn) btn.setAttribute('data-copy', data.phone);
            }

            // --- 3. Update Address ---
            const addressText = document.getElementById('addressText');
            if (addressText) {
                addressText.textContent = data.address;

                const btn = addressText.closest('.info-box')?.querySelector('.copy-btn');
                if (btn) btn.setAttribute('data-copy', data.address);
            }
        })
        .catch(err => console.error("Error loading contact info:", err));
}


// ============================================
// 19. CHATBOT SETUP
// ============================================
function setupChatBot() {
    const chatBtn = document.getElementById('aiChatBtn');
    const closeBtn = document.getElementById('close-chat');
    const widget = document.getElementById('chat-widget');
    const sendBtn = document.getElementById('send-chat-btn');
    const input = document.getElementById('chat-input');
    const msgs = document.getElementById('chat-messages');

    // Toggle Chat Window
    if (chatBtn) {
        chatBtn.addEventListener('click', () => {
            widget.classList.toggle('active');
            if (widget.classList.contains('active')) input.focus();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => widget.classList.remove('active'));
    }

    // Send Message Logic
    function sendMessage() {
        const text = input.value.trim();
        if (!text) return;

        // 1. Add User Message
        addMessage(text, 'user-msg');
        input.value = '';

        // 2. Add Loading Spinner
        const loadingId = addMessage('Thinking...', 'bot-msg');

        // 3. Call PHP Backend
        fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: text
                })
            })
            .then(res => res.json())
            .then(data => {
                // Remove loading, add real reply
                const loadEl = document.getElementById(loadingId);
                if (loadEl) loadEl.remove();

                // Format bullet points if present
                let cleanReply = data.reply;
                if (cleanReply.includes('*')) {
                    cleanReply = cleanReply.replace(/\*/g, '<br>• ');
                }
                addMessage(cleanReply, 'bot-msg', true); // true = render as HTML
            })
            .catch(err => {
                console.error(err);
                addMessage('Error connecting to AI.', 'bot-msg');
            });
    }

    if (sendBtn) sendBtn.addEventListener('click', sendMessage);

    // Allow "Enter" key to send
    if (input) {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    }

    function addMessage(text, className, isHTML = false) {
        const div = document.createElement('div');
        div.className = className;
        div.id = 'msg-' + Date.now(); // Unique ID for loading removal
        if (isHTML) div.innerHTML = text;
        else div.textContent = text;
        msgs.appendChild(div);
        msgs.scrollTop = msgs.scrollHeight; // Auto scroll to bottom
        return div.id;
    }
}

function initUserPage() {
    if (localStorage.getItem('isLoggedIn') !== 'true') {
        window.location.href = 'login.html';
        return;
    }

    const username = localStorage.getItem('username');
    if (!username) return;

    fetch('get_user_profile.php?username=' + encodeURIComponent(username))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 1. Populate Sidebar
                document.getElementById('user-name').textContent = data.user.username;
                document.getElementById('user-email').textContent = data.user.email;

                // Format and Display Joined Date
                const joinedEl = document.getElementById('user-joined');
                if (joinedEl) {
                    if (data.user.created_at) {
                        const dateObj = new Date(data.user.created_at);
                        const options = { year: 'numeric', month: 'long' };
                        joinedEl.textContent = dateObj.toLocaleDateString('en-US', options);
                    } else {
                        joinedEl.textContent = "Unknown";
                    }
                }

                // 2. Populate Table
                const tbody = document.getElementById('booking-history-body');
                tbody.innerHTML = '';

                if (data.bookings.length > 0) {
                    data.bookings.forEach((b, index) => {
                        const row = document.createElement('tr');

                        const totalPax = (parseInt(b.pax_adults) || 0) + (parseInt(b.pax_children) || 0);

                        let typeDisplay = "";
                        if (b.booking_type === 'custom') {
                            typeDisplay = "Custom Itinerary";
                            if (b.expanded_names) {
                                typeDisplay += `<br><span style="font-size:0.8em; color:#888; font-weight:400;">${b.expanded_names}</span>`;
                            }
                        } else {
                            typeDisplay = b.package_title || 'Standard Package';
                        }

                        let dateDisplay = b.booking_date;
                        if (b.booking_type === 'standard' && b.durationDays > 1) {
                            const startDate = new Date(b.booking_date);
                            const endDate = new Date(startDate);
                            endDate.setDate(startDate.getDate() + (parseInt(b.durationDays) - 1));
                            const endString = endDate.toISOString().split('T')[0];
                            dateDisplay = `${b.booking_date} <br><span style="font-size:0.8em; color:#aaa;">to ${endString}</span>`;
                        }

                        // Updated HTML (Status Removed)
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${typeDisplay}</td>
                            <td>${dateDisplay}</td>
                            <td>
                                <span style="background:#f4f4f4; padding:4px 10px; border-radius:20px; font-size:0.85rem;">
                                    👤 ${totalPax}
                                </span>
                            </td>
                            <td>RM ${parseFloat(b.total_cost).toFixed(2)}</td>
                        `;
                        
                        tbody.appendChild(row);
                    });
                } else {
                     tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No bookings found.</td></tr>';
                }
            }
        })
        .catch(err => console.error("Fetch Error:", err));
}

// ============================================
// NEW: LOAD FEATURED PACKAGES (HOME PAGE)
// ============================================
function loadFeaturedPackages() {
    const grid = document.getElementById('featured-packages-grid');
    if (!grid) return; // Stop if we aren't on the homepage

    // 1. Reuse your existing database fetcher
    fetchPackagesFromDB().then(packages => {
        // 2. Clear the "Loading..." text
        grid.innerHTML = '';

        // 3. Select the first 3 packages to display as "Featured"
        const topPackages = packages.slice(0, 3);

        if (topPackages.length === 0) {
            grid.innerHTML = '<p style="text-align:center; width:100%;">No packages found.</p>';
            return;
        }

        // 4. Create the cards
        topPackages.forEach(pkg => {
            // Use your existing image helper
            const imgSrc = resolveImagePath(pkg.imageUrl || pkg.image);
            const days = pkg.durationDays || 1;
            const price = parseFloat(pkg.pricePerPax || 0).toFixed(0);

            const card = document.createElement('div');
            card.className = 'feat-card fade-in visible';

            card.innerHTML = `
                <div class="feat-img-box">
                    <img src="${imgSrc}" alt="${pkg.name}" onerror="this.src='https://placehold.co/400x250?text=Eco+Tour'">
                    <span class="feat-price-tag">RM ${price}</span>
                </div>
                <div class="feat-body">
                    <div class="feat-title">${pkg.name}</div>
                    <div class="feat-meta">
                        <span>⏳ ${days} Days</span>
                        <span>🌿 ${pkg.type || 'Eco-Tour'}</span>
                    </div>
                    <a href="details.html?id=${pkg.id}" class="feat-btn">View Details</a>
                </div>
            `;
            grid.appendChild(card);
        });
    });
}