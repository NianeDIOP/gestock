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
            padding: 40px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f9fafb;
        }
        .invoice-container {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 40px;
            flex: 1;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #1f2937;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
            color: #6b7280;
        }
        .header .description {
            font-style: italic;
            color: #4b5563;
            margin-top: 10px;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-info div {
            flex: 1;
        }
        .invoice-info h2 {
            margin: 0 0 10px 0;
            font-size: 20px;
            color: #1f2937;
        }
        .invoice-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #6b7280;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #e5e7eb;
            text-align: left;
        }
        th {
            background-color: #f9fafb;
            font-weight: bold;
            color: #374151;
        }
        .total {
            margin-top: 30px;
            text-align: right;
            font-size: 18px;
            color: #1f2937;
        }
        .total p {
            margin: 8px 0;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            padding: 20px 0;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer .contact {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- En-tête de la facture -->
        <div class="header">
            <h1>{{ $settings->name ?? 'Nom de l\'entreprise' }}</h1>
            @if($settings->description)
                <p class="description">{{ $settings->description }}</p>
            @endif
            <p>{{ $settings->address ?? 'Adresse de l\'entreprise' }}</p>
            <p>Téléphone : {{ $settings->phone ?? 'N/A' }} | Email : {{ $settings->email ?? 'N/A' }}</p>
            <p>NINEA : {{ $settings->ninea ?? 'N/A' }}</p>
        </div>

        <!-- Informations de la vente -->
        <div class="invoice-info">
            <div>
                <h2>Facture</h2>
                <p><strong>Numéro de Vente :</strong> {{ $sale->sale_number }}</p>
                <p><strong>Date :</strong> {{ $sale->sale_date->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <h2>Client</h2>
                <p><strong>Nom :</strong> {{ $sale->client_name }}</p>
                <p><strong>Téléphone :</strong> {{ $sale->client_phone ?? 'N/A' }}</p>
            </div>
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
        <div class="contact">
            <p>Téléphone : {{ $settings->phone ?? 'N/A' }} | Email : {{ $settings->email ?? 'N/A' }}</p>
            <p>NINEA : {{ $settings->ninea ?? 'N/A' }}</p>
        </div>
        <p>Merci pour votre confiance !</p>
    </div>
</body>
</html>