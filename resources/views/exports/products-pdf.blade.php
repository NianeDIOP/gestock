<!DOCTYPE html>
<html>
<head>
    <title>Liste des produits</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h1 { text-align: center; }
        .header, .footer { text-align: center; margin-bottom: 20px; }
        .footer { margin-top: 20px; font-size: 12px; }
    </style>
</head>
<body>
    <!-- En-tête dynamique -->
    <div class="header">
        <h1>{{ $settings->name ?? 'Nom de l\'entreprise' }}</h1>
        <p>{{ $settings->address ?? 'Adresse de l\'entreprise' }}</p>
        <p>Téléphone : {{ $settings->phone ?? 'N/A' }}</p>
        <p>NINEA : {{ $settings->ninea ?? 'N/A' }}</p>
    </div>

    <h1>Liste des produits</h1>
    <table>
        <thead>
            <tr>
                <th>Référence</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Catégorie</th>
                <th>Prix</th>
                <th>Quantité</th>
                <th>Seuil de stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->reference }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->description }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ number_format($product->price, 0, ',', ' ') }} FCFA</td>
                    <td>{{ $product->quantity }}</td>
                    <td>{{ $product->stock_threshold }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pied de page dynamique -->
    <div class="footer">
        <p>{{ $settings->name ?? 'Nom de l\'entreprise' }} - {{ $settings->address ?? 'Adresse de l\'entreprise' }}</p>
        <p>Téléphone : {{ $settings->phone ?? 'N/A' }} | NINEA : {{ $settings->ninea ?? 'N/A' }}</p>
    </div>
</body>
</html>