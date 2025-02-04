<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport des ventes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 150px;
        }
        .total {
            font-weight: bold;
            background-color: #e9f7ef;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <h1>{{ $settings->name }}</h1>
        <p>{{ $settings->address }} | {{ $settings->phone }} | {{ $settings->ninea }}</p>
        <h2>Rapport des ventes</h2>
        <p>Période du {{ $startDate }} au {{ $endDate }}</p>
        <p>Généré le {{ $generatedDate }}</p>
    </div>

    <!-- Section Catégories -->
    <h3>Catégories</h3>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Nombre de produits</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->products_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Section Ventes -->
    <h3>Ventes</h3>
    <table>
        <thead>
            <tr>
                <th>Numéro de vente</th>
                <th>Date</th>
                <th>Client</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->sale_number }}</td>
                    <td>{{ $sale->sale_date->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->client_name }}</td>
                    <td>{{ number_format($sale->total, 2, ',', ' ') }} FCFA</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totaux -->
    <div class="total">
        <p>Total des ventes : {{ $totalSales }}</p>
        <p>Total des produits vendus : {{ $totalProductsSold }}</p>
        <p>Chiffre d'affaires total : {{ number_format($totalRevenue, 2, ',', ' ') }} FCFA</p>
    </div>
</body>
</html>