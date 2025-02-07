@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Gestion des utilisateurs</h2>
        @if(auth()->user()->isSuperAdmin())
            <button onclick="openAddUserModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-user-plus mr-2"></i>Nouvel utilisateur
            </button>
        @endif
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap">{{ $user->name }}</td>
                        <td class="px-4 py-4 whitespace-nowrap">{{ $user->email }}</td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $user->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="toggleUserStatus({{ $user->id }})" 
                                    class="text-indigo-600 hover:text-indigo-900 mr-2">
                                    <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>
                                <button onclick="confirmDelete({{ $user->id }})" 
                                    class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if(auth()->user()->isSuperAdmin())
<!-- Modal d'ajout d'utilisateur -->
<!-- Modal d'ajout d'utilisateur -->
<div id="addUserModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <!-- En-tête de la modale -->
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">Nouvel utilisateur</h3>
                    <button onclick="closeAddUserModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Formulaire -->
                <form id="addUserForm" action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="space-y-6">
                        <!-- Champ Nom -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom</label>
                            <input type="text" name="name" required 
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        </div>

                        <!-- Champ Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" required 
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        </div>

                        <!-- Champ Mot de passe -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
                            <input type="password" name="password" required 
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                        </div>

                        <!-- Champ Rôle -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rôle</label>
                            <select name="role" 
                                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600">
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                    </div>

                    <!-- Boutons de la modale -->
                    <div class="mt-8 flex justify-end space-x-4">
                        <button type="button" onclick="closeAddUserModal()" 
                                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// JavaScript pour gérer les modales et les actions
function openAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
}

function toggleUserStatus(userId) {
    fetch(`/users/${userId}/toggle-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

function confirmDelete(userId) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Vous ne pourrez pas annuler cette action !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer !',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire('Supprimé !', 'L\'utilisateur a été supprimé.', 'success');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                Swal.fire('Erreur', 'Une erreur s\'est produite lors de la suppression.', 'error');
            });
        }
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddUserModal();
    }
});
</script>
@endpush