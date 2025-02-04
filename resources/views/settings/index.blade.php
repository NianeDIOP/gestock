@extends('layouts.app')
@section('title', 'Paramètres')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-5">
        <!-- En-tête -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-cog text-blue-600 mr-2"></i>Paramètres
            </h2>
            <button onclick="openAuthModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-edit mr-2"></i>Modifier
            </button>
        </div>

        <!-- Modale de vérification -->
        <div id="authModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-2xl w-full max-w-md border border-gray-300 transform transition-all duration-300">
                <div class="p-6">
                    <!-- En-tête modale -->
                    <div class="flex justify-between items-center mb-5">
                        <div class="flex items-center">
                            <i class="fas fa-lock text-blue-600 mr-3 text-xl"></i>
                            <h3 class="text-xl font-semibold text-gray-800">Authentification requise</h3>
                        </div>
                        <button onclick="closeAuthModal()" class="text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <!-- Corps modale -->
                    <div class="space-y-4">
                        <p class="text-gray-600 text-sm">Veuillez saisir le mot de passe d'administration :</p>
                        
                        <input type="password" id="authPassword" 
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-blue-600"
                            placeholder="Mot de passe">
                        
                        <p id="passwordError" class="text-red-600 text-sm font-medium hidden"></p>
                    </div>

                    <!-- Pied de modale -->
                    <div class="mt-6 flex justify-end gap-3">
                        <button onclick="closeAuthModal()" 
                                class="px-5 py-2 text-gray-600 hover:text-gray-800 font-medium">
                            Annuler
                        </button>
                        <button onclick="verifyPassword()" 
                                class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                            Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <form action="{{ route('settings') }}" method="POST" id="settingsForm">
            @csrf
            <div class="space-y-5">
                <!-- Nom de l'entreprise -->
                <div class="form-group">
                    <label class="block mb-2 text-gray-700 font-medium">
                        <i class="fas fa-building text-blue-600 mr-2"></i>Nom de l'entreprise
                    </label>
                    <input type="text" name="name" value="{{ $settings->name ?? '' }}" 
                           class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg bg-gray-50" disabled>
                </div>

                <!-- NINEA -->
                <div class="form-group">
                    <label class="block mb-2 text-gray-700 font-medium">
                        <i class="fas fa-id-card text-blue-600 mr-2"></i>NINEA
                    </label>
                    <input type="text" name="ninea" value="{{ $settings->ninea ?? '' }}"
                           class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg bg-gray-50" disabled>
                </div>

                <!-- Adresse -->
                <div class="form-group">
                    <label class="block mb-2 text-gray-700 font-medium">
                        <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>Adresse
                    </label>
                    <input type="text" name="address" value="{{ $settings->address ?? '' }}"
                           class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg bg-gray-50" disabled>
                </div>

                <!-- Téléphone -->
                <div class="form-group">
                    <label class="block mb-2 text-gray-700 font-medium">
                        <i class="fas fa-phone text-blue-600 mr-2"></i>Téléphone
                    </label>
                    <input type="text" name="phone" value="{{ $settings->phone ?? '' }}"
                           class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg bg-gray-50" disabled>
                </div>
            </div>

            <!-- Bouton de soumission -->
            <div class="mt-8 text-center">
                <button type="submit" id="submitBtn" 
                        class="px-8 py-3 bg-gray-400 text-white rounded-lg font-bold cursor-not-allowed transition-all"
                        disabled>
                    <i class="fas fa-save mr-2"></i>Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAuthModal() {
        document.getElementById('authModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        document.getElementById('authPassword').focus();
    }

    function closeAuthModal() {
        document.getElementById('authModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        document.getElementById('passwordError').classList.add('hidden');
        document.getElementById('authPassword').value = '';
    }

    function verifyPassword() {
        const password = document.getElementById('authPassword').value;
        
        fetch("{{ route('settings.verify') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ password: password })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                enableForm();
                closeAuthModal();
            } else {
                document.getElementById('passwordError').textContent = data.message;
                document.getElementById('passwordError').classList.remove('hidden');
            }
        });
    }

    function enableForm() {
        // Activer les champs
        document.querySelectorAll('#settingsForm input[type="text"]').forEach(input => {
            input.disabled = false;
            input.classList.replace('bg-gray-50', 'bg-white');
            input.classList.add('hover:border-gray-300');
        });

        // Activer le bouton de soumission
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = false;
        submitBtn.classList.remove('cursor-not-allowed');
        submitBtn.classList.replace('bg-gray-400', 'bg-green-600');
        submitBtn.classList.add('hover:bg-green-700', 'shadow-md');

        // Ajouter un gestionnaire d'événements au formulaire
        const form = document.getElementById('settingsForm');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm();
        });
    }

    function submitForm() {
    const form = document.getElementById('settingsForm');
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Créer une notification de succès
            showNotification(data.message, 'success');
            
            // Désactiver le formulaire à nouveau
            disableForm();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Une erreur est survenue. Veuillez réessayer.', 'error');
    });

    function disableForm() {
    document.querySelectorAll('#settingsForm input[type="text"]').forEach(input => {
        input.disabled = true;
        input.classList.replace('bg-white', 'bg-gray-50');
        input.classList.remove('hover:border-gray-300');
    });

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.classList.add('cursor-not-allowed');
    submitBtn.classList.replace('bg-green-600', 'bg-gray-400');
    submitBtn.classList.remove('hover:bg-green-700', 'shadow-md');
}

// Fonction pour afficher les notifications
function showNotification(message, type) {
    // Créer l'élément de notification
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg transition-all transform translate-x-0 z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white`;
    
    // Ajouter le message
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-times-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Ajouter au DOM
    document.body.appendChild(notification);
    
    // Animation d'entrée
    requestAnimationFrame(() => {
        notification.style.transform = 'translateX(0)';
    });
    
    // Supprimer après 3 secondes
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}


    }

</script>

<style>
    .form-group {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: #f8fafc;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
    }

    #authModal {
        animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    input:disabled {
        cursor: not-allowed;
        opacity: 0.8;
    }

    .backdrop-blur-md {
        backdrop-filter: blur(8px);
    }

    .notification-enter {
        transform: translateX(100%);
        opacity: 0;
    }
    
    .notification-enter-active {
        transform: translateX(0);
        opacity: 1;
        transition: all 0.3s ease-out;
    }
    
    .notification-exit {
        transform: translateX(0);
        opacity: 1;
    }
    
    .notification-exit-active {
        transform: translateX(100%);
        opacity: 0;
        transition: all 0.3s ease-in;
    }
</style>
@endsection