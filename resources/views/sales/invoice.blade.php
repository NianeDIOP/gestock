<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture - {{ $sale->sale_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            margin-bottom: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total {
            margin-top: 20px;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
        }
        .content {
            flex: 1; /* Permet au contenu de prendre tout l'espace disponible */
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            padding: 20px 0;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <!-- En-tête de la facture -->
    <div class="header">
        <h1>{{ $settings->name ?? 'Nom de l\'entreprise' }}</h1>
        <p>{{ $settings->address ?? 'Adresse de l\'entreprise' }}</p>
        <p>Téléphone : {{ $settings->phone ?? 'N/A' }} | NINEA : {{ $settings->ninea ?? 'N/A' }}</p>
    </div>

    <!-- Contenu principal -->
    <div class="content">
        <!-- Informations de la vente -->
        <div>
            <h2>Facture</h2>
            <p><strong>Numéro de Vente :</strong> {{ $sale->sale_number }}</p>
            <p><strong>Date :</strong> {{ $sale->sale_date->format('d/m/Y H:i') }}</p>
            <p><strong>Client :</strong> {{ $sale->client_name }}</p>
            <p><strong>Téléphone du client :</strong> {{ $sale->client_phone ?? 'N/A' }}</p>
        </div>

        <!-- Détails des articles -->
        <h3>Détails des Articles</h3>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix Unitaire</th>
                    <th>Sous-total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                    <td>{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totaux -->
        <div class="total">
            <p><strong>Sous-total :</strong> {{ number_format($sale->subtotal, 0, ',', ' ') }} FCFA</p>
            <p><strong>TVA ({{ $sale->tax_rate }}%) :</strong> {{ number_format($sale->tax, 0, ',', ' ') }} FCFA</p>
            <p><strong>Total :</strong> {{ number_format($sale->total, 0, ',', ' ') }} FCFA</p>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <p>{{ $settings->name ?? 'Nom de l\'entreprise' }} - {{ $settings->address ?? 'Adresse de l\'entreprise' }}</p>
        <p>Téléphone : {{ $settings->phone ?? 'N/A' }} | NINEA : {{ $settings->ninea ?? 'N/A' }}</p>
        <p>Merci pour votre confiance !</p>
    </div>
</body>
</html>