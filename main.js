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