// ====================
// SIDEBAR
// ====================
document.addEventListener("DOMContentLoaded", function () {
    const showBtn = document.getElementById("showSidebarBtn");
    const sidebar = document.querySelector(".sidebar");
    const hideBtn = document.querySelector(".sidebar-hide-btn");

    if (showBtn) {
        showBtn.addEventListener("click", function () {
            sidebar.classList.add("active");
            showBtn.style.display = "none";
        });
    }

    if (hideBtn) {
        hideBtn.addEventListener("click", function () {
            sidebar.classList.remove("active");
            if (showBtn) showBtn.style.display = "block";
        });
    }

    // Cerrar sidebar al hacer clic fuera de ella en móviles
    document.addEventListener("click", function (event) {
        if (window.innerWidth <= 768) {
            if (sidebar.classList.contains("active") &&
                !sidebar.contains(event.target) &&
                event.target !== showBtn) {
                sidebar.classList.remove("active");
                if (showBtn) showBtn.style.display = "block";
            }
        }
    });

    // Evitar cierre en desktop
    sidebar.addEventListener("click", function (event) {
        event.stopPropagation();
    });

    // Inicializar funciones de usuario y carrito
    initUserDisplay();
    initCartCount();
});

// ====================
// LOGIN/LOGOUT
// ====================
function initUserDisplay() {
    // Mostrar información del usuario si está logueado
    const userEmail = localStorage.getItem("userEmail");
    const userName = localStorage.getItem("userName");

    const userNameDisplay = document.getElementById("userNameDisplay");
    const userEmailDisplay = document.getElementById("userEmailDisplay");

    if (userNameDisplay && userEmailDisplay) {
        if (userName && userEmail) {
            userNameDisplay.textContent = userName;
            userEmailDisplay.textContent = userEmail;
        } else {
            userNameDisplay.textContent = "Invitado";
            userEmailDisplay.textContent = "No has iniciado sesión";
        }
    }

    // Botón de logout en dropdown
    const logoutBtnDropdown = document.getElementById("logoutBtnDropdown");
    if (logoutBtnDropdown) {
        logoutBtnDropdown.addEventListener("click", function () {
            localStorage.removeItem("userEmail");
            localStorage.removeItem("userName");
            localStorage.removeItem("userId");
            localStorage.removeItem("userRole");
            alert("Sesión cerrada correctamente");
            window.location.href = "index.html";
        });
    }

    // Botón de logout en sidebar (si existe)
    const logoutBtnSidebar = document.getElementById("logoutBtnSidebar");
    if (logoutBtnSidebar) {
        logoutBtnSidebar.addEventListener("click", function () {
            localStorage.removeItem("userEmail");
            localStorage.removeItem("userName");
            localStorage.removeItem("userId");
            localStorage.removeItem("userRole");
            alert("Sesión cerrada correctamente");
            window.location.href = "index.html";
        });
    }
}

// ====================
// CARRITO - FUNCIONES PRINCIPALES
// ====================

// Función principal para agregar al carrito
async function addToCart(productId, productName, price, event = null) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const userId = localStorage.getItem('userId');
    if (!userId) {
        alert('Debes iniciar sesión para agregar productos al carrito');
        window.location.href = 'login.html';
        return;
    }
    
    try {
        // Agregar al carrito usando userId directamente
        const addResponse = await fetch('api.php?action=addToCart', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                userId: userId,
                tipo: 'album',
                id_producto: productId,
                precio: price,
                cantidad: 1
            })
        });
        
        const addResult = await addResponse.json();
        
        if (addResult.success) {
            // Feedback visual
            if (event && event.target) {
                const button = event.target;
                const originalText = button.textContent;
                button.textContent = '✓ Añadido';
                button.style.background = 'green';
                button.style.color = 'white';
                button.disabled = true;
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.style.background = '';
                    button.style.color = '';
                    button.disabled = false;
                }, 1500);
            }
            
            // Actualizar contador del carrito
            updateCartCount();
            
            // Mostrar notificación
            showCartNotification('Producto añadido al carrito');
        } else {
            alert('Error: ' + addResult.message);
        }
    } catch (error) {
        console.error('Error agregando al carrito:', error);
        alert('Error de conexión al servidor');
    }
}

// Función auxiliar para obtener userId (compatibilidad)
async function getUserIdForCart() {
    const userId = localStorage.getItem('userId');
    if (userId) return parseInt(userId);
    
    // Si no hay userId, intentar obtenerlo del email
    const userEmail = localStorage.getItem('userEmail');
    if (!userEmail) return null;
    
    try {
        const response = await fetch('api.php?action=getUserId', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: userEmail })
        });
        
        const data = await response.json();
        if (data.success && data.userId) {
            localStorage.setItem('userId', data.userId);
            return data.userId;
        }
        return null;
    } catch (error) {
        console.error('Error obteniendo userId:', error);
        return null;
    }
}

// Actualizar contador del carrito
async function updateCartCount() {
    const userId = localStorage.getItem('userId');
    if (!userId) return;
    
    try {
        const response = await fetch(`api.php?action=getCartItems&userId=${userId}`);
        const data = await response.json();
        
        if (data.success) {
            // Actualizar todos los botones del carrito
            document.querySelectorAll('.icon-btn').forEach(btn => {
                if (btn.textContent.includes('🛒') || (btn.href && btn.href.includes('carrito'))) {
                    if (data.count > 0) {
                        // Si ya tiene un span, actualizarlo, si no, agregarlo
                        let countSpan = btn.querySelector('.cart-count');
                        if (!countSpan) {
                            countSpan = document.createElement('span');
                            countSpan.className = 'cart-count';
                            btn.appendChild(countSpan);
                        }
                        countSpan.textContent = ` (${data.count})`;
                    } else {
                        // Eliminar el contador si no hay items
                        const countSpan = btn.querySelector('.cart-count');
                        if (countSpan) {
                            countSpan.remove();
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error actualizando contador:', error);
    }
}

// Inicializar contador del carrito
function initCartCount() {
    // Esperar a que se cargue el usuario
    setTimeout(() => {
        if (localStorage.getItem('userId')) {
            updateCartCount();
        }
    }, 1000);
}

// Mostrar notificación del carrito
function showCartNotification(message) {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = 'cart-notification';
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 15px 20px;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Añadir estilos de animación para notificaciones
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .cart-count {
        background: #8b0000;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
        margin-left: 5px;
    }
`;
document.head.appendChild(style);

// Hacer funciones disponibles globalmente
window.addToCart = addToCart;
window.getUserIdForCart = getUserIdForCart;
window.updateCartCount = updateCartCount;

// ====================
// DEBUG
// ====================
function debugCart() {
    console.log('Debug del carrito:');
    console.log('User ID en localStorage:', localStorage.getItem('userId'));
    console.log('User email en localStorage:', localStorage.getItem('userEmail'));
    
    // Probar API directamente
    fetch('api.php?action=getAlbums')
        .then(res => res.json())
        .then(data => console.log('API funciona:', data.success))
        .catch(err => console.error('API error:', err));
}

// Función para guardar datos de usuario después del login
function saveUserData(userData) {
    if (userData && userData.id) {
        localStorage.setItem('userId', userData.id);
        localStorage.setItem('userName', userData.nombre);
        localStorage.setItem('userEmail', userData.email);
        if (userData.role) {
            localStorage.setItem('userRole', userData.role);
        }
    }
}

// Verificar si hay usuario al cargar la página
window.addEventListener('load', function() {
    if (!localStorage.getItem('userId')) {
        // Intentar obtener userId si hay email pero no userId
        const userEmail = localStorage.getItem('userEmail');
        if (userEmail) {
            getUserIdForCart().then(userId => {
                if (userId) {
                    localStorage.setItem('userId', userId);
                    updateCartCount();
                }
            });
        }
    } else {
        updateCartCount();
    }
});

// ====================
// LOGIN FORM HANDLER
// ====================
function initLoginForm() {
    const loginForm = document.getElementById('loginForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                alert('Por favor, complete todos los campos');
                return;
            }
            
            try {
                const response = await fetch('api.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Guardar datos del usuario
                    saveUserData(data.user);
                    
                    // Mostrar mensaje de éxito
                    alert('Inicio de sesión exitoso');
                    
                    // Redirigir a la página principal
                    window.location.href = 'index.html';
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión con el servidor');
            }
        });
    }
}

// Función para guardar datos del usuario (ya existe en tu main.js)
function saveUserData(userData) {
    if (userData && userData.id) {
        localStorage.setItem('userId', userData.id);
        localStorage.setItem('userName', userData.nombre);
        localStorage.setItem('userEmail', userData.email);
        if (userData.role) {
            localStorage.setItem('userRole', userData.role);
        }
    }
}

// Inicializar el formulario de login si existe
document.addEventListener('DOMContentLoaded', function() {
    initLoginForm();
    // ... el resto de tu código existente
});

// ====================
// REGISTRO DE USUARIO CON VALIDACIONES COMPLETAS
// ====================
function initRegisterForm() {
    const registerForm = document.getElementById('registerForm');
    
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Obtener valores del formulario
            const nombre = document.getElementById('nombre').value.trim();
            const apellido = document.getElementById('apellido').value.trim();
            const email = document.getElementById('email').value.trim();
            const telefono = document.getElementById('telefono').value.trim();
            const fechaNacimiento = document.getElementById('fechaNacimiento').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            const messageElement = document.getElementById('registerMessage');
            
            // 1. VALIDAR NOMBRE Y APELLIDO
            if (nombre === '' || apellido === '') {
                showMessage(messageElement, 'Nombre y apellido son obligatorios', 'error');
                return;
            }
            
            // Validar que solo contengan letras y espacios
            const nameRegex = /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/;
            if (!nameRegex.test(nombre)) {
                showMessage(messageElement, 'El nombre solo puede contener letras', 'error');
                return;
            }
            
            if (!nameRegex.test(apellido)) {
                showMessage(messageElement, 'El apellido solo puede contener letras', 'error');
                return;
            }
            
            // 2. VALIDAR EMAIL (con @ y dominio)
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showMessage(messageElement, 'Por favor ingresa un email válido (ejemplo@dominio.com)', 'error');
                return;
            }
            
            // Validar dominio mínimo (ej: a@b.c)
            if (email.split('@')[1].length < 3) {
                showMessage(messageElement, 'El dominio del email no es válido', 'error');
                return;
            }
            
            // 3. VALIDAR TELÉFONO (10 dígitos, solo números)
            const phoneRegex = /^\d{10}$/;
            const cleanPhone = telefono.replace(/\D/g, ''); // Eliminar todo lo que no sea número
            
            if (!phoneRegex.test(cleanPhone)) {
                showMessage(messageElement, 'El teléfono debe tener exactamente 10 dígitos numéricos', 'error');
                document.getElementById('telefono').value = cleanPhone; // Mostrar solo números
                return;
            }
            
            // Actualizar el campo con solo números
            document.getElementById('telefono').value = cleanPhone;
            
            // 4. VALIDAR FECHA DE NACIMIENTO
            if (!fechaNacimiento) {
                showMessage(messageElement, 'La fecha de nacimiento es obligatoria', 'error');
                return;
            }
            
            const birthDate = new Date(fechaNacimiento);
            const today = new Date();
            
            // Calcular edad exacta
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            const dayDiff = today.getDate() - birthDate.getDate();
            
            if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                age--;
            }
            
            // Validar que sea mayor a 18 y menor a 100 años
            if (age < 18) {
                showMessage(messageElement, 'Debes tener al menos 18 años para registrarte', 'error');
                return;
            }
            
            if (age > 100) {
                showMessage(messageElement, 'La edad no puede ser mayor a 100 años', 'error');
                return;
            }
            
            // 5. VALIDAR CONTRASEÑA (mayúsculas, minúsculas, números, caracteres especiales, mínimo 9 caracteres)
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{9,}$/;
            
            // Validar longitud mínima
            if (password.length < 9) {
                showMessage(messageElement, 'La contraseña debe tener al menos 9 caracteres', 'error');
                return;
            }
            
            // Validar mayúsculas
            if (!/(?=.*[A-Z])/.test(password)) {
                showMessage(messageElement, 'La contraseña debe contener al menos una mayúscula', 'error');
                return;
            }
            
            // Validar minúsculas
            if (!/(?=.*[a-z])/.test(password)) {
                showMessage(messageElement, 'La contraseña debe contener al menos una minúscula', 'error');
                return;
            }
            
            // Validar números
            if (!/(?=.*\d)/.test(password)) {
                showMessage(messageElement, 'La contraseña debe contener al menos un número', 'error');
                return;
            }
            
            // Validar caracteres especiales
            if (!/(?=.*[@$!%*?&])/.test(password)) {
                showMessage(messageElement, 'La contraseña debe contener al menos un carácter especial (@$!%*?&)', 'error');
                return;
            }
            
            // Validar que no contenga espacios
            if (/\s/.test(password)) {
                showMessage(messageElement, 'La contraseña no puede contener espacios', 'error');
                return;
            }
            
            // 6. VERIFICAR QUE LAS CONTRASEÑAS COINCIDAN
            if (password !== confirmPassword) {
                showMessage(messageElement, 'Las contraseñas no coinciden', 'error');
                return;
            }
            
            // 7. Validar fortaleza adicional de contraseña (opcional)
            const strength = checkPasswordStrength(password);
            if (strength < 3) {
                showMessage(messageElement, 'La contraseña es demasiado débil. Añade más variedad de caracteres', 'error');
                return;
            }
            
            // Si pasa todas las validaciones del frontend, enviar al servidor
            showMessage(messageElement, 'Validando información...', 'info');
            
            try {
                const response = await fetch('api.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        nombre: nombre,
                        apellido: apellido,
                        email: email,
                        telefono: cleanPhone,
                        fecha_nac: fechaNacimiento,
                        password: password
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage(messageElement, '¡Registro exitoso! Redirigiendo...', 'success');
                    
                    // Opcional: Auto-login después del registro
                    localStorage.setItem('userEmail', email);
                    localStorage.setItem('userName', nombre + ' ' + apellido);
                    localStorage.setItem('userId', data.userId);
                    localStorage.setItem('userRole', data.user.role || 'user');
                    
                    // Redirigir después de 2 segundos
                    setTimeout(() => {
                        window.location.href = 'index.html';
                    }, 2000);
                } else {
                    showMessage(messageElement, 'Error: ' + data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage(messageElement, 'Error de conexión con el servidor', 'error');
            }
        });
    }
}

// Función para verificar fortaleza de contraseña
function checkPasswordStrength(password) {
    let strength = 0;
    
    // Longitud
    if (password.length >= 9) strength++;
    if (password.length >= 12) strength++;
    
    // Variedad de caracteres
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[@$!%*?&]/.test(password)) strength++;
    
    // Evitar patrones simples
    if (!/(.)\1{2,}/.test(password)) strength++; // No 3 caracteres repetidos
    
    return strength;
}

// Función para validar en tiempo real (opcional, mejora UX)
function initRealTimeValidation() {
    const inputs = {
        telefono: document.getElementById('telefono'),
        email: document.getElementById('email'),
        password: document.getElementById('password'),
        confirmPassword: document.getElementById('confirmPassword'),
        fechaNacimiento: document.getElementById('fechaNacimiento')
    };
    
    if (inputs.telefono) {
        inputs.telefono.addEventListener('input', function(e) {
            // Solo permitir números
            this.value = this.value.replace(/\D/g, '');
            
            // Limitar a 10 dígitos
            if (this.value.length > 10) {
                this.value = this.value.substring(0, 10);
            }
        });
    }
    
    if (inputs.password) {
        inputs.password.addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.getElementById('passwordStrength');
            
            if (!strengthMeter) {
                // Crear medidor de fortaleza si no existe
                const meter = document.createElement('div');
                meter.id = 'passwordStrength';
                meter.style.marginTop = '5px';
                meter.style.fontSize = '12px';
                this.parentNode.appendChild(meter);
            }
            
            // Actualizar medidor
            const strength = checkPasswordStrength(password);
            updatePasswordStrengthMeter(strength);
        });
    }
    
    if (inputs.confirmPassword) {
        inputs.confirmPassword.addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirm = this.value;
            
            if (confirm && password !== confirm) {
                this.style.borderColor = '#ff3333';
            } else {
                this.style.borderColor = '';
            }
        });
    }
}

// Función para actualizar el medidor de fortaleza de contraseña
function updatePasswordStrengthMeter(strength) {
    const meter = document.getElementById('passwordStrength');
    if (!meter) return;
    
    let text = '';
    let color = '';
    
    switch (true) {
        case (strength <= 2):
            text = 'Débil';
            color = '#ff3333';
            break;
        case (strength <= 4):
            text = 'Media';
            color = '#ff9900';
            break;
        case (strength <= 6):
            text = 'Fuerte';
            color = '#33cc33';
            break;
        default:
            text = 'Muy Fuerte';
            color = '#006600';
            break;
    }
    
    meter.textContent = `Fortaleza: ${text}`;
    meter.style.color = color;
}

// Función auxiliar para mostrar mensajes
function showMessage(element, text, type) {
    if (!element) return;
    
    element.textContent = text;
    element.style.display = 'block';
    
    // Resetear estilos
    element.style.cssText = '';
    
    // Estilos base
    element.style.padding = '12px';
    element.style.borderRadius = '6px';
    element.style.margin = '10px 0';
    element.style.fontSize = '14px';
    element.style.transition = 'all 0.3s ease';
    element.style.textAlign = 'center';
    
    // Estilos según el tipo de mensaje
    switch(type) {
        case 'error':
            element.style.color = '#721c24';
            element.style.background = '#f8d7da';
            element.style.border = '1px solid #f5c6cb';
            break;
        case 'success':
            element.style.color = '#155724';
            element.style.background = '#d4edda';
            element.style.border = '1px solid #c3e6cb';
            break;
        case 'info':
            element.style.color = '#0c5460';
            element.style.background = '#d1ecf1';
            element.style.border = '1px solid #bee5eb';
            break;
    }
}

// Añadir validación automática para fecha de nacimiento al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    initRegisterForm();
    initRealTimeValidation();
    
    // Establecer límites para fecha de nacimiento
    const fechaInput = document.getElementById('fechaNacimiento');
    if (fechaInput) {
        const today = new Date();
        const minDate = new Date();
        const maxDate = new Date();
        
        // Mínimo: 18 años atrás
        minDate.setFullYear(today.getFullYear() - 100);
        
        // Máximo: 18 años atrás
        maxDate.setFullYear(today.getFullYear() - 18);
        
        // Formatear fechas para input type="date"
        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };
        
        fechaInput.min = formatDate(minDate);
        fechaInput.max = formatDate(maxDate);
        fechaInput.title = `Debes tener entre 18 y 100 años`;
    }
});

// Añadir también validación en tiempo real para email
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.style.borderColor = '#ff3333';
                this.title = 'Formato inválido: ejemplo@dominio.com';
            } else {
                this.style.borderColor = '';
                this.title = '';
            }
        });
    }
});

