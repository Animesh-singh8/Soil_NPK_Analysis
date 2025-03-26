// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Dark mode toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;

    // Check for saved dark mode preference
    if (localStorage.getItem('darkMode') === 'enabled') {
        body.classList.add('dark-mode');
        darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }

    // Dark mode toggle functionality
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            if (body.classList.contains('dark-mode')) {
                body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', 'disabled');
                darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            } else {
                body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'enabled');
                darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }
        });
    }

    // Live NPK conversion (mg/kg to kg/ha)

    // Live NPK conversion (mg/kg to kg/ha)
document.querySelectorAll('.npk-input').forEach(input => {
    input.addEventListener('input', function() {
        let value = parseFloat(this.value);
        if (isNaN(value)) value = 0; // Handle empty or invalid input
        const conversionFactor = 2.24; // 1 mg/kg ≈ 2.24 kg/ha
        const convertedValue = (value * conversionFactor).toFixed(2);

        const displayId = this.getAttribute('data-display');
        const displayElement = document.getElementById(displayId);

        if (displayElement) {
            displayElement.textContent = convertedValue + ' kg/ha';
        }
    });
});

    /*const npkInputs = document.querySelectorAll('.npk-input');

    npkInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value) || 0;
            const conversionFactor = 2.24; // 1 mg/kg ≈ 2.24 kg/ha
            const convertedValue = (value * conversionFactor).toFixed(2);

            const displayId = this.getAttribute('data-display');
            const displayElement = document.getElementById(displayId);

            if (displayElement) {
                displayElement.textContent = convertedValue + ' kg/ha';
                displayElement.classList.add('fade-in');

                // Remove animation class after animation completes
                setTimeout(() => {
                    displayElement.classList.remove('fade-in');
                }, 500);
            }
        });
    }); */

    // State and district dropdown relationship
     
// Debugging district dropdown issue
const stateSelect = document.getElementById('state');
const districtSelect = document.getElementById('district');

if (stateSelect && districtSelect) {
    stateSelect.addEventListener('change', function() {
        const stateId = this.value;

        // Clear previous districts
        districtSelect.innerHTML = '<option value="">Select District</option>';

        if (stateId) {
            // Fetch districts using AJAX
            fetch(`get_districts.php?state_id=${stateId}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Fetched districts:", data); // Debugging log

                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.name;
                            districtSelect.appendChild(option);
                        });
                    } else {
                        console.error("No districts found or invalid response.");
                    }
                })
                .catch(error => console.error('Error fetching districts:', error));
        }
    });
}


    
    /*const stateSelect = document.getElementById('state');
    const districtSelect = document.getElementById('district');

    if (stateSelect && districtSelect) {
        stateSelect.addEventListener('change', function() {
            const stateId = this.value;

            // Clear current districts
            districtSelect.innerHTML = '<option value="">Select District</option>';

            if (stateId) {
                // Fetch districts for selected state using AJAX
                fetch(`get_districts.php?state_id=${stateId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.name;
                            districtSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching districts:', error));
            }
        });
    }*/

    // PDF Generation
    const generatePdfBtn = document.getElementById('generatePdf');

    if (generatePdfBtn) {
        generatePdfBtn.addEventListener('click', function(){ 
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Get the result data
            const cropName = document.getElementById('cropName').textContent;
            const stateName = document.getElementById('stateName').textContent;
            const districtName = document.getElementById('districtName').textContent;
            const plotArea = document.getElementById('plotArea').textContent;
            
            // Add title and header info
            doc.setFontSize(20);
            doc.text('Soil NPK Analysis Report', 105, 15, { align: 'center' });

            doc.setFontSize(12);
            doc.text(`Crop: ${cropName}`, 20, 30);
            doc.text(`Location: ${districtName}, ${stateName}`, 20, 40);
            doc.text(`Plot Area: ${plotArea}`, 20, 50);
            doc.text(`Date: ${new Date().toLocaleDateString()}`, 20, 60);

            // Add NPK comparison table
            doc.setFontSize(14);
            doc.text('NPK Comparison', 20, 75);

            const tableData = [];
            const tableColumns = [
                { header: 'Parameter', dataKey: 'parameter' },
                { header: 'Measured (kg/ha)', dataKey: 'measured' },
                { header: 'Required (kg/ha)', dataKey: 'required' },
                { header: 'Status', dataKey: 'status' }
            ];

            // Get table rows
            const tableRows = document.querySelectorAll('.table-comparison tbody tr');
            tableRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                tableData.push({
                    parameter: cells[0].textContent,
                    measured: cells[1].textContent,
                    required: cells[2].textContent,
                    status: cells[3].textContent
                });
            });

            // Add table using autoTable
            doc.autoTable({
                head: [tableColumns.map(col => col.header)],
                body: tableData.map(row => [
                    row.parameter,
                    row.measured,
                    row.required,
                    row.status
                ]),
                startY: 80
            });

            // Add fertilizer recommendations
            let recommendationsY = doc.lastAutoTable.finalY + 15;
            doc.setFontSize(14);
            doc.text('Fertilizer Recommendations', 20, recommendationsY);

            // Get recommendations
            const recommendations = document.querySelectorAll('.fertilizer-card');
            let yPos = recommendationsY + 10;

            recommendations.forEach(rec => {
                const title = rec.querySelector('h5').textContent;
                const amount = rec.querySelector('p').textContent;
                
                doc.setFontSize(12);
                doc.text(`${title}: ${amount}`, 20, yPos);
                yPos += 10;
            });

            // Add pH recommendation
            const phRec = document.getElementById('phRecommendation');
            if (phRec) {
                yPos += 5;
                doc.text('pH Recommendation:', 20, yPos);
                yPos += 10;
                doc.text(phRec.textContent, 25, yPos);
            }

            // Add footer
            doc.setFontSize(10);
            doc.text('Generated by SoilAnalyzer - www.soilanalyzer.com', 105, 285, { align: 'center' });

            // Save the PDF
            doc.save(`NPK_Analysis_${cropName}_${new Date().toISOString().slice(0,10)}.pdf`);
        });
    }

    // Form validation
    const analyzeForm = document.getElementById('analyzeForm');

    if (analyzeForm) {
        analyzeForm.addEventListener('submit', function(event) {
            let isValid = true;

            // Validate NPK inputs
            const npkInputs = document.querySelectorAll('.npk-input');
            npkInputs.forEach(input => {
                if (!input.value || isNaN(parseFloat(input.value))) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            // Validate dropdowns
            const stateSelect = document.getElementById('state');
            const districtSelect = document.getElementById('district');
            const cropSelect = document.getElementById('crop');

            if (!stateSelect.value) stateSelect.classList.add('is-invalid');
            if (!districtSelect.value) districtSelect.classList.add('is-invalid');
            if (!cropSelect.value) cropSelect.classList.add('is-invalid');

            // Prevent form submission if validation fails
            if (!isValid) event.preventDefault();
        });
    }
});
