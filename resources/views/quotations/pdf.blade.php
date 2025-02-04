<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Devis {{ $quotation->quotation_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f4f4f4; }
        .total { text-align: right; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $settings->name }}</h1>
        <p>{{ $settings->address }}<br>
        Tél : {{ $settings->phone }}<br>
        NINEA : {{ $settings->ninea }}</p>
        
        <h2>DEVIS N° {{ $quotation->quotation_number }}</h2>
        <p>Date: {{ $quotation->date->format('d/m/Y') }}</p>
    </div>

    <div class="client-info">
        <h3>Client</h3>
        <p>{{ $quotation->client_name }}<br>
        {{ $quotation->client_phone }}<br>
        {{ $quotation->client_email }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                <td>{{ number_format($item->quantity * $item->unit_price, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <p>Sous-total: {{ number_format($quotation->subtotal, 0, ',', ' ') }} FCFA</p>
        <p>TVA ({{ $quotation->tax }}%): {{ number_format($quotation->subtotal * $quotation->tax / 100, 0, ',', ' ') }} FCFA</p>
        <p><strong>Total: {{ number_format($quotation->total, 0, ',', ' ') }} FCFA</strong></p>
    </div>

    @if($quotation->notes)
    <div class="notes">
        <h3>Notes</h3>
        <p>{{ $quotation->notes }}</p>
    </div>
    @endif
</body>
</html>