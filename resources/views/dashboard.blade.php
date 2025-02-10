@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="container-fluid px-6 py-4 bg-gray-50 min-h-screen">
    <!-- Filtres -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">Tableau de bord</h1>
            <p class="text-sm text-gray-600" id="periodText">
                Période : {{ ucfirst($period) }} {{ $year }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3 w-full lg:w-auto">
            <select id="yearFilter" class="w-full sm:w-auto px-4 py-2 bg-white border rounded-lg shadow-sm">
                @for($y = 2020; $y <= date('Y'); $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select id="periodFilter" class="w-full sm:w-auto px-4 py-2 bg-white border rounded-lg shadow-sm">
                <option value="day">Aujourd'hui</option>
                <option value="week">Cette semaine</option>
                <option value="month">Ce mois</option>
                <option value="year">Cette année</option>
            </select>
            <div class="flex gap-2 w-full sm:w-auto">
                <button onclick="refreshStats()" 
                    class="flex-1 sm:flex-none px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    <i class="fas fa-sync-alt mr-2"></i>Actualiser
                </button>
                <button onclick="generateReport()" 
                    class="flex-1 sm:flex-none px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    <i class="fas fa-file-pdf mr-2"></i>Rapport
                </button>
            </div>
        </div>
    </div>

     <!-- Cartes statistiques responsive -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Carte Ventes -->
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Ventes totales</p>
                    <p class="text-xl font-bold text-gray-900 mt-1" id="salesStat">
                        {{ number_format($dailyStats['sales'], 0, ',', ' ') }} FCFA
                    </p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-shopping-cart text-blue-600 text-lg"></i>
                </div>
            </div>
        </div>

       <!-- Carte Produits vendus -->
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-green-500 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Produits vendus</p>
                    <p class="text-xl font-bold text-gray-900 mt-1" id="productsSoldStat">
                        {{ number_format($dailyStats['products_sold'], 0, ',', ' ') }}
                    </p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-box text-green-600 text-lg"></i>
                </div>
            </div>
        </div>

         <!-- Carte Ruptures de stock -->
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-red-500 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Ruptures de stock</p>
                    <p class="text-xl font-bold text-gray-900 mt-1" id="outOfStockStat">
                        {{ $dailyStats['out_of_stock'] }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                    </div>
                    <a href="{{ route('products.out_of_stock') }}" 
                    class="text-gray-500 hover:text-gray-700 p-2">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

         <!-- Carte Factures -->
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-purple-500 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Factures émises</p>
                    <p class="text-xl font-bold text-gray-900 mt-1" id="invoicesStat">
                        {{ $dailyStats['invoices'] }}
                    </p>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-file-invoice text-purple-600 text-lg"></i>
                </div>
            </div>
        </div>
        </div>

        <!-- Graphiques responsive -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
            <!-- Graphique des ventes -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Évolution des ventes</h3>
                </div>
                <div class="p-4" style="height: 300px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
      <!-- Graphique des catégories -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Ventes par catégorie</h3>
                </div>
                <div class="p-4" style="height: 300px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

            <!-- Autres graphiques responsive -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
            <!-- Modes de paiement -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Modes de paiement</h3>
                </div>
                <div class="p-4" style="height: 300px;">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        <!-- Top produits -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Top 5 des produits</h3>
            </div>
            <div class="p-4" style="height: 300px;">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>
        </div>

        <!-- Modal Alerte Stock -->
<div id="stockAlertModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        
        <!-- Contenu Modal -->
        <div class="relative bg-white rounded-lg w-full max-w-2xl">
            <!-- En-tête -->
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    Alerte Stock
                </h3>
                <button onclick="closeStockAlert()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Corps -->
            <div class="px-6 py-4 max-h-[60vh] overflow-y-auto">
                <div id="stockAlertContent" class="space-y-4">
                    @foreach($lowStockProducts as $product)
                        <div class="flex items-center justify-between p-4 {{ $product->quantity === 0 ? 'bg-red-50' : 'bg-orange-50' }} rounded-lg">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $product->name }}</h4>
                                <p class="text-sm text-gray-500">
                                    Réf: {{ $product->reference }} | 
                                    Catégorie: {{ $product->category->name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold {{ $product->quantity === 0 ? 'text-red-600' : 'text-orange-600' }}">
                                    {{ $product->quantity }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Seuil: {{ $product->stock_threshold }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Pied -->
            <div class="px-6 py-4 bg-gray-50 flex justify-between items-center rounded-b-lg">
                <span class="text-sm text-gray-500">
                    {{ $lowStockProducts->count() }} produit(s) en alerte
                </span>
                <div class="flex gap-3">
                    <a href="{{ route('products.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Gérer le stock
                    </a>
                    <button onclick="closeStockAlert(true)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Ne plus afficher
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
       // Initialisation des graphiques
       let charts = {
        sales: null,
        category: null,
        payment: null,
        topProducts: null
    };

document.addEventListener('DOMContentLoaded', () => {
    // Configuration initiale des graphiques
    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    padding: 20,
                    font: { size: window.innerWidth < 768 ? 10 : 12 }
                }
            }
        }
    };

    // Gestion des couleurs
    const colors = {
        primary: '#3B82F6',
        success: '#10B981',
        warning: '#F59E0B',
        danger: '#EF4444',
        info: '#6366F1'
    };

 
    function initAllCharts(period= 'month') {
        destroyAllCharts();
        
        // Graphique des ventes
        initSalesChart();
        initCategoryChart();
        initPaymentChart();
        initTopProductsChart();

        // Ajustement responsive
        handleResponsiveCharts();
    }

    function initSalesChart() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        charts.sales = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($salesChartData['labels']),
                datasets: [{
                    label: 'Ventes (FCFA)',
                    data: @json($salesChartData['sales']),
                    borderColor: colors.primary,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                }]
            },
            options: {
                ...chartDefaults,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => `${value.toLocaleString('fr-FR')} FCFA`
                        }
                    }
                }
            }
        });
    }

    function initCategoryChart() {
        const ctx = document.getElementById('categoryChart').getContext('2d');
        charts.category = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($categoryChartData->pluck('name')),
                datasets: [{
                    data: @json($categoryChartData->pluck('total_sales')),
                    backgroundColor: Object.values(colors)
                }]
            },
            options: {
                ...chartDefaults,
                cutout: '60%'
            }
        });
    }

    function initPaymentChart() {
        const ctx = document.getElementById('paymentChart').getContext('2d');
        charts.payment = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($paymentChartData->pluck('payment_method')),
                datasets: [{
                    label: 'Transactions',
                    data: @json($paymentChartData->pluck('count')),
                    backgroundColor: colors.primary
                }]
            },
            options: {
                ...chartDefaults,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    function initTopProductsChart() {
        const ctx = document.getElementById('topProductsChart').getContext('2d');
        charts.topProducts = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($topProducts->pluck('name')),
                datasets: [{
                    label: 'Quantité vendue',
                    data: @json($topProducts->pluck('total_quantity')),
                    backgroundColor: colors.success
                }]
            },
            options: {
                ...chartDefaults,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    // Gestion du responsive
    function handleResponsiveCharts() {
        const isMobile = window.innerWidth < 768;
        
        Object.values(charts).forEach(chart => {
            if (chart && chart.options.plugins.legend) {
                chart.options.plugins.legend.labels.font.size = isMobile ? 10 : 12;
                chart.update();
            }
        });
    }

    // Actualisation des statistiques
    async function refreshStats() {
    try {
        showLoading(true);
        
        const year = document.getElementById('yearFilter').value;
        const period = document.getElementById('periodFilter').value;
        
        const response = await fetch(`/dashboard/filter?year=${year}&period=${period}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        
        if (!data.stats || !data.charts) {
            throw new Error('Format de données invalide');
        }
        
        updateDisplayedStats(data.stats);
        updateCharts(data.charts);
        
        notifySuccess('Données actualisées avec succès');
    } catch (error) {
        console.error('Erreur détaillée:', error);
        notifyError('Erreur lors de l\'actualisation des données: ' + error.message);
    } finally {
        showLoading(false);
    }
}

    // Utilitaires
    function showLoading(show) {
        // Implémenter l'indicateur de chargement
    }

    function notifySuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Succès',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    }

    function notifyError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    }

    // Event Listeners
    window.addEventListener('resize', handleResponsiveCharts);
    document.getElementById('yearFilter').addEventListener('change', refreshStats);
    document.getElementById('periodFilter').addEventListener('change', refreshStats);

    // Initialisation
    initAllCharts();
});
// Fonctions de mise à jour du dashboard
function updateDisplayedStats(stats) {
   // Mise à jour des cartes statistiques
   document.getElementById('salesStat').textContent = 
       `${number_format(stats.sales, 0, ',', ' ')} FCFA`;
   document.getElementById('productsSoldStat').textContent = 
       number_format(stats.products_sold, 0, ',', ' ');
   document.getElementById('outOfStockStat').textContent = 
       stats.out_of_stock;
   document.getElementById('invoicesStat').textContent = 
       stats.invoices;

   // Mise à jour du texte de période
   updatePeriodText();
}

function updateCharts(chartsData) {
   // Mise à jour du graphique des ventes
   charts.sales.data.labels = chartsData.sales.labels;
   charts.sales.data.datasets[0].data = chartsData.sales.sales;
   charts.sales.update();

   // Mise à jour du graphique des catégories
   charts.category.data.labels = chartsData.categories.map(c => c.name);
   charts.category.data.datasets[0].data = chartsData.categories.map(c => c.total_sales);
   charts.category.update();

   // Mise à jour du graphique des paiements
   charts.payment.data.labels = chartsData.payments.map(p => p.payment_method);
   charts.payment.data.datasets[0].data = chartsData.payments.map(p => p.count);
   charts.payment.update();

   // Mise à jour du top des produits
   charts.topProducts.data.labels = chartsData.topProducts.map(p => p.name);
   charts.topProducts.data.datasets[0].data = chartsData.topProducts.map(p => p.total_quantity);
   charts.topProducts.update();
}

function updatePeriodText() {
   const period = document.getElementById('periodFilter').value;
   const year = document.getElementById('yearFilter').value;
   
   const periodNames = {
       day: 'Jour',
       week: 'Semaine',
       month: 'Mois',
       year: 'Année'
   };

   const periodLabel = periodNames[period] || 'Période';
   document.getElementById('periodText').textContent = 
       `Période : ${periodLabel} ${year}`;
}

// Fonctions utilitaires
function number_format(number, decimals = 0, dec_point = ',', thousands_sep = ' ') {
   number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
   const n = !isFinite(+number) ? 0 : +number;
   const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
   
   const toFixedFix = function(n, prec) {
       const k = Math.pow(10, prec);
       return '' + Math.round(n * k) / k;
   };
   
   let s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
   if (s[0].length > 3) {
       s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thousands_sep);
   }
   
   if ((s[1] || '').length < prec) {
       s[1] = s[1] || '';
       s[1] += new Array(prec - s[1].length + 1).join('0');
   }
   
   return s.join(dec_point);
}

// Gestionnaire d'impression
function handlePrint() {
   window.print();
}

function destroyAllCharts() {
    Object.values(charts).forEach(chart => {
        if (chart) {
            chart.destroy();
        }
    });
}

// Gestionnaire de génération de rapport
async function generateReport() {
   const year = document.getElementById('yearFilter').value;
   const period = document.getElementById('periodFilter').value;

   try {
       showLoading(true);
       window.open(`/generate-report?year=${year}&period=${period}`, '_blank');
   } catch (error) {
       notifyError('Erreur lors de la génération du rapport');
   } finally {
       showLoading(false);
   }
}

// Gestion du loader
function showLoading(show) {
   const loader = document.getElementById('loader');
   if (loader) {
       loader.style.display = show ? 'flex' : 'none';
   }
}

// Initialisation des événements
document.addEventListener('DOMContentLoaded', function() {
   // Initialiser les graphiques au chargement
   initAllCharts();

   // Gestionnaires d'événements pour les filtres
   document.getElementById('yearFilter').addEventListener('change', refreshStats);
   document.getElementById('periodFilter').addEventListener('change', refreshStats);

   // Gestionnaire d'impression
   document.getElementById('printBtn')?.addEventListener('click', handlePrint);

   // Gestionnaire de redimensionnement
   window.addEventListener('resize', debounce(handleResponsiveCharts, 250));
});

// Utilitaire debounce
function debounce(func, wait) {
   let timeout;
   return function executedFunction(...args) {
       const later = () => {
           clearTimeout(timeout);
           func(...args);
       };
       clearTimeout(timeout);
       timeout = setTimeout(later, wait);
   };

}

// Gestion des alertes stock
let stockAlertShown = false;

function checkStockAlerts() {
    if (stockAlertShown) return;
    
    fetch('/check-stock')
        .then(response => response.json())
        .then(data => {
            if (data.hasLowStock) {
                showStockAlert();
            }
        })
        .catch(error => console.error('Erreur:', error));
}

function showStockAlert() {
    const modal = document.getElementById('stockAlertModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    stockAlertShown = true;
}

function closeStockAlert(dismiss = false) {
    const modal = document.getElementById('stockAlertModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    
    if (dismiss) {
        fetch('/dismiss-stock-alert', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
    }
}

// Vérifier les alertes au chargement
document.addEventListener('DOMContentLoaded', function() {
    checkStockAlerts();
});
</script>
@endpush
@endsection