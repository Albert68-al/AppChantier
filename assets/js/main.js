// assets/js/main.js

class ChantierApp {
    constructor() {
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initCharts();
        this.initDatePickers();
        this.initSelect2();
    }
    
    bindEvents() {
        // Confirmation de suppression
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-delete')) {
                if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                    e.preventDefault();
                }
            }
        });
        
        // Filtres de tableau
        document.querySelectorAll('.table-filter').forEach(filter => {
            filter.addEventListener('input', this.filterTable.bind(this));
        });
        
        // Toggle sidebar mobile
        document.querySelector('.navbar-toggler')?.addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('show');
        });
        
        // Fermer sidebar en cliquant à l'extérieur (mobile)
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                const sidebar = document.querySelector('.sidebar');
                const target = e.target;
                if (sidebar.classList.contains('show') && 
                    !sidebar.contains(target) && 
                    !target.closest('.navbar-toggler')) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }
    
    filterTable(e) {
        const filterValue = e.target.value.toLowerCase();
        const tableId = e.target.dataset.table;
        const table = document.getElementById(tableId);
        
        if (!table) return;
        
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filterValue) ? '' : 'none';
        });
    }
    
    initCharts() {
        // Initialiser les graphiques Chart.js
        const chartElements = document.querySelectorAll('[data-chart]');
        
        chartElements.forEach(element => {
            const type = element.dataset.chartType || 'bar';
            const data = JSON.parse(element.dataset.chartData || '{}');
            const options = JSON.parse(element.dataset.chartOptions || '{}');
            
            new Chart(element.getContext('2d'), {
                type: type,
                data: data,
                options: options
            });
        });
    }
    
    initDatePickers() {
        // Initialiser les datepickers si Flatpickr est chargé
        if (typeof flatpickr !== 'undefined') {
            document.querySelectorAll('.datepicker').forEach(input => {
                flatpickr(input, {
                    dateFormat: 'Y-m-d',
                    locale: 'fr'
                });
            });
        }
    }
    
    initSelect2() {
        // Initialiser Select2 si chargé
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }
    }
    
    // Méthode pour afficher une notification
    showNotification(message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show`;
        alert.innerHTML = `
            <i class="fas ${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.alert-container') || 
                         document.querySelector('main') || 
                         document.body;
        
        container.prepend(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
    
    // Méthode pour générer un PDF
    generatePDF(elementId, filename = 'document.pdf') {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        const opt = {
            margin: 1,
            filename: filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };
        
        html2pdf().set(opt).from(element).save();
    }
}

// Initialiser l'application lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    window.app = new ChantierApp();
});

// Fonctions utilitaires
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function calculateAge(dateString) {
    const today = new Date();
    const birthDate = new Date(dateString);
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}