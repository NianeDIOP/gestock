<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'categories'; // Optionnel, car Laravel utilise "categories" par défaut

    /**
     * Les colonnes qui peuvent être remplies via un formulaire ou une requête.
     *
     * @var array
     */
    protected $fillable = [
        'name', // Colonne pour le nom de la catégorie
        'description', // Colonne pour la description (optionnelle)
    ];

    /**
     * Les colonnes qui doivent être cachées lors de la sérialisation.
     *
     * @var array
     */
    protected $hidden = [
        // Par exemple, si tu as des colonnes sensibles, tu peux les cacher ici
    ];

    /**
     * Les colonnes qui doivent être converties en types spécifiques.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime', // Convertit `created_at` en objet Carbon
        'updated_at' => 'datetime', // Convertit `updated_at` en objet Carbon
    ];
    
    // App\Models\Category.php
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
}