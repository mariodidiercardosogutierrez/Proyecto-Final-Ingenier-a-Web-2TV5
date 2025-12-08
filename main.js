// main.js - SISTEMA COMPLETO DE AUTENTICACIÓN CORREGIDO

console.log("MAIN.JS CARGADO - VERSIÓN CORREGIDA");

// ==================== VARIABLES GLOBALES ====================
let isLoggingOut = false;
let isInitialized = false; // Evitar múltiples inicializaciones

// ==================== FUNCIONES DE SESIÓN ====================

function saveSession(userData) {
    localStorage.setItem("isLoggedIn", "true");
    localStorage.setItem("userEmail", userData.email);
    localStorage.setItem("userRole", userData.role);
    localStorage.setItem("loginTime", new Date().toISOString());
}

function clearSession() {
    localStorage.removeItem("isLoggedIn");
    localStorage.removeItem("userEmail");
    localStorage.removeItem("userRole");
    localStorage.removeItem("loginTime");
}

function checkSession() {
    return localStorage.getItem("isLoggedIn") === "true";
}

function getUserData() {
    if (!checkSession()) return null;
    
    return {
        email: localStorage.getItem("userEmail"),
        role: localStorage.getItem("userRole"),
        loginTime: localStorage.getItem("loginTime")
    };
}

function protectPage(allowedRoles = []) {
    const currentPage = window.location.pathname.split('/').pop();
    
    // Páginas que NO requieren login
    const publicPages = ['login.html', 'register.html', 'cperdida.html'];
    
    if (publicPages.includes(currentPage)) {
        // Si ya está logueado y está en login/register, redirigir
        if (checkSession()) {
            const user = getUserData();
            window.location.href = user.role === "admin" ? "admin.html" : "index.html";
            return false;
        }
        return true;
    }
    
    // Para otras páginas, verificar login
    if (!checkSession()) {
        window.location.href = "login.html";
        return false;
    }
    
    const user = getUserData();
    
    if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
        window.location.href = user.role === "admin" ? "admin.html" : "index.html";
        return false;
    }
    
    return true;
}

// ==================== SIDEBAR ====================
function setupSidebar() {
    const sidebar = document.querySelector(".sidebar");
    const hideBtn = document.querySelector(".sidebar-hide-btn");
    const showBtn = document.getElementById("showSidebarBtn");
    const content = document.querySelector(".content");

    if (!sidebar || !content) return;

    // Configurar sidebar responsive
    if (window.innerWidth <= 768) {
        sidebar.classList.add("hidden");
        content.classList.add("full");
        if (showBtn) showBtn.style.display = "block";
    }

    if (hideBtn && showBtn) {
        hideBtn.addEventListener("click", () => {
            sidebar.classList.add("hidden");
            content.classList.add("full");
            if (showBtn) showBtn.style.display = "block";
        });

        showBtn.addEventListener("click", () => {
            sidebar.classList.remove("hidden");
            content.classList.remove("full");
            showBtn.style.display = "none";
        });
    }

    // Responsive
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            if (showBtn) showBtn.style.display = 'block';
        } else {
            if (showBtn) showBtn.style.display = 'none';
            sidebar.classList.remove("hidden");
            content.classList.remove("full");
        }
    });
}

// ==================== REGISTRO ====================
function setupRegister() {
    const form = document.getElementById('registerForm');
    const message = document.getElementById('registerMessage');

    if (!form || !message) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const nombre = document.getElementById('nombre').value.trim();
        const apellido = document.getElementById('apellido').value.trim();
        const email = document.getElementById('email').value.trim();
        const telefono = document.getElementById('telefono').value.trim();
        const fechaNacimiento = document.getElementById('fechaNacimiento').value;
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        message.textContent = '';
        message.className = 'register-notice';
        message.style.display = 'none';

        // Validaciones
        if(nombre === '' || apellido === ''){
            showMessage('Nombre y apellido son obligatorios', 'error');
            return;
        }

        const emailRegex = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;
        if(!emailRegex.test(email)){
            showMessage('Correo inválido', 'error');
            return;
        }

        if(!/^\d{10}$/.test(telefono)){
            showMessage('El teléfono debe tener 10 dígitos', 'error');
            return;
        }

        const hoy = new Date();
        const nacimiento = new Date(fechaNacimiento);
        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();
        if(mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())){
            edad--;
        }
        if(edad < 12){
            showMessage('Debes tener al menos 12 años para registrarte', 'error');
            return;
        }

        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]).{9,}$/;
        if(!passwordRegex.test(password)){
            showMessage('Contraseña debe tener mínimo 9 caracteres, incluyendo mayúscula, minúscula, número y carácter especial', 'error');
            return;
        }

        if(password !== confirmPassword){
            showMessage('Las contraseñas no coinciden', 'error');
            return;
        }

        // Registro exitoso
        showMessage('¡Registro exitoso! Redirigiendo al login...', 'success');
        
        const users = JSON.parse(localStorage.getItem("users") || "[]");
        users.push({
            nombre,
            apellido,
            email,
            telefono,
            fechaNacimiento,
            password: btoa(password),
            role: "user",
            fechaRegistro: new Date().toISOString()
        });
        localStorage.setItem("users", JSON.stringify(users));
        
        setTimeout(() => {
            window.location.href = "login.html";
        }, 2000);
    });
    
    function showMessage(text, type) {
        message.textContent = text;
        message.className = 'register-notice ' + type;
        message.style.display = 'block';
    }
}

// ==================== LOGIN ====================
function setupLogin() {
    const loginForm = document.getElementById("loginForm");
    if (!loginForm) return;

    // Si ya está logueado, redirigir
    if (checkSession()) {
        const user = getUserData();
        window.location.href = user.role === "admin" ? "admin.html" : "index.html";
        return;
    }

    loginForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();

        // Credenciales por defecto
        const adminEmail = "admin@music.com";
        const adminPass = "Admin123!";
        const userEmail = "user@music.com";
        const userPass = "User123!";

        // Verificar usuarios registrados
        const users = JSON.parse(localStorage.getItem("users") || "[]");
        const registeredUser = users.find(u => {
            try {
                return u.email === email && atob(u.password) === password;
            } catch {
                return false;
            }
        });

        let loginSuccess = false;
        let userData = {};

        if (email === adminEmail && password === adminPass) {
            loginSuccess = true;
            userData = { email: adminEmail, role: "admin" };
        } 
        else if (email === userEmail && password === userPass) {
            loginSuccess = true;
            userData = { email: userEmail, role: "user" };
        }
        else if (registeredUser) {
            loginSuccess = true;
            userData = { 
                email: registeredUser.email, 
                role: registeredUser.role || "user" 
            };
        }

        if (loginSuccess) {
            saveSession(userData);
            console.log("Login exitoso:", userData);
            
            setTimeout(() => {
                window.location.href = userData.role === "admin" ? "admin.html" : "index.html";
            }, 500);
        } else {
            alert("Correo o contraseña incorrectos");
            document.getElementById("password").value = "";
        }
    });
}

// ==================== LOGOUT ====================
function logoutUser() {
    if (isLoggingOut) return;
    isLoggingOut = true;
    
    const user = getUserData();
    const emailDisplay = user ? (user.email.length > 25 ? user.email.substring(0, 22) + '...' : user.email) : 'Usuario';
    
    if (confirm(`¿Cerrar sesión de ${emailDisplay}?`)) {
        clearSession();
        
        const userDropdown = document.querySelector('.user-dropdown');
        if (userDropdown) userDropdown.classList.remove('active');
        
        console.log("Sesión cerrada correctamente");
        window.location.href = "login.html";
        
        setTimeout(() => { isLoggingOut = false; }, 1000);
    } else {
        isLoggingOut = false;
    }
}

// ==================== DROPDOWN DE USUARIO ====================
function setupUserDropdown() {
    const userBtn = document.querySelector('.user-btn');
    const userDropdown = document.querySelector('.user-dropdown');
    
    if (!userBtn || !userDropdown) return;
    
    // Mostrar info del usuario
    const user = getUserData();
    if (user) {
        const emailDisplay = document.getElementById('userEmailDisplay');
        const roleDisplay = document.getElementById('userRoleDisplay');
        
        if (emailDisplay) emailDisplay.textContent = user.email.split('@')[0];
        if (roleDisplay) roleDisplay.textContent = user.role === 'admin' ? 'Administrador' : 'Usuario';
    }
    
    let openTimer = null;
    let closeTimer = null;
    
    userBtn.addEventListener('mouseenter', () => {
        if (openTimer) clearTimeout(openTimer);
        openTimer = setTimeout(() => {
            if (!userDropdown.classList.contains('active')) {
                userDropdown.classList.add('active');
            }
        }, 300);
    });
    
    userBtn.addEventListener('mouseleave', () => {
        if (openTimer) clearTimeout(openTimer);
        openTimer = null;
        
        setTimeout(() => {
            if (!userDropdown.matches(':hover')) {
                closeTimer = setTimeout(() => {
                    userDropdown.classList.remove('active');
                    closeTimer = null;
                }, 3000);
            }
        }, 100);
    });
    
    userDropdown.addEventListener('mouseenter', () => {
        if (closeTimer) clearTimeout(closeTimer);
        closeTimer = null;
    });
    
    userDropdown.addEventListener('mouseleave', () => {
        setTimeout(() => {
            if (!userBtn.matches(':hover')) {
                closeTimer = setTimeout(() => {
                    userDropdown.classList.remove('active');
                    closeTimer = null;
                }, 3000);
            }
        }, 50);
    });
    
    userBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('active');
    });
    
    document.addEventListener('click', (e) => {
        if (userDropdown.classList.contains('active') &&
            !userDropdown.contains(e.target) && 
            !userBtn.contains(e.target)) {
            userDropdown.classList.remove('active');
        }
    });
    
    // Botón de logout en dropdown
    const logoutBtn = document.getElementById('logoutBtnDropdown');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            logoutUser();
        });
    }
}

// ==================== ADMIN LOGOUT ====================
function setupAdminLogout() {
    const adminLogoutBtn = document.getElementById('logoutBtn');
    if (adminLogoutBtn) {
        adminLogoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            logoutUser();
        });
    }
}

// ==================== CATÁLOGO ====================
function setupCatalog() {
    // Solo si estamos en una página con catálogo
    if (!document.querySelector('.product-card')) return;
    
    // Miniaturas
    document.querySelectorAll('.product-card').forEach(card => {
        const mainImg = card.querySelector('.product-image img');
        card.querySelectorAll('.thumb').forEach(thumb => {
            thumb.addEventListener('click', e => {
                e.preventDefault();
                mainImg.src = thumb.src;
            });
        });
    });
    
    // Botones de género
    document.querySelectorAll('.genre-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.genre-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            console.log(`Filtrando por género: ${this.textContent}`);
        });
    });
    
    // Botones de compra
    document.querySelectorAll('.buy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const albumName = this.closest('.product-card').querySelector('.product-name').textContent;
            const albumPrice = this.closest('.product-card').querySelector('.meta-item.price').textContent;
            alert(`¡${albumName} agregado al carrito! ${albumPrice}`);
            addToCart(albumName, albumPrice);
        });
    });
}

// ==================== FUNCIONES AUXILIARES ====================
function addToCart(productName, price) {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const priceNumber = parseInt(price.replace(/[^0-9]/g, ''));
    
    const existingProduct = cart.find(item => item.name === productName);
    
    if (existingProduct) {
        existingProduct.quantity += 1;
    } else {
        cart.push({
            name: productName,
            price: priceNumber,
            quantity: 1,
            date: new Date().toISOString()
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    console.log(`Carrito actualizado: ${totalItems} productos`);
}

function handleImageErrors() {
    document.querySelectorAll('img').forEach(img => {
        img.onerror = function() {
            this.src = 'https://via.placeholder.com/400x300/8b0000/ffffff?text=Álbum+de+Jazz';
        };
    });
}

// ==================== USUARIOS POR DEFECTO ====================
function setupDefaultUsers() {
    if (!localStorage.getItem('users')) {
        const defaultUsers = [
            {
                nombre: "Admin",
                apellido: "Music",
                email: "admin@music.com",
                telefono: "1234567890",
                fechaNacimiento: "1990-01-01",
                password: btoa("Admin123!"),
                role: "admin",
                fechaRegistro: new Date().toISOString()
            },
            {
                nombre: "Usuario",
                apellido: "Normal",
                email: "user@music.com",
                telefono: "0987654321",
                fechaNacimiento: "2000-01-01",
                password: btoa("User123!"),
                role: "user",
                fechaRegistro: new Date().toISOString()
            }
        ];
        localStorage.setItem('users', JSON.stringify(defaultUsers));
        console.log("Usuarios por defecto creados");
    }
}

// ==================== INICIALIZACIÓN PRINCIPAL ====================
function initializeApp() {
    if (isInitialized) return;
    isInitialized = true;
    
    console.log("Inicializando aplicación...");
    
    // Verificar protección de páginas
    const currentPage = window.location.pathname.split('/').pop();
    
    if (currentPage === 'admin.html') {
        protectPage(['admin']);
    } else if (!['login.html', 'register.html', 'cperdida.html'].includes(currentPage)) {
        protectPage(['user', 'admin']);
    }
    
    // Configurar usuarios por defecto
    setupDefaultUsers();
    
    // Configurar componentes específicos
    setupSidebar();
    setupRegister();
    setupLogin();
    setupUserDropdown();
    setupAdminLogout();
    setupCatalog();
    
    // Imágenes
    handleImageErrors();
}

// ==================== EJECUCIÓN ====================
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    initializeApp();
}