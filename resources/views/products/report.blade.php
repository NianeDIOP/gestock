<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport d'Inventaire - {{ $periodLabel }}</title>
    <style>
        :root {
            --primary: #2563eb;
            --dark: #1e293b;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --text: #374151;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: var(--text);
            margin: 0;
            padding: 30px;
            background-color: #f8fafc;
        }

        .header {
            text-align: left;
            margin-bottom: 40px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .company-info {
            margin-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 15px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .company-details {
            color: #6b7280;
            font-size: 14px;
        }

        .company-details p {
            margin: 5px 0;
        }

        .report-info {
            color: #6b7280;
            font-size: 14px;
            margin-top: 10px;
        }

        .report-title {
            color: var(--dark);
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--dark);
        }

        .stat-value.success { color: var(--success); }
        .stat-value.warning { color: var(--warning); }
        .stat-value.danger { color: var(--danger); }

        .section {
            background: white;
            margin-bottom: 30px;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            page-break-inside: avoid;
        }

        .section-title {
            color: var(--dark);
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        thead {
            background: var(--dark);
            color: white;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .stock-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-success { background: #dcfce7; color: var(--success); }
        .status-warning { background: #fef3c7; color: var(--warning); }
        .status-danger { background: #fee2e2; color: var(--danger); }

        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 12px;
            margin-top: 40px;
            border-top: 1px solid #e5e7eb;
        }

        @media print {
            body {
                background: white;
                padding: 20px;
            }

            .section {
                box-shadow: none;
            }
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- En-tête du rapport -->
    <div class="header">
        <div class="company-info">
            <h1 class="company-name">{{ $settings->name }}</h1>
            <div class="company-details">
                <p>{{ $settings->address }}</p>
                <p>Tél: {{ $settings->phone }}</p>
                <p>NINEA: {{ $settings->ninea }}</p>
            </div>
        </div>
        <div class="report-header">
            <h2 class="report-title">Rapport d'Inventaire et d'Analyse des Stocks</h2>
            <div class="report-info">
                <p class="period">Période : {{ $periodLabel }}</p>
                <p class="generated-date">Généré le : {{ now()->format('d/m/Y H:i') }}</p>
                <p class="sales-summary">
                    Nombre de ventes sur la période : {{ number_format($stockStats['total_sales_count'], 0, ',', ' ') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Résumé des statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Valeur Totale du Stock</div>
            <div class="stat-value">{{ number_format($totalStockValue, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Chiffre d'Affaires {{ $periodLabel }}</div>
            <div class="stat-value success">{{ number_format($stockStats['total_sales'], 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Quantité Totale Vendue</div>
            <div class="stat-value">{{ number_format($stockStats['total_quantity_sold'], 0, ',', ' ') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Produits en Stock</div>
            <div class="stat-value">{{ $stockStats['total'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Produits en Rupture</div>
            <div class="stat-value danger">{{ $stockStats['out_of_stock'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Stock Faible</div>
            <div class="stat-value warning">{{ $stockStats['low_stock'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">CA Moyen par Vente</div>
            <div class="stat-value text-primary">
                {{ number_format($stockStats['average_sale'] ?? 0, 0, ',', ' ') }}
            </div>
            
        </div>
    </div>

    <!-- Performances de vente -->
    <div class="section page-break">
        <h2 class="section-title"> Performances Commerciales</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Meilleur Jour</div>
                <div class="stat-value success">{{ number_format($bestDay->total ?? 0, 0, ',', ' ') }} FCFA</div>
<div class="stat-trend">{{ isset($bestDay->date) ? Carbon\Carbon::parse($bestDay->date)->format('d/m/Y') : 'N/A' }}</div>

            </div>
            <div class="stat-card">
                <div class="stat-label">Paiements par Carte</div>
                <div class="stat-value">{{ number_format($paymentMethods->where('payment_method', 'card')->sum('total'), 0, ',', ' ') }} FCFA</div>
            </div>
        </div>
    </div>

    <!-- Analyse par catégorie -->
    <div class="section">
        <h2 class="section-title">Analyse Financière par Catégorie</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th>CA Total</th>
                        <th>Marge Moyenne</th>
                        <th>Produits Vendus</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categoryStats as $stat)
                    <tr>
                        <td>{{ $stat->name }}</td>
                        <td>{{ number_format($stat->total_sales, 0, ',', ' ') }} FCFA</td>
                        <td>{{ number_format($stat->profit_margin, 2, ',', ' ') }}%</td>
                        <td>{{ number_format($stat->total_quantity_sold, 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Détails des transactions -->
    <div class="section page-break">
        <h2 class="section-title"> Détail des Transactions</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Méthode</th>
                        <th>Montant Total</th>
                        <th>Nombre de Transactions</th>
                        <th>% du CA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paymentMethods as $method)
                    <tr>
                        <td>{{ ucfirst($method->payment_method) }}</td>
                        <td>{{ number_format($method->total, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $method->count }}</td>
                        <td>{{ number_format(($method->total / $stockStats['total_sales']) * 100, 2) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top des ventes -->
    <div class="section">
        <h2 class="section-title"> Top 10 des Produits les Plus Vendus</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th>Quantité Vendue</th>
                        <th>Chiffre d'Affaires</th>
                        <th>Stock Actuel</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bestSellingProducts as $product)
                    <tr>
                        <td>
                            <strong>{{ $product->name }}</strong><br>
                            <span class="text-sm text-gray-500">{{ $product->reference }}</span>
                        </td>
                        <td>{{ $product->category ? $product->category->name : 'Aucune catégorie' }}</td>
                        <td>{{ number_format($product->total_sold, 0, ',', ' ') }}</td>
                        <td>{{ number_format($product->total_revenue, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $product->quantity }}</td>
                        <td>
                            <span class="stock-status status-{{ $product->stock_status }}">
                                @if($product->quantity == 0)
                                    Rupture
                                @elseif($product->quantity <= $product->stock_threshold)
                                    Stock Faible
                                @else
                                    En Stock
                                @endif
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Alertes de stock -->
    <div class="section">
        <h2 class="section-title"> Alertes de Stock</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th>Stock Actuel</th>
                        <th>Seuil d'Alerte</th>
                        <th>Dernier Réapprovisionnement</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $product)
                    <tr>
                        <td>{{ $product->reference }}</td>
                        <td><strong>{{ $product->name }}</strong></td>
                        <td>{{ $product->category ? $product->category->name : 'Aucune catégorie' }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td>{{ $product->stock_threshold }}</td>
                        <td>{{ $product->last_restock_date ? $product->last_restock_date->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inventaire détaillé -->
    <div class="section">
        <h2 class="section-title">Inventaire Détaillé</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th>Prix Unitaire</th>
                        <th>Stock</th>
                        <th>Valeur Stock</th>
                        <th>Ventes {{ $periodLabel }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->reference }}</td>
                        <td><strong>{{ $product->name }}</strong></td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ number_format($product->price, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $product->quantity }}</td>
                        <td>{{ number_format($product->price * $product->quantity, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $product->total_sold ?? 0 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p>Ce rapport a été généré automatiquement par le système de gestion des stocks.</p>
        <p>Les données sont à jour au {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>