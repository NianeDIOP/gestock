@extends('layouts.app')
@section('title', 'Liste des catégories')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête avec titre et boutons -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 bg-white p-4 rounded-lg shadow gap-4">
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Liste des catégories</h2>
        <div class="flex flex-col sm:flex-row w-full md:w-auto gap-3">
            <a href="{{ route('categories.export.pdf') }}" 
                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors flex items-center justify-center">
                <i class="fas fa-file-pdf mr-2"></i>
                <span>Exporter PDF</span>
            </a>
            <button onclick="openAddModal()" 
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i>
                <span>Nouvelle catégorie</span>
            </button>
        </div>
    </div>

    <!-- Table principale -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-xs md:text-sm font-semibold text-gray-600">
                            Nom
                        </th>
                        <th class="px-4 py-3 md:px-6 md:py-4 text-left text-xs md:text-sm font-semibold text-gray-600">
                            Description
                        </th>
                        <th class="px-4 py-3 md:px-6 md:py-4 text-right text-xs md:text-sm font-semibold text-gray-600">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 md:px-6 md:py-4 text-sm">
                            <span class="font-medium text-gray-900">{{ $category->name }}</span>
                        </td>
                        <td class="px-4 py-3 md:px-6 md:py-4 text-sm text-gray-500">
                            {{ $category->description ?? 'Aucune description' }}
                        </td>
                        <td class="px-4 py-3 md:px-6 md:py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <button onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->description) }}')" 
                                    class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="confirmDelete({{ $category->id }})" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-folder-open text-gray-400 text-4xl mb-3"></i>
                                <p class="text-lg">Aucune catégorie trouvée</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($categories->hasPages())
        <div class="border-t px-4 py-3 md:px-6 md:py-4">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Le reste du code (modales) reste identique -->

<!-- Modal Ajout -->
<!-- Modal Ajout -->
<div id="addModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md mx-auto">
            <div class="flex items-center justify-between p-4 md:p-6 border-b">
                <h3 class="text-lg md:text-xl font-semibold text-gray-900">
                    <i class="fas fa-plus-circle mr-2 text-blue-600"></i>
                    Nouvelle catégorie
                </h3>
                <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form action="{{ route('categories.store') }}" method="POST" class="p-4 md:p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nom <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required 
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="3" 
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-3">
                    <button type="button" onclick="closeAddModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edition -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
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
    // Échapper les apostrophes dans les valeurs
    const safeName = name.replace(/'/g, "\\'");
    const safeDesc = description ? description.replace(/'/g, "\\'") : '';
    
    document.getElementById('editName').value = safeName;
    document.getElementById('editDescription').value = safeDesc;
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

document.addEventListener('DOMContentLoaded', function() {
    const mediaQuery = window.matchMedia('(max-width: 640px)');
    
    function handleMobileChanges(e) {
        const modals = document.querySelectorAll('#addModal, #editModal');
        modals.forEach(modal => {
            if (e.matches) {
                modal.classList.add('mobile-modal');
            } else {
                modal.classList.remove('mobile-modal');
            }
        });
    }

    mediaQuery.addListener(handleMobileChanges);
    handleMobileChanges(mediaQuery);
});

// Ajuster les fonctions existantes
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}
</script>
@endsection