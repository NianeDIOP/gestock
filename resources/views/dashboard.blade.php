@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="container-fluid px-6 py-4 bg-gray-50 min-h-screen">
    <!-- Filtres -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tableau de bord</h1>
            <p class="text-sm text-gray-600" id="periodText">
                Période : {{ ucfirst($period) }} {{ $year }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <select id="yearFilter" class="px-4 py-2 bg-white border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                @for($y = 2020; $y <= date('Y'); $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select id="periodFilter" class="px-4 py-2 bg-white border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                <option value="day" {{ $period == 'day' ? 'selected' : '' }}>Aujourd'hui</option>
                <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Cette semaine</option>
                <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Ce mois</option>
                <option value="year" {{ $period == 'year' ? 'selected' : '' }}>Cette année</option>
            </select>
            <button onclick="refreshStats()" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                Actualiser
            </button>
            <button onclick="generateReport()" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                <i class="fas fa-file-pdf mr-2"></i>Générer un rapport
            </button>
        </div>
    </div>

    <!-- Cartes statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Carte Ventes -->
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Ventes totales</p>
                    <p class="text-xl font-bold text-gray-900 mt-1" id="salesStat">
                        {{ number_format($dailyStats['sales'], 0, ',', ' ') }} FCFA
                    </p>
                </div>
                <div class="bg-blue-100 p-2 rounded-full">
                    <i class="fas fa-shopping-cart text-blue-600 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Carte Produits vendus -->
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Produits vendus</p>
                    <p class="text-xl font-bold text-gray-900 mt-1" id="productsSoldStat">
                        {{ number_format($dailyStats['products_sold'], 0, ',', ' ') }}
                    </p>
                </div>
                <div class="bg-green-100 p-2 rounded-full">
                    <i class="fas fa-box text-green-600 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Carte Ruptures de stock -->
        <!-- Carte Ruptures de stock -->
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Ruptures de stock</p>
                    <p class="text-xl font-bold text-gray-900 mt-1" id="outOfStockStat">
                        {{ $dailyStats['out_of_stock'] }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="bg-red-100 p-2 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                    </div>
                    <!-- Icône de flèche -->
                    <a href="{{ route('products.out_of_stock') }}" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-right text-lg"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Carte Factures -->
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Factures émises</p>
                    <p class="text-xl font-bold text-gray-900 mt-1" id="invoicesStat">
                        {{ $dailyStats['invoices'] }}
                    </p>
                </div>
                <div class="bg-purple-100 p-2 rounded-full">
                    <i class="fas fa-file-invoice text-purple-600 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        <!-- Graphique des ventes -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Évolution des ventes</h3>
            </div>
            <div class="p-4" style="height: 300px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <!-- Graphique des catégories -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Ventes par catégorie</h3>
            </div>
            <div class="p-4" style="height: 300px;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        <!-- Modes de paiement -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Modes de paiement</h3>
            </div>
            <div class="p-4" style="height: 300px;">
                <canvas id="paymentChart"></canvas>
            </div>
        </div>

        <!-- Top produits -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Top 5 des produits</h3>
            </div>
            <div class="p-4" style="height: 300px;">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const initialPeriod = document.getElementById('periodFilter').value;
    initAllCharts(initialPeriod);
});

let charts = {
    sales: null,
    category: null,
    payment: null,
    topProducts: null
};

// Initialisation principale
function initAllCharts(period) {
    destroyAllCharts();
    
    // Graphique des ventes
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    charts.sales = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: @json($salesChartData['labels']),
            datasets: [{
                label: 'Ventes (FCFA)',
                data: @json($salesChartData['sales']),
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2
            }]
        },
        options: getSalesChartOptions(period)
    });

    // Graphique des catégories
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    charts.category = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: @json($categoryChartData->pluck('name')),
            datasets: [{
                data: @json($categoryChartData->pluck('total_sales')),
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F59E0B', '#EC4899', '#6B7280'
                ],
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 20
                    }
                }
            }
        }
    });

    // Graphique des paiements
    const paymentCtx = document.getElementById('paymentChart').getContext('2d');
    charts.payment = new Chart(paymentCtx, {
        type: 'bar',
        data: {
            labels: @json($paymentChartData->pluck('payment_method')),
            datasets: [{
                label: 'Transactions',
                data: @json($paymentChartData->pluck('count')),
                backgroundColor: '#3B82F6',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    // Top produits
    const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
    charts.topProducts = new Chart(topProductsCtx, {
        type: 'bar',
        data: {
            labels: @json($topProducts->pluck('name')),
            datasets: [{
                label: 'Quantité vendue',
                data: @json($topProducts->pluck('total_quantity')),
                backgroundColor: '#3B82F6',
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

// Configuration dynamique du graphique des ventes
function getSalesChartOptions(period) {
    const isDaily = period === 'day';
    
    return {
        responsive: true,
        maintainAspectRatio: false,
        elements: {
            point: {
                radius: 3,
                hoverRadius: 5
            }
        },
        scales: {
            x: {
                type: 'category',
                ticks: {
                    callback: function(value) {
                        return isDaily ? `${this.getLabelForValue(value)}:00` : value;
                    },
                    autoSkip: true,
                    maxTicksLimit: isDaily ? 12 : 15
                },
                grid: {
                    display: false
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => `${Math.round(value).toLocaleString('fr-FR')} FCFA`
                },
                grid: {
                    color: '#e5e7eb'
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: ctx => `${ctx.parsed.y.toLocaleString('fr-FR')} FCFA`
                }
            }
        }
    };
}

function formatLabelForSQLite(label, period) {
    if (period === 'day' && label.length === 2) {
        return `${label}:00`;
    }
    return label;
}


// Destruction propre des graphiques
function destroyAllCharts() {
    Object.values(charts).forEach(chart => {
        if (chart) chart.destroy();
    });
    charts = {
        sales: null,
        category: null,
        payment: null,
        topProducts: null
    };
}

// Actualisation des données
async function refreshStats() {
    const year = document.getElementById('yearFilter').value;
    const period = document.getElementById('periodFilter').value;
    
    try {
        showLoading(true);
        
        const response = await fetch(`/dashboard/filter?year=${year}&period=${period}`);
        const { stats, charts: chartsData } = await response.json();

        updateStatsDisplay(stats);
        updateAllCharts(chartsData, period);
        updatePeriodDisplay(period, year);

    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors du chargement des données');
    } finally {
        showLoading(false);
    }
}

// Mise à jour des statistiques
function updateStatsDisplay(stats) {
    document.getElementById('salesStat').textContent = 
        `${Math.round(stats.sales).toLocaleString('fr-FR')} FCFA`;
    document.getElementById('productsSoldStat').textContent = 
        Math.round(stats.products_sold).toLocaleString('fr-FR');
    document.getElementById('outOfStockStat').textContent = stats.out_of_stock;
    document.getElementById('invoicesStat').textContent = stats.invoices;
}

// Mise à jour des graphiques
function updateAllCharts(chartsData, period) {
    destroyAllCharts();
    
    // Conversion des labels pour SQLite
    const formattedLabels = chartsData.sales.labels.map(label => {
        return period === 'day' ? `${label.padStart(2, '0')}:00` : label;
    });

    // Graphique des ventes
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    charts.sales = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: formattedLabels,
            datasets: [{
                label: 'Ventes (FCFA)',
                data: chartsData.sales.sales,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                borderWidth: 2
            }]
        },
        options: getSalesChartOptions(period)
    });



    // Catégories
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    charts.category = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: chartsData.categories.map(c => c.name),
            datasets: [{
                data: chartsData.categories.map(c => c.total_sales),
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F59E0B', '#EC4899', '#6B7280'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Paiements
    const paymentCtx = document.getElementById('paymentChart').getContext('2d');
    charts.payment = new Chart(paymentCtx, {
        type: 'bar',
        data: {
            labels: chartsData.payments.map(p => p.payment_method),
            datasets: [{
                label: 'Transactions',
                data: chartsData.payments.map(p => p.count),
                backgroundColor: '#3B82F6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Top produits
    const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
    charts.topProducts = new Chart(topProductsCtx, {
        type: 'bar',
        data: {
            labels: chartsData.topProducts.map(p => p.name),
            datasets: [{
                label: 'Quantité vendue',
                data: chartsData.topProducts.map(p => p.total_quantity),
                backgroundColor: '#3B82F6'
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: { x: { beginAtZero: true } }
        }
    });
}

// Helpers
function showLoading(show) {
    const loader = document.getElementById('loadingOverlay');
    if (loader) loader.style.display = show ? 'flex' : 'none';
}

function updatePeriodDisplay(period, year) {
    const periodNames = {
        day: 'Jour',
        week: 'Semaine',
        month: 'Mois',
        year: 'Année'
    };
    document.getElementById('periodText').textContent = 
        `Période : ${periodNames[period]} ${year}`;
}

function generateReport() {
    const year = document.getElementById('yearFilter').value;
    const period = document.getElementById('periodFilter').value;

    // Rediriger vers la route de génération du rapport
    window.location.href = `/generate-report?year=${year}&period=${period}`;
}
</script>
@endpush
@endsection