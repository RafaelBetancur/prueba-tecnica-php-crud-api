document.addEventListener('DOMContentLoaded', function() {
    const API_BASE_URL = 'http://localhost:8080/api';
    let currentEditId = null;

    // Elementos del DOM
    const cleanBadBtn = document.getElementById('clean-bad-btn');
    const deleteAllBtn = document.getElementById('delete-all-btn');
    const resultsList = document.getElementById('results-list');
    const resultForm = document.getElementById('result-form');
    const saveBtn = document.getElementById('save-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const badCount = document.getElementById('bad-count');
    const mediumCount = document.getElementById('medium-count');
    const goodCount = document.getElementById('good-count');
    
    cleanBadBtn.addEventListener('click', async () => {
        await cleanBadResults();
    });
    
    deleteAllBtn.addEventListener('click', async () => {
        await deleteAllResults();
    });
    
    resultForm.addEventListener('submit', handleFormSubmit);
    cancelBtn.addEventListener('click', resetForm);

    // Función para obtener los registros
    async function fetchResults() {
        console.log("Función fetchResults ejecutada");
        
        try {
            console.log("Iniciando fetch...");
            const response = await fetch(`${API_BASE_URL}/results`, {
                headers: {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                }
            });
            console.log("Respuesta recibida:", response);
            
            if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
            
            const data = await response.json();
            console.log("Datos parseados:", data);
            
            displayResults(data);
            updateStats(data);
        } catch (error) {
            console.error("Error completo:", error);
            alert(`Error completo: ${error.message}`);
        }
    }

document.getElementById('init-btn').addEventListener('click', async () => {
    try {
        const response = await fetch(`${API_BASE_URL}/initialize`, {
            method: 'POST'
        });
        const data = await response.json();
        alert(`Datos inicializados: ${data.total_records} registros creados`);
        fetchResults(); // Ahora sí debería mostrar los datos
    } catch (error) {
        console.error('Error inicializando:', error);
    }
});

    // Función para limpiar registros Bad
    async function cleanBadResults() {
        if (!confirm('¿Estás seguro de que quieres mejorar todos los registros Bad?')) return;
        
        try {
            const response = await fetch(`${API_BASE_URL}/improve`, {
                method: 'POST'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            alert(`Mejora completada. Barridos: ${data.total_sweeps}, Llamadas: ${data.total_calls}`);
            fetchResults();
        } catch (error) {
            console.error('Error cleaning bad results:', error);
            alert('Error al limpiar registros Bad. Ver consola para detalles.');
        }
    }

    // Función para eliminar todos los registros
    async function deleteAllResults() {
        if (!confirm('¿Estás seguro de que quieres eliminar TODOS los registros?')) return;
        
        try {
            const response = await fetch(`${API_BASE_URL}/results/all`, {
                method: 'DELETE'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            alert(data.message || 'Todos los registros eliminados');
            fetchResults();
        } catch (error) {
            console.error('Error deleting all results:', error);
            alert('Error al eliminar registros. Ver consola para detalles.');
        }
    }

    // Función para mostrar los resultados
    function displayResults(results) {
        resultsList.innerHTML = '';
        
        if (!results || results.length === 0) {
            resultsList.innerHTML = '<div class="result-item">No hay registros</div>';
            return;
        }

        results.forEach(result => {
            const resultItem = document.createElement('div');
            resultItem.className = `result-item ${result.category}`;
            resultItem.innerHTML = `
                <div>
                    <strong>ID:</strong> ${result.id} | 
                    <strong>Valor:</strong> ${result.value} | 
                    <strong>Categoría:</strong> ${result.category} | 
                    <strong>Intentos:</strong> ${result.attempt_number} | 
                    <strong>Mejorado:</strong> ${result.is_improved ? 'Sí' : 'No'}
                </div>
                <div class="result-actions">
                    <button class="edit-btn" data-id="${result.id}">Editar</button>
                    <button class="delete-btn" data-id="${result.id}">Eliminar</button>
                </div>
            `;
            resultsList.appendChild(resultItem);
        });

        // Agregar event listeners a los botones
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', (e) => editResult(e.target.dataset.id));
        });

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => deleteResult(e.target.dataset.id));
        });
    }

    // Función para actualizar estadísticas
    function updateStats(results) {
        const counts = {
            bad: 0,
            medium: 0,
            good: 0
        };

        results.forEach(result => {
            counts[result.category]++;
        });

        badCount.textContent = counts.bad;
        mediumCount.textContent = counts.medium;
        goodCount.textContent = counts.good;
    }

    // Función para editar un registro
    async function editResult(id) {
        try {
            const response = await fetch(`${API_BASE_URL}/results/${id}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            document.getElementById('result-id').value = result.id;
            document.getElementById('value').value = result.value;
            document.getElementById('category').value = result.category;
            
            currentEditId = id;
            saveBtn.textContent = 'Actualizar';
        } catch (error) {
            console.error('Error editing result:', error);
            alert('Error al cargar el registro. Ver consola para detalles.');
        }
    }

    // Función para eliminar un registro
    async function deleteResult(id) {
        if (!confirm('¿Estás seguro de que quieres eliminar este registro?')) return;
        
        try {
            const response = await fetch(`${API_BASE_URL}/results/${id}`, {
                method: 'DELETE'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            alert('Registro eliminado');
            fetchResults();
        } catch (error) {
            console.error('Error deleting result:', error);
            alert('Error al eliminar el registro. Ver consola para detalles.');
        }
    }

    // Función para manejar el formulario
    async function handleFormSubmit(e) {
        e.preventDefault();
        
        const formData = {
            value: parseInt(document.getElementById('value').value),
            category: document.getElementById('category').value
        };

        const id = document.getElementById('result-id').value;
        const url = id ? `${API_BASE_URL}/results/${id}` : `${API_BASE_URL}/results`;
        const method = id ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            alert(id ? 'Registro actualizado' : 'Registro creado');
            resetForm();
            fetchResults();
        } catch (error) {
            console.error('Error saving result:', error);
            alert('Error al guardar el registro. Ver consola para detalles.');
        }
    }

    // Función para resetear el formulario
    function resetForm() {
        resultForm.reset();
        document.getElementById('result-id').value = '';
        currentEditId = null;
        saveBtn.textContent = 'Guardar';
    }

    // Cargar registros al iniciar
    fetchResults();
});