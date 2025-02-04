<!DOCTYPE html>
<html>
<head>
   <meta charset="utf-8">
   <style>
       body { font-family: Arial, sans-serif; }
       .header { 
           text-align: center;
           margin-bottom: 30px;
       }
       .company-info {
           margin-bottom: 20px;
           text-align: center;
           font-size: 14px;
       }
       table {
           width: 100%;
           border-collapse: collapse;
           margin: 20px 0;
       }
       th, td {
           border: 1px solid #ddd;
           padding: 8px;
           text-align: left;
       }
       th { background-color: #f2f2f2; }
       .footer {
           position: fixed;
           bottom: 0;
           width: 100%;
           text-align: center;
           font-size: 12px;
           padding: 10px 0;
       }
   </style>
</head>
<body>
   <div class="header">
       <h2>{{ $settings->name }}</h2>
   </div>

   <div class="company-info">
       <p>NINEA: {{ $settings->ninea }}</p>
       <p>Adresse: {{ $settings->address }}</p>
       <p>Téléphone: {{ $settings->phone }}</p>
   </div>

   <h3>Liste des Catégories</h3>
   <table>
       <thead>
           <tr>
               <th>Nom</th>
               <th>Description</th>
           </tr>
       </thead>
       <tbody>
           @foreach($categories as $category)
               <tr>
                   <td>{{ $category->name }}</td>
                   <td>{{ $category->description ?? 'Aucune description' }}</td>
               </tr>
           @endforeach
       </tbody>
   </table>

   <div class="footer">
       Document généré le {{ date('d/m/Y') }}
   </div>
</body>
</html>