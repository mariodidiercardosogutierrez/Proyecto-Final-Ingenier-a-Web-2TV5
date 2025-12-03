// main.js - SISTEMA COMPLETO DE AUTENTICACIÓN

console.log("MAIN.JS CARGADO");

// ==================== VARIABLES GLOBALES ====================
let isLoggingOut = false; // Bandera para evitar múltiples logout simultáneos

// ==================== FUNCIONES DE SESIÓN ====================

// Guardar datos de sesión
function saveSession(userData) {
    localStorage.setItem("isLoggedIn", "true");
    localStorage.setItem("userEmail", userData.email);
    localStorage.setItem("userRole", userData.role);
    localStorage.setItem("loginTime", new Date().toISOString());
}

// Eliminar sesión
function clearSession() {
    localStorage.removeItem("isLoggedIn");
    localStorage.removeItem("userEmail");
    localStorage.removeItem("userRole");
    localStorage.removeItem("loginTime");
}

// Verificar si hay sesión activa
function checkSession() {
    return localStorage.getItem("isLoggedIn") === "true";
}

// Obtener datos del usuario
function getUserData() {
    if (!checkSession()) return null;
    
    return {
        email: localStorage.getItem("userEmail"),
        role: localStorage.getItem("userRole"),
        loginTime: localStorage.getItem("loginTime")
    };
}

// Proteger páginas que requieren login
function protectPage(allowedRoles = []) {
    if (!checkSession()) {
        // No está logueado, redirigir a login
        window.location.href = "login.html";
        return false;
    }
    
    const user = getUserData();
    
    // Si se especifican roles y el usuario no tiene permiso
    if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
        // Si es admin intentando acceder a user, o viceversa
        window.location.href = user.role === "admin" ? "admin.html" : "index.html";
        return false;
    }
    
    return true;
}

// ==================== SIDEBAR (solo si no estamos en login/register) ====================
const isLoginPage = document.getElementById("loginForm");
const isRegisterPage = document.getElementById("registerForm");

if (!isLoginPage && !isRegisterPage) {
    // Verificar sesión antes de mostrar contenido
    if (window.location.pathname.includes("admin.html")) {
        protectPage(["admin"]);
    } else if (!window.location.pathname.includes("login.html") && 
               !window.location.pathname.includes("register.html")) {
        protectPage(["user", "admin"]);
    }
    
    // Configurar sidebar
    const sidebar = document.querySelector(".sidebar");
    const hideBtn = document.querySelector(".sidebar-hide-btn");
    const showBtn = document.getElementById("showSidebarBtn");
    const content = document.querySelector(".content");

    if (hideBtn && showBtn && sidebar && content) {
        hideBtn.addEventListener("click", () => {
            sidebar.classList.add("hidden");
            content.classList.add("full");
            showBtn.style.display = "block";
        });

        showBtn.addEventListener("click", () => {
            sidebar.classList.remove("hidden");
            content.classList.remove("full");
            showBtn.style.display = "none";
        });
    }
}

// ==================== REGISTRO ====================
const form = document.getElementById('registerForm');
const message = document.getElementById('registerMessage');

if (form && message) {
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
        
        // Guardar usuario en localStorage (simulado)
        const users = JSON.parse(localStorage.getItem("users") || "[]");
        users.push({
            nombre,
            apellido,
            email,
            telefono,
            fechaNacimiento,
            password: btoa(password), // ¡CUIDADO! En producción usaría encriptación real
            role: "user",
            fechaRegistro: new Date().toISOString()
        });
        localStorage.setItem("users", JSON.stringify(users));
        
        // Redirigir después de 2 segundos
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
const loginForm = document.getElementById("loginForm");

if (loginForm) {
    // Si ya está logueado, redirigir según su rol
    if (checkSession()) {
        const user = getUserData();
        window.location.href = user.role === "admin" ? "admin.html" : "index.html";
    }
    
    loginForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();

        const adminEmail = "admin@music.com";
        const adminPass = "Admin123!";
        const userEmail = "user@music.com";
        const userPass = "User123!";

        // Verificar si hay usuarios registrados
        const users = JSON.parse(localStorage.getItem("users") || "[]");
        const registeredUser = users.find(u => u.email === email && atob(u.password) === password);

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
            userData = { email: registeredUser.email, role: registeredUser.role };
        }

        if (loginSuccess) {
            // Guardar sesión
            saveSession(userData);
            
            // Redirigir según rol
            setTimeout(() => {
                window.location.href = userData.role === "admin" ? "admin.html" : "index.html";
            }, 500);
        } else {
            alert("Correo o contraseña incorrectos");
        }
    });
}

// ==================== FUNCIÓN PRINCIPAL PARA CERRAR SESIÓN ====================
function logoutUser() {
    // Evitar múltiples ejecuciones simultáneas
    if (isLoggingOut) {
        console.log("Logout ya en proceso...");
        return;
    }
    
    isLoggingOut = true;
    
    const user = getUserData();
    let emailDisplay = user ? user.email : 'Usuario';
    
    // Acortar email si es muy largo
    if (emailDisplay.length > 25) {
        emailDisplay = emailDisplay.substring(0, 22) + '...';
    }
    
    const confirmMessage = user ? 
        `¿Cerrar sesión de ${emailDisplay}?` :
        "¿Estás seguro de que deseas cerrar sesión?";
    
    // Solo UN confirm
    if (confirm(confirmMessage)) {
        // Limpiar sesión
        clearSession();
        
        // Cerrar dropdown si está abierto
        const userDropdown = document.querySelector('.user-dropdown');
        if (userDropdown) {
            userDropdown.classList.remove('active');
        }
        
        console.log("Sesión cerrada correctamente");
        
        // Redirigir a login inmediatamente
        window.location.href = "login.html";
        
        // Resetear bandera después de un tiempo
        setTimeout(() => {
            isLoggingOut = false;
        }, 1000);
    } else {
        // Si cancela, resetear bandera
        isLoggingOut = false;
    }
}

// ==================== DROPDOWN DE USUARIO (para index.html) ====================
function setupUserDropdown() {
    const userBtn = document.querySelector('.user-btn');
    const userDropdown = document.querySelector('.user-dropdown');
    
    if (!userBtn || !userDropdown) return;
    
    // Mostrar info del usuario
    const user = getUserData();
    if (user) {
        const emailDisplay = document.getElementById('userEmailDisplay');
        const roleDisplay = document.getElementById('userRoleDisplay');
        
        if (emailDisplay) {
            emailDisplay.textContent = user.email.split('@')[0];
        }
        if (roleDisplay) {
            roleDisplay.textContent = user.role === 'admin' ? 'Administrador' : 'Usuario';
        }
    }
    
    // Variables para controlar timers
    let openTimer = null;
    let closeTimer = null;
    const HOVER_OPEN_DELAY = 300;
    const HOVER_CLOSE_DELAY = 3000;
    
    // Función para abrir el dropdown
    function openDropdown() {
        if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
        }
        
        if (!userDropdown.classList.contains('active')) {
            userDropdown.classList.add('active');
            console.log("Dropdown abierto por hover");
        }
    }
    
    // Función para cerrar el dropdown con delay
    function closeDropdownWithDelay() {
        if (openTimer) {
            clearTimeout(openTimer);
            openTimer = null;
        }
        
        if (!userDropdown.matches(':hover') && !userBtn.matches(':hover')) {
            closeTimer = setTimeout(() => {
                if (userDropdown.classList.contains('active')) {
                    userDropdown.classList.remove('active');
                    console.log("Dropdown cerrado después de tiempo");
                }
                closeTimer = null;
            }, HOVER_CLOSE_DELAY);
        }
    }
    
    // ===== EVENTOS PARA EL BOTÓN DE USUARIO =====
    
    userBtn.addEventListener('mouseenter', function() {
        if (openTimer) clearTimeout(openTimer);
        openTimer = setTimeout(openDropdown, HOVER_OPEN_DELAY);
    });
    
    userBtn.addEventListener('mouseleave', function() {
        if (openTimer) {
            clearTimeout(openTimer);
            openTimer = null;
        }
        
        setTimeout(() => {
            if (!userDropdown.matches(':hover')) {
                closeDropdownWithDelay();
            }
        }, 100);
    });
    
    // ===== EVENTOS PARA EL DROPDOWN =====
    
    userDropdown.addEventListener('mouseenter', function() {
        if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
        }
    });
    
    userDropdown.addEventListener('mouseleave', function(e) {
        setTimeout(() => {
            if (!userBtn.matches(':hover')) {
                closeDropdownWithDelay();
            }
        }, 50);
    });
    
    // ===== CLICK MANUAL =====
    
    userBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        if (openTimer) clearTimeout(openTimer);
        if (closeTimer) clearTimeout(closeTimer);
        
        openTimer = null;
        closeTimer = null;
        
        userDropdown.classList.toggle('active');
    });
    
    // Cerrar al hacer click fuera
    document.addEventListener('click', function(e) {
        if (userDropdown.classList.contains('active') &&
            !userDropdown.contains(e.target) && 
            !userBtn.contains(e.target)) {
            
            if (closeTimer) clearTimeout(closeTimer);
            userDropdown.classList.remove('active');
        }
    });
    
    // ===== BOTÓN DE CERRAR SESIÓN EN DROPDOWN =====
    
    const logoutBtn = document.getElementById('logoutBtnDropdown');
    if (logoutBtn) {
        // Usar evento con once: true para ejecutar solo una vez
        logoutBtn.addEventListener('click', function handleLogout(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation(); // Detener propagación inmediata
            
            // Limpiar timers
            if (openTimer) clearTimeout(openTimer);
            if (closeTimer) clearTimeout(closeTimer);
            
            // Remover este event listener después de ejecutar
            logoutBtn.removeEventListener('click', handleLogout);
            
            // Llamar a logoutUser
            logoutUser();
        }, { once: true }); // {once: true} asegura que se ejecute solo una vez
    }
    
    console.log("Dropdown configurado");
}

// ==================== CONFIGURACIÓN PARA ADMIN.HTML ====================
function setupLogoutButton() {
    // Buscar botón de logout específico en admin.html
    const adminLogoutBtn = document.getElementById('logoutBtn');
    if (adminLogoutBtn) {
        // Usar once: true para evitar múltiples bindings
        adminLogoutBtn.addEventListener('click', function handleAdminLogout(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Remover este event listener
            adminLogoutBtn.removeEventListener('click', handleAdminLogout);
            
            // Llamar a logoutUser
            logoutUser();
        }, { once: true });
    }
}

// ==================== INICIALIZACIÓN ====================
// Solo una inicialización limpia
function initializeApp() {
    console.log("Inicializando aplicación...");
    
    // Verificar sesión
    if (checkSession()) {
        const user = getUserData();
        console.log("Usuario logueado:", user);
    }
    
    // Configurar componentes solo si existen
    setupUserDropdown();
    setupLogoutButton();
}

// Ejecutar inicialización cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp);
} else {
    // DOM ya cargado
    initializeApp();
}

