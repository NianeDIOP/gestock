<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Liste des fournisseurs</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .company-details {
            font-size: 14px;
            color: #666;
        }
        .document-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            color: #333;
            text-transform: uppercase;
        }
        .generation-date {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        table { 
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td { 
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        th { 
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #666;
            padding: 10px;
        }
        .page-number:before {
            content: "Page " counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <div class="company-name">{{ $settings->name ?? 'Nom de l\'entreprise' }}</div>
            <div class="company-details">
                @if($settings->address)
                    <div>{{ $settings->address }}</div>
                @endif
                @if($settings->phone)
                    <div>Tél: {{ $settings->phone }}</div>
                @endif
                @if($settings->ninea)
                    <div>NINEA: {{ $settings->ninea }}</div>
                @endif
            </div>
        </div>
        
        <div class="document-title">Liste des Fournisseurs</div>
        <div class="generation-date">Document généré le {{ date('d/m/Y à H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Nom</th>
                <th style="width: 15%;">Contact</th>
                <th style="width: 20%;">Email</th>
                <th style="width: 15%;">Téléphone</th>
                <th style="width: 30%;">Adresse</th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->contact_person ?? '-' }}</td>
                    <td>{{ $supplier->email ?? '-' }}</td>
                    <td>{{ $supplier->phone ?? '-' }}</td>
                    <td>{{ $supplier->address ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Aucun fournisseur enregistré</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div class="page-number"></div>
        {{ $settings->name ?? 'Nom de l\'entreprise' }} - Liste des fournisseurs
    </div>
</body>
</html>