// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');

    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking on a link
    const navLinks = document.querySelectorAll('.nav-menu a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navMenu) navMenu.classList.remove('active');
            if (mobileMenuToggle) mobileMenuToggle.classList.remove('active');
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
});

// Contact Form Handler with PHP and Cloudflare Turnstile
const contactForm = document.getElementById('contactForm');
let turnstileToken = '';

// Callback cuando Turnstile se completa exitosamente
window.onTurnstileSuccess = function(token) {
    turnstileToken = token;
    document.getElementById('cf-turnstile-response').value = token;
    console.log('✅ Turnstile token generado correctamente');
};

if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get submit button
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.textContent;
        
        // Verificar que Turnstile haya sido completado
        const turnstileResponse = document.getElementById('cf-turnstile-response').value;
        if (!turnstileResponse || turnstileResponse === '') {
            alert('Por favor completá la verificación de seguridad antes de enviar el formulario.');
            return;
        }
        
        // Disable button and show loading
        submitButton.disabled = true;
        submitButton.textContent = 'Enviando...';
        
        submitForm();
        
        function submitForm() {
            // Get form data
            const formData = new FormData(contactForm);
            
            // Send form data to PHP script (usar ruta relativa para compatibilidad local)
            const scriptPath = contactForm.getAttribute('action') || 'send-email.php';
            fetch(scriptPath, {
                method: 'POST',
                body: formData
            })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success
                submitButton.textContent = '✓ Mensaje enviado';
                submitButton.style.background = '#10b981';
                
                // Show success message
                alert(data.message);
                
                // Reset form
                contactForm.reset();
                
                // Reset Turnstile widget
                if (typeof turnstile !== 'undefined') {
                    turnstile.reset();
                }
                turnstileToken = '';
                document.getElementById('cf-turnstile-response').value = '';
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                    submitButton.style.background = '';
                }, 3000);
            } else {
                // Error from server
                throw new Error(data.message || 'Error al enviar el mensaje');
            }
        })
            .catch(error => {
                // Error - fallback to WhatsApp
                console.error('Error al enviar email:', error);
                
                // Get form data for WhatsApp fallback
                const formDataObj = Object.fromEntries(formData);
                
                // Create WhatsApp message
                const message = `Hola, me contacto desde la web de Dataflow Services.\n\n` +
                    `Nombre: ${formDataObj.name}\n` +
                    `Email: ${formDataObj.email}\n` +
                    `Teléfono: ${formDataObj.phone || 'No proporcionado'}\n` +
                    `Servicio: ${formDataObj.service}\n\n` +
                    `Mensaje:\n${formDataObj.message}`;
                
                const encodedMessage = encodeURIComponent(message);
                const whatsappNumber = '5491121582109';
                
                // Show error message and offer WhatsApp alternative
                if (confirm('Hubo un error al enviar el email. ¿Querés enviar el mensaje por WhatsApp en su lugar?')) {
                    window.open(`https://wa.me/${whatsappNumber}?text=${encodedMessage}`, '_blank');
                }
                
                // Reset button
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
                
                // Reset Turnstile widget
                if (typeof turnstile !== 'undefined') {
                    turnstile.reset();
                }
                turnstileToken = '';
                document.getElementById('cf-turnstile-response').value = '';
            });
        }
    });
}

// Add active class to navigation based on current page
document.addEventListener('DOMContentLoaded', () => {
    try {
        const pathname = window.location.pathname;
        const currentPage = pathname.split('/').pop() || '';
        const navLinksAll = document.querySelectorAll('.nav-menu a');

        navLinksAll.forEach(link => {
            const linkPath = link.getAttribute('href');
            if (!linkPath) return;
            
            // Remove leading slash and compare
            const normalizedLinkPath = linkPath.replace(/^\//, '') || '';
            const normalizedCurrentPage = currentPage || '';
            
            // Check if this link matches the current page
            if (normalizedLinkPath === normalizedCurrentPage) {
                link.classList.add('active');
            } else if (normalizedCurrentPage === '' && (normalizedLinkPath === '' || linkPath === '/')) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    } catch (error) {
        console.error('Error setting active navigation:', error);
    }
});

// Intersection Observer for fade-in animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe elements for animation
document.addEventListener('DOMContentLoaded', () => {
    const animateElements = document.querySelectorAll('.service-card, .why-card, .problem-card, .pack-card, .experience-card, .case-card, .process-step');
    
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
});

// Update WhatsApp links with actual number and initial message
// Replace '549XXXXXXXXX' with your actual WhatsApp number in format: 549XXXXXXXXX (country code + area code + number)
document.addEventListener('DOMContentLoaded', () => {
    const whatsappLinks = document.querySelectorAll('a[href*="wa.me"]');
    const whatsappNumber = '5491121582109';
    const initialMessage = 'Hola, quiero consultar por un servicio...';
    const encodedMessage = encodeURIComponent(initialMessage);
    
    whatsappLinks.forEach(link => {
        const currentHref = link.getAttribute('href');
        // Only update if it doesn't already have a text parameter
        if (currentHref.includes('549XXXXXXXXX') || (!currentHref.includes('?text=') && currentHref.includes('wa.me'))) {
            link.setAttribute('href', `https://wa.me/${whatsappNumber}?text=${encodedMessage}`);
        }
    });
});

