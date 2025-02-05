@extends('layouts.app')
@section('title', 'Liste des catégories')

@section('content')
<div class="max-w-6xl mx-auto px-4">
   <div class="flex justify-between items-center mb-6">
       <h2 class="text-2xl font-bold text-gray-800">Liste des catégories</h2>
       <div class="flex space-x-3">
           <a href="{{ route('categories.export.pdf') }}" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 flex items-center">
               <i class="fas fa-file-pdf mr-2"></i>Exporter la liste
           </a>
           <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center">
               <i class="fas fa-plus mr-2"></i>Ajouter une catégorie
           </button>
       </div>
   </div>

   <div class="bg-white border rounded-lg">
       <div class="overflow-x-auto">
           <table class="w-full">
               <thead class="bg-gray-50">
                   <tr>
                       <th class="px-6 py-3 text-left text-gray-700 font-semibold">Nom</th>
                       <th class="px-6 py-3 text-left text-gray-700 font-semibold">Description</th>
                       <th class="px-6 py-3 text-right text-gray-700 font-semibold">Actions</th>
                   </tr>
               </thead>
               <tbody class="divide-y divide-gray-200">
                   @forelse ($categories as $category)
                       <tr class="hover:bg-gray-50">
                           <td class="px-6 py-4">{{ $category->name }}</td>
                           <td class="px-6 py-4">{{ $category->description ?? 'Aucune description' }}</td>
                           <td class="px-6 py-4 text-right space-x-2">
                               <button onclick="openEditModal({{ $category->id }}, '{{ $category->name }}', '{{ $category->description }}')" 
                                   class="text-blue-600 hover:text-blue-800">
                                   <i class="fas fa-edit"></i>
                               </button>
                               <button onclick="confirmDelete({{ $category->id }})" class="text-red-600 hover:text-red-800">
                                   <i class="fas fa-trash"></i>
                               </button>
                           </td>
                       </tr>
                   @empty
                       <tr>
                           <td colspan="3" class="px-6 py-4 text-center text-gray-500">Aucune catégorie trouvée</td>
                       </tr>
                   @endforelse
               </tbody>
           </table>
       </div>

       <div class="px-6 py-3 border-t">
           {{ $categories->links() }}
       </div>
   </div>
</div>

<!-- Modal Ajout -->
<div id="addModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
   <div class="flex min-h-screen justify-center items-center p-4">
       <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
           <div class="flex justify-between items-center p-6 border-b">
               <h3 class="text-lg font-semibold text-gray-900">Nouvelle catégorie</h3>
               <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-500">
                   <i class="fas fa-times"></i>
               </button>
           </div>

           <form action="{{ route('categories.store') }}" method="POST">
               @csrf
               <div class="p-6 space-y-4">
                   <div>
                       <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                       <input type="text" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                   </div>
                   <div>
                       <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                       <textarea name="description" rows="3" 
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"></textarea>
                   </div>
               </div>

               <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                   <button type="button" onclick="closeAddModal()"
                       class="px-4 py-2 text-gray-700 border rounded hover:bg-gray-50">
                       Annuler
                   </button>
                   <button type="submit"
                       class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                       Enregistrer
                   </button>
               </div>
           </form>
       </div>
   </div>
</div>

<!-- Modal Edition -->
<div id="editModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
   <div class="flex min-h-screen justify-center items-center p-4">
       <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
           <div class="flex justify-between items-center p-6 border-b">
               <h3 class="text-lg font-semibold text-gray-900">Modifier la catégorie</h3>
               <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-500">
                   <i class="fas fa-times"></i>
               </button>
           </div>

           <form id="editForm" method="POST">
               @csrf
               @method('PUT')
               <div class="p-6 space-y-4">
                   <div>
                       <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                       <input type="text" name="name" id="editName" required
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                   </div>
                   <div>
                       <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                       <textarea name="description" id="editDescription" rows="3"
                           class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"></textarea>
                   </div>
               </div>

               <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                   <button type="button" onclick="closeEditModal()"
                       class="px-4 py-2 text-gray-700 border rounded hover:bg-gray-50">
                       Annuler
                   </button>
                   <button type="submit"
                       class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                       Modifier
                   </button>
               </div>
           </form>
       </div>
   </div>
</div>

<script>


// Modifer les fonctions des modales
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Empêcher le scroll
}


function openEditModal(id, name, description) {
    document.getElementById('editName').value = name;
    document.getElementById('editDescription').value = description;
    document.getElementById('editForm').action = `/categories/${id}`;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.body.style.overflow = 'auto'; // Réactiver le scroll
}



// Ajouter pour la gestion des formulaires
document.addEventListener('DOMContentLoaded', function() {
    const addForm = document.querySelector('form[action*="categories.store"]');
    if(addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showNotification('Catégorie ajoutée avec succès');
                    closeAddModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Une erreur est survenue', 'error');
            });
        });
    }
});

function closeEditModal() {
   document.getElementById('editModal').classList.add('hidden');
}

function confirmDelete(id) {
    Swal.fire({
        title: 'Confirmation',
        text: 'Voulez-vous vraiment supprimer cette catégorie ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/categories/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Supprimé!', data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Erreur!', 'Cette catégorie contient des produits et ne peut pas être supprimée.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Erreur!', 'Une erreur est survenue.', 'error');
            });
        }
    });
}


// Ajouter ces fonctions pour les notifications
function showNotification(message, type = 'success') {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 3000
    });
}
</script>
@endsection