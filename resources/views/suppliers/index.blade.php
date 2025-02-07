<!-- resources/views/suppliers/index.blade.php -->
@extends('layouts.app')

@section('title', 'Liste des fournisseurs')

@section('content')
<div class="container px-4 mx-auto">
    <!-- En-tête -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Liste des fournisseurs</h2>
        <div class="flex gap-4 mt-4 sm:mt-0">
            <a href="#" 
               onclick="exportList(); return false;" 
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-sm">
                <i class="fas fa-file-download mr-2"></i>
                Exporter la liste
            </a>
            <button onclick="openAddModal()" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg text-sm">
                <i class="fas fa-plus mr-2"></i>
                Ajouter un fournisseur
            </button>
        </div>
    </div>

    <!-- Tableau des fournisseurs -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email/Téléphone</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adresse</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $supplier->name }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $supplier->contact_person ?? '-' }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $supplier->email ?? '-' }}</div>
                                <div class="text-gray-400">{{ $supplier->phone ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $supplier->address ?? '-' }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick="openEditModal({{ $supplier->id }})" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur ?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">Aucun fournisseur trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4 border-t">
            {{ $suppliers->links() }}
        </div>
    </div>

    <!-- Modal d'ajout -->
    <div id="addModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Ajouter un fournisseur</h3>
                    <button onclick="closeAddModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form action="{{ route('suppliers.store') }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nom -->
                            <div class="col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom du fournisseur <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            <!-- Personne contact -->
                            <div>
                                <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-1">Personne contact</label>
                                <input type="text" name="contact_person" id="contact_person" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" id="email" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            <!-- Téléphone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                                <input type="tel" name="phone" id="phone" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            <!-- Adresse -->
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                                <input type="text" name="address" id="address" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            <!-- Notes -->
                            <div class="col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="notes" id="notes" rows="3" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3 rounded-b-lg">
                        <button type="button" onclick="closeAddModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Annuler
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal d'édition -->
    <div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Modifier le fournisseur</h3>
                    <button onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nom -->
                            <div class="col-span-2">
                                <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Nom du fournisseur <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="edit_name" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            <!-- Personne contact -->
                            <div>
                                <label for="edit_contact_person" class="block text-sm font-medium text-gray-700 mb-1">Personne contact</label>
                                <input type="text" name="contact_person" id="edit_contact_person" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" id="edit_email" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            <!-- Téléphone -->
                            <div>
                                <label for="edit_phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                                <input type="tel" name="phone" id="edit_phone" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            <!-- Adresse -->
                            <div>
                                <label for="edit_address" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                                <input type="text" name="address" id="edit_address" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>

                            <!-- Notes -->
                            <div class="col-span-2">
                                <label for="edit_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea name="notes" id="edit_notes" rows="3" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3 rounded-b-lg">
                        <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Annuler
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Remplacez le script existant par celui-ci -->
<script>
    // Gestion du modal d'ajout
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        resetForm('addForm');
    }

    // Gestion du modal d'édition
    function openEditModal(id) {
        // Afficher un loader ou un indicateur de chargement si nécessaire
        document.getElementById('editModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Récupération des données du fournisseur
        fetch(`/suppliers/${id}/edit`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(supplier => {
            // Remplissage du formulaire
            document.getElementById('edit_name').value = supplier.name || '';
            document.getElementById('edit_contact_person').value = supplier.contact_person || '';
            document.getElementById('edit_email').value = supplier.email || '';
            document.getElementById('edit_phone').value = supplier.phone || '';
            document.getElementById('edit_address').value = supplier.address || '';
            document.getElementById('edit_notes').value = supplier.notes || '';

            // Mise à jour de l'action du formulaire
            document.getElementById('editForm').action = `/suppliers/${id}`;
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de la récupération des données.');
            closeEditModal();
        });
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        resetForm('editForm');
    }

    // Fonction utilitaire pour réinitialiser un formulaire
    function resetForm(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
            // Réinitialiser les messages d'erreur si présents
            const errorElements = form.getElementsByClassName('error-message');
            Array.from(errorElements).forEach(element => {
                element.textContent = '';
                element.classList.add('hidden');
            });
        }
    }

    // Validation du formulaire d'ajout
    function validateForm(form) {
        let isValid = true;
        const name = form.querySelector('[name="name"]');
        const email = form.querySelector('[name="email"]');

        // Validation du nom
        if (!name.value.trim()) {
            showError(name, 'Le nom est requis');
            isValid = false;
        } else {
            hideError(name);
        }

        // Validation de l'email si présent
        if (email.value.trim() && !isValidEmail(email.value)) {
            showError(email, 'L\'email n\'est pas valide');
            isValid = false;
        } else {
            hideError(email);
        }

        return isValid;
    }

    // Fonctions utilitaires pour la validation
    function showError(element, message) {
        const errorDiv = element.nextElementSibling || document.createElement('div');
        errorDiv.className = 'error-message text-red-500 text-sm mt-1';
        errorDiv.textContent = message;
        if (!element.nextElementSibling) {
            element.parentNode.appendChild(errorDiv);
        }
    }

    function hideError(element) {
        const errorDiv = element.nextElementSibling;
        if (errorDiv && errorDiv.classList.contains('error-message')) {
            errorDiv.remove();
        }
    }

    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // Gestionnaire pour la touche Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAddModal();
            closeEditModal();
        }
    });

    // Gestionnaire de confirmation pour la suppression
    function confirmDelete(event, formElement) {
        event.preventDefault();
        if (confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur ?')) {
            formElement.submit();
        }
    }

    // Initialisation des écouteurs d'événements quand le DOM est chargé
    document.addEventListener('DOMContentLoaded', function() {
        // Ajout des écouteurs pour la validation des formulaires
        const addForm = document.getElementById('addForm');
        const editForm = document.getElementById('editForm');

        if (addForm) {
            addForm.addEventListener('submit', function(event) {
                if (!validateForm(this)) {
                    event.preventDefault();
                }
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', function(event) {
                if (!validateForm(this)) {
                    event.preventDefault();
                }
            });
        }

        // Clic en dehors des modaux pour les fermer
        document.addEventListener('click', function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            
            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        });
    });

    // Ajoutez cette fonction dans votre script
function exportList() {
    // Afficher une notification de chargement
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    notification.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération du PDF en cours...';
    document.body.appendChild(notification);

    // Rediriger vers la route d'export
    window.location.href = '{{ route('suppliers.export') }}';

    // Retirer la notification après 3 secondes
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
</div>
@endsection