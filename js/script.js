document.addEventListener('DOMContentLoaded', function() {
    function updateAndCalculate() {
        const fields = Array.isArray(Object.values(tarifsOptions.fields)) ? Object.values(tarifsOptions.fields) : [];

        fields.forEach(field => {
            if (field.type === 'number' || field.type === 'select' || field.type === 'text') {
                updateOutput(field.id, `output${field.id}`);
            }
        });

        calculerTarif();
    }

    function updateOutput(inputId, outputId) {
        const input = document.getElementById(inputId);
        const output = document.getElementById(outputId);
        output.value = input.value;
    }

    function calculerTarif() {
        const fields = Array.isArray(Object.values(tarifsOptions.fields)) ? Object.values(tarifsOptions.fields): [];
        let total = 0;

        fields.forEach(field => {
            let quantity = 0;
            if (field.type === 'number' || field.type === 'select' || field.type === 'text') {
                quantity = parseInt(document.getElementById(field.id).value) || 0;
            }
            const price = parseFloat(field.price) || 0;
            total += quantity * price;
        });

        document.getElementById('totalPrice').innerText = total.toFixed(2);
    }

    // Initialiser les valeurs et le calcul au chargement
    updateAndCalculate();

    // Ajouter des écouteurs d'événements pour les inputs
    const inputs = document.querySelectorAll('.range-input, .number-input, .select-input');
    inputs.forEach(input => {
        input.addEventListener('input', updateAndCalculate);
        input.addEventListener('change', updateAndCalculate);
    });
});
