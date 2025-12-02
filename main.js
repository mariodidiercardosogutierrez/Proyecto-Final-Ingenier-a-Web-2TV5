const sidebar = document.querySelector(".sidebar");
const hideBtn = document.querySelector(".sidebar-hide-btn");
const showBtn = document.getElementById("showSidebarBtn");
const content = document.querySelector(".content");

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




const form = document.getElementById('registerForm');
const message = document.getElementById('registerMessage');

form.addEventListener('submit', function(e) {
    e.preventDefault();

    // Obtener valores
    const nombre = document.getElementById('nombre').value.trim();
    const apellido = document.getElementById('apellido').value.trim();
    const email = document.getElementById('email').value.trim();
    const telefono = document.getElementById('telefono').value.trim();
    const fechaNacimiento = document.getElementById('fechaNacimiento').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    // Limpiar mensaje previo
    message.textContent = '';
    message.className = 'register-notice';
    message.style.display = 'none';

    // Validar nombre y apellido
    if(nombre === '' || apellido === ''){
        message.textContent = 'Nombre y apellido son obligatorios';
        message.classList.add('error');
        message.style.display = 'block';
        return;
    }

    // Validar correo
    const emailRegex = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;
    if(!emailRegex.test(email)){
        message.textContent = 'Correo inválido';
        message.classList.add('error');
        message.style.display = 'block';
        return;
    }

    // Validar teléfono
    if(!/^\d{10}$/.test(telefono)){
        message.textContent = 'El teléfono debe tener 10 dígitos';
        message.classList.add('error');
        message.style.display = 'block';
        return;
    }

    // Validar fecha de nacimiento (mayor de 12 años)
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    if(mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())){
        edad--;
    }
    if(edad < 12){
        message.textContent = 'Debes tener al menos 12 años para registrarte';
        message.classList.add('error');
        message.style.display = 'block';
        return;
    }

    // Validar contraseña
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]).{9,}$/;
    if(!passwordRegex.test(password)){
        message.textContent = 'Contraseña debe tener mínimo 9 caracteres, incluyendo mayúscula, minúscula, número y carácter especial';
        message.classList.add('error');
        message.style.display = 'block';
        return;
    }

    // Confirmar contraseña
    if(password !== confirmPassword){
        message.textContent = 'Las contraseñas no coinciden';
        message.classList.add('error');
        message.style.display = 'block';
        return;
    }

    // Registro exitoso
    message.textContent = '¡Registro exitoso!';
    message.classList.add('success');
    message.style.display = 'block';
    form.reset();
});
